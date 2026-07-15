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
}
?>