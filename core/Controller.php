<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
	protected function get(string $key, mixed $default = null): mixed
	{
		return $_GET[$key] ?? $default;
	}

	protected function post(string $key, mixed $default = null): mixed
	{
		return $_POST[$key] ?? $default;
	}

	protected function view(string $view, array $data = [], string $layout = 'admin'): void
	{
		$root = dirname(__DIR__);
		$viewFile = $root . '/views/' . trim($view, '/') . '.php';
		$layoutFile = $root . '/views/layouts/' . basename($layout) . '.php';
		if (!is_file($viewFile) || !is_file($layoutFile)) {
			throw new \RuntimeException('View or layout file was not found.');
		}

		$baseUrl = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
		$pageTitle = $data['pageTitle'] ?? 'Fixmate Admin';
		extract($data, EXTR_SKIP);
		ob_start();
		try {
			require $viewFile;
			$viewContent = (string)ob_get_clean();
		} catch (\Throwable $exception) {
			ob_end_clean();
			throw $exception;
		}
		require $layoutFile;
	}

	protected function json(array $payload, int $statusCode = 200): never
	{
		http_response_code($statusCode);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
		exit;
	}

	protected function redirect(string $path): never
	{
		$baseUrl = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
		$target = str_starts_with($path, '/') ? $baseUrl . $path : $path;
		header('Location: ' . $target, true, 303);
		exit;
	}
}
