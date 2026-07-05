<?php
class AuthController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleLogin($inputData) {
        $username = $inputData['username'];
        $password = $inputData['password'];
        $deviceSig = $inputData['device_signature'] ?? null;

        // 1. ADMIN ROUTING (Does not require a device signature)
        $this->db->query("SELECT * FROM users WHERE username = :username AND role IN ('superadmin', 'subadmin')");
        $this->db->bind(':username', $username);
        $admin = $this->db->single();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            return json_encode(['success' => true, 'redirect_url' => '/admin/dashboard']);
        }

        // --- SECURITY GATE: For all following roles, a device signature MUST be present in the DB ---
        if (!$deviceSig) {
            return json_encode(['success' => false, 'message' => 'Access Denied: This device has not been bound by an Administrator.']);
        }

        // Look up the device binding to see what this laptop is configured to do
        $this->db->query("
            SELECT db.*, s.station_type, s.order_sequence 
            FROM device_bindings db
            JOIN stations s ON db.station_id = s.id
            WHERE db.device_signature = :sig
        ");
        $this->db->bind(':sig', $deviceSig);
        $binding = $this->db->single();

        if (!$binding) {
             return json_encode(['success' => false, 'message' => 'Access Denied: Invalid or compromised device signature.']);
        }

        // 2. EXAMINER ROUTING
        $this->db->query("SELECT * FROM users WHERE username = :username AND role = 'examiner'");
        $this->db->bind(':username', $username);
        $examiner = $this->db->single();

        if ($examiner && password_verify($password, $examiner['password_hash'])) {
            // Verify this specific examiner is assigned to this specific laptop
            if ($binding['examiner_id'] !== $examiner['id']) {
                return json_encode(['success' => false, 'message' => 'You are not assigned to this specific station laptop.']);
            }
            return json_encode(['success' => true, 'redirect_url' => '/examiner/rubric?station_id=' . $binding['station_id']]);
        }

        // 3. STUDENT ROUTING
        $this->db->query("SELECT * FROM students WHERE matric_number = :username");
        $this->db->bind(':username', $username);
        $student = $this->db->single();

        if ($student && password_verify($password, $student['password_hash'])) {
            // Check if student is part of the active exam session bound to this device
            $this->db->query("SELECT * FROM exam_session_student WHERE student_id = :sid AND exam_session_id = :eid");
            $this->db->bind(':sid', $student['id']);
            $this->db->bind(':eid', $binding['exam_session_id']);
            $isEnrolled = $this->db->single();

            if (!$isEnrolled) {
                return json_encode(['success' => false, 'message' => 'You are not enrolled in the exam currently running on this device.']);
            }

            // Route based on hardware configuration
            if ($binding['station_type'] === 'cbt') {
                return json_encode(['success' => true, 'redirect_url' => '/student/cbt?station_id=' . $binding['station_id']]);
            } else {
                return json_encode(['success' => true, 'redirect_url' => '/student/procedure_standby?station_id=' . $binding['station_id']]);
            }
        }

        return json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
}
?>