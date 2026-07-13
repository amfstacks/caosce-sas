<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Device Binding & Sync</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="deviceBinderController()" x-cloak>

    <!-- Header -->
    <header class="bg-slate-900 shadow-md flex-shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-blue-600/20 rounded-lg flex items-center justify-center border border-blue-500/30">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Offline Sync Center</h1>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Device Provisioning & Payload Management</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Network Status Indicator -->
                <div class="flex items-center gap-2 bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700">
                    <span class="relative flex h-2.5 w-2.5">
                      <span x-show="isOnline" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="isOnline ? 'text-green-400' : 'text-red-400'" x-text="isOnline ? 'System Online' : 'System Offline'"></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-4xl space-y-6">
            
            <!-- SECTION 1: Mini Stats Dashboard -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-800 px-6 py-4 flex justify-between items-center border-b border-slate-700">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Target Assignment Overview
                    </h2>
                    <span class="inline-flex items-center rounded-md bg-blue-500/10 px-2.5 py-0.5 text-xs font-bold text-blue-400 ring-1 ring-inset ring-blue-500/20 uppercase tracking-widest" x-text="target.station_type"></span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 bg-slate-50">
                    <div class="p-6">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Exam Session</p>
                        <p class="text-base font-bold text-slate-800 leading-tight" x-text="target.session_title"></p>
                    </div>
                    <div class="p-6">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Station Title</p>
                        <p class="text-base font-bold text-slate-800 leading-tight" x-text="target.station_title"></p>
                    </div>
                    <div class="p-6 flex items-center">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sequence</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-blue-600 leading-none" x-text="target.station_sequence"></span>
                                <span class="text-sm font-bold text-slate-500">Ring Position</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Offline Data Synchronization -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 relative overflow-hidden">
                
                <!-- Success Background overlay if synced -->
                <div x-show="localStats.isSynced" class="absolute inset-0 bg-green-50/50 pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Local Storage Payload</h3>
                            <p class="text-sm text-slate-500 mt-1">Download questions and rosters to run this station without internet.</p>
                        </div>
                        
                        <div class="mt-4 sm:mt-0" x-show="localStats.isSynced">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                Payload Cached
                            </span>
                        </div>
                    </div>

                    <!-- Sync Metrics -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                        <!-- Roster Metric -->
                        <div class="bg-white border rounded-xl p-5 flex items-center justify-between transition-colors" :class="localStats.isSynced ? 'border-green-200 shadow-sm' : 'border-slate-200'">
                            <div>
                                <p class="text-sm font-bold text-slate-600 mb-1">Enrolled Students</p>
                                <p class="text-2xl font-black text-slate-900" x-text="localStats.studentCount"></p>
                            </div>
                            <div class="w-12 h-12 rounded-full flex items-center justify-center" :class="localStats.isSynced ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                        </div>

                        <!-- Questions Metric -->
                        <div class="bg-white border rounded-xl p-5 flex items-center justify-between transition-colors" :class="localStats.isSynced ? 'border-green-200 shadow-sm' : 'border-slate-200'">
                            <div>
                                <p class="text-sm font-bold text-slate-600 mb-1">Loaded Questions</p>
                                <p class="text-2xl font-black text-slate-900" x-text="localStats.questionCount"></p>
                            </div>
                            <div class="w-12 h-12 rounded-full flex items-center justify-center" :class="localStats.isSynced ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        
                        <!-- Initial Sync Button (Only shows if NOT synced) -->
                        <button x-show="!localStats.isSynced" @click="downloadOfflineData" type="button" class="flex-1 flex justify-center items-center gap-2 rounded-xl bg-blue-600 px-6 py-4 text-sm font-bold text-white shadow-lg hover:bg-blue-500 transition-colors disabled:opacity-50" :disabled="isSyncing || !isOnline">
                            <svg x-show="isSyncing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg x-show="!isSyncing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span x-text="isSyncing ? 'Downloading Payload...' : 'Fetch Offline Data'"></span>
                        </button>

                        <!-- Synced State Buttons -->
                        <div x-show="localStats.isSynced" class="w-full flex flex-col sm:flex-row gap-3">
                            <button @click="downloadOfflineData" type="button" class="flex-1 rounded-xl bg-white border border-slate-300 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors disabled:opacity-50" :disabled="isSyncing || !isOnline">
                                <span x-text="isSyncing ? 'Refreshing...' : '↻ Reload Fresh Data'"></span>
                            </button>
                            
                            <button @click="unbindDevice" type="button" class="flex-1 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-bold text-red-700 shadow-sm hover:bg-red-100 transition-colors">
                                ⚠ Clear & Unbind Device
                            </button>

                            <button @click="launchExamMode" type="button" class="flex-[2] rounded-xl bg-green-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-green-600/20 hover:bg-green-500 transition-colors flex items-center justify-center gap-2">
                                <span>Launch Exam Screen</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                            </button>
                        </div>

                    </div>
                    
                    <p x-show="!isOnline" class="mt-4 text-xs font-bold text-red-500 text-center bg-red-50 py-2 rounded-lg border border-red-100">
                        Internet connection required to download or refresh data.
                    </p>

                </div>
            </div>
        </div>
    </main>

    <!-- Global Toast -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl shadow-xl ring-1 ring-black ring-opacity-5" :class="toast.type === 'error' ? 'bg-red-900' : 'bg-slate-800'" style="display: none;">
                <div class="p-4 flex items-start">
                    <svg x-show="toast.type === 'success'" class="h-6 w-6 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg x-show="toast.type === 'error'" class="h-6 w-6 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <p class="ml-3 text-sm font-bold text-white pt-0.5" x-text="toast.message"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>';

        function deviceBinderController() {
            return {
                isOnline: navigator.onLine,
                isSyncing: false,
                toast: { visible: false, message: '', type: 'success' },
                
                // Target from Gatekeeper payload
                target: {
                    session_id: '', session_title: '',
                    station_id: '', station_title: '', station_sequence: '', station_type: ''
                },

                // UI Stats
                localStats: {
                    isSynced: false,
                    studentCount: 0,
                    questionCount: 0
                },

                init() {
                    window.addEventListener('online', () => this.isOnline = true);
                    window.addEventListener('offline', () => this.isOnline = false);

                    // 1. Verify Gatekeeper Payload exists
                    const payloadStr = sessionStorage.getItem('caosce_binding_payload');
                    if (!payloadStr) {
                        alert("Unauthorized access. Redirecting to Gatekeeper.");
                        window.location.href = `/${TENANT_SLUG}/admin/gatekeeper`; // Adjust to your setup url
                        return;
                    }
                    this.target = JSON.parse(payloadStr);

                    // 2. Check if we already have offline data for this specific station
                    this.checkLocalPayload();
                },

                getBaseApiUrl() {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return TENANT_SLUG ? `${basePath}/${TENANT_SLUG}` : basePath;
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 4000);
                },

                checkLocalPayload() {
                    const offlineData = localStorage.getItem('caosce_offline_data');
                    if (offlineData) {
                        try {
                            const parsed = JSON.parse(offlineData);
                            // Verify the cached data matches our target!
                            if (parsed.station_id === this.target.station_id) {
                                this.localStats.studentCount = parsed.students ? parsed.students.length : 0;
                                this.localStats.questionCount = parsed.questions ? parsed.questions.length : 0;
                                this.localStats.isSynced = true;
                            }
                        } catch (e) {
                            console.error("Corrupted offline payload found.");
                        }
                    }
                },

                async downloadOfflineData() {
                    if (!this.isOnline) return;
                    this.isSyncing = true;

                    try {
                        let response = await fetch(this.getBaseApiUrl() + `/api/setup/download-payload?session_id=${this.target.session_id}&station_id=${this.target.station_id}`);
                        let data = await response.json();

                        if (data.success) {
                            // Structuring the ultimate offline payload
                            const offlinePayload = {
                                session_id: this.target.session_id,
                                station_id: this.target.station_id,
                                sync_timestamp: Date.now(),
                                students: data.payload.students, // Contains matric, name, raw_password
                                questions: data.payload.questions,
                                station_settings: data.payload.station_settings
                            };

                            // Save to LocalStorage (Your .exe wrapper will persist this)
                            localStorage.setItem('caosce_offline_data', JSON.stringify(offlinePayload));
                            
                            // Generate and bind device signature
                            if (!localStorage.getItem('caosce_device_signature')) {
                                localStorage.setItem('caosce_device_signature', this.generateUUID());
                            }

                            // Update UI
                            this.checkLocalPayload();
                            this.showToast('Data synchronized and secured locally!', 'success');
                        } else {
                            this.showToast(data.message || 'Failed to fetch payload.', 'error');
                        }
                    } catch (error) {
                        this.showToast('Network error during synchronization.', 'error');
                    } finally {
                        this.isSyncing = false;
                    }
                },

                unbindDevice() {
                    if(confirm("DANGER: This will delete all locally cached student passwords and questions, and remove the device signature. Proceed?")) {
                        // Clear the heavy lifting
                        localStorage.removeItem('caosce_offline_data');
                        localStorage.removeItem('caosce_device_signature');
                        
                        // Clear the session authorization
                        sessionStorage.removeItem('caosce_binding_payload');
                        sessionStorage.removeItem('caosce_provision_pin');
                        
                        // Kick back to Gatekeeper
                        window.location.href = this.getBaseApiUrl() + '/admin/gatekeeper'; // Adjust URL as needed
                    }
                },

                launchExamMode() {
                    if (!this.localStats.isSynced) {
                        this.showToast("Cannot launch: Payload is missing.", "error");
                        return;
                    }
                    // Redirect to the exam login page. That page will read `caosce_offline_data` 
                    // instead of calling the server!
                    window.location.href = this.getBaseApiUrl() + '/exam/login';
                },

                generateUUID() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                }
            }
        }
    </script>
</body>
</html>