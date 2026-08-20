<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function __construct(private readonly string $basePath = ''){
    }

    public function get(string $path, callable|array $handler): void {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void {
        $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, callable|array $handler): void{
        $normalizedPath = $path !== '/' ? rtrim($path, '/') : '/';
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $normalizedPath,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void{
    $method = strtoupper($method);
    $path = rawurldecode(parse_url($uri, PHP_URL_PATH) ?: '/');
    $path = $path !== '/' ? rtrim($path, '/') : '/';

    $basePath = rtrim($this->basePath, '/');
    if ($basePath !== '') {
        if ($path === $basePath) {
            $path = '/';
        } elseif (str_starts_with($path, $basePath . '/')) {
            $path = substr($path, strlen($basePath));
            $path = $path === '' ? '/' : $path;
        }
    }

    $pathMatchedWithAnotherMethod = false;

    foreach ($this->routes as $route) {
        $regex = preg_replace_callback(
            '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
            static fn (array $matches): string => '(?P<' . $matches[1] . '>[^/]+)',
            $route['path']
        );

        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches) !== 1) {
            continue;
        }

        if ($route['method'] !== $method) {
            $pathMatchedWithAnotherMethod = true;
            continue;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[] = $value;
            }
        }

        call_user_func_array($route['handler'], $params);
        return;
    }

    if ($pathMatchedWithAnotherMethod) {
        http_response_code(405);
        header('Allow: GET, POST');
        View::render('errors/405', ['title' => 'Método no permitido']);
        return;
    }
    
    http_response_code(404);
    View::render('errors/404', ['title' => 'Página no encontrada']);
    }
}

?>