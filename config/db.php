<?php
function dbConnect(): ?PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }

    try {
        $pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=qlktx_dhqg_hcm;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } catch (PDOException $e) {
        $pdo = null;
    }
    return $pdo;
}
