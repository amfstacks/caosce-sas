<?php
class SyncController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // 1. Receives individual clicks/ticks in real-time
    public function logTick($inputData) {
        $studentId = $inputData['student_id'];
        $examSessionId = $inputData['exam_session_id'];
        $questionId = $inputData['question_id']; 
        $answer = $inputData['answer']; 

        // Get the correct answer to log if they are correct real-time
        $this->db->query("SELECT correct_answer, score FROM station_questions WHERE id = :qid");
        $this->db->bind(':qid', $questionId);
        $qData = $this->db->single();
        
        $isCorrect = ($qData && $qData['correct_answer'] === $answer) ? 1 : 0;
        $earned = $isCorrect ? $qData['score'] : 0;

        // Upsert Answer
        $this->db->query("
            INSERT INTO student_responses (id, student_id, exam_session_id, station_id, question_id, answer_data, is_correct, score_earned) 
            VALUES (:id, :sid, :esid, (SELECT station_id FROM station_questions WHERE id = :qid), :qid, :ans, :is_correct, :earned)
            ON DUPLICATE KEY UPDATE answer_data = :ans, is_correct = :is_correct, score_earned = :earned, synced_at = CURRENT_TIMESTAMP
        ");
        $this->db->bind(':id', UuidHelper::v4());
        $this->db->bind(':sid', $studentId);
        $this->db->bind(':esid', $examSessionId);
        $this->db->bind(':qid', $questionId);
        $this->db->bind(':ans', $answer);
        $this->db->bind(':is_correct', $isCorrect);
        $this->db->bind(':earned', $earned);

        $this->db->execute();
        return json_encode(['success' => true]);
    }

    // 2. Receives the Complete Exam Payload (from CBT or Bulk Admin Sync)
    public function finalizeScore($inputData) {
        $studentId = $inputData['student_id'];
        $sessionId = $inputData['session_id'];
        $stationId = $inputData['station_id'];
        $totalScore = $inputData['total_score'];
        $maxPossible = $inputData['max_possible'];
        $breakdown = $inputData['breakdown']; // Array of objects

        try {
            $this->db->beginTransaction();

            // 1. Save Final Score
            $this->db->query("
                INSERT INTO station_scores (id, student_id, exam_session_id, station_id, total_score, max_possible) 
                VALUES (:id, :sid, :esid, :stid, :score, :max)
                ON DUPLICATE KEY UPDATE total_score = :score, max_possible = :max, synced_at = CURRENT_TIMESTAMP
            ");
            $this->db->bind(':id', UuidHelper::v4());
            $this->db->bind(':sid', $studentId);
            $this->db->bind(':esid', $sessionId);
            $this->db->bind(':stid', $stationId);
            $this->db->bind(':score', $totalScore);
            $this->db->bind(':max', $maxPossible);
            $this->db->execute();

            // 2. Loop and Bulk Update all Answers (Ensures 100% data integrity even if ticks failed)
            foreach ($breakdown as $item) {
                if(empty($item['answer_chosen'])) continue; // Skip unanswered
                
                $this->db->query("
                    INSERT INTO student_responses (id, student_id, exam_session_id, station_id, question_id, answer_data, is_correct, score_earned) 
                    VALUES (:id, :sid, :esid, :stid, :qid, :ans, :is_correct, :earned)
                    ON DUPLICATE KEY UPDATE answer_data = :ans, is_correct = :is_correct, score_earned = :earned, synced_at = CURRENT_TIMESTAMP
                ");
                $this->db->bind(':id', UuidHelper::v4());
                $this->db->bind(':sid', $studentId);
                $this->db->bind(':esid', $sessionId);
                $this->db->bind(':stid', $stationId);
                $this->db->bind(':qid', $item['question_id']);
                $this->db->bind(':ans', $item['answer_chosen']);
                $this->db->bind(':is_correct', $item['is_correct'] ? 1 : 0);
                $this->db->bind(':earned', $item['earned']);
                $this->db->execute();
            }

            // 3. Mark student as completed in the session map
            $this->db->query("UPDATE exam_session_student SET status = 'completed' WHERE student_id = :sid AND exam_session_id = :esid");
            $this->db->bind(':sid', $studentId);
            $this->db->bind(':esid', $sessionId);
            $this->db->execute();

            $this->db->commit();
            return json_encode(['success' => true]);

        } catch (Exception $e) {
            $this->db->rollBack();
            return json_encode(['success' => false, 'message' => 'Failed to write sync data.']);
        }
    }


    private function getSchoolId() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['school_id'] ?? null;
    }

    // --- FETCH CODES ---
    public function getCodes($inputData = null) {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) return json_encode(['success' => false, 'message' => 'Unauthorized']);

        $this->db->query("SELECT id, code, status, DATE_FORMAT(created_at, '%b %d, %Y - %h:%i %p') as formatted_date FROM sync_codes WHERE school_id = :school_id ORDER BY created_at DESC");
        $this->db->bind(':school_id', $schoolId);
        $codes = $this->db->resultSet();

        return json_encode(['success' => true, 'payload' => $codes]);
    }

    // --- GENERATE NEW CODE ---
    public function createCode($inputData = null) {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) return json_encode(['success' => false, 'message' => 'Unauthorized']);

        // Generate a random 6-character alphanumeric code
        $isUnique = false;
        $code = '';

        // Loop ensures the code doesn't already exist in the database (highly unlikely, but safe)
        while (!$isUnique) {
            $code = strtoupper(substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6));
            $this->db->query("SELECT id FROM sync_codes WHERE code = :code");
            $this->db->bind(':code', $code);
            if (!$this->db->single()) {
                $isUnique = true;
            }
        }

        $id = UuidHelper::v4();
        
        $this->db->query("INSERT INTO sync_codes (id, school_id, code, status) VALUES (:id, :sch, :code, 'active')");
        $this->db->bind(':id', $id);
        $this->db->bind(':sch', $schoolId);
        $this->db->bind(':code', $code);

        if ($this->db->execute()) {
            return json_encode(['success' => true, 'message' => 'New sync code generated successfully.', 'code' => $code]);
        }
        return json_encode(['success' => false, 'message' => 'Failed to generate code.']);
    }

    // --- TOGGLE STATUS ---
    public function toggleStatus($inputData) {
        $id = $inputData['id'] ?? '';
        $this->db->query("UPDATE sync_codes SET status = IF(status = 'active', 'disabled', 'active') WHERE id = :id AND school_id = :sch");
        $this->db->bind(':id', $id);
        $this->db->bind(':sch', $this->getSchoolId());
        
        if ($this->db->execute()) return json_encode(['success' => true, 'message' => 'Sync code status updated.']);
        return json_encode(['success' => false, 'message' => 'Action failed.']);
    }

    // --- DELETE CODE ---
    public function deleteCode($inputData) {
        $id = $inputData['id'] ?? '';
        
        $this->db->query("DELETE FROM sync_codes WHERE id = :id AND school_id = :sch");
        $this->db->bind(':id', $id);
        $this->db->bind(':sch', $this->getSchoolId());
        
        if ($this->db->execute()) return json_encode(['success' => true, 'message' => 'Sync code deleted permanently.']);
        return json_encode(['success' => false, 'message' => 'Action failed.']);
    }

    // --- VERIFY SYNC CODE FROM LAPTOP ---
    public function verifyCode($inputData) {
        $code = strtoupper(trim($inputData['code'] ?? ''));
        $slug = defined('CURRENT_TENANT_SLUG') ? CURRENT_TENANT_SLUG : null;

        if (empty($code) || !$slug) {
            return json_encode(['success' => false, 'message' => 'Invalid request.']);
        }

        // Check if the code is active and belongs to the correct school
        $this->db->query("
            SELECT sc.id 
            FROM sync_codes sc 
            JOIN schools s ON sc.school_id = s.id 
            WHERE sc.code = :code AND sc.status = 'active' AND s.slug = :slug
        ");
        $this->db->bind(':code', $code);
        $this->db->bind(':slug', $slug);
        
        if ($this->db->single()) {
            return json_encode(['success' => true]);
        }

        return json_encode(['success' => false, 'message' => 'Invalid or disabled sync code.']);
    }
}
?>