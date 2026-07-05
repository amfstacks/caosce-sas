<?php
class AdminSetupController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // --- 1. CREATE SESSION & AUTO-GENERATE STATIONS ---
    public function createSession($inputData) {
        $sessionId = UuidHelper::v4();

        try {
            $this->db->query("INSERT INTO exam_sessions (id, school_id, department_id, title, scheduled_date, status) VALUES (:id, :sch, :dept, :title, :sdate, 'draft')");
            $this->db->bind(':id', $sessionId);
            $this->db->bind(':sch', $inputData['school_id']);
            $this->db->bind(':dept', $inputData['department_id']); // 'ns' or 'mw'
            $this->db->bind(':title', $inputData['title']);
            $this->db->bind(':sdate', $inputData['scheduled_date']);
            $this->db->execute();

            // Auto-Generate the 6 NMCN Standard Stations
            for ($i = 1; $i <= 6; $i++) {
                $stationId = UuidHelper::v4();
                $type = ($i % 2 !== 0) ? 'procedure' : 'cbt'; // 1,3,5 = Procedure | 2,4,6 = CBT
                
                $this->db->query("INSERT INTO stations (id, exam_session_id, station_type, title, time_limit_minutes, order_sequence) VALUES (:id, :es_id, :type, :title, :time, :seq)");
                $this->db->bind(':id', $stationId);
                $this->db->bind(':es_id', $sessionId);
                $this->db->bind(':type', $type);
                $this->db->bind(':title', "Station $i - " . strtoupper($type));
                $this->db->bind(':time', 10); // Default 10 minutes per station
                $this->db->bind(':seq', $i);
                $this->db->execute();
            }

            return json_encode(['success' => true, 'session_id' => $sessionId, 'message' => 'Session and 6 stations created successfully.']);
        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Failed to create session.']);
        }
    }

    // --- 2. CSV STUDENT IMPORT ---
    public function importStudents($inputData) {
        // Assume file is uploaded via standard $_FILES in a traditional POST or Base64 in JSON
        $sessionId = $_POST['exam_session_id'];
        $schoolId = $_POST['school_id'];
        $deptId = $_POST['department_id'];
        $file = $_FILES['student_csv']['tmp_name'];

        if (($handle = fopen($file, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle, 1000, ",");

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $matricNumber = trim($data[0]);
                $fullName = trim($data[1]);
                
                // Generate a random 6-character raw password for offline recovery
                $rawPassword = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
                $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);

                // 1. Check if student exists in this school
                $this->db->query("SELECT id FROM students WHERE school_id = :sch AND matric_number = :matric");
                $this->db->bind(':sch', $schoolId);
                $this->db->bind(':matric', $matricNumber);
                $existing = $this->db->single();

                if ($existing) {
                    $studentId = $existing['id'];
                } else {
                    // Create new student
                    $studentId = UuidHelper::v4();
                    $this->db->query("INSERT INTO students (id, school_id, department_id, matric_number, full_name, password_hash, raw_password) VALUES (:id, :sch, :dept, :matric, :fname, :hash, :raw)");
                    $this->db->bind(':id', $studentId);
                    $this->db->bind(':sch', $schoolId);
                    $this->db->bind(':dept', $deptId);
                    $this->db->bind(':matric', $matricNumber);
                    $this->db->bind(':fname', $fullName);
                    $this->db->bind(':hash', $passwordHash);
                    $this->db->bind(':raw', $rawPassword);
                    $this->db->execute();
                }

                // 2. Map student to the Exam Session
                $pivotId = UuidHelper::v4();
                $this->db->query("INSERT IGNORE INTO exam_session_student (id, exam_session_id, student_id, status) VALUES (:id, :sess, :stud, 'pending')");
                $this->db->bind(':id', $pivotId);
                $this->db->bind(':sess', $sessionId);
                $this->db->bind(':stud', $studentId);
                $this->db->execute();
            }
            fclose($handle);
            return json_encode(['success' => true, 'message' => 'Students imported and enrolled successfully.']);
        }
        return json_encode(['success' => false, 'message' => 'Failed to read CSV file.']);
    }

    // --- 3. STATION ALLOCATION ---
    public function allocateExaminer($inputData) {
        $this->db->query("UPDATE stations SET examiner_id = :ex_id WHERE id = :st_id AND station_type = 'procedure'");
        $this->db->bind(':ex_id', $inputData['examiner_id']);
        $this->db->bind(':st_id', $inputData['station_id']);
        
        if ($this->db->execute()) {
            return json_encode(['success' => true]);
        }
        return json_encode(['success' => false]);
    }
}
?>