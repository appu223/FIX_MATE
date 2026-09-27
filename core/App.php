<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

final class App
{
    /** @var array<string, array<string, callable|array{class-string, string}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    public function run(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $basePath = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        if ($basePath !== '' && ($requestPath === $basePath || str_starts_with($requestPath, $basePath . '/'))) {
            $requestPath = substr($requestPath, strlen($basePath)) ?: '/';
        }
        $requestPath = $this->normalizePath($requestPath);

        if (!isset($this->routes[$method][$requestPath])) {
            http_response_code(404);
            echo '404 - Page not found';
            return;
        }

        try {
            $handler = $this->routes[$method][$requestPath];
            if (is_array($handler) && isset($handler[0], $handler[1]) && is_string($handler[0])) {
                $controller = new $handler[0]();
                $action = $handler[1];
                if (!is_callable([$controller, $action])) {
                    throw new \RuntimeException('The requested route handler is unavailable.');
                }
                $controller->{$action}();
                return;
            }

            $handler();
        } catch (Throwable $exception) {
            error_log((string)$exception);
            http_response_code(500);
            echo '500 - The request could not be completed. Check the PHP error log and database configuration.';
        }
    }

    private function normalizePath(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
