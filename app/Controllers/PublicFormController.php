<?php
// Adjust this path based on where you placed the PHPMailer files
require_once APPROOT . '/Core/PHPMailer-master/src/Exception.php';
require_once APPROOT . '/Core/PHPMailer-master/src/PHPMailer.php';
require_once APPROOT . '/Core/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PublicFormController {
    
    private $db;

    public function __construct() {
        // Initialize the Database core class
        $this->db = new Database();
    }
    
    public function submitContact($inputData) {
        $name = htmlspecialchars(strip_tags($inputData['name'] ?? ''));
        $email = filter_var($inputData['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars(strip_tags($inputData['phone'] ?? ''));
        $message = htmlspecialchars(strip_tags($inputData['message'] ?? ''));

        if (empty($name) || empty($email) || empty($message)) {
            return json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        }

        // --- 1. INSERT INTO DATABASE ---
        $this->db->query("INSERT INTO contact_messages (name, email, phone, message) VALUES (:name, :email, :phone, :message)");
        $this->db->bind(':name', $name);
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':message', $message);
        $this->db->execute();

        // --- 2. SEND EMAILS ---
        
        // Email to Admin
        $adminSubject = "New Support Request from $name";
        $adminBody = "<h3>New Contact Request</h3>
                      <p><strong>Name:</strong> $name</p>
                      <p><strong>Email:</strong> $email</p>
                      <p><strong>Phone:</strong> $phone</p>
                      <p><strong>Message:</strong><br/>".nl2br($message)."</p>";
        $this->sendSMTP(SYSTEM_EMAIL, $adminSubject, $adminBody);

        // Auto-reply to User
        $userSubject = "We received your message - CAOSCE Systems";
        $userBody = "<h3>Hello $name,</h3>
                     <p>Thank you for reaching out to CAOSCE Systems. We have received your message and our support team will get back to you shortly.</p>
                     <p>Best Regards,<br/>The CAOSCE Team</p>";
        $this->sendSMTP($email, $userSubject, $userBody);

        return json_encode(['success' => true, 'message' => 'Message sent successfully.']);
    }

    public function submitRequest($inputData) {
        $school = htmlspecialchars(strip_tags($inputData['institution_name'] ?? ''));
        $contact = htmlspecialchars(strip_tags($inputData['contact_person'] ?? ''));
        $role = htmlspecialchars(strip_tags($inputData['role'] ?? ''));
        $email = filter_var($inputData['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars(strip_tags($inputData['phone'] ?? ''));
        $capacity = htmlspecialchars(strip_tags($inputData['capacity'] ?? ''));

        if (empty($school) || empty($email) || empty($phone)) {
            return json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
        }

        // --- 1. INSERT INTO DATABASE ---
        $this->db->query("INSERT INTO demo_requests (institution_name, contact_person, role, email, phone, capacity) 
                          VALUES (:school, :contact, :role, :email, :phone, :capacity)");
        $this->db->bind(':school', $school);
        $this->db->bind(':contact', $contact);
        $this->db->bind(':role', $role);
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':capacity', $capacity);
        $this->db->execute();

        // --- 2. SEND EMAILS ---
        
        // Email to Admin (Sales/Onboarding)
        $adminSubject = "New Demo Request: $school";
        $adminBody = "<h3>New Institution Onboarding Request</h3>
                      <ul>
                          <li><strong>Institution:</strong> $school</li>
                          <li><strong>Contact Person:</strong> $contact ($role)</li>
                          <li><strong>Email:</strong> $email</li>
                          <li><strong>Phone:</strong> $phone</li>
                          <li><strong>Capacity:</strong> $capacity</li>
                      </ul>";
        $this->sendSMTP(SYSTEM_EMAIL, $adminSubject, $adminBody);
        $adminResult = $this->sendSMTP(SYSTEM_EMAIL, $adminSubject, $adminBody);

        // if ($adminResult !== true) {
        //     return json_encode(['success' => false, 'message' => 'SMTP Error: ' . $adminResult]);
        // }

        // Auto-reply to User
        $userSubject = "Your CAOSCE Demo Request is Confirmed!";
        $userBody = "<h3>Hello $contact,</h3>
                     <p>Thank you for requesting access to CAOSCE for <strong>$school</strong>.</p>
                     <p>Our onboarding team is reviewing your details and will call you shortly at $phone to schedule a live demonstration and discuss provisioning your offline workspace.</p>
                     <p>Best Regards,<br/>CAOSCE Onboarding Team</p>";
        $this->sendSMTP($email, $userSubject, $userBody);

        return json_encode(['success' => true, 'message' => 'Demo request submitted successfully.']);
    }

    // --- Core SMTP Helper Method ---
    private function sendSMTP($toEmail, $subject, $bodyHTML) {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Or ENCRYPTION_STARTTLS
            $mail->Port       = SMTP_PORT;

            // Recipients
            $mail->setFrom(SMTP_USER, SYSTEM_NAME);
            $mail->addAddress($toEmail);
            $mail->addReplyTo(SYSTEM_EMAIL, SYSTEM_NAME);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHTML;
            $mail->AltBody = strip_tags($bodyHTML); // Fallback for non-HTML clients

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log error silently in production
            error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>