<?php

namespace App\Core;

class Login
{
    public static function authenticate($username, $password)
    {
        // Get the database connection
        $db = DatabaseConnection::getDatabase();

        // Query for the user by username
        $stmt = $db->query('SELECT id, password, role FROM tbl_users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Check if the user exists and the password is correct
        if (!$user || !password_verify($password, $user['password'])) {
            Response::json([
                'status' => 'error',
                'message' => 'Invalid username or password.'
            ], Response::UNAUTHORIZED);
            return;
        }

        // Create JWT payload with the user ID and other claims
        $payload = [
            'sub' => $user['id'],              // subject (user ID)
            'role' => $user['role'],           // user role (admin, user, etc.)
            'iat' => time(),                   // issued at
            'exp' => time() + 3600             // expiration (1 hour)
        ];

        // Encode the payload to get the JWT
        $jwt = JWT::encode($payload);

        // Respond with the token
        Response::json([
            'status' => 'success',
            'message' => 'Login successful.',
            'token' => $jwt
        ], Response::OK);
    }
}
