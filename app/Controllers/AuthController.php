<?php
class AuthController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function handleLogin($inputData) {
        $username = $inputData['username'];
        $password = $inputData['password'];
        $deviceSig = $inputData['device_signature'] ?? null;

        // Helper function to check if user belongs to the slug in the URL
        $validateTenant = function($userSchoolId) {
            if (CURRENT_TENANT_SLUG !== null) {
                $this->db->query("SELECT id FROM schools WHERE slug = :slug");
                $this->db->bind(':slug', CURRENT_TENANT_SLUG);
                $school = $this->db->single();
                
                // If the URL slug doesn't match the user's actual school, reject them
                if ($school && $school['id'] !== $userSchoolId && $userSchoolId !== null) {
                    return false;
                }
            }
            return true;
        };

        // 1. ADMIN ROUTING
        $this->db->query("SELECT * FROM users WHERE username = :username AND role IN ('superadmin', 'subadmin')");
        $this->db->bind(':username', $username);
        $admin = $this->db->single();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            if (!$validateTenant($admin['school_id'])) return json_encode(['success' => false, 'message' => 'Invalid school portal.']);
            return json_encode(['success' => true, 'redirect_url' => '/admin/dashboard']);
        }

        // --- SECURITY GATE: For roles below, a device signature MUST be present ---
        if (!$deviceSig) {
            return json_encode(['success' => false, 'message' => 'Access Denied: This device has not been bound by an Administrator.']);
        }

        $this->db->query("
            SELECT db.*, s.station_type, s.order_sequence 
            FROM device_bindings db
            JOIN stations s ON db.station_id = s.id
            WHERE db.device_signature = :sig
        ");
        $this->db->bind(':sig', $deviceSig);
        $binding = $this->db->single();

        if (!$binding) return json_encode(['success' => false, 'message' => 'Access Denied: Invalid device signature.']);

        // 2. EXAMINER ROUTING
        $this->db->query("SELECT * FROM users WHERE username = :username AND role = 'examiner'");
        $this->db->bind(':username', $username);
        $examiner = $this->db->single();

        if ($examiner && password_verify($password, $examiner['password_hash'])) {
            if (!$validateTenant($examiner['school_id'])) return json_encode(['success' => false, 'message' => 'Invalid school portal.']);
            if ($binding['examiner_id'] !== $examiner['id']) return json_encode(['success' => false, 'message' => 'Not assigned to this laptop.']);
            
            if ($binding['examiner_id'] !== $examiner['id']) {
                // Login is successful, but they are at the wrong physical laptop
                return json_encode(['success' => true, 'redirect_url' => '/examiner/mismatch']);
            }
            return json_encode(['success' => true, 'redirect_url' => '/examiner/rubric?station_id=' . $binding['station_id']]);
        }

        // 3. STUDENT ROUTING
        $this->db->query("SELECT * FROM students WHERE matric_number = :username");
        $this->db->bind(':username', $username);
        $student = $this->db->single();

        if ($student && password_verify($password, $student['password_hash'])) {
            if (!$validateTenant($student['school_id'])) return json_encode(['success' => false, 'message' => 'Invalid school portal.']);
            
            $this->db->query("SELECT * FROM exam_session_student WHERE student_id = :sid AND exam_session_id = :eid");
            $this->db->bind(':sid', $student['id']);
            $this->db->bind(':eid', $binding['exam_session_id']);
            if (!$this->db->single()) return json_encode(['success' => false, 'message' => 'Not enrolled in this active exam session.']);

            if ($binding['station_type'] === 'cbt') {
                return json_encode(['success' => true, 'redirect_url' => '/student/cbt?station_id=' . $binding['station_id']]);
            } else {
                return json_encode(['success' => true, 'redirect_url' => '/student/procedure_standby?station_id=' . $binding['station_id']]);
            }
        }

        return json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }

   public function handleAdminLogin($inputData) {
        // Start session if not already started, so we can securely persist admin state
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $username = trim($inputData['username'] ?? '');
        $password = $inputData['password'] ?? '';

        if (empty($username) || empty($password)) {
            return json_encode(['success' => false, 'message' => 'Username and password are required.']);
        }

        // Fast query: Only look for admins
        $this->db->query("SELECT * FROM users WHERE username = :username AND role IN ('superadmin', 'subadmin') LIMIT 1");
        $this->db->bind(':username', $username);
        $admin = $this->db->single();
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            
            $targetSlug = null;

            // 1. If this admin belongs to a specific school, automatically fetch their slug
            if (!empty($admin['school_id'])) {
                $this->db->query("SELECT slug FROM schools WHERE id = :school_id LIMIT 1");
                $this->db->bind(':school_id', $admin['school_id']);
                $school = $this->db->single();

                if ($school) {
                    $targetSlug = $school['slug'];
                }

                // Security Check: If they tried logging into a SPECIFIC workspace url (e.g., /demo2026/admin/login)
                // but they actually belong to 'yag', we must block them.
                if (defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG !== null && CURRENT_TENANT_SLUG !== $targetSlug) {
                    return json_encode(['success' => false, 'message' => 'Unauthorized workspace access.']);
                }
            }

            // Secure the session for backend dashboard access
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_name'] = $admin['full_name'] ?? $admin['username'];
            $_SESSION['school_id'] = $admin['school_id'];
            
            // Optional: Store slug in session to easily build links in the admin dashboard later
            $_SESSION['tenant_slug'] = $targetSlug;

            // 2. Build the dynamic redirect URL based on the fetched slug
            // If they have a slug, send them to their branded dashboard. Otherwise, global dashboard.
            $redirectUrl = $targetSlug ? '/' . $targetSlug . '/admin/dashboard' : '/admin/dashboard';

            return json_encode([
                'success' => true, 
                'redirect_url' => $redirectUrl,
                'slug' => $targetSlug // Returning the slug just in case the JS needs it
            ]);
        }

        return json_encode(['success' => false, 'message' => 'Invalid admin credentials.']);
    }

    public function getTenantInfo($inputData = null) {
    if (!defined('CURRENT_TENANT_SLUG') || CURRENT_TENANT_SLUG === null) {
        return json_encode(['success' => false, 'message' => 'No workspace specified.']);
    }
// sleep(10);
    $this->db->query("SELECT name, logo_path ,cover_image_path FROM schools WHERE slug = :slug ");
    $this->db->bind(':slug', CURRENT_TENANT_SLUG);
    $school = $this->db->single();

    if ($school) {
        $baseDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

        // 2. Concatenate the Cover Image path if a filename exists
        if (!empty($school['cover_image_path'])) {
            $school['cover_image_path'] = $baseDir . '/uploads/' . CURRENT_TENANT_SLUG . '/' . $school['cover_image_path'];
        }

        // 3. Concatenate the Logo path if a filename exists
        if (!empty($school['logo_path'])) {
            $school['logo_path'] = $baseDir . '/uploads/' . CURRENT_TENANT_SLUG . '/' . $school['logo_path'];
        }
        return json_encode(['success' => true, 'payload' => $school]);
    }

    return json_encode(['success' => false, 'message' => 'School not found or license inactive.']);
}
public function validateWorkspace($inputData = null) {
        // Depending on how your Router passes GET data, we check both $inputData and $_GET
        $slug = $inputData['slug'] ?? $_GET['slug'] ?? '';
        $slug = trim(strtolower($slug));

        if (empty($slug)) {
            return json_encode(['success' => false, 'message' => 'Workspace code is required.']);
        }

        $this->db->query("SELECT slug, name, license_status FROM schools WHERE slug = :slug LIMIT 1");
        $this->db->bind(':slug', $slug);
        $school = $this->db->single();

        if (!$school) {
            return json_encode([
                'success' => false, 
                'message' => "Workspace '" . strtoupper($slug) . "' not found. Please check your code."
            ]);
        }

        // Check if the license is active
        if ($school['license_status'] === 'suspended') {
            return json_encode([
                'success' => false, 
                'message' => 'This workspace is currently suspended. Contact administration.'
            ]);
        }

        if ($school['license_status'] === 'expired') {
            return json_encode([
                'success' => false, 
                'message' => 'This workspace license has expired.'
            ]);
        }

        // Active and valid!
        return json_encode([
            'success' => true,
            'payload' => [
                'slug' => $school['slug'],
                'name' => $school['name']
            ]
        ]);
    }
}
?>