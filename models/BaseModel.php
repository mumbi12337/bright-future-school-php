<?php
require_once dirname(__DIR__) . '/includes/db.php';

abstract class BaseModel {
    protected $pdo;
    protected $table;
    
    public function getPdo() {
        return $this->pdo;
    }
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Find all records
     */
    public function findAll() {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Find record by ID
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create a new record
     */
    public function create($data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ({$placeholders}) RETURNING id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update a record
     */
    public function update($id, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $setClause = implode(' = ?, ', $columns) . ' = ?';
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = ?";
        $values[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Delete a record
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Find records by conditions
     */
    public function findBy($conditions) {
        $whereClause = [];
        $values = [];
        
        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = ?";
            $values[] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        
        return $stmt->fetchAll();
    }
}
?>