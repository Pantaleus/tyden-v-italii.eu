<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\Request;
use App\Core\Settings;

class AdminController extends Controller
{
    /**
     * Auth Guard - Redirects to login if session is not active
     */
    private function checkAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/login');
        }
    }

    /**
     * Admin login page and auth logic
     */
    public function login(Request $request): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['admin_id'])) {
            $this->redirect('/admin/dashboard');
        }

        $error = null;

        if ($request->isPost()) {
            $params = $request->getParams();
            $email = trim($params['email'] ?? '');
            $password = $params['password'] ?? '';

            if ($email === '' || $password === '') {
                $error = 'Vyplňte prosím všechna pole.';
            } else {
                $admin = DB::fetch("SELECT * FROM `admins` WHERE `email` = ?", [$email]);
                if ($admin && password_verify($password, $admin['password'])) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $this->redirect('/admin/dashboard');
                } else {
                    $error = 'Neplatný e-mail nebo heslo.';
                }
            }
        }

        // Render login page without standard admin sidebar layout
        extract(['error' => $error]);
        ob_start();
        require __DIR__ . '/../Views/admin/login.php';
        $content = ob_get_clean();
        
        // Output raw login page content
        echo $content;
    }

    /**
     * Logout logic
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_email']);
        session_destroy();
        $this->redirect('/admin/login');
    }

    /**
     * Stats Dashboard
     */
    public function dashboard(Request $request): void
    {
        $this->checkAuth();

        // 1. Fetch KPI metrics
        $totalViews = (int)DB::fetch("SELECT COUNT(*) FROM `tracker_logs`")['COUNT(*)'];
        $uniqueVisitors = (int)DB::fetch("SELECT COUNT(DISTINCT `ip_address`) FROM `tracker_logs`")['COUNT(DISTINCT `ip_address`)'];
        $totalCountries = (int)DB::fetch("SELECT COUNT(DISTINCT `country_code`) FROM `tracker_logs` WHERE `country_code` != 'XX'")['COUNT(DISTINCT `country_code`)'];
        $pendingComments = (int)DB::fetch("SELECT COUNT(*) FROM `comments` WHERE `is_approved` = 0")['COUNT(*)'];

        // 2. Fetch Breakdowns
        $topPages = DB::fetchAll(
            "SELECT `url_path`, COUNT(*) as cnt 
             FROM `tracker_logs` 
             GROUP BY `url_path` 
             ORDER BY cnt DESC LIMIT 5"
        );

        $devices = DB::fetchAll(
            "SELECT `device`, COUNT(*) as cnt 
             FROM `tracker_logs` 
             GROUP BY `device`"
        );

        $topCountries = DB::fetchAll(
            "SELECT `country_name`, `country_code`, COUNT(*) as cnt 
             FROM `tracker_logs` 
             WHERE `country_code` != 'XX' AND `country_code` IS NOT NULL
             GROUP BY `country_code`, `country_name` 
             ORDER BY cnt DESC LIMIT 5"
        );

        $topBrowsers = DB::fetchAll(
            "SELECT `browser`, COUNT(*) as cnt 
             FROM `tracker_logs` 
             GROUP BY `browser` 
             ORDER BY cnt DESC LIMIT 5"
        );

        $screenSizes = DB::fetchAll(
            "SELECT CONCAT(`screen_width`, 'x', `screen_height`) as res, COUNT(*) as cnt 
             FROM `tracker_logs` 
             WHERE `screen_width` > 0 
             GROUP BY res 
             ORDER BY cnt DESC LIMIT 5"
        );

        $this->render('admin/dashboard', [
            'title'          => 'Přehled statistik (Tracker)',
            'totalViews'     => $totalViews,
            'uniqueVisitors' => $uniqueVisitors,
            'totalCountries' => $totalCountries,
            'pendingComments'=> $pendingComments,
            'topPages'       => $topPages,
            'devices'        => $devices,
            'topCountries'   => $topCountries,
            'topBrowsers'    => $topBrowsers,
            'screenSizes'    => $screenSizes,
            'viewPath'       => 'admin/dashboard'
        ], 'admin');
    }

    public function settings(Request $request): void
    {
        $this->checkAuth();

        $adminId = (int)$_SESSION['admin_id'];
        $alert = null;

        // Fetch current admin
        $admin = DB::fetch("SELECT * FROM `admins` WHERE `id` = ?", [$adminId]);

        // Auto-generate QR token if missing
        if ($admin && empty($admin['qr_login_token'])) {
            $qrToken = bin2hex(random_bytes(16));
            DB::query("UPDATE `admins` SET `qr_login_token` = ? WHERE `id` = ?", [$qrToken, $adminId]);
            $admin['qr_login_token'] = $qrToken;
        }

        if ($request->isPost()) {
            $params = $request->getParams();
            
            if (($params['action'] ?? '') === 'regenerate_qr') {
                $qrToken = bin2hex(random_bytes(16));
                DB::query("UPDATE `admins` SET `qr_login_token` = ? WHERE `id` = ?", [$qrToken, $adminId]);
                $admin['qr_login_token'] = $qrToken;
                $alert = ['type' => 'success', 'message' => 'Přihlašovací QR token byl úspěšně vygenerován znovu.'];
            } else {
                // Save settings keys
                Settings::set('active_theme', $params['active_theme'] ?? 'warm_mediterranean');
                Settings::set('tinymce_api_key', $params['tinymce_api_key'] ?? 'no-api-key');
                Settings::set('smtp_host', $params['smtp_host'] ?? 'localhost');
                Settings::set('smtp_port', $params['smtp_port'] ?? '587');
                Settings::set('smtp_user', $params['smtp_user'] ?? '');
                Settings::set('smtp_pass', $params['smtp_pass'] ?? '');
                Settings::set('smtp_from_email', $params['smtp_from_email'] ?? 'info@tyden-v-italii.eu');
                Settings::set('smtp_from_name', $params['smtp_from_name'] ?? 'Týden v Itálii');

                $alert = ['type' => 'success', 'message' => 'Nastavení bylo úspěšně uloženo.'];
            }
        }

        $this->render('admin/settings', [
            'title' => 'Nastavení webu',
            'alert' => $alert,
            'settings' => Settings::getAll(),
            'qrToken' => $admin['qr_login_token'] ?? '',
            'viewPath' => 'admin/settings'
        ], 'admin');
    }

    /**
     * Trips list
     */
    public function trips(Request $request): void
    {
        $this->checkAuth();

        // Get trips list in default language
        $trips = DB::fetchAll(
            "SELECT t.*, tt.title 
             FROM `trips` t 
             LEFT JOIN `trip_translations` tt ON t.id = tt.trip_id AND tt.lang = 'cs'
             ORDER BY t.start_date DESC"
        );

        $this->render('admin/trips-list', [
            'title' => 'Správa cest',
            'trips' => $trips,
            'viewPath' => 'admin/trips'
        ], 'admin');
    }

    /**
     * Add Trip
     */
    public function addTrip(Request $request): void
    {
        $this->checkAuth();
        $error = null;

        if ($request->isPost()) {
            $params = $request->getParams();
            $startDate = $params['start_date'] ?? '';
            $endDate = $params['end_date'] ?? '';
            $coverImage = $params['cover_image'] ?? '';
            $isActive = isset($params['is_active']) ? 1 : 0;

            if ($startDate === '' || $endDate === '') {
                $error = 'Vyplňte prosím datum od a do.';
            } else {
                // Insert trip
                DB::query(
                    "INSERT INTO `trips` (`start_date`, `end_date`, `cover_image`, `is_active`) VALUES (?, ?, ?, ?)",
                    [$startDate, $endDate, $coverImage, $isActive]
                );
                $tripId = (int)DB::lastInsertId();

                // Save translations
                foreach (SUPPORTED_LANGS as $lang) {
                    $title = trim($params["title_$lang"] ?? '');
                    $desc = trim($params["description_$lang"] ?? '');
                    
                    DB::query(
                        "INSERT INTO `trip_translations` (`trip_id`, `lang`, `title`, `description`) VALUES (?, ?, ?, ?)",
                        [$tripId, $lang, $title, $desc]
                    );
                }

                // Redirect to edit page to handle timeline steps additions
                $this->redirect("/admin/trips/edit/$tripId");
            }
        }

        $this->render('admin/trip-add', [
            'title' => 'Přidat novou cestu',
            'error' => $error,
            'viewPath' => 'admin/trips'
        ], 'admin');
    }

    /**
     * Edit Trip & Timeline Steps
     */
    public function editTrip(Request $request): void
    {
        $this->checkAuth();
        $id = (int)$request->getRouteParam('id');
        $error = null;
        $alert = null;

        // Fetch Trip
        $trip = DB::fetch("SELECT * FROM `trips` WHERE `id` = ?", [$id]);
        if (!$trip) {
            $this->redirect('/admin/trips');
        }

        // Handle POST update (General info and translations)
        if ($request->isPost() && $request->getParam('action') === 'save_trip') {
            $params = $request->getParams();
            $startDate = $params['start_date'] ?? '';
            $endDate = $params['end_date'] ?? '';
            $coverImage = $params['cover_image'] ?? '';
            $isActive = isset($params['is_active']) ? 1 : 0;

            if ($startDate === '' || $endDate === '') {
                $error = 'Vyplňte datum cesty.';
            } else {
                // Update general fields
                DB::query(
                    "UPDATE `trips` SET `start_date` = ?, `end_date` = ?, `cover_image` = ?, `is_active` = ? WHERE `id` = ?",
                    [$startDate, $endDate, $coverImage, $isActive, $id]
                );

                // Update translations
                foreach (SUPPORTED_LANGS as $lang) {
                    $title = trim($params["title_$lang"] ?? '');
                    $desc = trim($params["description_$lang"] ?? '');

                    DB::query(
                        "INSERT INTO `trip_translations` (`trip_id`, `lang`, `title`, `description`) 
                         VALUES (?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE `title` = ?, `description` = ?",
                        [$id, $lang, $title, $desc, $title, $desc]
                    );
                }
                $alert = ['type' => 'success', 'message' => 'Cesta byla úspěšně aktualizována.'];
                $trip = DB::fetch("SELECT * FROM `trips` WHERE `id` = ?", [$id]); // Refresh values
            }
        }

        // Handle Timeline steps additions/deletions via POST
        if ($request->isPost() && $request->getParam('action') === 'add_step') {
            $params = $request->getParams();
            $type = $params['step_type'] ?? 'walk';
            $dep = $params['step_dep'] ?? null;
            $arr = $params['step_arr'] ?? null;
            $order = (int)($params['step_order'] ?? 0);

            DB::query(
                "INSERT INTO `timeline_steps` (`trip_id`, `step_order`, `transport_type`, `departure_time`, `arrival_time`) VALUES (?, ?, ?, ?, ?)",
                [$id, $order, $type, $dep, $arr]
            );
            $stepId = (int)DB::lastInsertId();

            foreach (SUPPORTED_LANGS as $lang) {
                $title = trim($params["step_title_$lang"] ?? '');
                $text = trim($params["step_text_$lang"] ?? '');

                DB::query(
                    "INSERT INTO `timeline_step_translations` (`step_id`, `lang`, `title`, `text`) VALUES (?, ?, ?, ?)",
                    [$stepId, $lang, $title, $text]
                );
            }
            $alert = ['type' => 'success', 'message' => 'Krok časové osy byl přidán.'];
        }

        // Handle Timeline edit step
        if ($request->isPost() && $request->getParam('action') === 'edit_step') {
            $params = $request->getParams();
            $stepId = (int)$params['edit_step_id'];
            $type = $params['step_type'] ?? 'walk';
            $dep = $params['step_dep'] ?? null;
            $arr = $params['step_arr'] ?? null;
            $order = (int)($params['step_order'] ?? 0);

            DB::query(
                "UPDATE `timeline_steps` SET `step_order` = ?, `transport_type` = ?, `departure_time` = ?, `arrival_time` = ? WHERE `id` = ? AND `trip_id` = ?",
                [$order, $type, $dep, $arr, $stepId, $id]
            );

            foreach (SUPPORTED_LANGS as $lang) {
                $title = trim($params["step_title_$lang"] ?? '');
                $text = trim($params["step_text_$lang"] ?? '');

                DB::query(
                    "INSERT INTO `timeline_step_translations` (`step_id`, `lang`, `title`, `text`) 
                     VALUES (?, ?, ?, ?) 
                     ON DUPLICATE KEY UPDATE `title` = ?, `text` = ?",
                    [$stepId, $lang, $title, $text, $title, $text]
                );
            }
            $alert = ['type' => 'success', 'message' => 'Krok časové osy byl úspěšně upraven.'];
        }

        // Handle Timeline delete step
        if ($request->isPost() && $request->getParam('action') === 'delete_step') {
            $stepId = (int)$request->getParam('step_id');
            DB::query("DELETE FROM `timeline_steps` WHERE `id` = ? AND `trip_id` = ?", [$stepId, $id]);
            $alert = ['type' => 'success', 'message' => 'Krok časové osy byl smazán.'];
        }

        // Fetch Trip translations
        $transRaw = DB::fetchAll("SELECT * FROM `trip_translations` WHERE `trip_id` = ?", [$id]);
        $translations = [];
        foreach ($transRaw as $tr) {
            $translations[$tr['lang']] = $tr;
        }

        // Fetch timeline steps
        $steps = DB::fetchAll(
            "SELECT s.*, st.title, st.text, st.lang 
             FROM `timeline_steps` s 
             LEFT JOIN `timeline_step_translations` st ON s.id = st.step_id
             WHERE s.trip_id = ? 
             ORDER BY s.step_order ASC",
            [$id]
        );

        // Group step translations
        $timelineSteps = [];
        foreach ($steps as $st) {
            $stepId = $st['id'];
            if (!isset($timelineSteps[$stepId])) {
                $timelineSteps[$stepId] = [
                    'id'             => $st['id'],
                    'step_order'     => $st['step_order'],
                    'transport_type' => $st['transport_type'],
                    'departure_time' => $st['departure_time'],
                    'arrival_time'   => $st['arrival_time'],
                    'trans'          => []
                ];
            }
            if ($st['lang']) {
                $timelineSteps[$stepId]['trans'][$st['lang']] = [
                    'title' => $st['title'],
                    'text'  => $st['text']
                ];
            }
        }

        $this->render('admin/trip-edit', [
            'title'        => 'Upravit cestu',
            'trip'         => $trip,
            'translations' => $translations,
            'steps'        => $timelineSteps,
            'error'        => $error,
            'alert'        => $alert,
            'viewPath'     => 'admin/trips'
        ], 'admin');
    }

    /**
     * Reorder Timeline Steps via AJAX
     */
    public function reorderTimeline(Request $request): void
    {
        $this->checkAuth();

        $params = $request->getParams();
        $order = $params['order'] ?? [];

        if (!is_array($order) || empty($order)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid order data']);
            exit;
        }

        try {
            foreach ($order as $index => $stepId) {
                $stepOrder = $index + 1;
                DB::query(
                    "UPDATE `timeline_steps` SET `step_order` = ? WHERE `id` = ?",
                    [$stepOrder, (int)$stepId]
                );
            }

            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Delete Trip
     */
    public function deleteTrip(Request $request): void
    {
        $this->checkAuth();
        $id = (int)$request->getRouteParam('id');
        DB::query("DELETE FROM `trips` WHERE `id` = ?", [$id]);
        $this->redirect('/admin/trips');
    }

    /**
     * Blog Posts List
     */
    public function posts(Request $request): void
    {
        $this->checkAuth();

        $posts = DB::fetchAll(
            "SELECT p.*, pt.title 
             FROM `posts` p 
             LEFT JOIN `post_translations` pt ON p.id = pt.post_id AND pt.lang = 'cs'
             ORDER BY p.created_at DESC"
        );

        $this->render('admin/posts-list', [
            'title' => 'Správa článků',
            'posts' => $posts,
            'viewPath' => 'admin/posts'
        ], 'admin');
    }

    /**
     * Add Blog Post
     */
    public function addPost(Request $request): void
    {
        $this->checkAuth();
        $error = null;

        if ($request->isPost()) {
            $params = $request->getParams();
            $coverImage = $params['cover_image'] ?? '';
            $isActive = isset($params['is_active']) ? 1 : 0;
            $tripId = !empty($params['trip_id']) ? (int)$params['trip_id'] : null;

            // Base verification (must have CZ title)
            if (empty($params['title_cs'])) {
                $error = 'Název článku v češtině je povinný.';
            } else {
                DB::query(
                    "INSERT INTO `posts` (`trip_id`, `cover_image`, `is_active`) VALUES (?, ?, ?)",
                    [$tripId, $coverImage, $isActive]
                );
                $postId = (int)DB::lastInsertId();

                // Save translations
                foreach (SUPPORTED_LANGS as $lang) {
                    $title = trim($params["title_$lang"] ?? '');
                    
                    // Generate Slug (Czech fallback, url safe slug)
                    $slugSource = $title ?: trim($params["title_cs"] ?? 'article-' . $postId);
                    $slug = $this->slugify($slugSource);
                    
                    // Avoid duplicate slugs in the same language
                    $slugCheck = DB::fetch("SELECT COUNT(*) as cnt FROM `post_translations` WHERE `slug` = ? AND `lang` = ?", [$slug, $lang]);
                    if ($slugCheck && (int)$slugCheck['cnt'] > 0) {
                        $slug .= '-' . $postId;
                    }

                    $content = $params["content_$lang"] ?? '';
                    $metaTitle = trim($params["meta_title_$lang"] ?? '');
                    $metaDesc = trim($params["meta_desc_$lang"] ?? '');

                    DB::query(
                        "INSERT INTO `post_translations` (`post_id`, `lang`, `title`, `slug`, `content`, `meta_title`, `meta_description`) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$postId, $lang, $title, $slug, $content, $metaTitle, $metaDesc]
                    );
                }

                $this->redirect('/admin/posts');
            }
        }

        // Fetch trips to link post optional association
        $trips = DB::fetchAll("SELECT t.id, tt.title FROM `trips` t JOIN `trip_translations` tt ON t.id = tt.trip_id WHERE tt.lang = 'cs' ORDER BY t.start_date DESC");

        $this->render('admin/post-add', [
            'title' => 'Přidat nový článek',
            'trips' => $trips,
            'error' => $error,
            'viewPath' => 'admin/posts'
        ], 'admin');
    }

    /**
     * Edit Blog Post
     */
    public function editPost(Request $request): void
    {
        $this->checkAuth();
        $id = (int)$request->getRouteParam('id');
        $error = null;
        $alert = null;

        // Fetch Post
        $post = DB::fetch("SELECT * FROM `posts` WHERE `id` = ?", [$id]);
        if (!$post) {
            $this->redirect('/admin/posts');
        }

        if ($request->isPost()) {
            $params = $request->getParams();
            $coverImage = $params['cover_image'] ?? '';
            $isActive = isset($params['is_active']) ? 1 : 0;
            $tripId = !empty($params['trip_id']) ? (int)$params['trip_id'] : null;

            if (empty($params['title_cs'])) {
                $error = 'Název článku v češtině je povinný.';
            } else {
                // Update general fields
                DB::query(
                    "UPDATE `posts` SET `trip_id` = ?, `cover_image` = ?, `is_active` = ? WHERE `id` = ?",
                    [$tripId, $coverImage, $isActive, $id]
                );

                // Update translations
                foreach (SUPPORTED_LANGS as $lang) {
                    $title = trim($params["title_$lang"] ?? '');
                    
                    // Slug generation
                    $slugSource = $title ?: trim($params["title_cs"] ?? 'article-' . $id);
                    $slug = $this->slugify($slugSource);

                    // Check for existing slug (excluding current post)
                    $slugCheck = DB::fetch("SELECT COUNT(*) as cnt FROM `post_translations` WHERE `slug` = ? AND `lang` = ? AND `post_id` != ?", [$slug, $lang, $id]);
                    if ($slugCheck && (int)$slugCheck['cnt'] > 0) {
                        $slug .= '-' . $id;
                    }

                    $content = $params["content_$lang"] ?? '';
                    $metaTitle = trim($params["meta_title_$lang"] ?? '');
                    $metaDesc = trim($params["meta_desc_$lang"] ?? '');

                    DB::query(
                        "INSERT INTO `post_translations` (`post_id`, `lang`, `title`, `slug`, `content`, `meta_title`, `meta_description`) 
                         VALUES (?, ?, ?, ?, ?, ?, ?) 
                         ON DUPLICATE KEY UPDATE `title` = ?, `slug` = ?, `content` = ?, `meta_title` = ?, `meta_description` = ?",
                        [$id, $lang, $title, $slug, $content, $metaTitle, $metaDesc, $title, $slug, $content, $metaTitle, $metaDesc]
                    );
                }

                $alert = ['type' => 'success', 'message' => 'Článek byl úspěšně uložen.'];
                $post = DB::fetch("SELECT * FROM `posts` WHERE `id` = ?", [$id]); // Refresh
            }
        }

        // Fetch translations
        $transRaw = DB::fetchAll("SELECT * FROM `post_translations` WHERE `post_id` = ?", [$id]);
        $translations = [];
        foreach ($transRaw as $tr) {
            $translations[$tr['lang']] = $tr;
        }

        // Trips selection list
        $trips = DB::fetchAll("SELECT t.id, tt.title FROM `trips` t JOIN `trip_translations` tt ON t.id = tt.trip_id WHERE tt.lang = 'cs' ORDER BY t.start_date DESC");

        $this->render('admin/post-edit', [
            'title'        => 'Upravit článek',
            'post'         => $post,
            'translations' => $translations,
            'trips'        => $trips,
            'error'        => $error,
            'alert'        => $alert,
            'viewPath'     => 'admin/posts'
        ], 'admin');
    }

    /**
     * Delete Blog Post
     */
    public function deletePost(Request $request): void
    {
        $this->checkAuth();
        $id = (int)$request->getRouteParam('id');
        DB::query("DELETE FROM `posts` WHERE `id` = ?", [$id]);
        $this->redirect('/admin/posts');
    }

    /**
     * Comments list & actions
     */
    public function comments(Request $request): void
    {
        $this->checkAuth();
        $alert = null;

        // Moderate comments (approve, mark spam, delete) via GET/POST
        $action = $request->getParam('action');
        $commentId = (int)$request->getParam('id');

        if ($commentId > 0 && $action) {
            if ($action === 'approve') {
                DB::query("UPDATE `comments` SET `is_approved` = 1 WHERE `id` = ?", [$commentId]);
                $alert = ['type' => 'success', 'message' => 'Komentář byl schválen.'];
            } elseif ($action === 'spam') {
                DB::query("UPDATE `comments` SET `is_approved` = -1 WHERE `id` = ?", [$commentId]);
                $alert = ['type' => 'success', 'message' => 'Komentář byl označen jako spam.'];
            } elseif ($action === 'delete') {
                DB::query("DELETE FROM `comments` WHERE `id` = ?", [$commentId]);
                $alert = ['type' => 'success', 'message' => 'Komentář byl úspěšně smazán.'];
            }
        }

        // Fetch all comments with post title info
        $comments = DB::fetchAll(
            "SELECT c.*, pt.title as post_title 
             FROM `comments` c 
             JOIN `post_translations` pt ON c.post_id = pt.post_id AND pt.lang = 'cs'
             ORDER BY c.created_at DESC"
        );

        $this->render('admin/comments-list', [
            'title'    => 'Správa komentářů',
            'comments' => $comments,
            'alert'    => $alert,
            'viewPath' => 'admin/comments'
        ], 'admin');
    }

    /**
     * Administrators List & CRUD
     */
    public function admins(Request $request): void
    {
        $this->checkAuth();
        $error = null;
        $alert = null;

        // Add admin logic
        if ($request->isPost()) {
            $params = $request->getParams();
            $email = trim($params['email'] ?? '');
            $password = $params['password'] ?? '';

            if ($email === '' || $password === '') {
                $error = 'Email i heslo jsou povinné.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Zadejte platný email.';
            } else {
                // Check if exists
                $check = DB::fetch("SELECT COUNT(*) as cnt FROM `admins` WHERE `email` = ?", [$email]);
                if ($check && (int)$check['cnt'] > 0) {
                    $error = 'Administrátor s tímto emailem již existuje.';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    DB::query("INSERT INTO `admins` (`email`, `password`) VALUES (?, ?)", [$email, $hash]);
                    $alert = ['type' => 'success', 'message' => 'Administrátor byl úspěšně vytvořen.'];
                }
            }
        }

        // Delete admin logic
        $deleteId = (int)$request->getParam('delete_id');
        if ($deleteId > 0) {
            // Prevent self-deletion
            if ($deleteId === (int)$_SESSION['admin_id']) {
                $error = 'Nemůžete smazat svůj vlastní účet!';
            } else {
                DB::query("DELETE FROM `admins` WHERE `id` = ?", [$deleteId]);
                $alert = ['type' => 'success', 'message' => 'Administrátor byl smazán.'];
            }
        }

        $adminsList = DB::fetchAll("SELECT * FROM `admins` ORDER BY `created_at` DESC");

        $this->render('admin/admins-list', [
            'title'      => 'Správa administrátorů',
            'adminsList' => $adminsList,
            'error'      => $error,
            'alert'      => $alert,
            'viewPath'   => 'admin/admins-list'
        ], 'admin');
    }

    /**
     * Slugify Helper utility
     */
    private function slugify(string $text): string
    {
        // Replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        
        // Transliterate (remove diacritics / accents)
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim
        $text = trim($text, '-');
        
        // Remove duplicate -
        $text = preg_replace('~-+~', '-', $text);
        
        // Lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
