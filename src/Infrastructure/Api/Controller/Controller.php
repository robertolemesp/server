<?php
namespace Infrastructure\Api\Controller;

use ReflectionClass;

abstract class Controller {
  protected function jsonResponse(mixed $data, int $statusCode): void {
    header('Content-Type: application/json');
    http_response_code($statusCode);

    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK;

    if (is_bool($data) || is_numeric($data)) 
      echo json_encode($data, $jsonFlags);
    elseif (is_array($data)) {
      $data = array_map(fn($item) => is_object($item) ? $this->objectToArray($item) : $item, $data);
      echo json_encode($data, $jsonFlags);
    }
    else 
      echo json_encode(['message' => (string) $data], $jsonFlags);

    exit;
  }

  protected function objectToArray(object $obj): array {
    $reflectionClass = new ReflectionClass($obj);
    $properties = $reflectionClass->getProperties();
    $array = [];

    foreach ($properties as $property) {
      $property->setAccessible(true);
      $value = $property->getValue($obj);

      if ($value instanceof \DateTime) 
        $value = $value->format('Y-m-d');

      $array[$property->getName()] = $value;
    }

    return $array;
  }
}
