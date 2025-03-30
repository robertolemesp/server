<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use Dotenv\Dotenv;

use Infrastructure\DependencyInjection\DependencyInjectionContainer;

use Infrastructure\Database\Config\DatabaseConfig;
use Infrastructure\Database\Setup\DatabaseSetup;

use Infrastructure\Database\Seeder\TestUserSeeder;
use Infrastructure\Api\Auth\Seeder\AuthUserSeeder;

use Infrastructure\Logging\LogService;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

$APP_DEFAULT_PORT = '8000';
$port = $_ENV['PORT'] ?? $APP_DEFAULT_PORT;

try {
  $dbConfig = DatabaseConfig::getCredentials();

  $dsnWithoutDb = "mysql:host={$dbConfig['host']}";
  $pdo = new PDO($dsnWithoutDb, $dbConfig['user'], $dbConfig['password']);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  LogService::info("Connected to MySQL server successfully.");

  $databaseSetup = new DatabaseSetup($pdo);
  $databaseSetup->run();

  $dsnWithDatabase = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}";
  $pdo = new PDO($dsnWithDatabase, $dbConfig['user'], $dbConfig['password']);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  LogService::info("Database connected successfully.");

  $diContainer = new DependencyInjectionContainer($pdo);


  if ($_ENV['APP_ENV'] !== 'production') {
    LogService::info("Running Auth and DB user seeder...");
  
    $testAuthUserSeeder = new AuthUserSeeder();
    $testAuthUserSeeder->seed();
  
    $testDbUserSeeder = new TestUserSeeder($diContainer->getCustomerService());
    $testDbUserSeeder->seed();
  }

  if (php_sapi_name() !== 'cli') {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    $router = $diContainer->getRouter();
    $router->route($requestUri, $requestMethod);
  }
} catch (PDOException $e) {

  LogService::error("Database connection failed: " . $e->getMessage());
  header('Content-Type: application/json');
  http_response_code(500);
  echo json_encode(["error" => "Database connection failed."]);

  exit;
} catch (\Throwable $e) {

  LogService::error("Unhandled application error: " . $e->getMessage());
  header('Content-Type: application/json');
  http_response_code(500);
  echo json_encode(["error" => "Internal Server Error."]);

  exit;
}

register_shutdown_function(function () {
  $error = error_get_last();
  if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    LogService::error("Fatal Error: {$error['message']} in {$error['file']} on line {$error['line']}");
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Fatal Error: ' . $error['message']]);
  }
});


