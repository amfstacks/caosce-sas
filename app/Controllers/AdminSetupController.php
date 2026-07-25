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

    // Inside your AdminSetupController.php
public function uploadSchoolLogo() {
    // Assuming you fetched the school_id using the CURRENT_TENANT_SLUG
    $schoolId = $_POST['school_id']; 
    $file = $_FILES['school_logo'];

    // Ensure it's a valid image
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (in_array($file['type'], $allowedTypes)) {
        
        // Create a safe, unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . $schoolId . '_' . time() . '.' . $extension;
        $destination = '../public/assets/logos/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Update the schools table with the filepath and mark setup as complete
            $this->db->query("UPDATE schools SET logo_path = :logo, is_setup_complete = 1 WHERE id = :id");
            $this->db->bind(':logo', $filename);
            $this->db->bind(':id', $schoolId);
            $this->db->execute();
            
            echo json_encode(['success' => true]);
            return;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Invalid file upload.']);
}

// --- FETCH SCHOOL SETTINGS ---
    public function getSchoolDetails($inputData = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $schoolId = $_SESSION['school_id'] ?? null;

        if (!$schoolId) {
            return json_encode(['success' => false, 'message' => 'Unauthorized']);
        }

        $this->db->query("SELECT name, slug, logo_path, cover_image_path FROM schools WHERE id = :id LIMIT 1");
        $this->db->bind(':id', $schoolId);
        $school = $this->db->single();

       if ($school) {
            $baseDir = defined('BASE_PATH') ? BASE_PATH : '';
            $cacheBuster = '?v=' . time(); // Forces browser to load the freshest image
            
            if (!empty($school['logo_path'])) {
                $school['logo_url'] = $baseDir . '/uploads/' . $school['slug'] . '/' . $school['logo_path'] . $cacheBuster;
            }
            if (!empty($school['cover_image_path'])) {
                $school['cover_url'] = $baseDir . '/uploads/' . $school['slug'] . '/' . $school['cover_image_path'] . $cacheBuster;
            }
            return json_encode(['success' => true, 'payload' => $school]);
        }

        return json_encode(['success' => false, 'message' => 'School not found']);
    }

    // --- UPDATE SCHOOL SETTINGS (Text + Files) ---
    public function updateSchoolDetails_old($inputData = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $schoolId = $_SESSION['school_id'] ?? null;
        $slug = CURRENT_TENANT_SLUG;

        if (!$schoolId || !$slug) {
            return json_encode(['success' => false, 'message' => 'Unauthorized or missing workspace.']);
        }

        // We use $_POST directly because FormData sends multipart/form-data, not raw JSON
        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            return json_encode(['success' => false, 'message' => 'School name cannot be empty.']);
        }

        // Define the tenant's upload directory (e.g., /uploads/yag/)
        // We use $_SERVER['DOCUMENT_ROOT'] mixed with your BASE_PATH to get the physical server path
        $physicalBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])), '/');
        // If your script is in public/index.php, step back one level. Adjust as needed.
        $physicalBase = preg_replace('/\/public$/', '', $physicalBase); 
        
        $uploadDir = $physicalBase . '/uploads/' . $slug . '/';

        // Create the directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $logoFilename = null;
        $coverFilename = null;
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

        // Handle Logo Upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['logo']['type'], $allowedTypes)) {
                $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $logoFilename = 'logo_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $logoFilename);
            }
        }

        // Handle Cover Image Upload
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            if (in_array($_FILES['cover']['type'], $allowedTypes)) {
                $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
                $coverFilename = 'cover_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['cover']['tmp_name'], $uploadDir . $coverFilename);
            }
        }

        // Dynamically build the UPDATE query based on what was uploaded
        $query = "UPDATE schools SET name = :name, is_setup_complete = 1";
        if ($logoFilename) $query .= ", logo_path = :logo";
        if ($coverFilename) $query .= ", cover_image_path = :cover";
        $query .= " WHERE id = :id";

        $this->db->query($query);
        $this->db->bind(':name', $name);
        $this->db->bind(':id', $schoolId);
        if ($logoFilename) $this->db->bind(':logo', $logoFilename);
        if ($coverFilename) $this->db->bind(':cover', $coverFilename);

        if ($this->db->execute()) {
            return json_encode(['success' => true, 'message' => 'Workspace settings updated successfully!']);
        }

        return json_encode(['success' => false, 'message' => 'Failed to update database.']);
    }

    // --- UPDATE SCHOOL SETTINGS (Text + Compressed Files) ---
    public function updateSchoolDetails($inputData = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $schoolId = $_SESSION['school_id'] ?? null;
        $slug = CURRENT_TENANT_SLUG;

        if (!$schoolId || !$slug) {
            return json_encode(['success' => false, 'message' => 'Unauthorized or missing workspace.']);
        }

        $name = trim($_POST['name'] ?? '');

        if (empty($name)) {
            return json_encode(['success' => false, 'message' => 'School name cannot be empty.']);
        }

        // 1. FIX THE DIRECTORY PATH
        // Get the directory where index.php lives (which is /public)
        $publicDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'])), '/');
        $uploadDir = $publicDir . '/uploads/' . $slug . '/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $logoFilename = null;
        $coverFilename = null;

        // 2. HELPER FUNCTION FOR COMPRESSION & SECURITY
        $processImage = function($fileArray, $prefix, $uploadDir) {
            // A. Enforce 2MB Size Limit (2 * 1024 * 1024 bytes)
            $maxSize = 2097152; 
            if ($fileArray['size'] > $maxSize) {
                return ['error' => 'File exceeds the 2MB size limit.'];
            }

            // B. Strict MIME Type Validation
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $fileArray['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowedMimes)) {
                return ['error' => 'Invalid file type. Only JPG, PNG, and WebP are allowed.'];
            }

            // C. Compress and Convert to JPG
            // $filename = $prefix . '_' . time() . '.jpg';
            $filename = $prefix . '.jpg';
            $destination = $uploadDir . $filename;

            if ($mime == 'image/jpeg') {
                $image = imagecreatefromjpeg($fileArray['tmp_name']);
            } elseif ($mime == 'image/png') {
                $image = imagecreatefrompng($fileArray['tmp_name']);
                // Convert transparent PNG backgrounds to white for JPG format
                $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
                imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                imagealphablending($bg, TRUE);
                imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                imagedestroy($image);
                $image = $bg;
            } elseif ($mime == 'image/webp') {
                $image = imagecreatefromwebp($fileArray['tmp_name']);
            }

            if (!$image) {
                return ['error' => 'Failed to process image file.'];
            }

            // Save the compressed image at 75% quality
            imagejpeg($image, $destination, 75);
            imagedestroy($image); // Free up memory

            return ['success' => true, 'filename' => $filename];
        };

        // 3. PROCESS UPLOADS
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoResult = $processImage($_FILES['logo'], 'logo', $uploadDir);
            if (isset($logoResult['error'])) {
                return json_encode(['success' => false, 'message' => 'Logo Error: ' . $logoResult['error']]);
            }
            $logoFilename = $logoResult['filename'];
        }

        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $coverResult = $processImage($_FILES['cover'], 'cover', $uploadDir);
            if (isset($coverResult['error'])) {
                return json_encode(['success' => false, 'message' => 'Cover Error: ' . $coverResult['error']]);
            }
            $coverFilename = $coverResult['filename'];
        }

        // 4. UPDATE DATABASE
        $query = "UPDATE schools SET name = :name, is_setup_complete = 1";
        if ($logoFilename) $query .= ", logo_path = :logo";
        if ($coverFilename) $query .= ", cover_image_path = :cover";
        $query .= " WHERE id = :id";

        $this->db->query($query);
        $this->db->bind(':name', $name);
        $this->db->bind(':id', $schoolId);
        if ($logoFilename) $this->db->bind(':logo', $logoFilename);
        if ($coverFilename) $this->db->bind(':cover', $coverFilename);

        if ($this->db->execute()) {
            return json_encode(['success' => true, 'message' => 'Workspace settings updated and optimized successfully!']);
        }

        return json_encode(['success' => false, 'message' => 'Failed to update database.']);
    }

}
?>