<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing & Calculator | CAOSCE Systems</title>

    <meta name="description" content="Transparent, pay-as-you-go pricing for CAOSCE offline clinical examination software. Only pay for the exam slots you use.">
    
    <!-- Favicon Suite -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/public/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/public/assets/favicon-16x16.png">
    
    <!-- Alpine.js & Tailwind -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel { 
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.2); 
        }
        [x-cloak] { display: none !important; }
        
        /* Custom range slider styling */
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 24px;
            width: 24px;
            border-radius: 50%;
            background: #2563eb;
            cursor: pointer;
            margin-top: -8px;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4);
        }
        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 8px;
            cursor: pointer;
            background: #e2e8f0;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased selection:bg-blue-600 selection:text-white" x-data="pricingController()">

    <!-- ========================================== -->
    <!-- NAVBAR (Re-used from Landing)              -->
    <!-- ========================================== -->
    <nav class="absolute top-0 w-full z-50 transition-all duration-300 border-b border-slate-200 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/" class="flex-shrink-0 flex items-center gap-3 cursor-pointer">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-sm text-white font-bold">C</div>
                    <span class="text-2xl font-extrabold text-slate-900 tracking-tight">CAOSCE</span>
                </a>
                
                <div class="hidden lg:flex items-center space-x-6">
                    <a href="tel:+2348034107132" class="flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-blue-600 transition-colors">
                        <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        +234 8034107132
                    </a>
                    <div class="h-4 w-px bg-slate-300"></div>
                    <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/" class="text-sm font-semibold text-slate-600 hover:text-blue-600 transition-colors">Back to Home</a>
                    <a href="admin/login" class="inline-flex items-center justify-center px-5 py-2 text-sm font-bold text-white transition-all bg-blue-600 hover:bg-blue-500 rounded-full shadow-md">
                        Admin Portal
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- PRICING HEADER                             -->
    <!-- ========================================== -->
    <div class="pt-32 pb-16 bg-gradient-to-b from-blue-50 to-slate-50 border-b border-slate-200">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight mb-6">
                Smart Pricing for <span class="text-blue-600">Smart Campuses</span>
            </h1>
            <p class="text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto">
                No rigid annual contracts. No hidden maintenance fees. Purchase <b>Exam Slots</b> to fund your institutional wallet and let the system scale dynamically with your student enrollment.
            </p>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- INTERACTIVE CALCULATOR                     -->
    <!-- ========================================== -->
    <div class="max-w-5xl mx-auto px-6 -mt-8 relative z-10 mb-24">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-5">
                
                <!-- Left: Controls -->
                <div class="lg:col-span-3 p-8 md:p-12 border-b lg:border-b-0 lg:border-r border-slate-100 bg-slate-50/50">
                    <h3 class="text-xl font-bold text-slate-900 mb-2">Estimate Your Investment</h3>
                    <p class="text-sm text-slate-500 mb-8">Enter the total number of students expected to sit for the offline examination cycle.</p>
                    
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-4">Number of Exam Slots (Students)</label>
                        <div class="flex items-center gap-4 mb-6">
                            <input type="number" x-model.number="slots" min="1" class="w-32 text-2xl font-black text-slate-900 bg-white border border-slate-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 text-center shadow-sm">
                            <span class="text-slate-400 font-semibold">Slots needed</span>
                        </div>
                        
                        <!-- Range Slider -->
                        <input type="range" x-model.number="slots" min="1" max="2500" step="1" class="w-full appearance-none bg-transparent">
                        
                        <div class="flex justify-between text-xs font-bold text-slate-400 mt-2 px-1">
                            <span>1</span>
                            <span>1,250</span>
                            <span>2,500+</span>
                        </div>
                    </div>

                    <!-- Dynamic Tier Alert -->
                    <div class="bg-blue-50 rounded-xl p-4 flex items-start gap-3 border border-blue-100 transition-all" x-cloak>
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-blue-900" x-text="`Unlocked: ${currentTier}`"></h4>
                            <p class="text-xs text-blue-700 mt-1">
                                You are currently getting the <span class="font-bold" x-text="formatNaira(pricePerSlot)"></span> per student rate. 
                                <span x-show="slots < 250">Upgrade to 250 slots to unlock the ₦400 rate.</span>
                                <span x-show="slots >= 250 && slots < 1000">Upgrade to 1,000 slots to unlock the maximum ₦300 discount.</span>
                                <span x-show="slots >= 1000">You have unlocked our maximum enterprise discount!</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right: Results -->
                <div class="lg:col-span-2 bg-slate-900 p-8 md:p-12 text-white flex flex-col justify-center relative overflow-hidden">
                    <!-- Decor -->
                    <div class="absolute top-0 right-0 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    
                    <div class="relative z-10">
                        <p class="text-slate-400 text-sm font-semibold uppercase tracking-wider mb-2">Total Wallet Cost</p>
                        <div class="text-4xl md:text-5xl font-black text-white mb-6 tracking-tight" x-text="formatNaira(totalCost)"></div>
                        
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center text-slate-300 text-sm">
                                <svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                <span><strong class="text-white" x-text="slots"></strong> Student Exam Slots</span>
                            </li>
                            <li class="flex items-center text-slate-300 text-sm">
                                <svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                <span>Rate: <strong class="text-white" x-text="formatNaira(pricePerSlot)"></strong> / slot</span>
                            </li>
                            <li class="flex items-center text-slate-300 text-sm">
                                <svg class="w-5 h-5 text-emerald-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                Unused slots roll over automatically
                            </li>
                        </ul>

                        <button @click="window.location.href='admin/register'" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-[0_0_20px_rgba(37,99,235,0.3)] hover:shadow-[0_0_30px_rgba(37,99,235,0.5)]">
                            Setup Workspace
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TIER BREAKDOWN                             -->
    <!-- ========================================== -->
    <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-slate-900">Volume-Based Pricing Tiers</h2>
                <p class="mt-4 text-slate-600">The more you scale across your campus, the more you save.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Dynamic Tiers Loop -->
                <template x-for="(tier, index) in tiers" :key="tier.name">
                    <div :class="{
                            'border-2 border-blue-600 rounded-3xl p-8 shadow-lg relative transform md:-translate-y-4 bg-white': index === 1,
                            'border border-slate-200 rounded-3xl p-8 hover:shadow-lg transition-shadow bg-white': index !== 1
                        }">
                        
                        <!-- Most Popular Badge (Only shows on the 2nd tier) -->
                        <div x-show="index === 1" class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-blue-600 text-white px-4 py-1 rounded-full text-xs font-bold tracking-wider uppercase">
                            Most Popular
                        </div>
                        
                        <h3 class="text-xl font-bold text-slate-900 mb-1" x-text="tier.name"></h3>
                        
                        <!-- Dynamic Min/Max formatting (handles the 999999 max value cleanly) -->
                        <p class="text-sm text-slate-500 mb-6">
                            <span x-text="tier.min.toLocaleString()"></span>
                            <span x-show="tier.max < 999999"> - <span x-text="tier.max.toLocaleString()"></span></span>
                            <span x-show="tier.max >= 999999">+</span>
                            Slots
                        </p>
                        
                        <div class="text-3xl font-black text-slate-900 mb-6">
                            <span x-text="formatNaira(tier.price).replace('.00', '')"></span> 
                            <span class="text-base font-medium text-slate-500">/slot</span>
                        </div>
                        
                        <p class="text-sm text-slate-600 leading-relaxed" x-text="tier.description"></p>
                    </div>
                </template>
                
                <!-- Loading Skeleton (Shows while API is fetching) -->
                <div x-show="!isReady" class="col-span-3 text-center py-12 text-slate-400">
                    <svg class="animate-spin h-8 w-8 mx-auto mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Loading dynamic pricing...
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FAIR-USE ESCROW EXPLANATION                -->
    <!-- ========================================== -->
    <div class="bg-slate-50 py-24 border-t border-slate-200">
        <div class="max-w-4xl mx-auto px-6">
            <h2 class="text-2xl font-extrabold text-slate-900 text-center mb-12">How the Wallet Ledger Protects You</h2>
            
            <div class="space-y-8">
                <div class="flex gap-6 items-start">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center flex-shrink-0 text-blue-600 font-bold text-xl">1</div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Pre-Fund Your Portal</h4>
                        <p class="text-slate-600 leading-relaxed">Deposit funds into your CAOSCE portal to purchase your required slots upfront. If you need 200 slots, your balance is exactly 200 available slots.</p>
                    </div>
                </div>
                <div class="flex gap-6 items-start">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center flex-shrink-0 text-amber-500 font-bold text-xl">2</div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">The Escrow Hold</h4>
                        <p class="text-slate-600 leading-relaxed">When you assign 150 students to an upcoming OSCE exam, the system temporarily places 150 slots into an "Escrow Hold." They belong to you, but cannot be double-spent on another exam.</p>
                    </div>
                </div>
                <div class="flex gap-6 items-start">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center justify-center flex-shrink-0 text-emerald-500 font-bold text-xl">3</div>
                    <div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Zero-Waste Deductions</h4>
                        <p class="text-slate-600 leading-relaxed">Slots are ONLY permanently deducted when an exam result is successfully synced. If a student is absent, drops the course, or is deleted from the roster prior to the exam, their reserved slot is automatically returned to your available balance. <strong>You never pay for absentees.</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FOOTER (Re-used from Landing)              -->
    <!-- ========================================== -->
    <footer class="bg-slate-900 py-12 border-t border-slate-800 text-center">
        <p class="text-slate-500 text-sm">© <?php echo date('Y'); ?> CAOSCE Systems. Engineered for African Clinical Education.</p>
    </footer>

    <!-- ========================================== -->
    <!-- ALPINE JS CONTROLLER                       -->
    <!-- ========================================== -->
   <!-- ========================================== -->
    <!-- ALPINE JS CONTROLLER                       -->
    <!-- ========================================== -->
    <script>
        function pricingController() {
            return {
                slots: 250, // Initial default value
                tiers: [], // Will hold data from DB
                isReady: false,

                // Initialize: Fetch data from API on load
                // async init() {
                //     let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                //     try {
                //         let response = await fetch(basePath + '/api/pricing/tiers');
                //         let data = await response.json();
                        
                //         if (data.success && data.payload.length > 0) {
                //             // Parse DB strings into numbers for accurate math
                //             this.tiers = data.payload.map(tier => ({
                //                 name: tier.tier_name,
                //                 min: parseInt(tier.min_slots),
                //                 max: parseInt(tier.max_slots),
                //                 price: parseFloat(tier.price_per_slot)
                //             }));
                //         }
                //     } catch (error) {
                //         console.error('Failed to load dynamic pricing', error);
                //         // Optional: Provide fallback hardcoded tiers here if DB fails
                //     } finally {
                //         this.isReady = true;
                //     }
                // },
// Initialize: Fetch data from API on load
                async init() {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    
                    // Setup descriptions to match the tiers (since they aren't in the DB yet)
                    const tierDescriptions = [
                        "Perfect for single classes, mid-terms, mock exams, or specialized clinical practicals.",
                        "Designed for full departmental finals, nursing councils, and comprehensive objective structures.",
                        "Enterprise scaling across multiple departments, maximizing institutional fee retention."
                    ];

                    try {
                        let response = await fetch(basePath + '/api/pricing/tiers');
                        let data = await response.json();
                        
                        if (data.success && data.payload && data.payload.length > 0) {
                            // Parse DB strings into numbers and attach descriptions
                            this.tiers = data.payload.map((tier, index) => ({
                                name: tier.tier_name,
                                min: parseInt(tier.min_slots),
                                max: parseInt(tier.max_slots),
                                price: parseFloat(tier.price_per_slot),
                                description: tierDescriptions[index] || "Custom scaling for your institution."
                            }));
                        } else {
                            throw new Error("Invalid payload from database");
                        }
                    } catch (error) {
                        console.warn('API fetch failed, loading fail-safe offline tiers.', error);
                        // FAIL-SAFE
                        this.tiers = [
                            { name: 'Standard Pack', min: 1, max: 249, price: 500, description: tierDescriptions[0] },
                            { name: 'Department Pack', min: 250, max: 999, price: 400, description: tierDescriptions[1] },
                            { name: 'Campus Pack', min: 1000, max: 999999, price: 300, description: tierDescriptions[2] }
                        ];
                    } finally {
                        this.isReady = true;
                    }
                },
                // Helper to find the active tier object based on current slots
                get activeTierData() {
                    if (this.tiers.length === 0) return null;
                    
                    let matchedTier = this.tiers.find(t => this.slots >= t.min && this.slots <= t.max);
                    
                    // If they somehow type a number higher than the max of the highest tier, 
                    // default to the highest available tier to prevent errors.
                    if (!matchedTier) {
                        return this.tiers[this.tiers.length - 1];
                    }
                    
                    return matchedTier;
                },

                // Determine Tier Name dynamically
                get currentTier() {
                    if (!this.isReady) return 'Loading...';
                    return this.activeTierData ? this.activeTierData.name : 'Unknown Tier';
                },

                // Determine Price dynamically
                get pricePerSlot() {
                    if (!this.isReady) return 0;
                    return this.activeTierData ? this.activeTierData.price : 0;
                },

                // Calculate Total Cost
                get totalCost() {
                    let cost = this.slots * this.pricePerSlot;
                    return isNaN(cost) ? 0 : cost;
                },

                // Format Currency visually
                formatNaira(amount) {
                    if(isNaN(amount) || amount === 0) return '₦0.00';
                    return '₦' + amount.toLocaleString('en-NG', {
                        minimumFractionDigits: 2, 
                        maximumFractionDigits: 2
                    });
                }
            }
        }
    </script>
</body>
</html>