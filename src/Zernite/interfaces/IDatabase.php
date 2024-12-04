<?php

namespace Zernite\interfaces;

use PDO;
use PDOStatement;

interface IDatabase {
    public function connect(): PDO;
    public function query(string $sql, array $params = []): PDOStatement;
    public function fetchAll(string $sql, array $params = []): array;
    public function fetchOne(string $sql, array $params = []): array;
    public function insert(string $table, array $data): bool;
    public function update(string $table, array $data, string $condition, array $params): bool;
    public function delete(string $table, string $condition, array $params): bool;
}