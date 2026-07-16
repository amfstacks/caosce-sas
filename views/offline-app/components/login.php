<!-- START OF COMPONENT: login.php -->
<div class="bg-white font-sans antialiased min-h-screen flex w-full" x-data="loginController()" x-init="initLogin()" x-cloak>

    <div x-show="isInitializing" class="fixed inset-0 bg-slate-900 z-50 flex flex-col items-center justify-center transition-opacity duration-300">
        <svg class="animate-spin h-10 w-10 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <p class="text-white font-medium animate-pulse">Initializing Exam Environment...</p>
    </div>

    <div class="hidden md:flex md:w-1/2 lg:w-3/5 relative bg-slate-900 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center transition-all duration-700 ease-in-out transform scale-105"
             :style="schoolData.cover_image_path 
                ? 'background-image: url(' + schoolData.cover_image_path + ')' 
                : 'background-image: url(https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80)'">
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-slate-900/20"></div>
        
        <div class="relative z-10 p-12 flex flex-col justify-end h-full text-white w-full">
            <p class="text-blue-400 font-bold tracking-widest uppercase mb-2"><?php echo APP_NAME ?? 'CASOCE'; ?> Platform</p>
            <h1 class="text-4xl lg:text-5xl font-bold tracking-tight mb-4 leading-tight" x-text="schoolData.name || '<?php echo APP_CAPTION ?? 'Clinical Examination'; ?>'"></h1>
            <p class="text-slate-300 text-lg max-w-xl font-medium">A secure, resilient environment for objective structured clinical examinations.</p>
        </div>
    </div>

    <div class="w-full md:w-1/2 lg:w-2/5 flex flex-col items-center justify-center p-8 sm:p-12 bg-white relative shadow-[-10px_0_30px_rgba(0,0,0,0.05)] z-10">
        
        <div class="w-full max-w-md" x-show="!isInitializing" x-transition.opacity.duration.500ms>
            
            <div class="flex flex-col items-center text-center mb-8">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 overflow-hidden ring-1 ring-slate-200 shadow-sm">
                    <template x-if="schoolData.logo_path">
                        <img :src="schoolData.logo_path" alt="School Logo" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!schoolData.logo_path">
                        <span class="text-xs text-slate-400 font-bold"><?php echo APP_NAME ?? 'CASOCE'; ?></span>
                    </template>
                </div>
                <h2 class="text-2xl font-bold text-slate-900">Secure Exam Login</h2>
            </div>

            <!-- Device Provisioning Status Dashboard -->
            <div class="mb-8">
                <!-- State 1: Device is Bound and Data is Loaded -->
                <template x-if="deviceState.isBound">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 shadow-sm">
                        <div class="flex items-center justify-between mb-3 border-b border-slate-200 pb-3">
                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                Device Status
                            </span>
                            
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider"
                                  :class="deviceState.isPayloadReady ? 'bg-green-100 text-green-700 ring-1 ring-green-500/20' : 'bg-amber-100 text-amber-700 ring-1 ring-amber-500/20'">
                                <span class="w-1.5 h-1.5 rounded-full" :class="deviceState.isPayloadReady ? 'bg-green-500' : 'bg-amber-500 animate-pulse'"></span>
                                <span x-text="deviceState.isPayloadReady ? 'Exam Ready' : 'Data Missing'"></span>
                            </span>
                        </div>
                        
                        <div>
                            <div class="flex items-baseline gap-2 mb-1">
                                <span class="text-sm font-black text-blue-600 uppercase tracking-widest" x-text="deviceState.type"></span>
                                <span class="text-xs font-bold text-slate-400" x-text="'• Station ' + deviceState.sequence"></span>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 leading-tight" x-text="deviceState.title"></h3>
                        </div>
                    </div>
                </template>

                <!-- State 2: Device is NOT Bound -->
                <template x-if="!deviceState.isBound">
                    <div class="bg-red-50/50 border border-red-100 rounded-xl p-5 text-center">
                        <svg class="mx-auto h-8 w-8 text-red-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <h3 class="text-sm font-bold text-red-800">Unassigned Device</h3>
                        <p class="text-xs font-medium text-red-600 mt-1">This laptop has not been locked to an exam station.</p>
                    </div>
                </template>
            </div>

            <div x-show="errorMessage" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md" style="display: none;">
                <p class="text-sm font-medium text-red-800" x-text="errorMessage"></p>
            </div>

            <!-- Offline Login Form -->
            <form @submit.prevent="submitLogin" class="space-y-5">
                <div>
                    <label for="username" class="block text-sm font-semibold leading-6 text-slate-900">Matric / Admin / Examiner ID</label>
                    <div class="mt-1">
                        <input type="text" id="username" x-model="formData.username" required autocomplete="username" class="block w-full rounded-lg border-slate-300 py-3 px-4 text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 sm:text-sm border bg-slate-50 transition-colors">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold leading-6 text-slate-900">Secure PIN / Password</label>
                    <div class="mt-1">
                        <input type="password" id="password" x-model="formData.password" required autocomplete="current-password" class="block w-full rounded-lg border-slate-300 py-3 px-4 text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 sm:text-sm border bg-slate-50 transition-colors">
                    </div>
                </div>

                <button type="submit" :disabled="isLoading" class="flex w-full justify-center items-center rounded-lg bg-blue-600 px-3 py-4 text-sm font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed mt-6 transform active:scale-[0.98]">
                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isLoading ? 'Authenticating...' : 'Sign In to Exam'"></span>
                </button>
            </form>

            <!-- Hardware Provisioning Link (Switches instantly to Setup View) -->
            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col items-center justify-center gap-2">
                <?php if(defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG !== null): ?>
                    <span class="inline-flex items-center rounded bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">
                        Workspace: <?php echo htmlspecialchars(CURRENT_TENANT_SLUG); ?>
                    </span>
                <?php endif; ?>
                
                <!-- NEW: Alpine Dispatch instead of href -->
                <button @click="$dispatch('navigate', 'setup')" class="inline-flex items-center justify-center gap-1.5 text-xs font-bold text-slate-400 hover:text-blue-600 transition-colors group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Hardware Provisioning Setup
                </button>
            </div>
            
        </div>
    </div>
</div>

<script>
    const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

    function loginController() {
        return {
            formData: { username: '', password: '' },
            errorMessage: '',
            isLoading: false,
            isInitializing: true,
            schoolData: { name: '', logo_path: '', cover_image_path: '' },
            
            deviceState: {
                isBound: false,
                isPayloadReady: false,
                title: '',
                type: '',
                sequence: ''
            },

            async initLogin() {
                // Fetch tenant branding and check device binding simultaneously
                await Promise.all([
                    this.loadTenantData(),
                    this.checkDeviceBinding()
                ]);
                this.isInitializing = false;
            },

            getBaseApiUrl() {
                let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                return TENANT_SLUG ? `${basePath}/${TENANT_SLUG}` : basePath;
            },

            async checkDeviceBinding() {
                let sig = localStorage.getItem('caosce_device_signature');
                let payload = await localforage.getItem('caosce_offline_data');

                if (sig && payload) {
                    try {
                        this.deviceState.isBound = true;
                        this.deviceState.title = payload.station_settings.title || 'Station Locked';
                        this.deviceState.type = payload.station_settings.station_type || 'Unknown';
                        this.deviceState.sequence = payload.station_settings.order_sequence || '#';
                        
                        if (payload.students && payload.students.length > 0 && payload.questions && payload.questions.length > 0) {
                            this.deviceState.isPayloadReady = true;
                        }
                    } catch(e) {
                        this.deviceState.isBound = false;
                    }
                } else {
                    this.deviceState.isBound = false;
                }
            },

            async loadTenantData() {
                const cacheKey = 'caosce_school_cache_' + TENANT_SLUG;
                const cachedData = localStorage.getItem(cacheKey);

                if (cachedData) {
                    this.schoolData = JSON.parse(cachedData);
                    return; // Return cached data quickly
                }

                if(navigator.onLine) {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/tenant-info');
                        let data = await response.json();
                        if (data.success) {
                            this.schoolData = data.payload;
                            localStorage.setItem(cacheKey, JSON.stringify(data.payload));
                        }
                    } catch (error) { console.log('Offline: Using default tenant branding.'); }
                }
            },

            async submitLogin() {
                this.isLoading = true;
                this.errorMessage = '';

                let inputUser = this.formData.username.trim().toLowerCase();
                let inputPass = this.formData.password.trim();

                // 1. ADMIN BYPASS (Access to Sync Dashboard)
                // If it's the admin, we allow them to log in to access the Sync Dashboard
                if (inputUser.includes('admin') || inputUser === 'sync') {
                    // In a real app, you might verify this against an offline admin hash
                    // or require them to be online to authenticate as admin.
                    sessionStorage.setItem('caosce_offline_auth', JSON.stringify({
                        role: 'admin',
                        name: 'System Administrator'
                    }));
                    this.$dispatch('navigate', 'sync'); // INSTANT ROUTING
                    return;
                }

                // Security Gate 1: Check if bound
                if (!this.deviceState.isBound) {
                    this.errorMessage = 'Access Denied: This device has not been bound by an Administrator.';
                    this.isLoading = false;
                    return;
                }

                // Security Gate 2: Check if payload is downloaded
                if (!this.deviceState.isPayloadReady) {
                    this.errorMessage = 'Data Missing: Please contact the admin to sync this device.';
                    this.isLoading = false;
                    return;
                }

                await this.performOfflineLogin(inputUser, inputPass);
            },

            async performOfflineLogin(inputUser, inputPass) {
                try {
                    let payload = await localforage.getItem('caosce_offline_data');
                    let stationType = payload.station_settings.station_type;

                    // 1. EXAMINER ROUTING (Procedure Stations Only)
                    if (stationType === 'procedure' && payload.examiner) {
                        if (payload.examiner.username.toLowerCase() === inputUser && payload.examiner.raw_password === inputPass) {
                            sessionStorage.setItem('caosce_offline_auth', JSON.stringify({
                                role: 'examiner',
                                id: payload.examiner.id,
                                name: payload.examiner.full_name,
                                username: payload.examiner.username,
                                login_time: Date.now()
                            }));
                            
                            // NEW: INSTANT SPA ROUTING
                            this.$dispatch('navigate', 'procedure');
                            return;
                        }
                    }

                    // 2. STUDENT ROUTING
                    if (payload.students) {
                        let student = payload.students.find(s => 
                            s.matric_number.toLowerCase() === inputUser && s.raw_password === inputPass
                        );

                        if (student) {
                            sessionStorage.setItem('caosce_offline_auth', JSON.stringify({
                                role: 'student',
                                id: student.id,
                                matric: student.matric_number,
                                name: student.full_name,
                                login_time: Date.now()
                            }));
                            
                            // NEW: INSTANT SPA ROUTING
                            if (stationType === 'cbt') {
                                this.$dispatch('navigate', 'cbt');
                            } else {
                                // If student logs into a procedure station, maybe just a waiting screen
                                // We route to 'procedure' component for now (or a dedicated standby)
                                this.$dispatch('navigate', 'procedure'); 
                            }
                            return;
                        }
                    }

                    // 3. NO MATCH FOUND
                    this.errorMessage = 'Invalid Matric Number/Examiner ID or Password.';

                } catch(e) {
                    console.error(e);
                    this.errorMessage = 'Critical Error reading offline database.';
                } finally {
                    this.isLoading = false;
                }
            }
        }
    }
</script>
