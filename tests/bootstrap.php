<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Infrastructure\Database\Setup\DatabaseSetup;
use Infrastructure\Database\Config\DatabaseConfig;

try {
  $dbConfig = DatabaseConfig::getCredentials();
  $dsn = sprintf("mysql:host=%s;charset=utf8mb4", $dbConfig['host']);

  $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  (new DatabaseSetup($pdo))->run();
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
