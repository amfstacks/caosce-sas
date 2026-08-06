<?php 
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
        <main class="flex-1 overflow-y-auto bg-slate-50 p-6 sm:p-10">
            <div class="max-w-7xl mx-auto space-y-8">

                <!-- 3-Column Balance Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Available Slots -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-bl-full -mr-8 -mt-8 pointer-events-none"></div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Available Slots</p>
                        <div class="text-3xl font-black text-slate-900" x-text="wallet.available_slots">0</div>
                        <p class="text-xs text-slate-500 mt-2">Ready to be assigned to exam sessions.</p>
                    </div>

                    <!-- Escrowed Slots -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-amber-50 rounded-bl-full -mr-8 -mt-8 pointer-events-none"></div>
                        <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-1">Escrowed (Reserved)</p>
                        <div class="text-3xl font-black text-slate-900" x-text="wallet.escrow_slots">0</div>
                        <p class="text-xs text-slate-500 mt-2">Locked for pending examinations.</p>
                    </div>

                    <!-- Lifetime Total -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 pointer-events-none"></div>
                        <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">Lifetime Slots Purchased</p>
                        <div class="text-3xl font-black text-slate-900" x-text="wallet.total_lifetime_slots">0</div>
                        <p class="text-xs text-slate-500 mt-2">Total volume acquired to date.</p>
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
                                            :class="log.slots_amount > 0 ? 'text-emerald-600' : 'text-slate-600'">
                                            <span x-text="log.slots_amount > 0 ? '+' + log.slots_amount : log.slots_amount"></span>
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
                            <input type="number" x-model.number="slots" min="1" class="w-36 text-2xl font-black text-slate-900 bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-center focus:ring-2 focus:ring-blue-500">
                            <span class="text-slate-500 font-semibold text-sm">Slots required</span>
                        </div>
                        
                        <!-- Range Slider -->
                        <input type="range" x-model.number="slots" min="1" max="2500" step="1" class="w-full appearance-none bg-slate-200 h-2 rounded-lg cursor-pointer accent-blue-600">
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
                                    <th class="py-2.5 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider text-right">Price per Slot</th>
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
                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white font-bold px-8 py-4 rounded-xl shadow-lg shadow-blue-600/30 transition-all flex items-center justify-center gap-2">
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
                wallet: { available_slots: 0, escrow_slots: 0, total_lifetime_slots: 0 },
                ledger: [],
                tiers: [],
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
                    try {
                        let res = await fetch(this.getBaseApiUrl() + '/api/admin/licensing/data');
                        let data = await res.json();
                        if(data.success) {
                            this.wallet = data.payload.wallet;
                            this.ledger = data.payload.ledger;
                            this.tiers = data.payload.tiers.map(t => ({
                                name: t.tier_name,
                                min: parseInt(t.min_slots),
                                max: parseInt(t.max_slots),
                                price: parseFloat(t.price_per_slot)
                            }));
                        }
                    } catch(e) { console.error("Error fetching licensing data"); }
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

                // Paystack Payment Integration
                initiatePaystackPayment() {
                    let handler = PaystackPop.setup({
                        key: 'pk_test_YOUR_ACTUAL_PAYSTACK_PUBLIC_KEY', // Replace with your public key or inject via PHP
                        email: 'admin@' + (window.location.hostname || 'caosce.com'),
                        amount: this.totalCost * 100, // Paystack expects amount in Kobo
                        currency: 'NGN',
                        ref: 'CAOSCE_' + Math.floor((Math.random() * 1000000000) + 1),
                        callback: (response) => {
                            this.verifyPaystackTransaction(response.reference);
                        },
                        onClose: () => {
                            this.showToast('Payment window closed.');
                        }
                    });
                    handler.openIframe();
                },

                async verifyPaystackTransaction(reference) {
                    this.showModal = false;
                    this.showToast('Verifying payment and updating wallet...');
                    
                    try {
                        let res = await fetch(this.getBaseApiUrl() + '/api/admin/licensing/verify-payment', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ reference: reference, slots: this.slots, amount: this.totalCost })
                        });
                        let data = await res.json();
                        if(data.success) {
                            this.showToast('Slots successfully added to your wallet!');
                            await this.fetchLicensingData(); // Refresh UI
                        } else {
                            alert(data.message || 'Payment verification failed.');
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