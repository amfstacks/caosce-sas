<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database Handler
    private $stmt;

    public function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true, // Keeps connection open for faster sequential queries
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Always fetch as associative array
        ];

        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Log error internally, never expose to user
            die("Database Connection Error."); 
        }
    }

    // Prepare statement with query
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Bind values dynamically
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value): $type = PDO::PARAM_INT; break;
                case is_bool($value): $type = PDO::PARAM_BOOL; break;
                case is_null($value): $type = PDO::PARAM_NULL; break;
                default: $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Execute the prepared statement
    public function execute() {
        return $this->stmt->execute();
    }

    // Get multiple records as an array of objects/arrays
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Get single record
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    // --- Transaction Wrapper Methods ---
    
    // Begin a transaction
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    // Commit a transaction
    public function commit() {
        return $this->dbh->commit();
    }

    // Rollback a transaction
    public function rollBack() {
        return $this->dbh->rollBack();
    }
}