<?php
class BindingController {
    
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // 1. Fetch form data (Sessions, Stations, and existing PINs)
    public function getBindingData() {
        try {
            // Get all active sessions using correct table 'exam_sessions'
            $this->db->query("SELECT id, title FROM exam_sessions ORDER BY created_at DESC");
            $sessions = $this->db->resultSet();

            // Get all stations. Using aliases so the JS doesn't break (exam_session_id AS session_id, etc.)
            $this->db->query("
                SELECT id, exam_session_id AS session_id, title, order_sequence AS sequence, station_type AS type 
                FROM stations 
                ORDER BY order_sequence ASC
            ");
            $stations = $this->db->resultSet();

            // Get existing PINs with joined tables matching your schema
            $this->db->query("
                SELECT p.id, p.pin_code as code, p.label, p.is_active as active, p.created_at,
                       s.title as session_title, st.title as station_title, st.order_sequence as station_sequence
                FROM device_binding_pins p
                LEFT JOIN exam_sessions s ON p.session_id = s.id
                LEFT JOIN stations st ON p.station_id = st.id
                ORDER BY p.created_at DESC
            ");
            $codes = $this->db->resultSet();

            return json_encode([
                'success' => true,
                'payload' => [
                    'sessions' => $sessions,
                    'stations' => $stations,
                    'codes' => $codes
                ]
            ]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }

    // 2. Generate and Save a New PIN
    public function generatePin($inputData) {
        if (empty($inputData['session_id']) || empty($inputData['station_id']) || empty($inputData['pin_code'])) {
            return json_encode(['success' => false, 'message' => 'Session, Station, and PIN are required.']);
        }

        try {
            $this->db->query("
                INSERT INTO device_binding_pins (id, pin_code, label, session_id, station_id, is_active) 
                VALUES (:id, :pin, :label, :session_id, :station_id, 1)
            ");
            $this->db->bind(':id', UuidHelper::v4());
            $this->db->bind(':pin', trim($inputData['pin_code']));
            $this->db->bind(':label', !empty($inputData['label']) ? trim($inputData['label']) : 'Setup Team');
            $this->db->bind(':session_id', $inputData['session_id']);
            $this->db->bind(':station_id', $inputData['station_id']);
            $this->db->execute();

            return json_encode(['success' => true]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Failed to save PIN.']);
        }
    }

    // 3. Toggle PIN Status
    public function togglePin($inputData) {
        if (empty($inputData['id'])) return json_encode(['success' => false]);
        
        try {
            $this->db->query("UPDATE device_binding_pins SET is_active = :status WHERE id = :id");
            $this->db->bind(':status', $inputData['active'] ? 1 : 0);
            $this->db->bind(':id', $inputData['id']);
            $this->db->execute();
            return json_encode(['success' => true]);
        } catch (Exception $e) {
            return json_encode(['success' => false]);
        }
    }

    // 4. Delete PIN
    public function deletePin($inputData) {
        if (empty($inputData['id'])) return json_encode(['success' => false]);
        
        try {
            $this->db->query("DELETE FROM device_binding_pins WHERE id = :id");
            $this->db->bind(':id', $inputData['id']);
            $this->db->execute();
            return json_encode(['success' => true]);
        } catch (Exception $e) {
            return json_encode(['success' => false]);
        }
    }

    // 5. Verify PIN (Called by the Gatekeeper Screen)
    public function verifyPin($inputData) {
        $pin = trim($inputData['pin'] ?? '');

        if (empty($pin)) {
            return json_encode(['success' => false, 'message' => 'Please enter a PIN.']);
        }

        try {
            // Join the PIN with the Exam Session and Station to get the friendly titles
            $this->db->query("
                SELECT p.id as pin_id, p.is_active, p.session_id, p.station_id,
                       s.title as session_title, 
                       st.title as station_title, 
                       st.order_sequence as station_sequence,
                       st.station_type
                FROM device_binding_pins p
                JOIN exam_sessions s ON p.session_id = s.id
                JOIN stations st ON p.station_id = st.id
                WHERE p.pin_code = :pin
            ");
            $this->db->bind(':pin', $pin);
            $result = $this->db->single();

            if (!$result) {
                return json_encode(['success' => false, 'message' => 'Invalid PIN code.']);
            }

            if ((int)$result['is_active'] !== 1) {
                return json_encode(['success' => false, 'message' => 'This PIN has been revoked by the administrator.']);
            }

            // PIN is valid, return the payload for the UI
            return json_encode([
                'success' => true,
                'payload' => [
                    'session_id' => $result['session_id'],
                    'session_title' => $result['session_title'],
                    'station_id' => $result['station_id'],
                    'station_title' => $result['station_title'],
                    'station_sequence' => $result['station_sequence'],
                    'station_type' => $result['station_type']
                ]
            ]);

        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Database error during verification.']);
        }
    }

    // 6. Download Full Offline Payload
    public function downloadOfflinePayload() {
        $sessionId = $_GET['session_id'] ?? null;
        $stationId = $_GET['station_id'] ?? null;

        if (!$sessionId || !$stationId) {
            return json_encode(['success' => false, 'message' => 'Missing session or station context.']);
        }

        try {
            // A. Fetch Station Settings
            $this->db->query("SELECT * FROM stations WHERE id = :station_id");
            $this->db->bind(':station_id', $stationId);
            $stationSettings = $this->db->single();

            // B. Fetch All Questions for this Station
            $this->db->query("SELECT id, question_text, opt_a, opt_b, opt_c, opt_d, correct_answer, score, order_seq FROM station_questions WHERE station_id = :station_id ORDER BY order_seq ASC");
            $this->db->bind(':station_id', $stationId);
            $questions = $this->db->resultSet();

            // C. Fetch Roster (Students mapped to this session with their RAW PASSWORDS so they can login offline)
            // Note: In an offline system, the frontend must verify passwords locally, so we send the raw_password.
            $this->db->query("
                SELECT s.id, s.matric_number, s.full_name, s.raw_password
                FROM exam_session_student ess
                JOIN students s ON ess.student_id = s.id
                WHERE ess.exam_session_id = :session_id
            ");
            $this->db->bind(':session_id', $sessionId);
            $students = $this->db->resultSet();

            return json_encode([
                'success' => true,
                'payload' => [
                    'station_settings' => $stationSettings,
                    'questions' => $questions,
                    'students' => $students
                ]
            ]);

        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Database error compiling payload.']);
        }
    }
}