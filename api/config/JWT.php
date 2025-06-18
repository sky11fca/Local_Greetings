<?php
class JWT{
    private static $secretKey = "4kL/QMq4iO99vYxhetbVh+uu606R+DzJu1j+yAqb5iQ=";
    private static $algorithm = "HS256";

    public static function generate(array $payload, int $expiry = 3600): string
    {
     $header = json_encode([
         'typ' => 'JWT',
         'alg' => self::$algorithm,
     ]);
     $payload['iat'] = time();
     $payload['exp'] = time() + $expiry;

     $base64UrlHeader = self::base64UrlEncode($header);
     $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

     $signature = hash_hmac('sha256', $base64UrlHeader, self::$secretKey, true);
     $base64UrlSignature = self::base64UrlEncode($signature);

     return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validate(string $token): ?array {
        $parts = explode(".", $token);

        if(count($parts)!==3){
            return null;
        }

        list($header, $payload, $signature) = $parts;

        $expectedSignature = self::base64UrlEncode(
            hash_hmac('sha256', $header . "." . $payload, self::$secretKey, true)
        );

        if(!hash_equals($signature, $expectedSignature)){
            return null;
        }

        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);

        if(isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()){
            return null;
        }

        return $decodedPayload;
    }

    private static function base64UrlEncode(string $data) : string{
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
    private static function base64UrlDecode(string $data): string {
        $padded = str_pad($data, strlen($data) % 4, '=', STR_PAD_RIGHT);
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $padded));
    }
}