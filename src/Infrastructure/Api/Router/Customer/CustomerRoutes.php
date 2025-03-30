<?php
namespace Infrastructure\Api\Router\Customer;

use Infrastructure\Api\Controller\Customer\CustomerController;
use Infrastructure\Api\Auth\Middleware\JWTMiddleware;

use Exception;

class CustomerRoutes {
  private static array $publicRoutes = [
    'POST' => [
      '/customer/credentials',
    ]
  ];

  public static function register(array &$router, CustomerController $controller, string $jwtSecret) {
    $protectRoute = fn($handler) => function (...$params) use ($handler, $jwtSecret) {
      if (!self::isPublicRoute())
        self::applyAuthMiddleware($jwtSecret);
      
      return call_user_func_array($handler, $params);
    };

    $router['POST']['/customer/credentials'] = fn() => $controller->validateCredentials();
    $router['POST']['/customer/exists'] = fn() => $controller->exists();

    $router['POST']['/customer'] = $protectRoute(fn() => $controller->create());
    $router['PUT']['/customer/(\d+)'] = $protectRoute(fn($id) => $controller->update((int) $id));
    $router['GET']['/customer'] = $protectRoute(fn() => $controller->list());
    $router['DELETE']['/customer/(\d+)'] = $protectRoute(fn($id) => $controller->remove((int) $id));
    $router['POST']['/customer/(\d+)/address'] = $protectRoute(fn($customerId) => $controller->updateAddress((int) $customerId));
    $router['PUT']['/customer/(\d+)/address'] = $protectRoute(fn($customerId) => $controller->updateAddress((int) $customerId));
    $router['DELETE']['/customer/(\d+)/address'] = $protectRoute(fn($customerId) => $controller->removeAddress((int) $customerId));
  }

  private static function applyAuthMiddleware(string $jwtSecret): void {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!str_starts_with($authHeader, 'Bearer ')) {
      http_response_code(401);
      echo json_encode(['error' => 'Missing or invalid Authorization header']);
      exit;
    }

    $token = substr($authHeader, 7);

    try {
      JWTMiddleware::decode($token, $jwtSecret);
    } catch (Exception $e) {
      http_response_code(401);
      echo json_encode(['error' => 'Unauthorized: ' . $e->getMessage()]);
      exit;
    }
  }

  private static function isPublicRoute(): bool {
    if ($_ENV['APP_ENV'] === 'test')
      return true;

    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    return in_array($path, self::$publicRoutes[$method] ?? [], true);
  }
}
