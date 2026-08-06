<?php
class LicensingController {
    private $db;
    
    // Replace this with your actual Paystack Secret Key (ideally from a config file)
    private $paystackSecretKey = ''; 

    public function __construct() {
        $this->db = new Database();
    }

    private function getSchoolId() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['school_id'] ?? null;
    }

    // --- 1. FETCH LICENSING DATA & CALCULATE WALLET ---
    // public function getLicensingData() {
    //     $schoolId = $this->getSchoolId();
    //     if (!$schoolId) {
    //         echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    //         exit;
    //     }

    //     try {
    //         // Aggregate the actual wallet balance dynamically from the Ledger (Immutable Source of Truth)
    //         // Purchases and Refunds ADD to available balance. Deductions and Escrow Holds SUBTRACT.
    //         $this->db->query("
    //             SELECT 
    //                 SUM(CASE WHEN transaction_type IN ('purchase', 'escrow_refund') THEN slots_amount ELSE 0 END) -
    //                 SUM(CASE WHEN transaction_type IN ('deduction', 'escrow_hold') THEN slots_amount ELSE 0 END) AS calculated_available,
    //                 SUM(CASE WHEN transaction_type = 'escrow_hold' THEN slots_amount ELSE 0 END) -
    //                 SUM(CASE WHEN transaction_type = 'escrow_refund' THEN slots_amount ELSE 0 END) -
    //                 SUM(CASE WHEN transaction_type = 'deduction' THEN slots_amount ELSE 0 END) AS calculated_escrow,
    //                 SUM(CASE WHEN transaction_type = 'purchase' THEN slots_amount ELSE 0 END) AS calculated_lifetime
    //             FROM slot_ledger_logs 
    //             WHERE school_id = :sch
    //         ");
    //         $this->db->bind(':sch', $schoolId);
    //         $aggregates = $this->db->single();

    //         $wallet = [
    //             'available_slots' => (int)($aggregates['calculated_available'] ?? 0),
    //             'escrow_slots' => (int)($aggregates['calculated_escrow'] ?? 0),
    //             'total_lifetime_slots' => (int)($aggregates['calculated_lifetime'] ?? 0)
    //         ];

    //         // Update the cached wallet table just to keep it in sync for fast reads elsewhere
    //         $this->db->query("
    //             INSERT INTO school_slot_wallets (id, school_id, available_slots, escrow_slots, total_lifetime_slots) 
    //             VALUES (:id, :sch, :avail, :escrow, :life)
    //             ON DUPLICATE KEY UPDATE available_slots = :avail, escrow_slots = :escrow, total_lifetime_slots = :life
    //         ");
    //         $this->db->bind(':id', UuidHelper::v4());
    //         $this->db->bind(':sch', $schoolId);
    //         $this->db->bind(':avail', $wallet['available_slots']);
    //         $this->db->bind(':escrow', $wallet['escrow_slots']);
    //         $this->db->bind(':life', $wallet['total_lifetime_slots']);
    //         $this->db->execute();

    //         // Fetch recent successful ledger logs
    //         $this->db->query("SELECT * FROM slot_ledger_logs WHERE school_id = :sch ORDER BY created_at DESC LIMIT 50");
    //         $this->db->bind(':sch', $schoolId);
    //         $ledger = $this->db->resultSet();

    //         // Fetch pending payment attempts for the "Requery" UI
    //         $this->db->query("SELECT * FROM payment_transactions WHERE school_id = :sch AND status = 'pending' ORDER BY created_at DESC");
    //         $this->db->bind(':sch', $schoolId);
    //         $pendingPayments = $this->db->resultSet();

    //         // Fetch Tiers
    //         $this->db->query("SELECT tier_name, min_slots, max_slots, price_per_slot FROM pricing_tiers WHERE is_active = 1 ORDER BY min_slots ASC");
    //         $tiers = $this->db->resultSet();

    //         echo json_encode([
    //             'success' => true,
    //             'payload' => [
    //                 'wallet' => $wallet,
    //                 'ledger' => $ledger,
    //                 'pending_payments' => $pendingPayments,
    //                 'tiers' => $tiers
    //             ]
    //         ]);
    //         exit;

    //     } catch (Exception $e) {
    //         echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    //         exit;
    //     }
    // }

    // --- 1. FETCH LICENSING DATA & CALCULATE WALLET ---
    public function getLicensingData_old_heavycomputaion() {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        try {
            // ==========================================
            // STEP A: THE AUTO-SETTLEMENT AUDIT
            // ==========================================
            
            // 1. Calculate True Usage (1 slot per student per exam session)
            // Using CONCAT ensures a student who takes 2 different sessions counts as 2 slots.
            $this->db->query("
                SELECT COUNT(DISTINCT exam_session_id, student_id) as total_usage 
                FROM student_responses 
                WHERE school_id = :sch
            ");
            $this->db->bind(':sch', $schoolId);
            $usageResult = $this->db->single();
            $trueUsage = (int)($usageResult['total_usage'] ?? 0);

            // 2. Calculate Already Deducted Slots in the Ledger
            $this->db->query("
                SELECT SUM(slots_amount) as already_deducted 
                FROM slot_ledger_logs 
                WHERE school_id = :sch AND transaction_type = 'deduction'
            ");
            $this->db->bind(':sch', $schoolId);
            $deductedResult = $this->db->single();
            $alreadyDeducted = (int)($deductedResult['already_deducted'] ?? 0);

            // 3. Reconcile and Auto-Deduct if new exams were synced
            if ($trueUsage > $alreadyDeducted) {
                $unbilledSlots = $trueUsage - $alreadyDeducted;
                
                $ledgerId = UuidHelper::v4();
                $this->db->query("
                    INSERT INTO slot_ledger_logs (id, school_id, transaction_type, slots_amount, naira_value, reference_id, description) 
                    VALUES (:id, :sch, 'deduction', :slots, 0.00, 'AUTO_SETTLE', :desc)
                ");
                $this->db->bind(':id', $ledgerId);
                $this->db->bind(':sch', $schoolId);
                $this->db->bind(':slots', $unbilledSlots);
                $this->db->bind(':desc', "System Auto-Settlement: Deducted {$unbilledSlots} slot(s).");
                $this->db->execute();
            }

            // ==========================================
            // STEP B: AGGREGATE THE WALLET BALANCE
            // ==========================================

            // Aggregate the actual wallet balance dynamically from the Ledger (Immutable Source of Truth)
            $this->db->query("
                SELECT 
                    SUM(CASE WHEN transaction_type IN ('purchase', 'escrow_refund') THEN slots_amount ELSE 0 END) -
                    SUM(CASE WHEN transaction_type IN ('deduction', 'escrow_hold') THEN slots_amount ELSE 0 END) AS calculated_available,
                    
                    SUM(CASE WHEN transaction_type = 'escrow_hold' THEN slots_amount ELSE 0 END) -
                    SUM(CASE WHEN transaction_type = 'escrow_refund' THEN slots_amount ELSE 0 END) -
                    SUM(CASE WHEN transaction_type = 'deduction' THEN slots_amount ELSE 0 END) AS calculated_escrow,
                    
                    SUM(CASE WHEN transaction_type = 'deduction' THEN slots_amount ELSE 0 END) AS calculated_used,
                    
                    SUM(CASE WHEN transaction_type = 'purchase' THEN slots_amount ELSE 0 END) AS calculated_lifetime
                FROM slot_ledger_logs 
                WHERE school_id = :sch
            ");
            $this->db->bind(':sch', $schoolId);
            $aggregates = $this->db->single();

            $wallet = [
                'available_slots' => max(0, (int)($aggregates['calculated_available'] ?? 0)),
                'escrow_slots' => max(0, (int)($aggregates['calculated_escrow'] ?? 0)),
                'used_slots' => max(0, (int)($aggregates['calculated_used'] ?? 0)),
                'total_lifetime_slots' => max(0, (int)($aggregates['calculated_lifetime'] ?? 0))
            ];

            // Update the cached wallet table just to keep it in sync for fast reads elsewhere
            $this->db->query("
                INSERT INTO school_slot_wallets (id, school_id, available_slots, escrow_slots, total_lifetime_slots) 
                VALUES (:id, :sch, :avail, :escrow, :life)
                ON DUPLICATE KEY UPDATE available_slots = :avail, escrow_slots = :escrow, total_lifetime_slots = :life
            ");
            $this->db->bind(':id', UuidHelper::v4());
            $this->db->bind(':sch', $schoolId);
            $this->db->bind(':avail', $wallet['available_slots']);
            $this->db->bind(':escrow', $wallet['escrow_slots']);
            $this->db->bind(':life', $wallet['total_lifetime_slots']);
            $this->db->execute();

            // ==========================================
            // STEP C: FETCH DATA FOR THE UI
            // ==========================================

            // Fetch recent successful ledger logs
            $this->db->query("SELECT * FROM slot_ledger_logs WHERE school_id = :sch ORDER BY created_at DESC LIMIT 50");
            $this->db->bind(':sch', $schoolId);
            $ledger = $this->db->resultSet();

            // Fetch pending payment attempts for the "Requery" UI
            $this->db->query("SELECT * FROM payment_transactions WHERE school_id = :sch AND status = 'pending' ORDER BY created_at DESC");
            $this->db->bind(':sch', $schoolId);
            $pendingPayments = $this->db->resultSet();

            // Fetch Tiers
            $this->db->query("SELECT tier_name, min_slots, max_slots, price_per_slot FROM pricing_tiers WHERE is_active = 1 ORDER BY min_slots ASC");
            $tiers = $this->db->resultSet();

            echo json_encode([
                'success' => true,
                'payload' => [
                    'wallet' => $wallet,
                    'ledger' => $ledger,
                    'pending_payments' => $pendingPayments,
                    'tiers' => $tiers
                ]
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
            exit;
        }
    }

    public function getLicensingData() {
        $schoolId = $this->getSchoolId();
        if (!$schoolId) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        try {
            // ==========================================
            // STEP A: AGGREGATE THE WALLET BALANCE
            // ==========================================
            // Transactions are now beautifully simple: 
            // 'purchase' (+), 'refund' (+), 'usage' (-)

            $this->db->query("
                SELECT 
                    SUM(CASE WHEN transaction_type IN ('purchase', 'refund') THEN slots_amount ELSE 0 END) -
                    SUM(CASE WHEN transaction_type = 'usage' THEN slots_amount ELSE 0 END) AS calculated_available,
                    
                    SUM(CASE WHEN transaction_type = 'usage' THEN slots_amount ELSE 0 END) - 
                    SUM(CASE WHEN transaction_type = 'refund' THEN slots_amount ELSE 0 END) AS calculated_used,
                    
                    SUM(CASE WHEN transaction_type = 'purchase' THEN slots_amount ELSE 0 END) AS calculated_lifetime
                FROM slot_ledger_logs 
                WHERE school_id = :sch
            ");
            $this->db->bind(':sch', $schoolId);
            $aggregates = $this->db->single();

            $wallet = [
                'available_slots' => max(0, (int)($aggregates['calculated_available'] ?? 0)),
                'used_slots' => max(0, (int)($aggregates['calculated_used'] ?? 0)),
                'total_lifetime_slots' => max(0, (int)($aggregates['calculated_lifetime'] ?? 0)),
                'escrow_slots' => 0 // Kept at 0 so your DB schema doesn't break if the column still exists
            ];

            // Update the cached wallet table just to keep it in sync for fast reads elsewhere
            $this->db->query("
                INSERT INTO school_slot_wallets (id, school_id, available_slots, escrow_slots, total_lifetime_slots) 
                VALUES (:id, :sch, :avail, 0, :life)
                ON DUPLICATE KEY UPDATE available_slots = :avail, escrow_slots = 0, total_lifetime_slots = :life
            ");
            $this->db->bind(':id', UuidHelper::v4());
            $this->db->bind(':sch', $schoolId);
            $this->db->bind(':avail', $wallet['available_slots']);
            $this->db->bind(':life', $wallet['total_lifetime_slots']);
            $this->db->execute();

            // ==========================================
            // STEP B: FETCH DATA FOR THE UI
            // ==========================================

            // Fetch recent successful ledger logs
            $this->db->query("SELECT * FROM slot_ledger_logs WHERE school_id = :sch ORDER BY created_at DESC LIMIT 50");
            $this->db->bind(':sch', $schoolId);
            $ledger = $this->db->resultSet();

            // Fetch pending payment attempts for the "Requery" UI
            $this->db->query("SELECT * FROM payment_transactions WHERE school_id = :sch AND status = 'pending' ORDER BY created_at DESC");
            $this->db->bind(':sch', $schoolId);
            $pendingPayments = $this->db->resultSet();

            // Fetch Tiers
            $this->db->query("SELECT tier_name, min_slots, max_slots, price_per_slot FROM pricing_tiers WHERE is_active = 1 ORDER BY min_slots ASC");
            $tiers = $this->db->resultSet();

            echo json_encode([
                'success' => true,
                'payload' => [
                    'wallet' => $wallet,
                    'ledger' => $ledger,
                    'pending_payments' => $pendingPayments,
                    'tiers' => $tiers
                ]
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
            exit;
        }
    }

    // --- 2. INITIATE PAYMENT (Server-Side Calculation & Logging) ---
    public function initiatePayment() {
        $schoolId = $this->getSchoolId();
        $input = json_decode(file_get_contents('php://input'), true);
        $slotsRequested = intval($input['slots'] ?? 0);

        if ($slotsRequested < 10) {
            echo json_encode(['success' => false, 'message' => 'Minimum of 10 slots required.']);
            exit;
        }

        try {
            // Server-side price calculation (Never trust the frontend amount)
            $this->db->query("SELECT price_per_slot FROM pricing_tiers WHERE is_active = 1 AND :slots BETWEEN min_slots AND max_slots LIMIT 1");
            $this->db->bind(':slots', $slotsRequested);
            $tier = $this->db->single();
            
            // Fallback to highest tier if somehow out of bounds
            if (!$tier) {
                $this->db->query("SELECT price_per_slot FROM pricing_tiers WHERE is_active = 1 ORDER BY min_slots DESC LIMIT 1");
                $tier = $this->db->single();
            }
            
            $calculatedAmount = $slotsRequested * floatval($tier['price_per_slot']);
            
            // Generate Atomic Reference
            $reference = 'CAOSCE_' . round(microtime(true) * 1000) . '_' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

            // Log the attempt
            $transId = UuidHelper::v4();
            $this->db->query("
                INSERT INTO payment_transactions (id, school_id, reference, slots_requested, amount_expected, status) 
                VALUES (:id, :sch, :ref, :slots, :amount, 'pending')
            ");
            $this->db->bind(':id', $transId);
            $this->db->bind(':sch', $schoolId);
            $this->db->bind(':ref', $reference);
            $this->db->bind(':slots', $slotsRequested);
            $this->db->bind(':amount', $calculatedAmount);
            $this->db->execute();

            // Return safe data back to frontend to open the Paystack Modal
            echo json_encode([
                'success' => true,
                'payload' => [
                    'reference' => $reference,
                    'amount_kobo' => $calculatedAmount * 100, // Paystack requires kobo
                    'email' => $_SESSION['admin_email'] ?? 'admin@caosce.com'
                ]
            ]);
            exit;

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Failed to initialize payment.']);
            exit;
        }
    }

    // --- 3. VERIFY & REQUERY PAYMENT ---
    public function verifyPayment() {
        $schoolId = $this->getSchoolId();
        $input = json_decode(file_get_contents('php://input'), true);
        $reference = $input['reference'] ?? null;

        if (!$reference) {
            echo json_encode(['success' => false, 'message' => 'Reference required.']);
            exit;
        }

        try {
            // 1. Fetch our local log
            $this->db->query("SELECT * FROM payment_transactions WHERE reference = :ref AND school_id = :sch LIMIT 1");
            $this->db->bind(':ref', $reference);
            $this->db->bind(':sch', $schoolId);
            $localTx = $this->db->single();

            if (!$localTx) {
                echo json_encode(['success' => false, 'message' => 'Transaction reference not found in our records.']);
                exit;
            }

            if ($localTx['status'] === 'success') {
                echo json_encode(['success' => true, 'message' => 'Payment was already verified and credited.']);
                exit;
            }

            // 2. cURL to Paystack to get absolute truth
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . rawurlencode($reference));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $this->paystackSecretKey,
                "Content-Type: application/json"
            ]);
            
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                echo json_encode(['success' => false, 'message' => 'Network error communicating with Paystack.']);
                exit;
            }

            $paystackData = json_decode($response, true);

            // 3. Process Paystack Response
            if ($paystackData && isset($paystackData['data']) && $paystackData['data']['status'] === 'success') {
                
                $paidAmountNaira = $paystackData['data']['amount'] / 100;

                // Security check: Did they pay the full expected amount?
                if ($paidAmountNaira >= $localTx['amount_expected']) {
                    
                    // Mark transaction as success
                    $this->db->query("UPDATE payment_transactions SET status = 'success', paystack_response = :resp WHERE id = :id");
                    $this->db->bind(':resp', $response);
                    $this->db->bind(':id', $localTx['id']);
                    $this->db->execute();

                    // Insert Immutable Ledger Record (Wallet calculation happens automatically on next fetch)
                    $ledgerId = UuidHelper::v4();
                    $this->db->query("
                        INSERT INTO slot_ledger_logs (id, school_id, transaction_type, slots_amount, naira_value, reference_id, description) 
                        VALUES (:id, :sch, 'purchase', :slots, :amount, :ref, :desc)
                    ");
                    $this->db->bind(':id', $ledgerId);
                    $this->db->bind(':sch', $schoolId);
                    $this->db->bind(':slots', $localTx['slots_requested']);
                    $this->db->bind(':amount', $paidAmountNaira);
                    $this->db->bind(':ref', $reference);
                    $this->db->bind(':desc', "Purchased {$localTx['slots_requested']} exam slots.");
                    $this->db->execute();

                    echo json_encode(['success' => true, 'message' => 'Payment verified successfully. Slots added.']);
                    exit;

                } else {
                    echo json_encode(['success' => false, 'message' => 'Partial payment detected. Please contact support.']);
                    exit;
                }
            } else {
                // If Paystack says it failed or abandoned
                $status = $paystackData['data']['status'] ?? 'failed';
                $this->db->query("UPDATE payment_transactions SET status = :status, paystack_response = :resp WHERE id = :id");
                $this->db->bind(':status', $status);
                $this->db->bind(':resp', $response);
                $this->db->bind(':id', $localTx['id']);
                $this->db->execute();

                echo json_encode(['success' => false, 'message' => 'Paystack reports this payment was not successful.']);
                exit;
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Server error during verification.']);
            exit;
        }
    }
}
?>