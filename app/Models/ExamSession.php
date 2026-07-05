<?php
class ExamSession {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function create($data) {
        $sessionId = UuidHelper::v4();

        $this->db->query('INSERT INTO exam_sessions (id, school_id, department_id, title, scheduled_date) VALUES (:id, :school_id, :department_id, :title, :scheduled_date)');
        
        $this->db->bind(':id', $sessionId);
        $this->db->bind(':school_id', $data['school_id']);
        $this->db->bind(':department_id', $data['department_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':scheduled_date', $data['scheduled_date']);

        if ($this->db->execute()) {
            return $sessionId; // Return the UUID so we can generate the 6 stations next
        }
        return false;
    }
}