<?php
namespace Infrastructure\Database\Connection;

use PDO;
use PDOException;

use Infrastructure\Database\Config\DatabaseConfig;

class DatabaseConnection {
  private static ?PDO $pdo = null;

  public static function getConnection(): PDO {
    if (self::$pdo === null) {
      try {
        $dbConfig = DatabaseConfig::getCredentials();
        
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset=utf8mb4";

        self::$pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
        ]);
      } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
      }
    }
    return self::$pdo;
  }
}
