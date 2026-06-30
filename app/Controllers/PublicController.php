<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\DB;
use App\Core\Request;
use App\Core\Language;
use App\Core\Tracker;
use App\Core\Settings;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class PublicController extends Controller
{
    public function home(Request $request): void
    {
        $lang = Language::get();

        // Fetch recent active trips
        $trips = DB::fetchAll(
            "SELECT t.*, tt.title, tt.description 
             FROM `trips` t 
             JOIN `trip_translations` tt ON t.id = tt.trip_id 
             WHERE t.is_active = 1 AND tt.lang = ? 
             ORDER BY t.start_date DESC LIMIT 3",
            [$lang]
        );

        // Fetch recent active posts
        $posts = DB::fetchAll(
            "SELECT p.*, pt.title, pt.slug, pt.content, pt.meta_description 
             FROM `posts` p 
             JOIN `post_translations` pt ON p.id = pt.post_id 
             WHERE p.is_active = 1 AND pt.lang = ? 
             ORDER BY p.created_at DESC LIMIT 3",
            [$lang]
        );

        $this->render('public/home', [
            'title' => Language::translate('nav.home', 'Domů'),
            'trips' => $trips,
            'posts' => $posts,
            'viewPath' => 'public/home'
        ]);
    }

    public function trips(Request $request): void
    {
        $lang = Language::get();

        $trips = DB::fetchAll(
            "SELECT t.*, tt.title, tt.description 
             FROM `trips` t 
             JOIN `trip_translations` tt ON t.id = tt.trip_id 
             WHERE t.is_active = 1 AND tt.lang = ? 
             ORDER BY t.start_date DESC",
            [$lang]
        );

        $this->render('public/trips', [
            'title' => Language::translate('nav.trips', 'Cesty'),
            'trips' => $trips,
            'viewPath' => 'public/trips'
        ]);
    }

    public function tripDetail(Request $request): void
    {
        $id = (int)$request->getRouteParam('id');
        $lang = Language::get();

        // Fetch trip info
        $trip = DB::fetch(
            "SELECT t.*, tt.title, tt.description 
             FROM `trips` t 
             JOIN `trip_translations` tt ON t.id = tt.trip_id 
             WHERE t.id = ? AND t.is_active = 1 AND tt.lang = ?",
            [$id, $lang]
        );

        if (!$trip) {
            http_response_code(404);
            $this->render('public/404', ['title' => 'Cesta nenalezena', 'viewPath' => 'public/404']);
            return;
        }

        // Fetch timeline steps
        $steps = DB::fetchAll(
            "SELECT s.*, st.title, st.text 
             FROM `timeline_steps` s 
             JOIN `timeline_step_translations` st ON s.id = st.step_id 
             WHERE s.trip_id = ? AND st.lang = ? 
             ORDER BY s.step_order ASC",
            [$id, $lang]
        );

        $this->render('public/trip-detail', [
            'title' => $trip['title'],
            'trip' => $trip,
            'steps' => $steps,
            'viewPath' => 'public/trip-detail'
        ]);
    }

    public function blog(Request $request): void
    {
        $lang = Language::get();

        $posts = DB::fetchAll(
            "SELECT p.*, pt.title, pt.slug, pt.meta_description 
             FROM `posts` p 
             JOIN `post_translations` pt ON p.id = pt.post_id 
             WHERE p.is_active = 1 AND pt.lang = ? 
             ORDER BY p.created_at DESC",
            [$lang]
        );

        $this->render('public/blog', [
            'title' => Language::translate('nav.blog', 'Blog'),
            'posts' => $posts,
            'viewPath' => 'public/blog'
        ]);
    }

    public function blogPost(Request $request): void
    {
        $slug = $request->getRouteParam('slug');
        $lang = Language::get();

        // Fetch post detail
        $post = DB::fetch(
            "SELECT p.*, pt.post_id, pt.title, pt.content, pt.meta_title, pt.meta_description 
             FROM `posts` p 
             JOIN `post_translations` pt ON p.id = pt.post_id 
             WHERE pt.slug = ? AND pt.lang = ? AND p.is_active = 1",
            [$slug, $lang]
        );

        if (!$post) {
            http_response_code(404);
            $this->render('public/404', ['title' => 'Článek nenalezen', 'viewPath' => 'public/404']);
            return;
        }

        // Handle comment submission
        $alert = null;
        if ($request->isPost()) {
            $params = $request->getParams();

            // 1. Honeypot check
            if (!empty($params['website_url'])) {
                // It is a bot
                $alert = ['type' => 'error', 'message' => Language::translate('comment.error_spam', 'Detekován spam')];
            } 
            // 2. CSRF Token verification
            elseif (!$this->verifyCsrf($params['csrf_token'] ?? '')) {
                $alert = ['type' => 'error', 'message' => 'Neplatný bezpečnostní token. Zkuste to znovu.'];
            } 
            // 3. Validation
            else {
                $name = trim($params['author_name'] ?? '');
                $email = trim($params['author_email'] ?? '');
                $content = trim($params['comment_text'] ?? '');

                if ($name === '' || $email === '' || $content === '') {
                    $alert = ['type' => 'error', 'message' => 'Všechna pole musí být vyplněna.'];
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $alert = ['type' => 'error', 'message' => 'Zadejte prosím platnou e-mailovou adresu.'];
                } else {
                    // Save comment (is_approved default 0 = pending)
                    DB::query(
                        "INSERT INTO `comments` (`post_id`, `author_name`, `author_email`, `content`, `ip_address`) VALUES (?, ?, ?, ?, ?)",
                        [$post['id'], $name, $email, $content, $request->getIp()]
                    );
                    $alert = ['type' => 'success', 'message' => Language::translate('comment.success')];
                }
            }
        }

        // Fetch approved comments for post
        $comments = DB::fetchAll(
            "SELECT * FROM `comments` WHERE `post_id` = ? AND `is_approved` = 1 ORDER BY `created_at` ASC",
            [$post['id']]
        );

        $this->render('public/blog-post', [
            'title' => $post['title'],
            'metaDescription' => $post['meta_description'],
            'post' => $post,
            'comments' => $comments,
            'alert' => $alert,
            'csrf_token' => $this->generateCsrf(),
            'viewPath' => 'public/blog-post'
        ]);
    }

    public function contact(Request $request): void
    {
        $alert = null;

        if ($request->isPost()) {
            $params = $request->getParams();

            // Honeypot & CSRF check
            if (!empty($params['phone_number'])) {
                $alert = ['type' => 'error', 'message' => 'Spam zablokován.'];
            } elseif (!$this->verifyCsrf($params['csrf_token'] ?? '')) {
                $alert = ['type' => 'error', 'message' => 'Neplatný bezpečnostní token.'];
            } else {
                $name = trim($params['name'] ?? '');
                $email = trim($params['email'] ?? '');
                $message = trim($params['message'] ?? '');

                if ($name === '' || $email === '' || $message === '') {
                    $alert = ['type' => 'error', 'message' => 'Všechna pole jsou povinná.'];
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $alert = ['type' => 'error', 'message' => 'Zadejte platný e-mail.'];
                } else {
                    // Try sending email via PHPMailer using SMTP settings
                    if ($this->sendContactEmail($name, $email, $message)) {
                        $alert = ['type' => 'success', 'message' => 'Zpráva byla úspěšně odeslána! Ozvu se vám co nejdříve.'];
                    } else {
                        $alert = ['type' => 'error', 'message' => 'Zprávu se nepodařilo odeslat. Zkuste to prosím později nebo napište přímo na email.'];
                    }
                }
            }
        }

        $this->render('public/contact', [
            'title' => Language::translate('nav.contact', 'Kontakt'),
            'alert' => $alert,
            'csrf_token' => $this->generateCsrf(),
            'viewPath' => 'public/contact'
        ]);
    }

    /**
     * AJAX endpoint to log client resolution
     */
    public function trackScreen(Request $request): void
    {
        if ($request->isPost()) {
            $params = $request->getParams();
            $width = (int)($params['width'] ?? 0);
            $height = (int)($params['height'] ?? 0);

            if ($width > 0 && $height > 0) {
                Tracker::updateScreen($width, $height);
                $this->json(['status' => 'success']);
                return;
            }
        }
        $this->json(['status' => 'error'], 400);
    }

    /**
     * Dynamic robots.txt
     */
    public function robots(Request $request): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin/\n";
        echo "Sitemap: " . BASE_URL . "/sitemap.xml\n";
        exit;
    }

    /**
     * Dynamic sitemap.xml for SEO
     */
    public function sitemap(Request $request): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        // Add static routes
        $staticRoutes = ['', '/trips', '/blog', '/contact'];
        foreach ($staticRoutes as $route) {
            $url = $xml->addChild('url');
            $url->addChild('loc', BASE_URL . $route);
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', $route === '' ? '1.0' : '0.8');
        }

        // Add posts for all supported languages
        $posts = DB::fetchAll("SELECT pt.slug, pt.lang, p.created_at FROM `posts` p JOIN `post_translations` pt ON p.id = pt.post_id WHERE p.is_active = 1");
        foreach ($posts as $post) {
            $url = $xml->addChild('url');
            // Adding lang param to url or structure
            $url->addChild('loc', BASE_URL . '/blog/' . $post['slug'] . '?lang=' . $post['lang']);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.6');
        }

        // Add trips
        $trips = DB::fetchAll("SELECT t.id, t.created_at FROM `trips` t WHERE t.is_active = 1");
        foreach ($trips as $trip) {
            $url = $xml->addChild('url');
            $url->addChild('loc', BASE_URL . '/trips/' . $trip['id']);
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.7');
        }

        echo $xml->asXML();
        exit;
    }

    /**
     * SMTP sender using PHPMailer
     */
    private function sendContactEmail(string $name, string $email, string $messageContent): bool
    {
        $mail = new PHPMailer(true);
        try {
            $host = Settings::get('smtp_host', 'localhost');
            $port = (int)Settings::get('smtp_port', '587');
            $user = Settings::get('smtp_user', '');
            $pass = Settings::get('smtp_pass', '');
            $fromEmail = Settings::get('smtp_from_email', 'info@tyden-v-italii.eu');
            $fromName = Settings::get('smtp_from_name', 'Týden v Itálii');

            if ($user !== '') {
                $mail->isSMTP();
                $mail->Host       = $host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $user;
                $mail->Password   = $pass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $port;
            } else {
                $mail->isMail(); // Fallback to php mail()
            }

            // Recipients
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($fromEmail); // Sends email to administrator
            $mail->addReplyTo($email, $name);

            // Content
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Kontaktní formulář: ' . $name;
            $mail->Body    = sprintf(
                "<h3>Nová zpráva z kontaktního formuláře:</h3>
                <p><strong>Jméno:</strong> %s</p>
                <p><strong>E-mail:</strong> %s</p>
                <p><strong>Zpráva:</strong><br>%s</p>",
                htmlspecialchars($name),
                htmlspecialchars($email),
                nl2br(htmlspecialchars($messageContent))
            );

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('PHPMailer error: ' . $mail->ErrorInfo . ' | Code: ' . $e->getMessage());
            return false;
        }
    }
}
