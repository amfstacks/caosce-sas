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
    // public function saveStationConfig_old($inputData) {
    //     $station = $inputData['station'] ?? null;
    //     if (!$station || empty($station['id'])) {
    //         return json_encode(['success' => false, 'message' => 'Invalid station data.']);
    //     }

    //     // 1. Update Core Station Parameters
    //     $this->db->query("UPDATE stations SET title = :title, examiner_id = :examiner_id, score_per_question = :score, is_confirmed = :confirmed WHERE id = :id");
    //     $this->db->bind(':title', !empty($station['title']) ? trim($station['title']) : null);
    //     $this->db->bind(':examiner_id', !empty($station['examiner_id']) ? $station['examiner_id'] : null);
    //     $this->db->bind(':score', !empty($station['score_per_question']) ? $station['score_per_question'] : null);
    //     $this->db->bind(':confirmed', $station['confirmed'] ? 1 : 0);
    //     $this->db->bind(':id', $station['id']);
    //     $this->db->execute();

    //     // 2. Wipe existing questions for a clean slate
    //     $this->db->query("DELETE FROM station_questions WHERE station_id = :station_id");
    //     $this->db->bind(':station_id', $station['id']);
    //     $this->db->execute();

    //     // 3. Insert the new questions array
    //     if (!empty($station['questions']) && is_array($station['questions'])) {
    //         $seq = 1;
    //         foreach ($station['questions'] as $q) {
    //             // Skip empty questions
    //             if (empty(trim($q['text']))) continue;

    //             $qId = UuidHelper::v4();
    //             $this->db->query("
    //                 INSERT INTO station_questions (id, station_id, question_text, opt_a, opt_b, opt_c, opt_d, correct_answer, score, order_seq) 
    //                 VALUES (:id, :station_id, :text, :optA, :optB, :optC, :optD, :answer, :score, :seq)
    //             ");
    //             $this->db->bind(':id', $qId);
    //             $this->db->bind(':station_id', $station['id']);
    //             $this->db->bind(':text', trim($q['text']));
    //             $this->db->bind(':optA', !empty($q['optA']) ? trim($q['optA']) : null);
    //             $this->db->bind(':optB', !empty($q['optB']) ? trim($q['optB']) : null);
    //             $this->db->bind(':optC', !empty($q['optC']) ? trim($q['optC']) : null);
    //             $this->db->bind(':optD', !empty($q['optD']) ? trim($q['optD']) : null);
    //             $this->db->bind(':answer', !empty($q['correct_answer']) ? $q['correct_answer'] : null);
    //             $this->db->bind(':score', !empty($q['score']) ? $q['score'] : 1.00);
    //             $this->db->bind(':seq', $seq);
    //             $this->db->execute();
                
    //             $seq++;
    //         }
    //     }

    //     return json_encode(['success' => true]);
    // }
    // // --- 3. Save Station Configuration & Question Bank ---
    // public function saveStationConfig($inputData) {
    //     $station = $inputData['station'] ?? null;
    //     if (!$station || empty($station['id'])) {
    //         return json_encode(['success' => false, 'message' => 'Invalid station data.']);
    //     }

    //     try {
    //         $this->db->beginTransaction();

    //         // 1. Update Core Station Parameters
    //         $this->db->query("UPDATE stations SET title = :title, examiner_id = :examiner_id, score_per_question = :score, is_confirmed = :confirmed WHERE id = :id");
    //         $this->db->bind(':title', !empty($station['title']) ? trim($station['title']) : '');
    //         $this->db->bind(':examiner_id', !empty($station['examiner_id']) ? $station['examiner_id'] : null);
    //         $this->db->bind(':score', !empty($station['score_per_question']) ? $station['score_per_question'] : null);
    //         $this->db->bind(':confirmed', !empty($station['confirmed']) ? 1 : 0);
    //         $this->db->bind(':id', $station['id']);
    //         $this->db->execute();

    //         // 2. Wipe existing questions for a clean slate
    //         $this->db->query("DELETE FROM station_questions WHERE station_id = :station_id");
    //         $this->db->bind(':station_id', $station['id']);
    //         $this->db->execute();

    //         // 3. Insert the new questions array
    //         if (!empty($station['questions']) && is_array($station['questions'])) {
    //             $seq = 1;
    //             foreach ($station['questions'] as $q) {
    //                 // Safely extract text (prevents silent PHP undefined key errors)
    //                 $text = isset($q['text']) ? trim($q['text']) : '';
    //                 if (empty($text)) continue;

    //                 $qId = UuidHelper::v4();
    //                 $this->db->query("
    //                     INSERT INTO station_questions (id, station_id, question_text, opt_a, opt_b, opt_c, opt_d, correct_answer, score, order_seq) 
    //                     VALUES (:id, :station_id, :text, :optA, :optB, :optC, :optD, :answer, :score, :seq)
    //                 ");
    //                 $this->db->bind(':id', $qId);
    //                 $this->db->bind(':station_id', $station['id']);
    //                 $this->db->bind(':text', $text);
    //                 $this->db->bind(':optA', !empty($q['optA']) ? trim($q['optA']) : null);
    //                 $this->db->bind(':optB', !empty($q['optB']) ? trim($q['optB']) : null);
    //                 $this->db->bind(':optC', !empty($q['optC']) ? trim($q['optC']) : null);
    //                 $this->db->bind(':optD', !empty($q['optD']) ? trim($q['optD']) : null);
    //                 $this->db->bind(':answer', !empty($q['correct_answer']) ? $q['correct_answer'] : null);
    //                 $this->db->bind(':score', !empty($q['score']) ? $q['score'] : 1.00);
    //                 $this->db->bind(':seq', $seq);
    //                 $this->db->execute();
                    
    //                 $seq++;
    //             }
    //         }

    //         $this->db->commit();
    //         return json_encode(['success' => true]);

    //     } catch (Exception $e) {
    //         $this->db->rollBack();
    //         // This will throw the exact error to the UI so we can see what's wrong!
    //         return json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]); 
    //     }
    // }

    // --- 3. Save Station Configuration (CORE SETTINGS ONLY) ---
    public function saveStationConfig($inputData) {
        $station = $inputData['station'] ?? null;
        if (!$station || empty($station['id'])) {
            return json_encode(['success' => false, 'message' => 'Invalid station data.']);
        }

        try {
            // We ONLY update the core parameters now. We do NOT touch the questions table here anymore.
            $this->db->query("UPDATE stations SET title = :title, examiner_id = :examiner_id, score_per_question = :score, is_confirmed = :confirmed WHERE id = :id");
            $this->db->bind(':title', !empty($station['title']) ? trim($station['title']) : '');
            $this->db->bind(':examiner_id', !empty($station['examiner_id']) ? $station['examiner_id'] : null);
            $this->db->bind(':score', !empty($station['score_per_question']) ? $station['score_per_question'] : null);
            $this->db->bind(':confirmed', !empty($station['confirmed']) ? 1 : 0);
            $this->db->bind(':id', $station['id']);
            $this->db->execute();

            return json_encode(['success' => true]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]); 
        }
    }

    // --- 4. INSTANT SAVE: Add or Edit a Single Question ---
    public function saveSingleQuestion($inputData) {
        if (empty($inputData['station_id']) || empty($inputData['text'])) {
            return json_encode(['success' => false, 'message' => 'Station ID and Question Text are required.']);
        }

        $id = !empty($inputData['id']) ? $inputData['id'] : null;
        $stationId = $inputData['station_id'];
        $text = trim($inputData['text']);
        $optA = !empty($inputData['optA']) ? trim($inputData['optA']) : null;
        $optB = !empty($inputData['optB']) ? trim($inputData['optB']) : null;
        $optC = !empty($inputData['optC']) ? trim($inputData['optC']) : null;
        $optD = !empty($inputData['optD']) ? trim($inputData['optD']) : null;
        $answer = !empty($inputData['correct_answer']) ? trim($inputData['correct_answer']) : null;
        $score = !empty($inputData['score']) ? $inputData['score'] : 1.00;

        try {
            if ($id) {
                // EDIT EXISTING QUESTION
                $this->db->query("
                    UPDATE station_questions 
                    SET question_text = :text, opt_a = :optA, opt_b = :optB, opt_c = :optC, opt_d = :optD, correct_answer = :answer, score = :score 
                    WHERE id = :id
                ");
                $this->db->bind(':id', $id);
            } else {
                // ADD NEW QUESTION
                $id = UuidHelper::v4();
                
                // Get the next sequence number for this station
                $this->db->query("SELECT MAX(order_seq) as max_seq FROM station_questions WHERE station_id = :station_id");
                $this->db->bind(':station_id', $stationId);
                $row = $this->db->single();
                $nextSeq = ($row && $row['max_seq']) ? $row['max_seq'] + 1 : 1;

                $this->db->query("
                    INSERT INTO station_questions (id, station_id, question_text, opt_a, opt_b, opt_c, opt_d, correct_answer, score, order_seq) 
                    VALUES (:id, :station_id, :text, :optA, :optB, :optC, :optD, :answer, :score, :seq)
                ");
                $this->db->bind(':id', $id);
                $this->db->bind(':station_id', $stationId);
                $this->db->bind(':seq', $nextSeq);
            }
            
            $this->db->bind(':text', $text);
            $this->db->bind(':optA', $optA);
            $this->db->bind(':optB', $optB);
            $this->db->bind(':optC', $optC);
            $this->db->bind(':optD', $optD);
            $this->db->bind(':answer', $answer);
            $this->db->bind(':score', $score);
            $this->db->execute();

            return json_encode(['success' => true, 'question_id' => $id]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    }

    // --- 5. INSTANT DELETE: Remove a Single Question ---
    public function deleteSingleQuestion($inputData) {
        if (empty($inputData['id'])) {
            return json_encode(['success' => false, 'message' => 'Question ID required.']);
        }

        try {
            $this->db->query("DELETE FROM station_questions WHERE id = :id");
            $this->db->bind(':id', $inputData['id']);
            $this->db->execute();

            return json_encode(['success' => true]);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
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

    // --- 6. Bulk Upload CSV Question Bank ---
    public function uploadBulkQuestions($inputData = null) {
        $stationId = $_POST['station_id'] ?? null;
        
        if (!$stationId || !isset($_FILES['question_file'])) {
            return json_encode(['success' => false, 'message' => 'Missing file or station ID.']);
        }

        $file = $_FILES['question_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return json_encode(['success' => false, 'message' => 'File upload error.']);
        }

        // Fetch station context to determine type and CBT score configuration
        $this->db->query("SELECT station_type, score_per_question FROM stations WHERE id = :id");
        $this->db->bind(':id', $stationId);
        $station = $this->db->single();

        if (!$station) {
            return json_encode(['success' => false, 'message' => 'Invalid station context.']);
        }

        $isCbt = ($station['station_type'] === 'cbt');

        // PRE-FLIGHT CHECK: Ensure CBT has a score set before allowing upload
        if ($isCbt && empty($station['score_per_question'])) {
            return json_encode(['success' => false, 'message' => 'Upload Aborted: Please set and save the "Score per Question (Marks)" in the Core Parameters before uploading CBT questions.']);
        }

        $handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($handle); // Skip header

        if (!$header) {
            fclose($handle);
            return json_encode(['success' => false, 'message' => 'The CSV file is empty.']);
        }

        // Map column indices dynamically to be foolproof
        $headerMap = [];
        foreach ($header as $index => $col) {
            $headerMap[strtolower(trim($col))] = $index;
        }

        // STRICT HEADER VALIDATION BASED ON STATION TYPE
        if ($isCbt) {
            $requiredHeaders = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'];
            foreach ($requiredHeaders as $req) {
                if (!isset($headerMap[$req])) {
                    fclose($handle);
                    return json_encode(['success' => false, 'message' => "Upload Aborted: CBT CSV must contain the header '$req'."]);
                }
            }
        } else {
            $requiredHeaders = ['question', 'score'];
            foreach ($requiredHeaders as $req) {
                if (!isset($headerMap[$req])) {
                    fclose($handle);
                    return json_encode(['success' => false, 'message' => "Upload Aborted: Procedure CSV must contain the header '$req'."]);
                }
            }
        }

        $this->db->beginTransaction(); 

        try {
            // Get current max sequence to append new questions at the end
            $this->db->query("SELECT MAX(order_seq) as max_seq FROM station_questions WHERE station_id = :station_id");
            $this->db->bind(':station_id', $stationId);
            $row = $this->db->single();
            $seq = ($row && $row['max_seq']) ? $row['max_seq'] + 1 : 1;

            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $text = trim($row[$headerMap['question']] ?? '');
                if (empty($text)) continue;

                if ($isCbt) {
                    $optA = trim($row[$headerMap['option_a']] ?? '');
                    $optB = trim($row[$headerMap['option_b']] ?? '');
                    $optC = trim($row[$headerMap['option_c']] ?? '');
                    $optD = trim($row[$headerMap['option_d']] ?? '');
                    $answer = trim($row[$headerMap['correct_answer']] ?? '');
                    // CBT inherits the score from the core parameters
                    $score = $station['score_per_question']; 
                } else {
                    $optA = $optB = $optC = $optD = $answer = null;
                    // Procedure extracts the score directly from the CSV row
                    $csvScore = trim($row[$headerMap['score']] ?? '');
                    $score = is_numeric($csvScore) ? $csvScore : 1.00;
                }

                $qId = UuidHelper::v4();
                $this->db->query("
                    INSERT INTO station_questions (id, station_id, question_text, opt_a, opt_b, opt_c, opt_d, correct_answer, score, order_seq) 
                    VALUES (:id, :station_id, :text, :optA, :optB, :optC, :optD, :answer, :score, :seq)
                ");
                $this->db->bind(':id', $qId);
                $this->db->bind(':station_id', $stationId);
                $this->db->bind(':text', $text);
                $this->db->bind(':optA', !empty($optA) ? $optA : null);
                $this->db->bind(':optB', !empty($optB) ? $optB : null);
                $this->db->bind(':optC', !empty($optC) ? $optC : null);
                $this->db->bind(':optD', !empty($optD) ? $optD : null);
                // Force correct answer to uppercase (A, B, C, D) just in case
                $this->db->bind(':answer', !empty($answer) ? strtoupper($answer) : null); 
                $this->db->bind(':score', $score);
                $this->db->bind(':seq', $seq);
                $this->db->execute();
                
                $seq++;
            }
            
            $this->db->commit();
            fclose($handle);
            return json_encode(['success' => true]);

        } catch (Exception $e) {
            $this->db->rollBack();
            fclose($handle);
            return json_encode(['success' => false, 'message' => 'DB error during CSV import.']);
        }
    }
}
?>