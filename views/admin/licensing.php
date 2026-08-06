<?php 
// echo $_SESSION['school_id'] ;
// exit;
$activeMenu = 'licensing'; 
$pageTitle = 'Licensing & Wallet Management';
include '../views/layouts/header.php'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased flex h-screen overflow-hidden" x-data="licensingController()" x-cloak>

    <!-- Sidebar Inclusion -->
    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Professional Header -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-6 sm:px-10 flex-shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-4 sm:gap-6">
                <button @click="goBack()" type="button" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </button>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Licensing & Slot Wallet</h1>
                    <p class="text-sm text-slate-500 font-medium mt-0.5">Manage your institution's examination slot balance, escrow reservations, and purchases.</p>
                </div>
            </div>

            <button @click="openPurchaseModal()" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 hover:bg-blue-500 transition-all">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Purchase Exam Slots
            </button>
        </header>

        <!-- Scrollable Page Content -->
        <!-- Scrollable Page Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 p-6 sm:p-10">
            <div x-show="isLoading" class="flex flex-col items-center justify-center py-32" x-cloak>
                    <svg class="animate-spin h-12 w-12 text-blue-600 mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm font-bold text-slate-500 uppercase tracking-wider animate-pulse">Fetching licensing data...</p>
                </div>
            <div class="max-w-7xl mx-auto">
                
                <!-- FULL PAGE LOADER -->
                

                <!-- ACTUAL CONTENT (Hidden while loading) -->
                <div x-show="true" class="space-y-8" x-cloak>
                    
                    <!-- 4-Column Balance Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <!-- Available Slots -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden hover:shadow-md transition-shadow">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 pointer-events-none"></div>
                            <p class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">Available</p>
                            <div class="text-3xl font-black text-slate-900" x-text="wallet.available_slots || 0">0</div>
                            <p class="text-xs text-slate-500 mt-2">Ready to be assigned to exam sessions.</p>
                        </div>

                        <!-- Escrowed Slots -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden hover:shadow-md transition-shadow">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-4 -mt-4 pointer-events-none"></div>
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-1">Escrowed</p>
                            <div class="text-3xl font-black text-slate-900" x-text="wallet.escrow_slots || 0">0</div>
                            <p class="text-xs text-slate-500 mt-2">Locked for pending examinations.</p>
                        </div>
                        
                        <!-- Used Slots -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden hover:shadow-md transition-shadow">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-rose-50 rounded-bl-full -mr-4 -mt-4 pointer-events-none"></div>
                            <p class="text-xs font-bold uppercase tracking-wider text-rose-600 mb-1">Total Used</p>
                            <div class="text-3xl font-black text-slate-900" x-text="wallet.used_slots || 0">0</div>
                            <p class="text-xs text-slate-500 mt-2">Permanently deducted from synced exams.</p>
                        </div>

                        <!-- Lifetime Total -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden hover:shadow-md transition-shadow">
                            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 pointer-events-none"></div>
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">Lifetime Volume</p>
                            <div class="text-3xl font-black text-slate-900" x-text="wallet.total_lifetime_slots || 0">0</div>
                            <p class="text-xs text-slate-500 mt-2">Total slots purchased to date.</p>
                        </div>
                    </div>

                    <!-- Pending Payments (Requery Section) -->
                    <div x-show="pending_payments.length > 0" class="bg-amber-50 border border-amber-200 shadow-sm sm:rounded-2xl overflow-hidden mb-8">
                        <div class="px-6 py-4 border-b border-amber-200 bg-amber-100/50 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-amber-900 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Pending Payments (Action Required)
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <template x-for="payment in pending_payments" :key="payment.id">
                                <div class="bg-white rounded-xl p-4 border border-amber-100 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4 transition-all hover:shadow-md">
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Reference: <span class="text-slate-900 font-mono" x-text="payment.reference"></span></p>
                                        <p class="text-sm font-medium text-slate-700">
                                            Attempted to purchase <strong class="text-slate-900" x-text="payment.slots_requested"></strong> slots for <strong class="text-slate-900" x-text="formatNaira(payment.amount_expected)"></strong>.
                                        </p>
                                    </div>
                                    <button @click="verifyPaystackTransaction(payment.reference)" class="w-full sm:w-auto px-5 py-2.5 bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-2 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        Requery Status
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Transaction Ledger History -->
                    <div class="bg-white shadow-sm sm:rounded-2xl overflow-hidden border border-slate-200">
                        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Payment & Slot Ledger History</h3>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Type</th>
                                        <th class="px-3 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Description</th>
                                        <th class="px-3 py-3.5 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Slots</th>
                                        <th class="px-3 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Amount Paid</th>
                                        <th class="px-3.5 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <template x-for="log in ledger" :key="log.id">
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                                      :class="{
                                                          'bg-emerald-100 text-emerald-700': log.transaction_type === 'purchase',
                                                          'bg-amber-100 text-amber-700': log.transaction_type === 'escrow_hold',
                                                          'bg-blue-100 text-blue-700': log.transaction_type === 'escrow_refund',
                                                          'bg-slate-100 text-slate-600': log.transaction_type === 'deduction'
                                                      }"
                                                      x-text="log.transaction_type"></span>
                                            </td>
                                            <td class="px-3 py-4 text-sm font-medium text-slate-800" x-text="log.description"></td>
                                            <td class="whitespace-nowrap px-3 py-4 text-center text-sm font-bold"
                                                :class="log.transaction_type === 'deduction' ? 'text-red-600' : 'text-emerald-600'">
                                                <span x-text="log.transaction_type === 'deduction' ? '-' + log.slots_amount : '+' + log.slots_amount"></span>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-bold text-slate-900" x-text="formatNaira(log.naira_value)"></td>
                                            <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm text-slate-500" x-text="log.created_at"></td>
                                        </tr>
                                    </template>
                                    
                                    <tr x-show="ledger.length === 0">
                                        <td colspan="5" class="py-12 text-center text-sm text-slate-500">
                                            No ledger transactions recorded yet. Purchase slots to begin.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </div> <!-- End of Content Wrapper -->

            </div>
        </main>

        <!-- PURCHASE SLOTS MODAL (Integrated Calculator & Paystack Trigger) -->
       <!-- PURCHASE SLOTS MODAL (Integrated Calculator & Paystack Trigger) -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div @click.away="showModal = false" class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-100">
                
                <!-- Modal Header -->
                <div class="px-8 py-6 bg-slate-900 text-white flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-extrabold">Purchase Exam Slots</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Volume discounts apply automatically based on package tiers.</p>
                    </div>
                    <button @click="showModal = false" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white transition-colors">✕</button>
                </div>

                <!-- Modal Body (Calculator Interface) -->
                <div class="p-8 space-y-6">
                    
                    <!-- Slider & Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Number of Exam Slots (Students)</label>
                        <div class="flex items-center gap-4 mb-4">
                            <!-- <input type="number" x-model.number="slots" min="1" class="w-36 text-2xl font-black text-slate-900 bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-center focus:ring-2 focus:ring-blue-500"> -->
                            <input type="number" x-model.number="slots" min="10" @blur="if(slots < 10) slots = 10" class="w-36 text-2xl font-black text-slate-900 bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-center focus:ring-2 focus:ring-blue-500">
                            <span class="text-slate-500 font-semibold text-sm">Slots required</span>
                        </div>
                        
                        <!-- Range Slider -->
                        <!-- <input type="range" x-model.number="slots" min="1" max="2500" step="1" class="w-full appearance-none bg-slate-200 h-2 rounded-lg cursor-pointer accent-blue-600"> -->
                        <input type="range" x-model.number="slots" min="10" max="2500" step="1" class="w-full appearance-none bg-slate-200 h-2 rounded-lg cursor-pointer accent-blue-600">
                    </div>

                    <!-- Active Tier Notice -->
                    <div class="bg-blue-50 rounded-2xl p-4 flex items-start gap-3 border border-blue-100">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0 mt-0.5 font-bold">i</div>
                        <div>
                            <h4 class="text-sm font-bold text-blue-900" x-text="`Active Tier: ${currentTier}`"></h4>
                            <p class="text-xs text-blue-700 mt-0.5">
                                Rate applied: <strong x-text="formatNaira(pricePerSlot)"></strong> per slot. Unused slots roll over automatically.
                            </p>
                        </div>
                    </div>

                    <!-- Dynamic Pricing Reference Table -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="py-2.5 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Package Tier</th>
                                    <th class="py-2.5 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Volume Range</th>
                                    <th class="py-2.5 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider text-right">Price per Slot/Student</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="tier in tiers" :key="tier.name">
                                    <tr class="transition-colors duration-200" :class="currentTier === tier.name ? 'bg-blue-50/50' : 'bg-white'">
                                        <td class="py-2.5 px-4 flex items-center gap-2">
                                            <!-- Dot Indicator for Active Row -->
                                            <span class="w-2 h-2 rounded-full transition-all" :class="currentTier === tier.name ? 'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.8)]' : 'bg-transparent'"></span>
                                            <span :class="currentTier === tier.name ? 'text-blue-700 font-extrabold' : 'text-slate-700 font-semibold'" x-text="tier.name"></span>
                                        </td>
                                        <td class="py-2.5 px-4 text-slate-600 font-medium">
                                            <span x-text="tier.min.toLocaleString()"></span>
                                            <span x-show="tier.max < 999999"> - <span x-text="tier.max.toLocaleString()"></span></span>
                                            <span x-show="tier.max >= 999999">+</span>
                                            Slots
                                        </td>
                                        <td class="py-2.5 px-4 text-right font-black" :class="currentTier === tier.name ? 'text-blue-700' : 'text-slate-800'">
                                            <span x-text="formatNaira(tier.price).replace('.00', '')"></span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <!-- Cost Summary Box -->
                    <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-center sm:text-left">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Investment</p>
                            <div class="text-3xl font-black text-slate-900 mt-1" x-text="formatNaira(totalCost)"></div>
                        </div>
                      <button @click="initiatePaystackPayment()" 
        :disabled="slots < 10"
        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-500 disabled:opacity-50 disabled:hover:bg-blue-600 text-white font-bold px-8 py-4 rounded-xl shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Pay Securely
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Toast Notification -->
        <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
            <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
                <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto w-full max-w-sm rounded-xl bg-slate-800 shadow-xl p-4 flex items-start text-white">
                    <p class="text-sm font-bold" x-text="toast.message"></p>
                </div>
            </div>
        </div>

    </div>

    <!-- Paystack Inline JS SDK -->
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        function licensingController() {
            return {
                isLoading: true,
                wallet: { available_slots: 0, escrow_slots: 0, used_slots: 0, total_lifetime_slots: 0 },
                ledger: [],
                tiers: [],
                pending_payments: [],
                slots: 100,
                showModal: false,
                isReady: false,
                toast: { visible: false, message: '' },
                
                init() {
                    this.fetchLicensingData();
                },

                getBaseApiUrl() {
                    const tenantSlug = '<?php echo CURRENT_TENANT_SLUG ?? ""; ?>';
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return tenantSlug ? `${basePath}/${tenantSlug}` : basePath;
                },

                goBack() {
                    window.location.href = this.getBaseApiUrl() + '/admin/dashboard';
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

            async fetchLicensingData() {
                this.isLoading = true;
                    try {
                        let res = await fetch(this.getBaseApiUrl() + '/api/admin/licensing/data');
                        let data = await res.json();
                        if(data.success) {
                            this.wallet = data.payload.wallet;
                            this.ledger = data.payload.ledger;
                            
                            // ADD THIS LINE TO LOAD PENDING PAYMENTS:
                            this.pending_payments = data.payload.pending_payments || []; 
                            
                            this.tiers = data.payload.tiers.map(t => ({
                                name: t.tier_name,
                                min: parseInt(t.min_slots),
                                max: parseInt(t.max_slots),
                                price: parseFloat(t.price_per_slot)
                            }));
                        }
                    } catch(e) { console.error("Error fetching licensing data"); } finally {
                        this.isLoading = false; // Hide loader when done
                    }
                },

                openPurchaseModal() {
                    this.showModal = true;
                },

                get activeTierData() {
                    if (this.tiers.length === 0) return { name: 'Standard Pack', price: 500 };
                    let currentSlots = Number(this.slots) || 0;
                    let matchedTier = this.tiers.find(t => currentSlots >= t.min && currentSlots <= t.max);
                    return matchedTier || this.tiers[this.tiers.length - 1];
                },

                get currentTier() {
                    return this.activeTierData.name;
                },

                get pricePerSlot() {
                    return this.activeTierData.price;
                },

                get totalCost() {
                    let currentSlots = Number(this.slots) || 0;
                    let cost = currentSlots * this.pricePerSlot;
                    return isNaN(cost) ? 0 : cost;
                },

                formatNaira(amount) {
                    if(!amount || isNaN(amount)) return '₦0.00';
                    return '₦' + Number(amount).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

               // Add 'pending_payments: []' to your return {} object at the top of licensingController()
// Add an HTML section above the Ledger table to loop through pending_payments with a "Requery" button calling verifyPaystackTransaction(ref)

async initiatePaystackPayment() {
    this.showToast('Initializing secure payment...');
    
    try {
        // 1. Get atomic reference and strict price from our Backend
        let initRes = await fetch(this.getBaseApiUrl() + '/api/admin/licensing/initiate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ slots: this.slots })
        });
        
        let initData = await initRes.json();
        
        if (!initData.success) {
            alert(initData.message);
            return;
        }

        let securePayload = initData.payload;

        // 2. Open Paystack using the Backend's strict data
        let handler = PaystackPop.setup({
            key: 'pk_test_165ca8d2378b5ab6c7430de54a306ca75947759c', 
            email: securePayload.email,
            amount: securePayload.amount_kobo,
            currency: 'NGN',
            ref: securePayload.reference, // The backend-generated atomic reference
            // split_code: 'SPL_p6gm6zFsVy',
            callback: (response) => {
                // 3. Verify Payment
                this.verifyPaystackTransaction(response.reference);
            },
            onClose: () => {
                this.showToast('Payment window closed. You can requery this later.');
                this.fetchLicensingData(); // Refresh to show the new pending attempt
            }
        });
        handler.openIframe();
        
    } catch (e) {
        alert('Network error initializing payment.');
    }
},

async verifyPaystackTransaction(reference) {
    this.showModal = false;
    this.showToast('Verifying payment on the server...');
    
    try {
        let res = await fetch(this.getBaseApiUrl() + '/api/admin/licensing/verify-payment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reference: reference })
        });
        
        let data = await res.json();
        
        if (data.success) {
            this.showToast(data.message);
            await this.fetchLicensingData(); // Refresh UI balances
        } else {
            alert(data.message || 'Payment verification failed.');
            await this.fetchLicensingData(); // Refresh to update status
        }
    } catch(e) {
        alert('Network error verifying payment.');
    }
}
            }
        }
    </script>
</body>
</html>