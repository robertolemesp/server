<?php

namespace Infrastructure\Api\Auth\Seeder;

use Exception;

class AuthUserSeeder {
  private const TOKEN_FILE_PATH = __DIR__ . '/../test_jwt_token.txt';

  public function seed(): void {
    $email = 'test_user@seeding.com';
    $sub = '9cae11af-cb68-4022-b0fe-120f8ae3614b';
    $secret = $_ENV['NEXTAUTH_SECRET'] ?? '';

    if (empty($secret)) {
      throw new Exception('NEXTAUTH_SECRET is not set.');
    }

    $payload = [
      'email' => $email,
      'sub' => $sub,
      'iat' => time(),
    ];

    $token = self::generateJWT($payload, $secret);

    self::saveTokenToFile($token, self::TOKEN_FILE_PATH);
  }

  private static function generateJWT(array $payload, string $secret): string {
    $header = [
      'alg' => 'HS256',
      'typ' => 'JWT'
    ];

    $headerB64 = self::base64UrlEncode(json_encode($header));
    $payloadB64 = self::base64UrlEncode(json_encode($payload));

    $data = $headerB64 . '.' . $payloadB64;

    $signature = hash_hmac('sha256', $data, $secret, true);
    $signatureB64 = self::base64UrlEncode($signature);

    return $headerB64 . '.' . $payloadB64 . '.' . $signatureB64;
  }

  private static function saveTokenToFile(string $token, string $path): void {
    if (!file_put_contents($path, $token)) 
      throw new Exception("Failed to write token file.");
  }

  private static function base64UrlEncode(string $data): string {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
  }
}
