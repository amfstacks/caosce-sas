<?php
class AdminController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function bindDevice($inputData) {
        // Generate a UUID for the binding record itself
        $bindingId = UuidHelper::v4();

        $this->db->query('INSERT INTO device_bindings (id, exam_session_id, station_id, examiner_id, device_signature) VALUES (:id, :session, :station, :examiner, :sig)');
        
        $this->db->bind(':id', $bindingId);
        $this->db->bind(':session', $inputData['exam_session_id']);
        $this->db->bind(':station', $inputData['station_id']);
        $this->db->bind(':examiner', $inputData['examiner_id']); // Nullable for CBT
        $this->db->bind(':sig', $inputData['device_signature']);

        if ($this->db->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error during binding.']);
        }
    }
}
?>