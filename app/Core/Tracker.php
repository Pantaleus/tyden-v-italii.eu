<?php
declare(strict_types=1);

namespace App\Core;

class Tracker
{
    /**
     * Start tracking the current request.
     * Logs the visit into MariaDB visitor_logs.
     */
    public static function track(Request $request): void
    {
        // Don't track admin pages to avoid polluting stats
        $path = $request->getUri();
        if (str_starts_with($path, '/admin')) {
            return;
        }

        // Avoid tracking AJAX/stat endpoints
        if (str_starts_with($path, '/track-screen') || str_contains($path, '.')) {
            return;
        }

        try {
            $ip = $request->getIp();
            $userAgent = $request->getUserAgent();
            $referrer = $request->getReferer();

            // Setup session identification
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $sessionId = session_id();

            // Detect browser, OS, and device
            $browser = self::detectBrowser($userAgent);
            $os = self::detectOS($userAgent);
            $device = self::detectDevice($userAgent);

            // Fetch GeoIP info (with database caching)
            $geo = self::getGeoData($ip);

            // Insert log
            DB::query(
                "INSERT INTO `tracker_logs` 
                (`ip_address`, `country_code`, `country_name`, `city`, `user_agent`, `browser`, `os`, `device`, `url_path`, `referrer`, `session_id`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $ip,
                    $geo['country_code'],
                    $geo['country_name'],
                    $geo['city'],
                    $userAgent,
                    $browser,
                    $os,
                    $device,
                    $path,
                    $referrer,
                    $sessionId
                ]
            );

            // Keep track of visit ID in session so client-side can update screen resolution later
            $_SESSION['last_tracker_id'] = DB::lastInsertId();

        } catch (\Exception $e) {
            // Silently log error, do not break public website
            error_log('Tracker error: ' . $e->getMessage());
        }
    }

    /**
     * Update screen size for the last logged visit
     */
    public static function updateScreen(int $width, int $height): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $visitId = $_SESSION['last_tracker_id'] ?? null;
        if ($visitId) {
            try {
                DB::query(
                    "UPDATE `tracker_logs` SET `screen_width` = ?, `screen_height` = ? WHERE `id` = ?",
                    [$width, $height, $visitId]
                );
                return true;
            } catch (\Exception $e) {
                error_log('Tracker screen update error: ' . $e->getMessage());
            }
        }
        return false;
    }

    /**
     * Get Geo Location data from ip-api.com with local database cache
     */
    private static function getGeoData(string $ip): array
    {
        $default = ['country_code' => 'XX', 'country_name' => 'Unknown', 'city' => 'Unknown'];
        
        // Skip local IPs
        if ($ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return ['country_code' => 'CZ', 'country_name' => 'Localhost / Czechia', 'city' => 'Praha'];
        }

        try {
            // Check if IP details already exist in DB cache
            $cached = DB::fetch(
                "SELECT `country_code`, `country_name`, `city` FROM `tracker_logs` WHERE `ip_address` = ? AND `country_code` IS NOT NULL LIMIT 1",
                [$ip]
            );

            if ($cached) {
                return $cached;
            }

            // Call free ip-api.com service (with 2-second timeout to prevent slowing down pages)
            $url = "http://ip-api.com/json/" . urlencode($ip) . "?fields=status,message,country,countryCode,city";
            $ctx = stream_context_create([
                'http' => ['timeout' => 2]
            ]);
            $response = @file_get_contents($url, false, $ctx);

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    return [
                        'country_code' => $data['countryCode'] ?? 'XX',
                        'country_name' => $data['country'] ?? 'Unknown',
                        'city'         => $data['city'] ?? 'Unknown'
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log('GeoIP error for ' . $ip . ': ' . $e->getMessage());
        }

        return $default;
    }

    private static function detectOS(string $ua): string
    {
        $osList = [
            '/windows nt 10/i'      => 'Windows 10/11',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/macintosh|mac os x/i' => 'macOS',
            '/ipad/i'               => 'iPadOS',
            '/iphone/i'             => 'iOS',
            '/android/i'            => 'Android',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu'
        ];

        foreach ($osList as $regex => $osName) {
            if (preg_match($regex, $ua)) {
                return $osName;
            }
        }
        return 'Unknown OS';
    }

    private static function detectBrowser(string $ua): string
    {
        $browserList = [
            '/chrome/i'    => 'Chrome',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/edge|edg/i'  => 'Edge',
            '/opera|opr/i' => 'Opera',
            '/msie|trident/i' => 'Internet Explorer'
        ];

        // Specific detection because Chrome UA string often contains Safari and vice versa
        if (preg_match('/edge|edg/i', $ua)) {
            return 'Edge';
        }
        if (preg_match('/chrome/i', $ua) && preg_match('/safari/i', $ua)) {
            return 'Chrome';
        }
        if (preg_match('/firefox/i', $ua)) {
            return 'Firefox';
        }
        if (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) {
            return 'Safari';
        }

        foreach ($browserList as $regex => $browserName) {
            if (preg_match($regex, $ua)) {
                return $browserName;
            }
        }
        return 'Unknown Browser';
    }

    private static function detectDevice(string $ua): string
    {
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            return 'tablet';
        }
        if (preg_match('/mobile|iphone|ipod|android|blackberry|phone/i', $ua)) {
            return 'mobile';
        }
        return 'desktop';
    }
}
