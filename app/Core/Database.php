<?php

namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = config('database');
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";

            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            } catch (PDOException $e) {
                throw new Exception("Lỗi kết nối CSDL: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function beginTransaction(): bool {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool {
        return self::getConnection()->rollBack();
    }

    public static function query(string $sql, array $params = []): \PDOStatement {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array {
        $res = self::query($sql, $params)->fetch();
        return $res ?: null;
    }

    public static function execute(string $sql, array $params = []): bool {
        return self::query($sql, $params)->rowCount() >= 0;
    }

    public static function lastInsertId(): string {
        return self::getConnection()->lastInsertId();
    }
}
