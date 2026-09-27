<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

final class Database
{
	private static ?self $instance = null;
	private PDO $pdo;

	private function __construct()
	{
		$host = getenv('FIXMATE_DB_HOST') ?: '127.0.0.1';
		$port = getenv('FIXMATE_DB_PORT') ?: '3306';
		$name = getenv('FIXMATE_DB_NAME') ?: 'fixmate_db';
		$user = getenv('FIXMATE_DB_USER') ?: 'root';
		$pass = getenv('FIXMATE_DB_PASS') ?: '';
		$charset = getenv('FIXMATE_DB_CHARSET') ?: 'utf8mb4';
		$dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

		$this->pdo = new PDO($dsn, $user, $pass, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => true,
		]);
	}

	public static function getInstance(): self
	{
		return self::$instance ??= new self();
	}

	public function fetch(string $sql, array $params = []): ?array
	{
		$row = $this->statement($sql, $params)->fetch();
		return $row === false ? null : $row;
	}

	public function fetchAll(string $sql, array $params = []): array
	{
		return $this->statement($sql, $params)->fetchAll();
	}

	public function run(string $sql, array $params = []): PDOStatement
	{
		return $this->statement($sql, $params);
	}

	public function lastInsertId(): string|false
	{
		return $this->pdo->lastInsertId();
	}

	public function getConnection(): PDO
	{
		return $this->pdo;
	}

	public function beginTransaction(): bool
	{
		return $this->pdo->beginTransaction();
	}

	public function commit(): bool
	{
		return $this->pdo->commit();
	}

	public function rollBack(): bool
	{
		return $this->pdo->rollBack();
	}

	private function statement(string $sql, array $params): PDOStatement
	{
		$statement = $this->pdo->prepare($sql);
		$statement->execute($params);
		return $statement;
	}
}
