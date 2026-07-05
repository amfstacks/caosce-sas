<?php
class SyncController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // 1. Receives individual clicks/ticks in real-time (or from the offline queue)
    public function logAction($inputData) {
        $studentId = $inputData['student_id'];
        $examSessionId = $inputData['exam_session_id'];
        $questionId = $inputData['question_id']; // Can be a CBT question ID or Procedure Rubric ID
        $answer = $inputData['answer']; // Can be an option ID or a numeric score

        // Insert or Update the response
        $this->db->query("
            INSERT INTO student_responses (id, student_id, exam_session_id, question_id, answer_data) 
            VALUES (:id, :sid, :esid, :qid, :ans)
            ON DUPLICATE KEY UPDATE answer_data = :ans
        ");
        $this->db->bind(':id', UuidHelper::v4());
        $this->db->bind(':sid', $studentId);
        $this->db->bind(':esid', $examSessionId);
        $this->db->bind(':qid', $questionId);
        $this->db->bind(':ans', $answer);

        if ($this->db->execute()) {
            return json_encode(['success' => true]);
        }
        return json_encode(['success' => false], 500);
    }

    // 2. Receives the Final Score from an Examiner at a Procedure Station
    public function finalizeScore($inputData) {
        $this->saveFinalStationScore(
            $inputData['student_id'],
            $inputData['exam_session_id'],
            $inputData['station_id'],
            $inputData['total_score']
        );
        return json_encode(['success' => true]);
    }

    // 3. Receives the Final Submission from a Student at a CBT Station
    public function finalizeCbt($inputData) {
        $studentId = $inputData['student_id'];
        $examSessionId = $inputData['exam_session_id'];
        $stationId = $inputData['station_id'];
        $answers = $inputData['answers']; // Associative array of {question_id: option_id}

        // Loop through the CBT answers and save them
        foreach ($answers as $qId => $optId) {
            $this->logAction([
                'student_id' => $studentId,
                'exam_session_id' => $examSessionId,
                'question_id' => $qId,
                'answer' => $optId
            ]);
        }

        // Calculate CBT Score automatically
        $totalScore = 0;
        foreach ($answers as $qId => $optId) {
            // Find if the selected option is correct (Assuming you have an 'is_correct' boolean in cbt_options)
            $this->db->query("SELECT o.is_correct, q.marks FROM cbt_options o JOIN cbt_questions q ON o.question_id = q.id WHERE o.id = :oid AND q.id = :qid");
            $this->db->bind(':oid', $optId);
            $this->db->bind(':qid', $qId);
            $result = $this->db->single();
            
            if ($result && $result['is_correct']) {
                $totalScore += $result['marks'];
            }
        }

        $this->saveFinalStationScore($studentId, $examSessionId, $stationId, $totalScore);
        
        // Mark student as completed for this specific station so they can move on
        $this->db->query("UPDATE exam_session_student SET status = 'in_progress' WHERE student_id = :sid AND exam_session_id = :esid");
        $this->db->bind(':sid', $studentId);
        $this->db->bind(':esid', $examSessionId);
        $this->db->execute();

        return json_encode(['success' => true]);
    }

    // Internal helper to save the station score
    private function saveFinalStationScore($studentId, $sessionId, $stationId, $score) {
        $this->db->query("
            INSERT INTO station_scores (id, student_id, exam_session_id, station_id, score) 
            VALUES (:id, :sid, :esid, :stid, :score)
            ON DUPLICATE KEY UPDATE score = :score
        ");
        $this->db->bind(':id', UuidHelper::v4());
        $this->db->bind(':sid', $studentId);
        $this->db->bind(':esid', $sessionId);
        $this->db->bind(':stid', $stationId);
        $this->db->bind(':score', $score);
        $this->db->execute();
    }
}
?>