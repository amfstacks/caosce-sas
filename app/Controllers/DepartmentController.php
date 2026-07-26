<?php
class DepartmentController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function getSchoolId() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['school_id'] ?? null;
    }

    // --- FETCH DEPARTMENTS ---
    public function getDepartments($inputData = null) {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) return json_encode(['success' => false, 'message' => 'Unauthorized']);

        $this->db->query("SELECT id, name, dept_code, status, DATE_FORMAT(created_at, '%b %d, %Y') as formatted_date FROM departments WHERE school_id = :school_id ORDER BY created_at DESC");
        $this->db->bind(':school_id', $schoolId);
        $departments = $this->db->resultSet();

        return json_encode(['success' => true, 'payload' => $departments]);
    }

    // --- ADD SINGLE DEPARTMENT ---
    public function addDepartment($inputData) {
        $schoolId = $this->getSchoolId();
        
        $name = trim($inputData['name'] ?? '');
        $deptCode = trim($inputData['dept_code'] ?? '');

        if (empty($name) || empty($deptCode)) {
            return json_encode(['success' => false, 'message' => 'Department name and code are required.']);
        }

        // Validate ENUM strictly based on your SQL schema
        if (!in_array($deptCode, ['ns', 'mw'])) {
            return json_encode(['success' => false, 'message' => 'Invalid department code selected.']);
        }

        // Prevent exact duplicates within the same school
        $this->db->query("SELECT id FROM departments WHERE school_id = :sch AND (name = :name AND dept_code = :code)");
        $this->db->bind(':sch', $schoolId);
        $this->db->bind(':name', $name);
        $this->db->bind(':code', $deptCode);
        if ($this->db->single()) {
            return json_encode(['success' => false, 'message' => 'A department with this name or code already exists in your workspace.']);
        }

        $deptId = UuidHelper::v4();

        $this->db->query("INSERT INTO departments (id, school_id, name, dept_code, status) VALUES (:id, :sch, :name, :code, 'active')");
        $this->db->bind(':id', $deptId);
        $this->db->bind(':sch', $schoolId);
        $this->db->bind(':name', $name);
        $this->db->bind(':code', $deptCode);

        if ($this->db->execute()) {
            return json_encode(['success' => true, 'message' => 'Department added successfully.']);
        }
        return json_encode(['success' => false, 'message' => 'Failed to add department.']);
    }

    // --- TOGGLE STATUS (Disable/Enable) ---
    public function toggleStatus($inputData) {
        $id = $inputData['id'] ?? '';
        $this->db->query("UPDATE departments SET status = IF(status = 'active', 'disabled', 'active') WHERE id = :id AND school_id = :sch");
        $this->db->bind(':id', $id);
        $this->db->bind(':sch', $this->getSchoolId());
        
        if ($this->db->execute()) return json_encode(['success' => true, 'message' => 'Department status updated.']);
        return json_encode(['success' => false, 'message' => 'Action failed.']);
    }
}
?>