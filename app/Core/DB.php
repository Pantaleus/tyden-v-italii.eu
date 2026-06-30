<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class DB
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // Load configuration if not defined
            if (!defined('DB_HOST')) {
                require_once __DIR__ . '/../../config.php';
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                if (DEV_MODE) {
                    throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
                } else {
                    // Log error privately and show user-friendly error
                    error_log('Database connection error: ' . $e->getMessage());
                    http_response_code(500);
                    exit('Došlo k chybě při připojení k databázi. Zkuste to prosím později.');
                }
            }
        }

        return self::$instance;
    }

    /**
     * Helper to run queries easily with parameters
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Helper to fetch all rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Helper to fetch a single row
     */
    public static function fetch(string $sql, array $params = [])
    {
        return self::query($sql, $params)->fetch();
    }

    /**
     * Helper to get last inserted ID
     */
    public static function lastInsertId(): string
    {
        return self::getInstance()->lastInsertId();
    }
}
