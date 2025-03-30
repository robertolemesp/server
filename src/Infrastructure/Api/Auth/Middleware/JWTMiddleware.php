<?php

namespace Infrastructure\Api\Auth\Middleware;

use Exception;
use InvalidArgumentException;

class JWTMiddleware {

  public static function decode(string $token, string $secret): array {
    $parts = explode('.', $token);

    if (count($parts) !== 3) 
      throw new InvalidArgumentException('Invalid token format');

    [$headerB64, $payloadB64, $signatureB64] = $parts;

    $header = json_decode(self::base64UrlDecode($headerB64), true);
    $payload = json_decode(self::base64UrlDecode($payloadB64), true);
    $signature = self::base64UrlDecode($signatureB64);

    if (!$header || !$payload) 
      throw new Exception('Failed to decode JWT');

    if ($header['alg'] !== 'HS256' || $header['typ'] !== 'JWT') 
      throw new Exception('Unsupported algorithm or type');

    $data = $headerB64 . '.' . $payloadB64;
    $computedSignature = hash_hmac('sha256', $data, $secret, true);

    if (!hash_equals($computedSignature, $signature)) 
      throw new Exception('Invalid signature');

    if (isset($payload['exp']) && $payload['exp'] < time()) 
      throw new Exception('Token expired');

    return $payload;
  }

  private static function base64UrlDecode(string $data): string {
    $remainder = strlen($data) % 4;
    if ($remainder) 
      $data .= str_repeat('=', 4 - $remainder);

    return base64_decode(strtr($data, '-_', '+/'));
  }
}
