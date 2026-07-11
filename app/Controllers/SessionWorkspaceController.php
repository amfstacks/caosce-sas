<?php
class SessionWorkspaceController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getWorkspaceData($inputData = null) {
        if (!defined('CURRENT_TENANT_SLUG') || CURRENT_TENANT_SLUG === null) {
            return json_encode(['success' => false, 'message' => 'Invalid workspace.']);
        }

        $sessionId = $_GET['id'] ?? null;
        if (!$sessionId) {
            return json_encode(['success' => false, 'message' => 'Session ID required.']);
        }

        // 1. Fetch Session Data
        $this->db->query("
            SELECT es.id, es.title, es.scheduled_date as date, d.name as department 
            FROM exam_sessions es 
            JOIN departments d ON es.department_id = d.id 
            WHERE es.id = :id
        ");
        $this->db->bind(':id', $sessionId);
        $session = $this->db->single();

        if (!$session) return json_encode(['success' => false, 'message' => 'Session not found.']);

        // 2. Fetch Enrolled Students
        $this->db->query("
            SELECT s.id, s.matric_number as matric, s.full_name as name, s.raw_password as password 
            FROM students s
            JOIN exam_session_student ess ON s.id = ess.student_id
            WHERE ess.exam_session_id = :id
            ORDER BY s.matric_number ASC
        ");
        $this->db->bind(':id', $sessionId);
        $students = $this->db->resultSet();

        // 3. Fetch Available Examiners
        $this->db->query("
            SELECT id, full_name as name 
            FROM users 
            WHERE school_id = (SELECT school_id FROM exam_sessions WHERE id = :id) 
            AND role = 'examiner'
        ");
        $this->db->bind(':id', $sessionId);
        $examiners = $this->db->resultSet();

        // 4. Fetch Stations
        $this->db->query("
            SELECT id, order_sequence as sequence, station_type as type, title, 
                   score_per_question, examiner_id, is_confirmed as confirmed 
            FROM stations 
            WHERE exam_session_id = :id 
            ORDER BY order_sequence ASC
        ");
        $this->db->bind(':id', $sessionId);
        $stations = $this->db->resultSet();

        // 5. Fetch Questions and attach them to stations
        foreach ($stations as &$station) {
            // Typecast for frontend strictly
            $station['confirmed'] = (bool)$station['confirmed']; 
            
            $this->db->query("
                SELECT id, question_text as text, opt_a as optA, opt_b as optB, 
                       opt_c as optC, opt_d as optD, correct_answer, score 
                FROM station_questions 
                WHERE station_id = :station_id 
                ORDER BY order_seq ASC
            ");
            $this->db->bind(':station_id', $station['id']);
            $questions = $this->db->resultSet();
            
            // Add frontend helper flags
            foreach ($questions as &$q) {
                $q['saved'] = false;
            }
            $station['questions'] = $questions;
        }

        return json_encode([
            'success' => true,
            'payload' => [
                'sessionData' => $session,
                'students' => $students,
                'availableExaminers' => $examiners,
                'stations' => $stations
            ]
        ]);
    }

    // --- 1. Save or Update Student ---
    public function saveStudent($inputData) {
        if (empty($inputData['matric']) || empty($inputData['name']) || empty($inputData['session_id'])) {
            return json_encode(['success' => false, 'message' => 'Missing required fields.']);
        }

        $sessionId = $inputData['session_id'];
        
        // Get School ID and Department ID from the session
        $this->db->query("SELECT school_id, department_id FROM exam_sessions WHERE id = :session_id");
        $this->db->bind(':session_id', $sessionId);
        $session = $this->db->single();
        
        if (!$session) return json_encode(['success' => false, 'message' => 'Invalid session.']);

        $studentId = $inputData['id'] ?? null;
        $passwordHash = password_hash($inputData['password'], PASSWORD_DEFAULT);

        if (!$studentId || is_numeric($studentId)) { 
            // It's a new student (Alpine gives numeric timestamp IDs temporarily)
            // First, check if matric number already exists in this school
            $this->db->query("SELECT id FROM students WHERE school_id = :school_id AND matric_number = :matric");
            $this->db->bind(':school_id', $session['school_id']);
            $this->db->bind(':matric', trim($inputData['matric']));
            $existingStudent = $this->db->single();

            if ($existingStudent) {
                $studentId = $existingStudent['id'];
                // Update their password if it's an existing student being re-added
                $this->db->query("UPDATE students SET full_name = :name, password_hash = :hash, raw_password = :raw WHERE id = :id");
                $this->db->bind(':name', trim($inputData['name']));
                $this->db->bind(':hash', $passwordHash);
                $this->db->bind(':raw', $inputData['password']);
                $this->db->bind(':id', $studentId);
                $this->db->execute();
            } else {
                // Create brand new student
                $studentId = UuidHelper::v4();
                $this->db->query("INSERT INTO students (id, school_id, department_id, matric_number, full_name, password_hash, raw_password) VALUES (:id, :school, :dept, :matric, :name, :hash, :raw)");
                $this->db->bind(':id', $studentId);
                $this->db->bind(':school', $session['school_id']);
                $this->db->bind(':dept', $session['department_id']);
                $this->db->bind(':matric', trim($inputData['matric']));
                $this->db->bind(':name', trim($inputData['name']));
                $this->db->bind(':hash', $passwordHash);
                $this->db->bind(':raw', $inputData['password']);
                $this->db->execute();
            }
        } else {
            // Edit existing student record
            $this->db->query("UPDATE students SET matric_number = :matric, full_name = :name, password_hash = :hash, raw_password = :raw WHERE id = :id");
            $this->db->bind(':matric', trim($inputData['matric']));
            $this->db->bind(':name', trim($inputData['name']));
            $this->db->bind(':hash', $passwordHash);
            $this->db->bind(':raw', $inputData['password']);
            $this->db->bind(':id', $studentId);
            $this->db->execute();
        }

        // Enroll them in the session if not already enrolled
        $this->db->query("SELECT id FROM exam_session_student WHERE exam_session_id = :session_id AND student_id = :student_id");
        $this->db->bind(':session_id', $sessionId);
        $this->db->bind(':student_id', $studentId);
        if (!$this->db->single()) {
            $enrollId = UuidHelper::v4();
            $this->db->query("INSERT INTO exam_session_student (id, exam_session_id, student_id, status) VALUES (:id, :session_id, :student_id, 'pending')");
            $this->db->bind(':id', $enrollId);
            $this->db->bind(':session_id', $sessionId);
            $this->db->bind(':student_id', $studentId);
            $this->db->execute();
        }

        return json_encode(['success' => true]);
    }

    // --- 2. Remove Student from Session ---
    public function removeStudent($inputData) {
        if (empty($inputData['student_id']) || empty($inputData['session_id'])) {
            return json_encode(['success' => false]);
        }
        
        // We only delete the enrollment record, preserving the student for other exams
        $this->db->query("DELETE FROM exam_session_student WHERE exam_session_id = :session_id AND student_id = :student_id");
        $this->db->bind(':session_id', $inputData['session_id']);
        $this->db->bind(':student_id', $inputData['student_id']);
        
        return json_encode(['success' => $this->db->execute()]);
    }

    // --- 3. Save Station Configuration & Question Bank ---
    public function saveStationConfig($inputData) {
        $station = $inputData['station'] ?? null;
        if (!$station || empty($station['id'])) {
            return json_encode(['success' => false, 'message' => 'Invalid station data.']);
        }

        // 1. Update Core Station Parameters
        $this->db->query("UPDATE stations SET title = :title, examiner_id = :examiner_id, score_per_question = :score, is_confirmed = :confirmed WHERE id = :id");
        $this->db->bind(':title', !empty($station['title']) ? trim($station['title']) : null);
        $this->db->bind(':examiner_id', !empty($station['examiner_id']) ? $station['examiner_id'] : null);
        $this->db->bind(':score', !empty($station['score_per_question']) ? $station['score_per_question'] : null);
        $this->db->bind(':confirmed', $station['confirmed'] ? 1 : 0);
        $this->db->bind(':id', $station['id']);
        $this->db->execute();

        // 2. Wipe existing questions for a clean slate
        $this->db->query("DELETE FROM station_questions WHERE station_id = :station_id");
        $this->db->bind(':station_id', $station['id']);
        $this->db->execute();

        // 3. Insert the new questions array
        if (!empty($station['questions']) && is_array($station['questions'])) {
            $seq = 1;
            foreach ($station['questions'] as $q) {
                // Skip empty questions
                if (empty(trim($q['text']))) continue;

                $qId = UuidHelper::v4();
                $this->db->query("
                    INSERT INTO station_questions (id, station_id, question_text, opt_a, opt_b, opt_c, opt_d, correct_answer, score, order_seq) 
                    VALUES (:id, :station_id, :text, :optA, :optB, :optC, :optD, :answer, :score, :seq)
                ");
                $this->db->bind(':id', $qId);
                $this->db->bind(':station_id', $station['id']);
                $this->db->bind(':text', trim($q['text']));
                $this->db->bind(':optA', !empty($q['optA']) ? trim($q['optA']) : null);
                $this->db->bind(':optB', !empty($q['optB']) ? trim($q['optB']) : null);
                $this->db->bind(':optC', !empty($q['optC']) ? trim($q['optC']) : null);
                $this->db->bind(':optD', !empty($q['optD']) ? trim($q['optD']) : null);
                $this->db->bind(':answer', !empty($q['correct_answer']) ? $q['correct_answer'] : null);
                $this->db->bind(':score', !empty($q['score']) ? $q['score'] : 1.00);
                $this->db->bind(':seq', $seq);
                $this->db->execute();
                
                $seq++;
            }
        }

        return json_encode(['success' => true]);
    }

    // --- 4. Bulk Upload CSV Roster ---
    // --- 4. Bulk Upload CSV Roster ---
    public function uploadBulkRoster($inputData = null) {
        $sessionId = $_POST['session_id'] ?? null;
        $passwordStrategy = $_POST['password_strategy'] ?? 'generate'; // 'matric' or 'generate'
        
        if (!$sessionId || !isset($_FILES['roster_file'])) {
            return json_encode(['success' => false, 'message' => 'Missing file or session ID.']);
        }

        $file = $_FILES['roster_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return json_encode(['success' => false, 'message' => 'File upload error.']);
        }

        // Get session context
        $this->db->query("SELECT school_id, department_id FROM exam_sessions WHERE id = :id");
        $this->db->bind(':id', $sessionId);
        $session = $this->db->single();
        if (!$session) return json_encode(['success' => false, 'message' => 'Invalid session context.']);

        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return json_encode(['success' => false, 'message' => 'The CSV file is completely empty.']);
        }

        // Normalize headers to lowercase to avoid case-sensitivity issues
        $headerMap = [];
        foreach ($header as $index => $col) {
            $headerMap[strtolower(trim($col))] = $index;
        }

        // STRICT COLUMN VALIDATION
        if (!isset($headerMap['matric_no']) || !isset($headerMap['full_name']) || !isset($headerMap['password'])) {
            fclose($handle);
            return json_encode([
                'success' => false, 
                'message' => 'Upload Aborted: The CSV must contain exactly these headers: matric_no, full_name, password'
            ]);
        }

        // Get dynamic column indices
        $idxMatric = $headerMap['matric_no'];
        $idxName = $headerMap['full_name'];
        $idxPass = $headerMap['password'];

        $this->db->beginTransaction(); 

        try {
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Safely extract data using dynamic indices
                $matric = trim($row[$idxMatric] ?? '');
                $name = trim($row[$idxName] ?? '');
                $csvPassword = trim($row[$idxPass] ?? '');
                
                if (empty($matric) || empty($name)) continue; // Skip totally blank rows

                // Apply Password Strategy
                $rawPassword = $csvPassword;
                if (empty($rawPassword)) {
                    if ($passwordStrategy === 'matric') {
                        $rawPassword = $matric;
                    } else {
                        // Generate a random 4-digit PIN
                        $rawPassword = (string) rand(1000, 9999);
                    }
                }

                $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);

                // Insert or Update Student...
                $this->db->query("SELECT id FROM students WHERE school_id = :school_id AND matric_number = :matric");
                $this->db->bind(':school_id', $session['school_id']);
                $this->db->bind(':matric', $matric);
                $existing = $this->db->single();

                if ($existing) {
                    $studentId = $existing['id'];
                    // Update password if they are being re-uploaded
                    $this->db->query("UPDATE students SET full_name = :name, password_hash = :hash, raw_password = :raw WHERE id = :id");
                    $this->db->bind(':name', $name);
                    $this->db->bind(':hash', $passwordHash);
                    $this->db->bind(':raw', $rawPassword);
                    $this->db->bind(':id', $studentId);
                    $this->db->execute();
                } else {
                    $studentId = UuidHelper::v4();
                    $this->db->query("INSERT INTO students (id, school_id, department_id, matric_number, full_name, password_hash, raw_password) VALUES (:id, :school, :dept, :matric, :name, :hash, :raw)");
                    $this->db->bind(':id', $studentId);
                    $this->db->bind(':school', $session['school_id']);
                    $this->db->bind(':dept', $session['department_id']);
                    $this->db->bind(':matric', $matric);
                    $this->db->bind(':name', $name);
                    $this->db->bind(':hash', $passwordHash);
                    $this->db->bind(':raw', $rawPassword);
                    $this->db->execute();
                }

                // Enroll
                $this->db->query("SELECT id FROM exam_session_student WHERE exam_session_id = :sess AND student_id = :stu");
                $this->db->bind(':sess', $sessionId);
                $this->db->bind(':stu', $studentId);
                
                if (!$this->db->single()) {
                    $enrollId = UuidHelper::v4();
                    $this->db->query("INSERT INTO exam_session_student (id, exam_session_id, student_id, status) VALUES (:id, :sess, :stu, 'pending')");
                    $this->db->bind(':id', $enrollId);
                    $this->db->bind(':sess', $sessionId);
                    $this->db->bind(':stu', $studentId);
                    $this->db->execute();
                }
            }
            
            $this->db->commit();
            fclose($handle);
            return json_encode(['success' => true]);

        } catch (Exception $e) {
            $this->db->rollBack();
            fclose($handle);
            return json_encode(['success' => false, 'message' => 'Database error during CSV import.']);
        }
    }
}
?>