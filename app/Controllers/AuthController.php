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

        // Helper function to check if user belongs to the slug in the URL
        $validateTenant = function($userSchoolId) {
            if (CURRENT_TENANT_SLUG !== null) {
                $this->db->query("SELECT id FROM schools WHERE slug = :slug");
                $this->db->bind(':slug', CURRENT_TENANT_SLUG);
                $school = $this->db->single();
                
                // If the URL slug doesn't match the user's actual school, reject them
                if ($school && $school['id'] !== $userSchoolId && $userSchoolId !== null) {
                    return false;
                }
            }
            return true;
        };

        // 1. ADMIN ROUTING
        $this->db->query("SELECT * FROM users WHERE username = :username AND role IN ('superadmin', 'subadmin')");
        $this->db->bind(':username', $username);
        $admin = $this->db->single();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            if (!$validateTenant($admin['school_id'])) return json_encode(['success' => false, 'message' => 'Invalid school portal.']);
            return json_encode(['success' => true, 'redirect_url' => '/admin/dashboard']);
        }

        // --- SECURITY GATE: For roles below, a device signature MUST be present ---
        if (!$deviceSig) {
            return json_encode(['success' => false, 'message' => 'Access Denied: This device has not been bound by an Administrator.']);
        }

        $this->db->query("
            SELECT db.*, s.station_type, s.order_sequence 
            FROM device_bindings db
            JOIN stations s ON db.station_id = s.id
            WHERE db.device_signature = :sig
        ");
        $this->db->bind(':sig', $deviceSig);
        $binding = $this->db->single();

        if (!$binding) return json_encode(['success' => false, 'message' => 'Access Denied: Invalid device signature.']);

        // 2. EXAMINER ROUTING
        $this->db->query("SELECT * FROM users WHERE username = :username AND role = 'examiner'");
        $this->db->bind(':username', $username);
        $examiner = $this->db->single();

        if ($examiner && password_verify($password, $examiner['password_hash'])) {
            if (!$validateTenant($examiner['school_id'])) return json_encode(['success' => false, 'message' => 'Invalid school portal.']);
            if ($binding['examiner_id'] !== $examiner['id']) return json_encode(['success' => false, 'message' => 'Not assigned to this laptop.']);
            
            if ($binding['examiner_id'] !== $examiner['id']) {
                // Login is successful, but they are at the wrong physical laptop
                return json_encode(['success' => true, 'redirect_url' => '/examiner/mismatch']);
            }
            return json_encode(['success' => true, 'redirect_url' => '/examiner/rubric?station_id=' . $binding['station_id']]);
        }

        // 3. STUDENT ROUTING
        $this->db->query("SELECT * FROM students WHERE matric_number = :username");
        $this->db->bind(':username', $username);
        $student = $this->db->single();

        if ($student && password_verify($password, $student['password_hash'])) {
            if (!$validateTenant($student['school_id'])) return json_encode(['success' => false, 'message' => 'Invalid school portal.']);
            
            $this->db->query("SELECT * FROM exam_session_student WHERE student_id = :sid AND exam_session_id = :eid");
            $this->db->bind(':sid', $student['id']);
            $this->db->bind(':eid', $binding['exam_session_id']);
            if (!$this->db->single()) return json_encode(['success' => false, 'message' => 'Not enrolled in this active exam session.']);

            if ($binding['station_type'] === 'cbt') {
                return json_encode(['success' => true, 'redirect_url' => '/student/cbt?station_id=' . $binding['station_id']]);
            } else {
                return json_encode(['success' => true, 'redirect_url' => '/student/procedure_standby?station_id=' . $binding['station_id']]);
            }
        }

        return json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }

    public function getTenantInfo($inputData = null) {
    if (!defined('CURRENT_TENANT_SLUG') || CURRENT_TENANT_SLUG === null) {
        return json_encode(['success' => false, 'message' => 'No workspace specified.']);
    }
// sleep(10);
    $this->db->query("SELECT name, logo_path ,cover_image_path FROM schools WHERE slug = :slug ");
    $this->db->bind(':slug', CURRENT_TENANT_SLUG);
    $school = $this->db->single();

    if ($school) {
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

        // 2. Concatenate the Cover Image path if a filename exists
        if (!empty($school['cover_image_path'])) {
            $school['cover_image_path'] = $baseDir . '/uploads/' . CURRENT_TENANT_SLUG . '/' . $school['cover_image_path'];
        }

        // 3. Concatenate the Logo path if a filename exists
        if (!empty($school['logo_path'])) {
            $school['logo_path'] = $baseDir . '/uploads/' . CURRENT_TENANT_SLUG . '/' . $school['logo_path'];
        }
        return json_encode(['success' => true, 'payload' => $school]);
    }

    return json_encode(['success' => false, 'message' => 'School not found or license inactive.']);
}
}
?>