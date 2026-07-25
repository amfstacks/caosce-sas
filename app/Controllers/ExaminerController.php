<?php
class ExaminerController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function getSchoolId() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['school_id'] ?? null;
    }

    // --- FETCH EXAMINERS ---
    public function getExaminers($inputData = null) {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) return json_encode(['success' => false, 'message' => 'Unauthorized']);

        $this->db->query("SELECT id, username, raw_password, full_name, status, created_at FROM users WHERE school_id = :school_id AND role = 'examiner' ORDER BY created_at DESC");
        $this->db->bind(':school_id', $schoolId);
        $examiners = $this->db->resultSet();

        return json_encode(['success' => true, 'payload' => $examiners]);
    }

    // --- ADD SINGLE EXAMINER ---
    public function addExaminer($inputData) {
        $schoolId = $this->getSchoolId();
        
        $username = trim($inputData['username'] ?? '');
        $password = trim($inputData['password'] ?? '');
        $fullName = trim($inputData['full_name'] ?? '');

        if (empty($username) || empty($password) || empty($fullName)) {
            return json_encode(['success' => false, 'message' => 'All fields are required.']);
        }

        // Check uniqueness
        $this->db->query("SELECT id FROM users WHERE username = :username");
        $this->db->bind(':username', $username);
        if ($this->db->single()) {
            return json_encode(['success' => false, 'message' => 'Username already exists.']);
        }

        $userId = UuidHelper::v4();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->db->query("INSERT INTO users (id, school_id, role, username, password_hash, raw_password, full_name, status) VALUES (:id, :sch, 'examiner', :usr, :hash, :raw, :fname, 'active')");
        $this->db->bind(':id', $userId);
        $this->db->bind(':sch', $schoolId);
        $this->db->bind(':usr', $username);
        $this->db->bind(':hash', $hash);
        $this->db->bind(':raw', $password);
        $this->db->bind(':fname', $fullName);

        if ($this->db->execute()) {
            return json_encode(['success' => true, 'message' => 'Examiner added successfully.']);
        }
        return json_encode(['success' => false, 'message' => 'Failed to add examiner.']);
    }

    // --- BULK CSV UPLOAD ---
    public function importExaminers($inputData = null) {
        $schoolId = $this->getSchoolId();
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            return json_encode(['success' => false, 'message' => 'Invalid file upload.']);
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $addedCount = 0;
        $skipCount = 0;

        if (($handle = fopen($file, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ","); // Skip header row

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $fullName = trim($data[0] ?? '');
                $username = trim($data[1] ?? '');
                $password = trim($data[2] ?? '');

                if (empty($fullName) || empty($username) || empty($password)) continue;

                // Check for duplicates
                $this->db->query("SELECT id FROM users WHERE username = :usr");
                $this->db->bind(':usr', $username);
                if ($this->db->single()) {
                    $skipCount++;
                    continue;
                }

                $userId = UuidHelper::v4();
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $this->db->query("INSERT INTO users (id, school_id, role, username, password_hash, raw_password, full_name, status) VALUES (:id, :sch, 'examiner', :usr, :hash, :raw, :fname, 'active')");
                $this->db->bind(':id', $userId);
                $this->db->bind(':sch', $schoolId);
                $this->db->bind(':usr', $username);
                $this->db->bind(':hash', $hash);
                $this->db->bind(':raw', $password);
                $this->db->bind(':fname', $fullName);
                $this->db->execute();
                
                $addedCount++;
            }
            fclose($handle);
            return json_encode(['success' => true, 'message' => "Import complete! Added $addedCount new examiners. Skipped $skipCount duplicates."]);
        }
        return json_encode(['success' => false, 'message' => 'Failed to read CSV.']);
    }

    // --- UPDATE EXAMINER ---
    public function updateExaminer($inputData) {
        $schoolId = $this->getSchoolId();
        $id = $inputData['id'] ?? '';
        $username = trim($inputData['username'] ?? '');
        $fullName = trim($inputData['full_name'] ?? '');
        $password = trim($inputData['password'] ?? '');

        // Verify ownership
        $this->db->query("SELECT id FROM users WHERE id = :id AND school_id = :sch");
        $this->db->bind(':id', $id);
        $this->db->bind(':sch', $schoolId);
        if (!$this->db->single()) return json_encode(['success' => false, 'message' => 'Examiner not found.']);

        $query = "UPDATE users SET full_name = :fname, username = :usr";
        if (!empty($password)) {
            $query .= ", password_hash = :hash, raw_password = :raw";
        }
        $query .= " WHERE id = :id";

        $this->db->query($query);
        $this->db->bind(':fname', $fullName);
        $this->db->bind(':usr', $username);
        $this->db->bind(':id', $id);
        
        if (!empty($password)) {
            $this->db->bind(':hash', password_hash($password, PASSWORD_DEFAULT));
            $this->db->bind(':raw', $password);
        }

        if ($this->db->execute()) {
            return json_encode(['success' => true, 'message' => 'Examiner updated successfully.']);
        }
        return json_encode(['success' => false, 'message' => 'Update failed.']);
    }

    // --- TOGGLE STATUS ---
    public function toggleStatus($inputData) {
        $id = $inputData['id'] ?? '';
        $this->db->query("UPDATE users SET status = IF(status = 'active', 'disabled', 'active') WHERE id = :id AND school_id = :sch");
        $this->db->bind(':id', $id);
        $this->db->bind(':sch', $this->getSchoolId());
        
        if ($this->db->execute()) return json_encode(['success' => true, 'message' => 'Status updated.']);
        return json_encode(['success' => false, 'message' => 'Action failed.']);
    }

    // --- DELETE & ARCHIVE ---
    public function deleteExaminer($inputData) {
        $id = $inputData['id'] ?? '';
        $schoolId = $this->getSchoolId();

        // 1. Fetch user data
        $this->db->query("SELECT * FROM users WHERE id = :id AND school_id = :sch");
        $this->db->bind(':id', $id);
        $this->db->bind(':sch', $schoolId);
        $user = $this->db->single();

        if (!$user) return json_encode(['success' => false, 'message' => 'Examiner not found.']);

        try {
            $this->db->beginTransaction();

            // 2. Insert into deleted_users
            $this->db->query("INSERT INTO deleted_users (id, school_id, role, username, password_hash, raw_password, full_name, status, original_created_at) VALUES (:id, :sch, :role, :usr, :hash, :raw, :fname, :status, :created)");
            $this->db->bind(':id', $user['id']);
            $this->db->bind(':sch', $user['school_id']);
            $this->db->bind(':role', $user['role']);
            $this->db->bind(':usr', $user['username']);
            $this->db->bind(':hash', $user['password_hash']);
            $this->db->bind(':raw', $user['raw_password']);
            $this->db->bind(':fname', $user['full_name']);
            $this->db->bind(':status', $user['status']);
            $this->db->bind(':created', $user['created_at']);
            $this->db->execute();

            // 3. Delete from active users
            $this->db->query("DELETE FROM users WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->commit();
            return json_encode(['success' => true, 'message' => 'Examiner deleted and archived.']);
        } catch (Exception $e) {
            $this->db->rollBack();
            return json_encode(['success' => false, 'message' => 'Failed to delete examiner.']);
        }
    }
}
?>