<?php

namespace App\Core;

use PDO;

abstract class Model {
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function all(): array {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    public function find($id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function where(string $column, $value): array {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = :val");
        $stmt->execute(['val' => $value]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, array $data): bool {
        $fields = '';
        foreach ($data as $key => $val) {
            $fields .= "{$key} = :{$key}, ";
        }
        $fields = rtrim($fields, ', ');
        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET {$fields} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id): bool {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }
}
