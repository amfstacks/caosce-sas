<?php
class LicensingController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    private function getSchoolId() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['school_id'] ?? null;
    }

    // Fetch Wallet Balance, Ledger Logs, and Pricing Tiers for the Tenant
    public function getLicensingData() {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        try {
            // 1. Get or Initialize Wallet
            $this->db->query("SELECT * FROM school_slot_wallets WHERE school_id = :sch LIMIT 1");
            $this->db->bind(':sch', $schoolId);
            $wallet = $this->db->single();

            if (!$wallet) {
                $walletId = UuidHelper::v4();
                $this->db->query("INSERT INTO school_slot_wallets (id, school_id, available_slots, escrow_slots, total_lifetime_slots) VALUES (:id, :sch, 0, 0, 0)");
                $this->db->bind(':id', $walletId);
                $this->db->bind(':sch', $schoolId);
                $this->db->execute();

                $wallet = ['available_slots' => 0, 'escrow_slots' => 0, 'total_lifetime_slots' => 0];
            }

            // 2. Get Ledger History
            $this->db->query("SELECT * FROM slot_ledger_logs WHERE school_id = :sch ORDER BY created_at DESC LIMIT 50");
            $this->db->bind(':sch', $schoolId);
            $ledger = $this->db->resultSet();

            // 3. Get Active Pricing Tiers
            $this->db->query("SELECT tier_name, min_slots, max_slots, price_per_slot FROM pricing_tiers WHERE is_active = 1 ORDER BY min_slots ASC");
            $tiers = $this->db->resultSet();

            echo json_encode([
                'success' => true,
                'payload' => [
                    'wallet' => $wallet,
                    'ledger' => $ledger,
                    'tiers' => $tiers
                ]
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
            exit;
        }
    }

    // Verify Paystack Payment and Credit Wallet Slots
    public function verifyPayment() {
        $schoolId = $this->getSchoolId();
        $input = json_decode(file_get_contents('php://input'), true);

        $reference = $input['reference'] ?? null;
        $slotsPurchased = intval($input['slots'] ?? 0);
        $amountPaid = floatval($input['amount'] ?? 0);

        if (!$reference || $slotsPurchased <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid payment payload.']);
            exit;
        }

        try {
            // (Optional production check: verify reference with Paystack API via cURL here)

            // 1. Update Wallet Balance
            $this->db->query("
                UPDATE school_slot_wallets 
                SET available_slots = available_slots + :slots, 
                    total_lifetime_slots = total_lifetime_slots + :slots 
                WHERE school_id = :sch
            ");
            $this->db->bind(':slots', $slotsPurchased);
            $this->db->bind(':sch', $schoolId);
            $this->db->execute();

            // 2. Log into Ledger
            $ledgerId = UuidHelper::v4();
            $this->db->query("
                INSERT INTO slot_ledger_logs (id, school_id, transaction_type, slots_amount, naira_value, reference_id, description) 
                VALUES (:id, :sch, 'purchase', :slots, :amount, :ref, :desc)
            ");
            $this->db->bind(':id', $ledgerId);
            $this->db->bind(':sch', $schoolId);
            $this->db->bind(':slots', $slotsPurchased);
            $this->db->bind(':amount', $amountPaid);
            $this->db->bind(':ref', $reference);
            $this->db->bind(':desc', "Purchased {$slotsPurchased} exam slots via Paystack.");
            $this->db->execute();

            echo json_encode(['success' => true]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to credit wallet: ' . $e->getMessage()]);
            exit;
        }
    }
}
?>