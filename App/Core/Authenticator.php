<?php

namespace App\Core;

class Authenticator
{
    /**
     * Validates the JWT token in the Authorization header and checks if it's valid.
     *
     * @param string $authHeader The Authorization header containing the Bearer token.
     * @return array The decoded token payload if valid, otherwise returns an error response.
     */
    public static function validateToken(string $authHeader): array
    {
        // Check if the Authorization header is provided
        if (empty($authHeader)) {
            Response::json([
                'status' => 'error',
                'message' => 'Authorization header missing',
                'headers_received' => self::getAllHeaders()  // Add headers for debugging
            ], Response::UNAUTHORIZED);
            return [];
        }

        // Extract the token from the Authorization header
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        } else {
            Response::json([
                'status' => 'error',
                'message' => 'Invalid token format. Bearer token required.'
            ], Response::UNAUTHORIZED);
            return null;
        }

        // Decode and verify the token
        $decoded = JWT::decode($token);

        // If decoding failed or the token is invalid, respond with an error
        if (empty($decoded)) {
            Response::json([
                'status' => 'error',
                'message' => 'Invalid or expired token'
            ], Response::UNAUTHORIZED);
            return null;
        }

        // Optionally, additional user authorization checks can go here, e.g., user roles or permissions.

        // Return the decoded payload if valid
        return $decoded;
    }

    /**
     * Verifies if the user has the required role for access to a specific resource.
     *
     * @param array $decoded The decoded JWT payload.
     * @param string $requiredRole The required role for access.
     * @return bool True if the user has the required role, otherwise false.
     */
    public static function hasRole(array $decoded, string $requiredRole): bool
    {
        // Check if the decoded token contains a role field
        return isset($decoded['role']) && $decoded['role'] === $requiredRole;
    }
    
    /**
     * Helper function to retrieve all headers as an associative array.
     *
     * @return array
     */
    private static function getAllHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = str_replace('_', '-', substr($key, 5));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }
}
