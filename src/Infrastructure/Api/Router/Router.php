<?php
namespace Infrastructure\Api\Router;

use Infrastructure\Api\Router\Customer\CustomerRoutes;
use Infrastructure\Api\Controller\Customer\CustomerController;

class Router {
  private CustomerController $customerController;
  private string $jwtSecret;

  public function __construct(CustomerController $customerController) {
    $this->customerController = $customerController;

    $this->jwtSecret = $_ENV['NEXTAUTH_SECRET'] ?? '';

    if (empty($this->jwtSecret)) 
      throw new \Exception('NEXTAUTH_SECRET is not set in environment variables.');
  }

  private function setDefaultHeaders(): void {
    header('Access-Control-Allow-Origin: http://localhost:3000');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, ALL, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
      http_response_code(200);
      exit();
    }
  }

  public function route(string $requestUri, string $requestMethod): void {
    $this->setDefaultHeaders();

    try {
      $router = [
        'POST' => [],
        'GET' => [],
        'PUT' => [],
        'ALL' => [],
        'DELETE' => [],
      ];

      $router['GET']['/'] = fn() => $this->jsonResponse(['message' => 'Application is Running'], 200);

      CustomerRoutes::register($router, $this->customerController, $this->jwtSecret);

      if (!isset($router[$requestMethod])) {
        $this->jsonResponse(['errors' => ['Method Not Allowed']], 405);
        return;
      }

      if ($this->matchRoute($router[$requestMethod], $requestUri)) 
        return;
      
      if ($this->matchRoute($router['ALL'], $requestUri)) 
        return;
      
      $this->jsonResponse(['errors' => ['Route Not Found']], 404);
    } catch (\Throwable $e) {
        $this->jsonResponse(['errors' => ['Internal Server Error', $e->getMessage()]], 500);
    }
  }

  private function matchRoute(array $routes, string $requestUri): bool {
    foreach ($routes as $pattern => $handler) 
      if (preg_match('#^' . $pattern . '$#', $requestUri, $matches)) {
        array_shift($matches);
        call_user_func_array($handler, $matches);
        return true;
      }

    return false;
  }


  private function jsonResponse(array $data, int $statusCode): void {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    exit();
  }
}

