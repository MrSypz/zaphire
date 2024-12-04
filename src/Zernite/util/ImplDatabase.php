<?php

namespace Zernite\util;

use PDO;
use PDOException;
use PDOStatement;
use Zernite\config\DatabaseConfig;
use Zernite\interfaces\IDatabase;

class ImplDatabase implements IDatabase {
    private string $host;
    private string $dbName;
    private string $username;
    private string $password;
    private ?PDO $conn = null;

    public function __construct() {
        $this->host = DatabaseConfig::getHost();
        $this->dbName = DatabaseConfig::getDatabase();
        $this->username = DatabaseConfig::getUser();
        $this->password = DatabaseConfig::getPassword();
        $this->connect();
    }

    public function connect(): PDO {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->username, $this->password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                throw new \Exception("Database connection error: ".$e->getMessage());
            }
        }

        return $this->conn;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchOne(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetch() ?: [];
    }

    public function insert(string $table, array $data): bool {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($key) => ":$key", array_keys($data)));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->query($sql, $data);

        return $stmt->rowCount() > 0;
    }
    public function insertandId(string $table, array $data): int|false {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($key) => ":$key", array_keys($data)));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $stmt = $this->query($sql, $data);

        if ($stmt->rowCount() > 0) {
            return (int) $this->conn->lastInsertId();
        }
        return false;
    }


    public function update(string $table, array $data, string $condition, array $params): bool {
        $setClause = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
        $sql = "UPDATE $table SET $setClause WHERE $condition";

        return $this->query($sql, array_merge($data, $params))->rowCount() > 0;
    }

    public function delete(string $table, string $condition, array $params): bool {
        $sql = "DELETE FROM $table WHERE $condition";

        return $this->query($sql, $params)->rowCount() > 0;
    }
}
