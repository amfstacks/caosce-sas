<div class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="syncDashboardController()" x-init="initSync()">

    <!-- Header -->
    <header class="bg-slate-900 shadow-md flex-shrink-0 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-blue-600/20 rounded-lg flex items-center justify-center border border-blue-500/30">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Sync Command Center</h1>
                    <p class="text-xs text-slate-400 font-medium mt-0.5" x-text="stationTitle"></p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <button @click="exitSyncMonitor" class="text-xs font-bold text-red-400 hover:text-red-300 bg-red-400/10 hover:bg-red-400/20 px-3 py-1.5 rounded-lg transition-colors border border-red-400/20">
                    Exit Monitor &rarr;
                </button>
                <div class="flex items-center gap-2 bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700">
                    <span class="relative flex h-2.5 w-2.5">
                      <span x-show="isOnline" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="isOnline ? 'text-green-400' : 'text-red-400'" x-text="isOnline ? 'Online' : 'Offline'"></span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        
        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Enrolled</p>
                    <p class="text-2xl font-black text-slate-900" x-text="metrics.total"></p>
                </div>
                <div class="bg-slate-100 p-3 rounded-lg text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Awaiting Exam</p>
                    <p class="text-2xl font-black text-slate-600" x-text="metrics.notStarted"></p>
                </div>
                <div class="bg-slate-100 p-3 rounded-lg text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <div class="bg-amber-50 rounded-xl shadow-sm border border-amber-200 p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Pending Sync</p>
                    <p class="text-2xl font-black text-amber-700" x-text="metrics.pending"></p>
                </div>
                <div class="bg-amber-100 p-3 rounded-lg text-amber-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
            </div>

            <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-5 flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-green-600 uppercase tracking-wider">Safely Synced</p>
                    <p class="text-2xl font-black text-green-700" x-text="metrics.synced"></p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg text-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>
        </div>

        <!-- Master Action Panel & Live Progress -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row items-center justify-between gap-4">
            
            <div class="w-full md:w-1/2">
                <template x-if="isSyncingAll">
                    <div>
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-sm font-bold text-blue-600 animate-pulse">Syncing Payload Queue...</span>
                            <span class="text-xs font-bold text-slate-500"><span x-text="syncProgress.current"></span> / <span x-text="syncProgress.total"></span> Complete</span>
                        </div>
                        <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-200">
                            <div class="h-full bg-blue-500 progress-bar-striped animate-progress transition-all duration-300" :style="'width: ' + ((syncProgress.current / syncProgress.total) * 100) + '%'"></div>
                        </div>
                    </div>
                </template>
                <template x-if="!isSyncingAll">
                    <p class="text-sm text-slate-500 font-medium">Bulk operation pushes all completed exam records to the master server.</p>
                </template>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <button @click="startBulkSync" :disabled="metrics.pending === 0 || isSyncingAll || !isOnline" class="flex-1 md:flex-none px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg x-show="isSyncingAll" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <svg x-show="!isSyncingAll" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span x-text="isSyncingAll ? 'Syncing...' : 'Sync All Pending'"></span>
                </button>

                <!-- Data Wipe Dropdown -->
                <!-- <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" :disabled="metrics.pending > 0" title="Clear data only allowed when no pending syncs exist" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg border border-slate-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        System Wipe
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden" x-transition>
                        <button @click="clearExamRecords" class="w-full text-left px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 border-b border-slate-100">
                            1. Clear Submitted Records Only
                        </button>
                        <button @click="clearAllData" class="w-full text-left px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 bg-red-50/30">
                            2. Nuke All Data & Unbind Device
                        </button>
                    </div>
                </div> -->

                <!-- System Tools & Wipe Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg border border-slate-300 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        System Tools
                    </button>
                    
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden" x-transition style="display: none;">
                        
                        <!-- NEW: Tenant Data Actions -->
                        <div class="px-4 py-2 bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">Tenant Data</div>
                        <button @click="refetchTenantData" :disabled="!isOnline" class="w-full text-left px-4 py-3 text-sm font-bold text-blue-600 hover:bg-blue-50 border-b border-slate-100 disabled:opacity-50 flex items-center justify-between">
                            Refetch School Info
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </button>
                        <button @click="clearTenantData" class="w-full text-left px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                            Clear Local Cache
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>

                        <!-- Existing Wipe Actions -->
                        <div class="px-4 py-2 bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500 uppercase tracking-wider">Exam Records</div>
                        <button @click="clearExamRecords" :disabled="metrics.pending > 0" class="w-full text-left px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 border-b border-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            Clear Submitted Records Only
                        </button>
                        <button @click="clearAllData" :disabled="metrics.pending > 0" class="w-full text-left px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 bg-red-50/30 disabled:opacity-50 disabled:cursor-not-allowed">
                            Nuke All Data & Unbind
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Master Roster List -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Device Cohort Log</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-white">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Matric Number</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Student Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Score</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <template x-for="student in mergedRoster" :key="student.id">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 font-mono font-bold text-sm text-slate-900" x-text="student.matric"></td>
                                
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-600" x-text="student.name"></td>
                                
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-slate-800">
                                    <span x-show="student.hasSubmission" x-text="student.total_score + ' / ' + student.max_possible"></span>
                                    <span x-show="!student.hasSubmission" class="text-slate-400 font-normal">--</span>
                                </td>

                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span x-show="student.status === 'not_started'" class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600 ring-1 ring-inset ring-slate-500/10">Awaiting</span>
                                    
                                    <span x-show="student.status === 'syncing'" class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                        <svg class="animate-spin h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Pushing...
                                    </span>
                                    
                                    <span x-show="student.status === 'pending'" class="inline-flex items-center gap-1.5 rounded-md bg-amber-50 px-2 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Pending Sync
                                    </span>
                                    
                                    <span x-show="student.status === 'synced'" class="inline-flex items-center gap-1.5 rounded-md bg-green-50 px-2 py-1 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                        <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        Synced
                                    </span>
                                </td>

                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Actions only appear if they have a submission -->
                                        <template x-if="student.hasSubmission && student.status !== 'syncing'">
                                            <div class="flex gap-2">
                                                
                                                <button @click="resetAttempt(student.id)" title="Delete submission to allow retake" class="px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 rounded border border-red-200 transition-colors">
                                                    Reset Attempt
                                                </button>

                                                <button x-show="student.status === 'synced'" @click="forceResync(student.id)" :disabled="!isOnline" class="px-3 py-1.5 text-xs font-bold text-blue-600 hover:bg-blue-50 rounded border border-blue-200 transition-colors disabled:opacity-50">
                                                    Force Resync
                                                </button>
                                                
                                            </div>
                                        </template>
                                    </div>
                                </td>

                            </tr>
                        </template>
                        <tr x-show="mergedRoster.length === 0">
                            <td colspan="5" class="py-12 text-center text-sm text-slate-500">
                                No students loaded in offline payload.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Global Toast -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl shadow-xl ring-1 ring-black ring-opacity-5" :class="toast.type === 'error' ? 'bg-red-900' : 'bg-slate-800'" style="display: none;">
                <div class="p-4 flex items-start">
                    <svg x-show="toast.type === 'success'" class="h-6 w-6 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <p class="ml-3 text-sm font-bold text-white pt-0.5" x-text="toast.message"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? "global"; ?>';

    function syncDashboardController() {
        return {
            isOnline: navigator.onLine,
            toast: { visible: false, message: '', type: 'success' },
            
            stationTitle: '',
            baseRoster: [],
            examRecords: [],
            mergedRoster: [],
            
            isSyncingAll: false,
            syncProgress: { current: 0, total: 0 },

            // NEW: Only initialize when the component becomes active!
            initSync() {
                window.addEventListener('online', () => this.isOnline = true);
                window.addEventListener('offline', () => this.isOnline = false);
                
                window.addEventListener('view-activated', async (e) => {
                    if (e.detail === 'sync') {
                        await this.loadData();
                    }
                });
            },

            // NEW: Destroy admin auth token and go back to login screen
            exitSyncMonitor() {
                sessionStorage.removeItem('caosce_offline_auth');
                window.dispatchEvent(new CustomEvent('navigate', { detail: 'login' }));
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

            async loadData() {
                try {
                    const payloadStr = localStorage.getItem('caosce_offline_data');
                    const dbPayload = await localforage.getItem('caosce_offline_data');
                    
                    let payload = dbPayload;
                    if (!payload && payloadStr) payload = JSON.parse(payloadStr);

                    if (!payload || !payload.students) {
                        alert("Critical Error: No offline payload found. This device is not properly bound.");
                        window.dispatchEvent(new CustomEvent('navigate', { detail: 'setup' }));
                        return;
                    }

                    this.stationTitle = payload.station_settings.title;
                    this.baseRoster = payload.students;
                    
                    this.examRecords = await localforage.getItem('caosce_exam_records') || [];
                    this.compileRoster();

                } catch(e) {
                    this.showToast("Failed to read local database", "error");
                }
            },

            compileRoster() {
                this.mergedRoster = this.baseRoster.map(student => {
                    let submission = this.examRecords.find(r => r.student_id === student.id);
                    
                    if (submission) {
                        return {
                            id: student.id,
                            matric: student.matric_number,
                            name: student.full_name,
                            hasSubmission: true,
                            record_id: submission.record_id,
                            total_score: submission.total_score,
                            max_possible: submission.max_possible,
                            status: submission.sync_status || 'pending',
                            raw_submission: submission
                        };
                    } else {
                        return {
                            id: student.id,
                            matric: student.matric_number,
                            name: student.full_name,
                            hasSubmission: false,
                            status: 'not_started'
                        };
                    }
                });
            },

            get metrics() {
                let total = this.mergedRoster.length;
                let notStarted = this.mergedRoster.filter(s => s.status === 'not_started').length;
                let pending = this.mergedRoster.filter(s => s.status === 'pending').length;
                let synced = this.mergedRoster.filter(s => s.status === 'synced').length;
                return { total, notStarted, pending, synced };
            },

            async startBulkSync() {
                if (!this.isOnline || this.metrics.pending === 0) return;
                
                this.isSyncingAll = true;
                let pendingStudents = this.mergedRoster.filter(s => s.status === 'pending');
                
                this.syncProgress.total = pendingStudents.length;
                this.syncProgress.current = 0;

                for (let student of pendingStudents) {
                    let idx = this.mergedRoster.findIndex(s => s.id === student.id);
                    this.mergedRoster[idx].status = 'syncing';
                    await this.pushRecordToServer(student.raw_submission);
                    this.syncProgress.current++;
                }

                this.isSyncingAll = false;
                this.showToast("Bulk Sync Queue Completed", "success");
                await this.loadData();
            },

            async forceResync(studentId) {
                if (!this.isOnline) return;
                let student = this.mergedRoster.find(s => s.id === studentId);
                if (!student || !student.raw_submission) return;

                let idx = this.mergedRoster.findIndex(s => s.id === studentId);
                this.mergedRoster[idx].status = 'syncing';

                await this.pushRecordToServer(student.raw_submission);
                await this.loadData();
                this.showToast(`Record forcefully synced.`);
            },

            async pushRecordToServer(record) {
                try {
                    let response = await fetch(this.getBaseApiUrl() + '/api/sync/cbt-score', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(record)
                    });
                    
                    let data = await response.json();
                    
                    if (data.success) {
                        let dbRecords = await localforage.getItem('caosce_exam_records');
                        let recordIdx = dbRecords.findIndex(r => r.record_id === record.record_id);
                        if (recordIdx > -1) {
                            dbRecords[recordIdx].sync_status = 'synced';
                            await localforage.setItem('caosce_exam_records', dbRecords);
                        }
                    }
                } catch(e) {}
            },

            async resetAttempt(studentId) {
                let student = this.mergedRoster.find(s => s.id === studentId);
                if(!confirm(`DANGER: Delete exam attempt for ${student.matric}? They will have to start over.`)) return;

                let dbRecords = await localforage.getItem('caosce_exam_records');
                dbRecords = dbRecords.filter(r => r.student_id !== studentId);
                await localforage.setItem('caosce_exam_records', dbRecords);

                const payload = await localforage.getItem('caosce_offline_data');
                if (payload) {
                    localStorage.removeItem(`caosce_progress_${studentId}_${payload.station_id}`);
                }

                this.showToast(`Attempt cleared.`, "success");
                await this.loadData();
            },

            async clearExamRecords() {
                if (this.metrics.pending > 0) {
                    alert("Sync pending records first.");
                    return;
                }
                if (confirm("Delete all submitted exam records?")) {
                    await localforage.setItem('caosce_exam_records', []);
                    this.showToast("Exam records wiped.", "success");
                    await this.loadData();
                }
            },

            async clearAllData() {
                if (this.metrics.pending > 0) {
                    alert("Sync pending records first.");
                    return;
                }
                if (confirm("NUCLEAR OPTION: Delete ALL data and UNBIND device?")) {
                    await localforage.removeItem('caosce_exam_records');
                    await localforage.removeItem('caosce_offline_data');
                    localStorage.removeItem('caosce_device_signature');
                    
                    Object.keys(localStorage).forEach(key => {
                        if(key.startsWith('caosce_progress_')) localStorage.removeItem(key);
                    });

                    alert("Wiped. Redirecting to setup.");
                    window.dispatchEvent(new CustomEvent('navigate', { detail: 'setup' }));
                }
            },
            async refetchTenantData() {
                if (!this.isOnline) {
                    alert("You must be online to fetch the latest school information.");
                    return;
                }
                
                try {
                    this.showToast("Fetching latest school data...", "success");
                    let response = await fetch(this.getBaseApiUrl() + '/api/tenant-info');
                    let data = await response.json();
                    
                    if (data.success) {
                        let slug = '<?php echo CURRENT_TENANT_SLUG ?? "global"; ?>';
                        // This exactly targets the cache key used by your Login page!
                        localStorage.setItem('caosce_school_cache_' + slug, JSON.stringify(data.payload));
                        this.showToast("School data updated successfully!", "success");
                    } else {
                        this.showToast(data.message || "Failed to fetch school data.", "error");
                    }
                } catch (e) {
                    this.showToast("Network error while fetching school data.", "error");
                }
            },

            clearTenantData() {
                if (confirm("Are you sure you want to completely clear the downloaded school logos and branding from this device?")) {
                    let slug = '<?php echo CURRENT_TENANT_SLUG ?? "global"; ?>';
                    localStorage.removeItem('caosce_school_cache_' + slug);
                    this.showToast("School data cache cleared.", "success");
                }
            },
        }
    }
</script>