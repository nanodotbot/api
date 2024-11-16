<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function get($uri, $controller){
        $this->add($uri, $controller, 'GET');
    }
    public function post($uri, $controller){
        $this->add($uri, $controller, 'POST');
    }
    public function put($uri, $controller){
        $this->add($uri, $controller, 'PUT');
    }
    public function patch($uri, $controller){
        $this->add($uri, $controller, 'PATCH');
    }
    public function delete($uri, $controller){
        $this->add($uri, $controller, 'DELETE');
    }
    protected function add($uri, $controller, $method){
        // Convert dynamic segments to regex with named capture groups
        $uri = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[^/]+)', $uri);
        $uri = '#^' . $uri . '$#';

        $this->routes[] = [
            'uri' => $uri,
            'method' => $method,
            'controller' => $controller
        ];
    }

    public function route($uri, $method){
        // dd($this->routes);
        foreach ($this->routes as $route) {
            // dd($uri);
            if (preg_match($route['uri'], $uri, $matches) && $route['method'] === strtoupper($method)) {
                // Filter only named parameters from $matches
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Include the controller and pass the $params array
                return require base_path($route['controller']);
            }
        }
        // Send a JSON response if the route is not found
        Response::json([
            'status' => 'error',
            'message' => 'Not Found'
        ], Response::NOT_FOUND);
    }
}
