<?php
declare(strict_types=1);

namespace App\Core;

/** Lightweight session identity helper used by customer and admin controllers. */
final class Auth
{
	public static function check(): bool
	{
		return (int)($_SESSION['fixmate_user_id'] ?? 0) > 0;
	}

	public static function id(): int
	{
		return (int)($_SESSION['fixmate_user_id'] ?? 0);
	}

	public static function role(): string
	{
		return (string)($_SESSION['fixmate_user_role'] ?? '');
	}

	public static function user(): ?array
	{
		if (!self::check()) {
			return null;
		}

		return [
			'id'    => self::id(),
			'name'  => (string)($_SESSION['fixmate_user_name'] ?? ''),
			'email' => (string)($_SESSION['fixmate_user_email'] ?? ''),
			'role'  => (string)($_SESSION['fixmate_user_role'] ?? ''),
		];
	}

	public static function login(array $user): void
	{
		if (session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		session_regenerate_id(true);
		$_SESSION['fixmate_user_id'] = (int)($user['id'] ?? 0);
		$_SESSION['fixmate_user_name'] = (string)($user['name'] ?? '');
		$_SESSION['fixmate_user_email'] = (string)($user['email'] ?? '');
		$_SESSION['fixmate_user_role'] = (string)($user['role'] ?? '');
	}

	public static function logout(): void
	{
		$_SESSION = [];

		if (session_status() === PHP_SESSION_ACTIVE) {
			$params = session_get_cookie_params();
			if (ini_get('session.use_cookies')) {
				setcookie(session_name(), '', [
					'expires'  => time() - 42000,
					'path'     => $params['path'],
					'domain'   => $params['domain'],
					'secure'   => $params['secure'],
					'httponly' => $params['httponly'],
					'samesite' => $params['samesite'] ?? 'Lax',
				]);
			}
			session_destroy();
		}
	}
}
