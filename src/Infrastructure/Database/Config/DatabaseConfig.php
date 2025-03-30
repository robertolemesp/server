<?php
namespace Infrastructure\Database\Config;

class DatabaseConfig {
  public static function getCredentials(): array {
    return [
      'host' => $_ENV['DB_HOST'] ?: 'localhost',
      'dbname' => ($_ENV['APP_ENV'] === 'test' ? 
          'robertos_mission_test'
        : 
          $_ENV['DB_NAME']) ?? 'robertos_mission',
      'user' => $_ENV['DB_USER'] ?: 'root',
      'password' => $_ENV['DB_PASSWORD'] ?: 'root'
    ];
  }
}
