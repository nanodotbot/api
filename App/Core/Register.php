<?php

namespace App\Core;

class Register
{
    /**
     * Handle user registration.
     *
     * @param string $username The username of the new user.
     * @param string $email The email of the new user.
     * @param string $password The plain text password.
     * @return void
     */
    public static function registerUser($username, $email, $password)
    {
        // Get the database connection
        $db = DatabaseConnection::getDatabase();

        // Check if the email already exists
        $stmt = $db->query('SELECT id FROM tbl_users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $existingUser = $stmt->fetch();

        // If the email is taken, respond with an error
        if ($existingUser) {
            Response::json([
                'status' => 'error',
                'message' => 'Email is already taken.'
            ], Response::BAD_REQUEST);
            return;
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert the new user into the database
        $stmt = $db->query('INSERT INTO tbl_users (username, email, password, role) VALUES (:username, :email, :password, :role)');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':role' => 'user'  // Default user role
        ]);

        // If the insert was successful, respond with a success message
        if ($stmt->rowCount() > 0) {
            Response::json([
                'status' => 'success',
                'message' => 'User registered successfully.'
            ], Response::CREATED);
        } else {
            // If something went wrong, respond with an error
            Response::json([
                'status' => 'error',
                'message' => 'Failed to register user.'
            ], Response::SERVER_ERROR);
        }
    }
}
