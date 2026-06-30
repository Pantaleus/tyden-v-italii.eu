<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\Request;

class ApiController extends Controller
{
    private ?array $currentAdmin = null;

    /**
     * Auth Guard for API requests.
     * Checks Bearer Token in request headers.
     */
    private function checkApiAuth(Request $request): bool
    {
        // Fetch headers
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            
            // Check in DB
            $tokenData = DB::fetch(
                "SELECT t.*, a.email 
                 FROM `api_tokens` t 
                 JOIN `admins` a ON t.admin_id = a.id 
                 WHERE t.token = ? AND t.expires_at > NOW() 
                 LIMIT 1",
                [$token]
            );

            if ($tokenData) {
                $this->currentAdmin = [
                    'id'    => (int)$tokenData['admin_id'],
                    'email' => $tokenData['email']
                ];
                return true;
            }
        }

        $this->json(['error' => 'Unauthorized'], 401);
        return false;
    }

    /**
     * POST /api/login
     * Login via credentials, returning a session bearer token.
     */
    public function login(Request $request): void
    {
        $params = $request->getParams();
        $email = trim($params['email'] ?? '');
        $password = $params['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->json(['error' => 'Email and password are required'], 400);
            return;
        }

        $admin = DB::fetch("SELECT * FROM `admins` WHERE `email` = ?", [$email]);
        if ($admin && password_verify($password, $admin['password'])) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

            DB::query(
                "INSERT INTO `api_tokens` (`admin_id`, `token`, `expires_at`) VALUES (?, ?, ?)",
                [$admin['id'], $token, $expiry]
            );

            $this->json([
                'status' => 'success',
                'token'  => $token,
                'email'  => $admin['email']
            ]);
        } else {
            $this->json(['error' => 'Invalid email or password'], 401);
        }
    }

    /**
     * POST /api/qr-login
     * Login via a pre-generated long-lived QR token.
     */
    public function qrLogin(Request $request): void
    {
        $params = $request->getParams();
        $qrToken = trim($params['qr_token'] ?? '');

        if ($qrToken === '') {
            $this->json(['error' => 'QR token is required'], 400);
            return;
        }

        $admin = DB::fetch("SELECT * FROM `admins` WHERE `qr_login_token` = ?", [$qrToken]);
        if ($admin) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

            DB::query(
                "INSERT INTO `api_tokens` (`admin_id`, `token`, `expires_at`) VALUES (?, ?, ?)",
                [$admin['id'], $token, $expiry]
            );

            $this->json([
                'status' => 'success',
                'token'  => $token,
                'email'  => $admin['email']
            ]);
        } else {
            $this->json(['error' => 'Invalid or expired QR token'], 401);
        }
    }

    /**
     * GET /api/stats
     * Return dashboard metrics.
     */
    public function stats(Request $request): void
    {
        $this->checkApiAuth($request);

        $totalViews = (int)DB::fetch("SELECT COUNT(*) FROM `tracker_logs`")['COUNT(*)'];
        $uniqueVisitors = (int)DB::fetch("SELECT COUNT(DISTINCT `ip_address`) FROM `tracker_logs`")['COUNT(DISTINCT `ip_address`)'];
        $totalCountries = (int)DB::fetch("SELECT COUNT(DISTINCT `country_code`) FROM `tracker_logs` WHERE `country_code` != 'XX'")['COUNT(DISTINCT `country_code`)'];
        $pendingComments = (int)DB::fetch("SELECT COUNT(*) FROM `comments` WHERE `is_approved` = 0")['COUNT(*)'];

        $topPages = DB::fetchAll("SELECT `url_path`, COUNT(*) as cnt FROM `tracker_logs` GROUP BY `url_path` ORDER BY cnt DESC LIMIT 5");
        $devices = DB::fetchAll("SELECT `device`, COUNT(*) as cnt FROM `tracker_logs` GROUP BY `device`");
        $topCountries = DB::fetchAll("SELECT `country_name`, `country_code`, COUNT(*) as cnt FROM `tracker_logs` WHERE `country_code` != 'XX' GROUP BY `country_code`, `country_name` ORDER BY cnt DESC LIMIT 5");

        $this->json([
            'metrics' => [
                'total_views'      => $totalViews,
                'unique_visitors'  => $uniqueVisitors,
                'total_countries'  => $totalCountries,
                'pending_comments' => $pendingComments,
            ],
            'top_pages'     => $topPages,
            'devices'       => $devices,
            'top_countries' => $topCountries
        ]);
    }

    /**
     * GET /api/trips
     * List all trips with translations.
     */
    public function getTrips(Request $request): void
    {
        $this->checkApiAuth($request);

        $trips = DB::fetchAll("SELECT * FROM `trips` ORDER BY `start_date` DESC");
        
        $result = [];
        foreach ($trips as $trip) {
            $trans = DB::fetchAll("SELECT * FROM `trip_translations` WHERE `trip_id` = ?", [$trip['id']]);
            $steps = DB::fetchAll("SELECT * FROM `timeline_steps` WHERE `trip_id` = ? ORDER BY `step_order` ASC", [$trip['id']]);
            
            $stepsWithTrans = [];
            foreach ($steps as $step) {
                $stepTrans = DB::fetchAll("SELECT * FROM `timeline_step_translations` WHERE `step_id` = ?", [$step['id']]);
                $step['translations'] = [];
                foreach ($stepTrans as $st) {
                    $step['translations'][$st['lang']] = [
                        'title' => $st['title'],
                        'text'  => $st['text']
                    ];
                }
                $stepsWithTrans[] = $step;
            }

            $trip['translations'] = [];
            foreach ($trans as $t) {
                $trip['translations'][$t['lang']] = [
                    'title'       => $t['title'],
                    'description' => $t['description']
                ];
            }
            $trip['steps'] = $stepsWithTrans;
            $result[] = $trip;
        }

        $this->json($result);
    }

    /**
     * POST /api/trips
     * Create trip and translations.
     */
    public function createTrip(Request $request): void
    {
        $this->checkApiAuth($request);
        $params = $request->getParams();

        $startDate = $params['start_date'] ?? '';
        $endDate = $params['end_date'] ?? '';
        $coverImage = $params['cover_image'] ?? '';
        $isActive = isset($params['is_active']) ? 1 : 0;

        if ($startDate === '' || $endDate === '') {
            $this->json(['error' => 'Start date and end date are required'], 400);
            return;
        }

        DB::query(
            "INSERT INTO `trips` (`start_date`, `end_date`, `cover_image`, `is_active`) VALUES (?, ?, ?, ?)",
            [$startDate, $endDate, $coverImage, $isActive]
        );
        $tripId = (int)DB::lastInsertId();

        // Save translations
        $langs = ['cs', 'en', 'it'];
        foreach ($langs as $lang) {
            $title = trim($params["title_$lang"] ?? '');
            $desc = trim($params["description_$lang"] ?? '');
            DB::query(
                "INSERT INTO `trip_translations` (`trip_id`, `lang`, `title`, `description`) VALUES (?, ?, ?, ?)",
                [$tripId, $lang, $title, $desc]
            );
        }

        $this->json(['status' => 'success', 'trip_id' => $tripId]);
    }

    /**
     * PUT /api/trips/{id}
     * Update trip.
     */
    public function updateTrip(Request $request): void
    {
        $this->checkApiAuth($request);
        $id = (int)$request->getRouteParam('id');
        $params = $request->getParams();

        $startDate = $params['start_date'] ?? '';
        $endDate = $params['end_date'] ?? '';
        $coverImage = $params['cover_image'] ?? '';
        $isActive = isset($params['is_active']) ? 1 : 0;

        DB::query(
            "UPDATE `trips` SET `start_date` = ?, `end_date` = ?, `cover_image` = ?, `is_active` = ? WHERE `id` = ?",
            [$startDate, $endDate, $coverImage, $isActive, $id]
        );

        $langs = ['cs', 'en', 'it'];
        foreach ($langs as $lang) {
            $title = trim($params["title_$lang"] ?? '');
            $desc = trim($params["description_$lang"] ?? '');

            DB::query(
                "INSERT INTO `trip_translations` (`trip_id`, `lang`, `title`, `description`) 
                 VALUES (?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE `title` = ?, `description` = ?",
                [$id, $lang, $title, $desc, $title, $desc]
            );
        }

        $this->json(['status' => 'success']);
    }

    /**
     * DELETE /api/trips/{id}
     */
    public function deleteTrip(Request $request): void
    {
        $this->checkApiAuth($request);
        $id = (int)$request->getRouteParam('id');
        DB::query("DELETE FROM `trips` WHERE `id` = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    /**
     * GET /api/posts
     * List all posts.
     */
    public function getPosts(Request $request): void
    {
        $this->checkApiAuth($request);

        $posts = DB::fetchAll("SELECT * FROM `posts` ORDER BY `created_at` DESC");
        
        $result = [];
        foreach ($posts as $post) {
            $trans = DB::fetchAll("SELECT * FROM `post_translations` WHERE `post_id` = ?", [$post['id']]);
            $post['translations'] = [];
            foreach ($trans as $t) {
                $post['translations'][$t['lang']] = [
                    'title'            => $t['title'],
                    'slug'             => $t['slug'],
                    'content'          => $t['content'],
                    'meta_title'       => $t['meta_title'],
                    'meta_description' => $t['meta_description']
                ];
            }
            $result[] = $post;
        }

        $this->json($result);
    }

    /**
     * POST /api/posts
     */
    public function createPost(Request $request): void
    {
        $this->checkApiAuth($request);
        $params = $request->getParams();

        $coverImage = $params['cover_image'] ?? '';
        $isActive = isset($params['is_active']) ? 1 : 0;
        $tripId = !empty($params['trip_id']) ? (int)$params['trip_id'] : null;

        if (empty($params['title_cs'])) {
            $this->json(['error' => 'Czech title is required'], 400);
            return;
        }

        DB::query("INSERT INTO `posts` (`trip_id`, `cover_image`, `is_active`) VALUES (?, ?, ?)", [$tripId, $coverImage, $isActive]);
        $postId = (int)DB::lastInsertId();

        $langs = ['cs', 'en', 'it'];
        foreach ($langs as $lang) {
            $title = trim($params["title_$lang"] ?? '');
            
            // Helper slugify inline
            $slugSource = $title ?: trim($params["title_cs"] ?? 'article-' . $postId);
            $slug = $this->slugify($slugSource);

            $content = $params["content_$lang"] ?? '';
            $metaTitle = trim($params["meta_title_$lang"] ?? '');
            $metaDesc = trim($params["meta_desc_$lang"] ?? '');

            DB::query(
                "INSERT INTO `post_translations` (`post_id`, `lang`, `title`, `slug`, `content`, `meta_title`, `meta_description`) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$postId, $lang, $title, $slug, $content, $metaTitle, $metaDesc]
            );
        }

        $this->json(['status' => 'success', 'post_id' => $postId]);
    }

    /**
     * DELETE /api/posts/{id}
     */
    public function deletePost(Request $request): void
    {
        $this->checkApiAuth($request);
        $id = (int)$request->getRouteParam('id');
        DB::query("DELETE FROM `posts` WHERE `id` = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    /**
     * GET /api/comments
     */
    public function getComments(Request $request): void
    {
        $this->checkApiAuth($request);
        
        $comments = DB::fetchAll(
            "SELECT c.*, pt.title as post_title 
             FROM `comments` c 
             JOIN `post_translations` pt ON c.post_id = pt.post_id AND pt.lang = 'cs' 
             ORDER BY c.created_at DESC"
        );
        $this->json($comments);
    }

    /**
     * POST /api/comments/{id}/approve
     */
    public function approveComment(Request $request): void
    {
        $this->checkApiAuth($request);
        $id = (int)$request->getRouteParam('id');
        DB::query("UPDATE `comments` SET `is_approved` = 1 WHERE `id` = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    /**
     * POST /api/comments/{id}/spam
     */
    public function spamComment(Request $request): void
    {
        $this->checkApiAuth($request);
        $id = (int)$request->getRouteParam('id');
        DB::query("UPDATE `comments` SET `is_approved` = -1 WHERE `id` = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    /**
     * DELETE /api/comments/{id}
     */
    public function deleteComment(Request $request): void
    {
        $this->checkApiAuth($request);
        $id = (int)$request->getRouteParam('id');
        DB::query("DELETE FROM `comments` WHERE `id` = ?", [$id]);
        $this->json(['status' => 'success']);
    }

    /**
     * POST /api/register-push
     * Save token from Expo App.
     */
    public function registerPushToken(Request $request): void
    {
        $this->checkApiAuth($request);
        $params = $request->getParams();
        $token = trim($params['push_token'] ?? '');

        if ($token === '') {
            $this->json(['error' => 'Push token is required'], 400);
            return;
        }

        DB::query(
            "INSERT INTO `push_tokens` (`admin_id`, `expo_push_token`) VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE `created_at` = CURRENT_TIMESTAMP",
            [$this->currentAdmin['id'], $token]
        );

        $this->json(['status' => 'success']);
    }

    /**
     * POST /api/upload
     * File uploader receiver.
     */
    public function upload(Request $request): void
    {
        $this->checkApiAuth($request);
        
        if (empty($_FILES['file'])) {
            $this->json(['error' => 'No file uploaded'], 400);
            return;
        }

        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'webp'];
        if (!in_array($ext, $allowedExtensions, true)) {
            $this->json(['error' => 'Invalid file format. Only JPG, PNG, GIF, WEBP, MP4, MOV allowed.'], 400);
            return;
        }

        $newName = bin2hex(random_bytes(16)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/';
        
        // Ensure path exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            $fileUrl = BASE_URL . '/public/uploads/' . $newName;
            $this->json([
                'status' => 'success',
                'url'    => $fileUrl
            ]);
        } else {
            $this->json(['error' => 'Failed to save file'], 500);
        }
    }

    /**
     * GET /api/settings
     */
    public function getSettings(Request $request): void
    {
        $this->checkApiAuth($request);
        $this->json(\App\Core\Settings::getAll());
    }

    /**
     * POST /api/settings
     */
    public function updateSettings(Request $request): void
    {
        $this->checkApiAuth($request);
        $params = $request->getParams();

        foreach ($params as $key => $value) {
            if (in_array($key, ['active_theme', 'tinymce_api_key', 'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_from_email', 'smtp_from_name'], true)) {
                \App\Core\Settings::set($key, (string)$value);
            }
        }

        $this->json(['status' => 'success']);
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }
}
