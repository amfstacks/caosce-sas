<?php
class AdminController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }
  public function getDashboardStats($inputData = null) {
        if (!defined('CURRENT_TENANT_SLUG') || CURRENT_TENANT_SLUG === null) {
            return json_encode(['success' => false, 'message' => 'Invalid workspace.']);
        }

        // 1. Get the School ID for the current tenant
        $this->db->query("SELECT id FROM schools WHERE slug = :slug");
        $this->db->bind(':slug', CURRENT_TENANT_SLUG);
        $school = $this->db->single();

        if (!$school) {
            return json_encode(['success' => false, 'message' => 'School not found.']);
        }
        $schoolId = $school['id'];

        // 2. Count Active Sessions
        $this->db->query("SELECT COUNT(*) as count FROM exam_sessions WHERE school_id = :school_id AND status = 'active'");
        $this->db->bind(':school_id', $schoolId);
        $activeSessions = $this->db->single()['count'];

        // 3. Count Bound Devices (Join via exam_sessions to ensure tenant isolation)
        $this->db->query("SELECT COUNT(db.id) as count 
                          FROM device_bindings db 
                          JOIN exam_sessions es ON db.exam_session_id = es.id 
                          WHERE es.school_id = :school_id");
        $this->db->bind(':school_id', $schoolId);
        $boundDevices = $this->db->single()['count'];

        // 4. Count Total Enrolled Students
        $this->db->query("SELECT COUNT(*) as count FROM students WHERE school_id = :school_id");
        $this->db->bind(':school_id', $schoolId);
        $totalStudents = $this->db->single()['count'];

        // 5. FETCH THE ACTUAL LIST OF ACTIVE SESSIONS FOR THE DASHBOARD TABLE
        $this->db->query("SELECT id, title, scheduled_date, status FROM exam_sessions WHERE school_id = :school_id AND status = 'active' ORDER BY scheduled_date DESC LIMIT 5");
        $this->db->bind(':school_id', $schoolId);
        $activeSessionsList = $this->db->resultSet();

        // Return the payload
        return json_encode([
            'success' => true,
            'payload' => [
                'activeSessions' => $activeSessions,
                'boundDevices' => $boundDevices,
                'totalStudents' => $totalStudents,
                'activeSessionsList' => $activeSessionsList // Passes the array to the frontend
            ]
        ]);
    }
    public function getSessionDetails($inputData = null) {
        if (!isset($_GET['id'])) return json_encode(['success' => false, 'message' => 'Session ID required']);
        $sessionId = $_GET['id'];

        // 1. Get Base Session Info
        $this->db->query("SELECT * FROM exam_sessions WHERE id = :id AND school_id = (SELECT id FROM schools WHERE slug = :slug)");
        $this->db->bind(':id', $sessionId);
        $this->db->bind(':slug', CURRENT_TENANT_SLUG);
        $session = $this->db->single();

        if (!$session) return json_encode(['success' => false, 'message' => 'Session not found']);

        // 2. Count Enrolled Students for this specific session
        $this->db->query("SELECT COUNT(*) as count FROM exam_session_student WHERE exam_session_id = :id");
        $this->db->bind(':id', $sessionId);
        $totalStudents = $this->db->single()['count'];

        // 3. Get Stations & Examiner Assignments
        $this->db->query("
            SELECT s.id, s.title, s.station_type, s.order_sequence, s.time_limit_minutes, 
                   u.full_name as examiner_name 
            FROM stations s
            LEFT JOIN users u ON s.examiner_id = u.id
            WHERE s.exam_session_id = :id 
            ORDER BY s.order_sequence ASC
        ");
        $this->db->bind(':id', $sessionId);
        $stations = $this->db->resultSet();

        // Calculate Lecturers (Count how many stations have an examiner assigned)
        $assignedLecturers = 0;
        foreach($stations as $st) {
            if ($st['examiner_name'] !== null) $assignedLecturers++;
        }

        return json_encode([
            'success' => true,
            'payload' => [
                'session' => $session,
                'summary' => [
                    'total_students' => $totalStudents,
                    'total_stations' => count($stations),
                    'assigned_lecturers' => $assignedLecturers
                ],
                'stations' => $stations
            ]
        ]);
    }

    public function bindDevice($inputData) {
        // Generate a UUID for the binding record itself
        $bindingId = UuidHelper::v4();

        $this->db->query('INSERT INTO device_bindings (id, exam_session_id, station_id, examiner_id, device_signature) VALUES (:id, :session, :station, :examiner, :sig)');
        
        $this->db->bind(':id', $bindingId);
        $this->db->bind(':session', $inputData['exam_session_id']);
        $this->db->bind(':station', $inputData['station_id']);
        $this->db->bind(':examiner', $inputData['examiner_id']); // Nullable for CBT
        $this->db->bind(':sig', $inputData['device_signature']);

        if ($this->db->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error during binding.']);
        }
    }

    // --- ADD THESE TO AdminController.php ---

    // Helper to get School ID safely
    private function getSchoolId() {
        $this->db->query("SELECT id FROM schools WHERE slug = :slug");
        $this->db->bind(':slug', CURRENT_TENANT_SLUG);
        $school = $this->db->single();
        return $school ? $school['id'] : null;
    }

    public function getDepartments($inputData = null) {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) return json_encode(['success' => false]);

        $this->db->query("SELECT id, name, dept_code FROM departments WHERE school_id = :school_id");
        $this->db->bind(':school_id', $schoolId);
        return json_encode(['success' => true, 'payload' => $this->db->resultSet()]);
    }

    public function getAllSessions($inputData = null) {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) return json_encode(['success' => false]);

        // Join to get the actual department name instead of just the ID
        $this->db->query("
            SELECT es.*, d.name as department_name 
            FROM exam_sessions es 
            JOIN departments d ON es.department_id = d.id 
            WHERE es.school_id = :school_id 
            ORDER BY es.scheduled_date DESC
        ");
        $this->db->bind(':school_id', $schoolId);
        return json_encode(['success' => true, 'payload' => $this->db->resultSet()]);
    }

    public function saveSession($inputData) {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) return json_encode(['success' => false, 'message' => 'Auth Error']);

        $id = !empty($inputData['id']) ? $inputData['id'] : null;
        $title = trim($inputData['title']);
        $date = $inputData['scheduled_date'];
        $deptId = $inputData['department_id'];
        
        // Catch the status (default to draft if not provided)
        $status = !empty($inputData['status']) ? $inputData['status'] : 'draft';

        if ($id) {
            // EDIT EXISTING: Now updates the status as well!
            $this->db->query("UPDATE exam_sessions SET title = :title, scheduled_date = :date, department_id = :dept, status = :status WHERE id = :id AND school_id = :school_id");
            $this->db->bind(':id', $id);
        } else {
            // CREATE NEW
            $id = UuidHelper::v4();
            $this->db->query("INSERT INTO exam_sessions (id, school_id, department_id, title, scheduled_date, status) VALUES (:id, :school_id, :dept, :title, :date, :status)");
        }
         $this->db->bind(':id', $id);
        $this->db->bind(':title', $title);
        $this->db->bind(':date', $date);
        $this->db->bind(':dept', $deptId);
        $this->db->bind(':status', $status);
        $this->db->bind(':school_id', $schoolId);
        
        if ($this->db->execute()) {
            return json_encode(['success' => true]);
        }
        return json_encode(['success' => false, 'message' => 'Database error']);
    }
}
?>