<?php
// Add CORS headers to allow requests from other origins (modify as needed for production)
// Allow all origins (wildcard)
header("Access-Control-Allow-Origin: *");
// Allow specific methods (GET, POST, PUT, DELETE, OPTIONS)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// Allow specific headers (including Authorization)
header("Access-Control-Allow-Headers: Authorization, Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

use App\Core\Login;
use App\Core\Register;
use App\Core\Router;
use App\Core\Authenticator;

const BASE_PATH = __DIR__ . "/../";

require str_replace('/', DIRECTORY_SEPARATOR, BASE_PATH  . 'App/Core/functions.php');

spl_autoload_register(function($class){
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require base_path("$class.php");
});

// Extracts the URL path without the query string
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Handle the login route separately (no token needed)
if ($path === '/login' && $method === 'POST') {
    // Parse the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    
    // Perform authentication
    Login::authenticate($username, $password);
    exit;
}

// Handle the register route
if ($path === '/register' && $method === 'POST') {
    // Parse the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $username = $input['username'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    // Call the Register class to handle user creation
    Register::registerUser($username, $email, $password);
    exit;
}


// JWT
// Get the Authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
// Authenticate the request
$decodedToken = Authenticator::validateToken($authHeader);
// Stop further processing if authorization fails
if (!$decodedToken) {
    exit; // Prevent further processing if unauthorized
}


// Initialize router and load routes
$router = new Router();
$routes = require base_path('App/Core/routes.php');

// Route the request
$router->route($path, $method);
