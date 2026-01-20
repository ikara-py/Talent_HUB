<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;


class Database {
    private static $connection = null;
    
    public static function connect(): PDO {
        if (self::$connection === null) {
            $dotenv = Dotenv::createImmutable(__DIR__  . '/..');
            $dotenv->load();
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=utf8mb4",
                $_ENV['db_host'],
                $_ENV['db_name']
            );

            try {
                self::$connection = new PDO(
                    $dsn,
                    $_ENV['db_user'],
                    $_ENV['db_pass'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
            } catch (PDOException $err) {
                die("Connection failed: " . $err->getMessage());
            }
        }
        return self::$connection;
    }
}
