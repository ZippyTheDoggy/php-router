<?php

class Router {
    private array $routes = array();

    public function __construct() {
        $this->routes = array(
            'GET' => array(),
            'POST' => array()
        );
    }

    public function get($path, $handler) {
        if(sizeof(explode("?", $path)) > 1) {
            $path = explode("?", $path)[0];
        }
        $this->routes['GET'][strtolower($path)] = $handler;
    }
    public function post($path, $handler) {
        $this->routes['POST'][strtolower($path)] = $handler;
    }

    public function run() {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        $path = strtolower($_SERVER["REQUEST_URI"]);
        if(in_array($path, array_keys($this->routes[$method]))) {
            $handler = $this->routes[$method][$path];
            http_response_code(200);
            echo $handler([]);
        } else {
            if(sizeof(explode("?", $path))) {
                $newPath = explode("?", $path)[0];
                if (in_array($newPath, array_keys($this->routes[$method]))) {
                    $newHandler = $this->routes[$method][$newPath];
                    http_response_code(200);
                    $params = array();
                    foreach($_GET as $key => $value) {
                        $params[$key] = $value;
                    }
                    echo $newHandler($params);
                }
            } else {
                http_response_code(404);
                echo "404 Page Not Found";
            }
        }
    }
}

// add support for complex paths like /user/:id/profile

$app = new Router();

echo $app->get("/", function ($params) {
    return "/ : " . var_dump($params);
});
$app->get("/hi", function ($params) {
    return "/hi : " . var_dump($params);
});

echo $app->run();

?>