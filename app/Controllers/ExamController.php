<?php
class ExamController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getPayload($inputData) {
        $deviceSig = $inputData['device_signature'] ?? null;
        $userId = $inputData['user_id'] ?? null;
        $role = $inputData['role'] ?? null;

        if (!$deviceSig || !$userId) {
            return json_encode(['success' => false, 'message' => 'Missing device or user identifiers.']);
        }

        // 1. Verify the Device Binding & Get Station Info
        $this->db->query("
            SELECT db.exam_session_id, s.id as station_id, s.station_type, s.title as station_title, s.time_limit_minutes, es.title as session_title 
            FROM device_bindings db
            JOIN stations s ON db.station_id = s.id
            JOIN exam_sessions es ON db.exam_session_id = es.id
            WHERE db.device_signature = :sig
        ");
        $this->db->bind(':sig', $deviceSig);
        $stationData = $this->db->single();

        if (!$stationData) {
            return json_encode(['success' => false, 'message' => 'Unrecognized device signature.']);
        }

        // 2. Fetch the Active User's Data
        $studentData = null;
        if ($role === 'student') {
            $this->db->query("SELECT id, matric_number, full_name, nmcn_index_number FROM students WHERE id = :id");
            $this->db->bind(':id', $userId);
            $studentData = $this->db->single();
        } else if ($role === 'examiner') {
            $this->db->query("
                SELECT st.id, st.matric_number, st.full_name 
                FROM exam_session_student ess
                JOIN students st ON ess.student_id = st.id
                WHERE ess.exam_session_id = :es_id AND ess.status != 'completed'
            ");
            $this->db->bind(':es_id', $stationData['exam_session_id']);
            $studentData = $this->db->resultSet(); 
        }

        // 3. Construct the Evaluation Payload (CBT vs Procedure)
        $evaluationData = [];

        if ($stationData['station_type'] === 'cbt') {
            $this->db->query("SELECT id, question_text, marks FROM cbt_questions WHERE station_id = :st_id");
            $this->db->bind(':st_id', $stationData['station_id']);
            $questions = $this->db->resultSet();

            foreach ($questions as &$q) {
                $this->db->query("SELECT id, option_text FROM cbt_options WHERE question_id = :q_id");
                $this->db->bind(':q_id', $q['id']);
                $q['options'] = $this->db->resultSet();
            }
            $evaluationData = $questions;

        } else if ($stationData['station_type'] === 'procedure') {
            $this->db->query("SELECT id, task_description, max_score, is_critical_step FROM procedural_rubrics WHERE station_id = :st_id ORDER BY sequence_order ASC");
            $this->db->bind(':st_id', $stationData['station_id']);
            $evaluationData = $this->db->resultSet();
        }

        // --- NEW: Fetch School Branding for Offline Storage ---
        $this->db->query("SELECT name, logo_path FROM schools WHERE slug = :slug");
        $this->db->bind(':slug', CURRENT_TENANT_SLUG);
        $schoolData = $this->db->single();

        $base64Logo = null;
        if ($schoolData && !empty($schoolData['logo_path'])) {
            $absolutePath = '../public/assets/logos/' . $schoolData['logo_path'];
            if (file_exists($absolutePath)) {
                $mime = mime_content_type($absolutePath);
                $binaryData = file_get_contents($absolutePath);
                $base64Logo = 'data:' . $mime . ';base64,' . base64_encode($binaryData);
            }
        }
        // ------------------------------------------------------

        // 4. Package and Return the JSON
        $payload = [
            'branding' => [
                'school_name' => $schoolData['name'] ?? 'Exam Center',
                'logo_base64' => $base64Logo
            ],
            'session' => [
                'id' => $stationData['exam_session_id'],
                'title' => $stationData['session_title'],
                'station_id' => $stationData['station_id'],
                'station_title' => $stationData['station_title'],
                'station_type' => $stationData['station_type'],
                'time_limit_minutes' => $stationData['time_limit_minutes'],
                'evaluation_content' => $evaluationData
            ],
            'target' => $studentData
        ];

        return json_encode(['success' => true, 'payload' => $payload]);
    }
}
?>