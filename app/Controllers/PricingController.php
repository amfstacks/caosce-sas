<?php
class PricingController {
    
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // --- FETCH ACTIVE PRICING TIERS ---
    public function getActiveTiers() {
        try {
            // Fetch all active tiers, ordered from lowest volume to highest
            $this->db->query("
                SELECT tier_name, min_slots, max_slots, price_per_slot 
                FROM pricing_tiers 
                WHERE is_active = 1 
                ORDER BY min_slots ASC
            ");
            $tiers = $this->db->resultSet();

            return json_encode([
                'success' => true,
                'payload' => $tiers
            ]);

        } catch (Exception $e) {
            return json_encode(['success' => false, 'message' => 'Database error compiling pricing tiers.']);
        }
    }
}
?>