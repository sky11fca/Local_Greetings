<?php

class JWT {
    private static $secret_key = APP_SECRET_KEY; // Use global constant
    private static $algorithm = 'HS256';

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public static function generate($data) {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $base64UrlHeader = self::base64UrlEncode($header);
        
        $payload = json_encode([
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24), // 24 hours
            'data' => $data
        ]);
        $base64UrlPayload = self::base64UrlEncode($payload);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validate($token) {
        if (!$token) return null;

        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
        
        $payload = self::base64UrlDecode($base64UrlPayload);
        $decodedPayload = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) return null;

        if (empty($decodedPayload['exp']) || $decodedPayload['exp'] < time()) {
            return null; // Token is expired
        }
        
        $header = self::base64UrlDecode($base64UrlHeader);
        $decodedHeader = json_decode($header, true);

        if (empty($decodedHeader['alg']) || $decodedHeader['alg'] !== self::$algorithm) {
            return null; // Algorithm mismatch
        }

        $expectedSignature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $expectedBase64UrlSignature = self::base64UrlEncode($expectedSignature);
        
        if (!hash_equals($expectedBase64UrlSignature, $base64UrlSignature)) {
            return null; // Signature verification failed
        }

        return $decodedPayload['data'];
    }
}