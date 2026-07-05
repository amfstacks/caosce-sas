<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Find user by username (used during login)
    public function findByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    // Create a new user (Subadmin or Examiner)
    public function create($data) {
        $this->db->query('INSERT INTO users (id, school_id, role, username, password_hash, raw_password, full_name) VALUES (:id, :school_id, :role, :username, :password_hash, :raw_password, :full_name)');
        
        // Generate UUID v4 instantly
        $this->db->bind(':id', UuidHelper::v4());
        $this->db->bind(':school_id', $data['school_id']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password_hash', password_hash($data['raw_password'], PASSWORD_DEFAULT));
        $this->db->bind(':raw_password', $data['raw_password']);
        $this->db->bind(':full_name', $data['full_name']);

        return $this->db->execute();
    }
}