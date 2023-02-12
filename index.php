<?php

class Router {
    private array $routes = [];
    public function __construct() {
        $this->routes = [
            "GET" => [],
            "POST" => []
        ];
    }
    public function get($route, $handler) {
        $split = explode("/", strtolower($route));
        $mods = [];
        $idx = -1;
        foreach ($split as $part) {
            $idx++;
            if ($part == "" && $idx < 1)
                continue;
            if(str_starts_with($part, ":")) {
                array_push($mods, [1, substr($part, 1)]);
            } else {
                array_push($mods, [0, $part]);
            }
        }
        array_push($this->routes["GET"], ["mods" => $mods, "handler" => $handler]);
    }
    public function post($route, $handler) {
        $split = explode("/", strtolower($route));
        $mods = [];
        foreach ($split as $part) {
            if ($part == "")
                continue;
            if(str_starts_with($part, ":")) {
                array_push($mods, [1, substr($part, 1)]);
            } else {
                array_push($mods, [0, $part]);
            }
        }
        array_push($this->routes["POST"], ["mods" => $mods, "handler" => $handler]);
    }
    public function render($page, $variables, $folder = "views/") {
        $re = '/(?<!@){\s*([A-Za-z_$]+)\s*}/m';
        $re2 = "/@{\s*([A-Za-z_$]+)\s*}/m";
        $code = file_get_contents(__DIR__ . "/" . $folder . $page);
        $matches = null;
        preg_match_all($re, $code, $matches);
        $error = false;
        foreach ($matches[0] as $match) {
            $wanted = $match;
            if (str_starts_with($wanted, "{"))
                $wanted = substr($wanted, 1);
            if (str_ends_with($wanted, "}"))
                $wanted = substr($wanted, 0, -1);
            $wanted = trim($wanted);
            if (!array_key_exists($wanted, $variables)) {
                $error = true;
                throw new Exception("Variable $wanted not found in variables array for document $page");
            }
        }
        if(!$error) {
            $code = preg_replace_callback($re, function ($matches) use ($variables) {
                $wanted = $matches[0];
                if (str_starts_with($wanted, "{"))
                    $wanted = substr($wanted, 1);
                if (str_ends_with($wanted, "}"))
                    $wanted = substr($wanted, 0, -1);
                $wanted = trim($wanted);
                return $variables[$wanted];
            }, $code);
        }
        $code = preg_replace($re2, "{ $1 }", $code);
        return $code;
    }
    public function ensureParam($name, $container, $default) {
        return array_key_exists($name, $container) ? $container[$name] : $default;
    }
    public function run($method, $route, $debug = false) {
        $found = false;
        foreach ($this->routes["GET"] as $rt => ["mods" => $mods, "handler" => $handler]) {
            if (sizeof($mods) == 1 && $mods[0][1] == "404")
                $found = true;
        }
        if (!$found) {
            throw new Exception("404 page not found, please add a GET route for /404");
        }
        $method = strtoupper($method);
        $route = strtolower($route);
        $split = explode("/", $route);
        $poss = [];
        foreach ($this->routes[$method] as ["mods" => $mods, "handler" => $handler])
            if (sizeof($mods) == sizeof($split) - 1)
                array_push($poss, ["mods" => $mods, "handler" => $handler]);            
        if(sizeof($poss) < 1) {
            if ($debug)
                echo "Rerouting to /404, no poss.";
            return $this->run("GET", "/404", $debug);
        }
        foreach ($poss as $pos => ["mods" => $mods, "handler" => $handler]) {
            $matches = true;
            $params = [];
            $idx = 0;
            foreach ($mods as $mod => [$id, $section]) {
                $idx++;
                if ($id == 1) {
                    $params[$section] = $split[$idx];
                    $matches = $matches && true;
                    continue;
                }
                if ($id == 0 && $section == $split[$idx])
                    $matches = $matches && true;
                else {
                    if ($debug)
                        echo "In mod foreach, breaking match due to $section != $split[$idx]<br>";
                    $matches = false;
                    break;
                }
                
            }
            if($matches) {
                if ($debug) {
                    echo "Matches to ";
                    var_dump($mods);
                    echo "<br>";
                }
                return $handler($params, $_GET);
            } else {
                if ($debug)
                    echo "No match.<br>";
                continue;
            }
        }
        if ($debug)
            echo "End of run, going 404.<br>";
        return $this->run("GET", "/404", $debug);
    }
}

$app = new Router();

$app->get("/404", function ($pathParams, $getParams) {
    return "<h1>404: Page Not Found</h1>";
});

$app->get("/", function ($pathParams, $getParams) use ($app) {
    return $app->render("index.html", []);
});

$app->get("/users/:id/profile", function ($pathParams, $getParams) use ($app) {
    $id = $pathParams['id'];
    return $app->render("user_container.html", [
        "id" => $id,
    ]);
});

$app->get("/echo/:stuff", function ($pathParams, $getParams) use ($app) {
    $stuff = $pathParams['stuff'];
    return $app->render("echo.html", [
        "stuff" => $stuff,
    ]);
});

echo $app->run($_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]);