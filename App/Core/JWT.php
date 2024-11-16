<?php

namespace App\Core;

class JWT
{
    private static string $secret;

    /**
     * Initialize JWT configuration
     */
    public static function init()
    {
        $config = require base_path('App/Core/config.php');
        self::$secret = $config['jwt']['secret_key'];
    }

    /**
     * Encodes a payload to generate a JWT token
     *
     * @param array $payload The payload to encode (e.g., user data and claims)
     * @param string $secret The secret key for signing the token
     * @param string $algorithm The algorithm used for signing (default is HS256)
     * @return string The generated JWT token
     */
    public static function encode(array $payload, string $algorithm = 'HS256'): string
    {
        if (empty(self::$secret)) {
            self::init();
        }

        // Define the supported algorithms and their corresponding hash algorithms
        $supported_algs = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];

        // Check if the selected algorithm is supported
        if (!isset($supported_algs[$algorithm])) {
            Response::json([
                'status' => 'error',
                'message' => 'Unsupported algorithm'
            ], Response::SERVER_ERROR);
            return '';
        }

        // Create the header with the algorithm information
        $header = [
            'alg' => $algorithm,
            'typ' => 'JWT',
        ];

        // Base64Url encode the header and payload
        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        // Create the signature using the specified algorithm and secret key
        $signature = hash_hmac($supported_algs[$algorithm], "$base64UrlHeader.$base64UrlPayload", self::$secret, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        // Concatenate the parts to form the final token
        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    /**
     * Decodes and verifies a JWT token
     *
     * @param string $token The JWT token to decode
     * @param string $secret The secret key used to verify the token
     * @param string $algorithm The algorithm used for signing (default is HS256)
     * @return array The decoded payload if valid, otherwise an error response
     */
    public static function decode(string $token, string $algorithm = 'HS256'): array
    {
        // Load secret key from config
        if (empty(self::$secret)) {
            self::init();
        }

        // Split the token into header, payload, and signature
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            Response::json([
                'status' => 'error',
                'message' => 'Invalid token format'
            ], Response::BAD_REQUEST);
            return [];
        }
    
        [$base64UrlHeader, $base64UrlPayload, $base64UrlSignature] = $parts;
    
        // Decode header and payload
        $header = json_decode(self::base64UrlDecode($base64UrlHeader), true);
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
    
        // Validate algorithm
        if ($header['alg'] !== $algorithm) {
            Response::json([
                'status' => 'error',
                'message' => 'Algorithm not allowed'
            ], Response::FORBIDDEN);
            return [];
        }
    
        // Verify the signature
        $supported_algs = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];
        // Verify the signature
        $expectedSignature = self::base64UrlEncode(hash_hmac($supported_algs[$algorithm], "$base64UrlHeader.$base64UrlPayload", self::$secret, true));
        if ($base64UrlSignature !== $expectedSignature) {
            Response::json([
                'status' => 'error',
                'message' => 'Invalid token signature'
            ], Response::UNAUTHORIZED);
            return [];
        }
    
        // Check for expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            Response::json([
                'status' => 'error',
                'message' => 'Token has expired'
            ], Response::UNAUTHORIZED);
            return [];
        }
    
        return $payload;
    }
    
    /**
     * Base64 URL encode a string
     *
     * @param string $data The data to encode
     * @return string The base64 URL encoded string
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL decode a string
     *
     * @param string $data The data to decode
     * @return string The decoded string
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
