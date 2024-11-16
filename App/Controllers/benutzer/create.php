<?php

use App\Core\DatabaseConnection;
use App\Core\Response;

// Get the database connection
$db = DatabaseConnection::getDatabase();

// Parse input JSON
$input = json_decode(file_get_contents('php://input'), true);

// Extract and validate inputs
$username = htmlspecialchars($input['username'] ?? '', ENT_QUOTES, 'UTF-8');

// Check required fields
$errors = [];
if (empty($username)) {
    $errors['username'] = 'Username is required.';
}
if (!empty($errors)) {
    Response::json([
        'status' => 'error',
        'message' => 'Invalid input',
        'data' => $errors
    ], Response::BAD_REQUEST);
    exit;
}

// Generate and hash API key
$apiKey = bin2hex(random_bytes(32));  // The actual API key the user will receive
$hashedApiKey = password_hash($apiKey, PASSWORD_DEFAULT); // Hashed version for storage

try {
    // Insert the new user with the hashed API key
    $query = 'INSERT INTO tbl_benutzer (username, api_key) VALUES (:username, :api_key)';
    $stmt = $db->query($query);
    $stmt->execute([
        ':username' => $username,
        ':api_key' => $hashedApiKey
    ]);

    // Success response with only the plain API key
    Response::json([
        'status' => 'success',
        'message' => 'User created successfully!',
        'data' => [
            'username' => $username,
            'api_key' => $apiKey  // Return only the plain API key here
        ]
    ], Response::CREATED);
} catch (Exception $e) {
    // Handle unexpected errors with a generic error message
    Response::json([
        'status' => 'error',
        'message' => 'An error occurred while creating the user.'
    ], Response::SERVER_ERROR);
}
