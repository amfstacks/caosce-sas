<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Device Binding</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="deviceBinderController()" x-cloak>

    <!-- Header -->
    <header class="bg-slate-900 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <h1 class="text-xl font-bold text-white">Device Binding Wizard</h1>
            </div>
            <a href="/<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>/admin/dashboard" class="text-slate-300 hover:text-white text-sm font-medium">
                &larr; Back to Dashboard
            </a>
        </div>
    </header>

    <!-- Main Wizard Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full overflow-hidden border border-slate-200">
            
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50">
                <h2 class="text-lg font-bold text-slate-900">Configure Offline Exam Device</h2>
                <p class="mt-1 text-sm text-slate-500">Lock this physical laptop to a specific exam station. This will download the offline payload and secure the device.</p>
            </div>

            <form @submit.prevent="bindDevice" class="px-8 py-8 space-y-8">
                
                <!-- Step 1: Select Session -->
                <div>
                    <label class="flex items-center text-sm font-bold text-slate-900 mb-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 mr-2 text-xs">1</span>
                        Select Active Exam Session
                    </label>
                    <select x-model="selectedSessionId" @change="loadStations" required class="block w-full rounded-md border-slate-300 py-3 px-4 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border bg-slate-50">
                        <option value="">-- Choose a session --</option>
                        <template x-for="session in sessions" :key="session.id">
                            <option :value="session.id" x-text="session.title + ' (' + session.date + ')'"></option>
                        </template>
                    </select>
                </div>

                <!-- Step 2: Select Station -->
                <div x-show="selectedSessionId" x-transition.opacity>
                    <label class="flex items-center text-sm font-bold text-slate-900 mb-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 mr-2 text-xs">2</span>
                        Select Target Station
                    </label>
                    <select x-model="selectedStationId" required class="block w-full rounded-md border-slate-300 py-3 px-4 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm border bg-slate-50">
                        <option value="">-- Choose the station for this laptop --</option>
                        <template x-for="station in availableStations" :key="station.id">
                            <option :value="station.id" :disabled="!station.confirmed" x-text="'Station ' + station.sequence + ' - ' + station.type.toUpperCase() + (station.confirmed ? '' : ' (Draft - Cannot Bind)')"></option>
                        </template>
                    </select>
                </div>

                <!-- Step 3: Review Configuration -->
                <div x-show="selectedStation" x-transition.opacity class="bg-blue-50 border border-blue-100 rounded-lg p-5">
                    <h3 class="text-sm font-bold text-blue-900 mb-3 border-b border-blue-200 pb-2">Target Station Overview</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="block text-blue-600 font-medium text-xs uppercase tracking-wider">Type</span>
                            <span class="font-bold text-slate-800" x-text="selectedStation?.type.toUpperCase()"></span>
                        </div>
                        <div>
                            <span class="block text-blue-600 font-medium text-xs uppercase tracking-wider">Station Title</span>
                            <span class="font-bold text-slate-800" x-text="selectedStation?.title"></span>
                        </div>
                        
                        <div x-show="selectedStation?.type === 'procedure'">
                            <span class="block text-blue-600 font-medium text-xs uppercase tracking-wider">Assigned Examiner</span>
                            <span class="font-bold text-slate-800" x-text="selectedStation?.examiner_name"></span>
                        </div>
                        
                        <div>
                            <span class="block text-blue-600 font-medium text-xs uppercase tracking-wider">Questions/Steps Loaded</span>
                            <span class="font-bold text-slate-800" x-text="selectedStation?.question_count + ' items'"></span>
                        </div>
                    </div>
                </div>

                <!-- Network Warning -->
                <div x-show="selectedStation && !isOnline" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">No Internet Connection</h3>
                            <p class="mt-2 text-sm text-red-700">You must be connected to the internet to download the initial payload and bind this device.</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-4 border-t border-slate-200 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" :disabled="!selectedStation || isBinding || !isOnline">
                        <svg x-show="isBinding" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isBinding ? 'Downloading Payload & Binding...' : 'Lock & Bind Device'"></span>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <!-- Global Toast Notification -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-slate-800 shadow-lg ring-1 ring-black ring-opacity-5" style="display: none;">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <!-- Success Icon -->
                            <svg x-show="toast.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <!-- Error Icon -->
                            <svg x-show="toast.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-white" x-text="toast.message"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Use the dynamic tenant slug for redirects and API calls
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>';

        function deviceBinderController() {
            return {
                isOnline: navigator.onLine,
                isBinding: false,
                toast: { visible: false, message: '', type: 'success' },
                
                sessions: [],
                availableStations: [],
                
                selectedSessionId: '',
                selectedStationId: '',

                init() {
                    if (!sessionStorage.getItem('caosce_provision_token')) {
        window.location.href = `/${TENANT_SLUG}/setup/device`;
        return;
    }
                    window.addEventListener('online', () => this.isOnline = true);
                    window.addEventListener('offline', () => this.isOnline = false);
                    this.fetchSessions();
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 4000);
                },

                // Computed property to easily get the full station object based on selection
                get selectedStation() {
                    if (!this.selectedStationId) return null;
                    return this.availableStations.find(s => s.id === this.selectedStationId);
                },

                async fetchSessions() {
                    // Simulated backend fetch
                    this.sessions = [
                        { id: 'sess-001', title: 'Maternal Health Practical OSCE', date: 'August 15, 2026' }
                    ];
                },

                async loadStations() {
                    this.selectedStationId = ''; // Reset dependent dropdown
                    
                    if (!this.selectedSessionId) {
                        this.availableStations = [];
                        return;
                    }

                    // Simulated backend fetch of CONFIRMED stations for this session
                    // Notice Station 2 is not confirmed, so it gets disabled in the UI
                    this.availableStations = [
                        { id: 'st-1', sequence: 1, type: 'procedure', title: 'IV Cannulation', examiner_name: 'Dr. Sarah Samson', question_count: 5, confirmed: true },
                        { id: 'st-2', sequence: 2, type: 'cbt', title: 'Anatomy Review', examiner_name: null, question_count: 0, confirmed: false },
                        { id: 'st-4', sequence: 4, type: 'cbt', title: 'Pharmacology Quiz', examiner_name: null, question_count: 20, confirmed: true }
                    ];
                },

                // Standard UUID generator for cryptographic device signatures
                generateUUID() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                },

                async bindDevice() {
                    if (!this.isOnline || !this.selectedStationId) return;

                    this.isBinding = true;
                    let signature = this.generateUUID();

                    try {
                        /* 
                        // IN PRODUCTION: You will hit your PHP backend here
                        let response = await fetch(`/${TENANT_SLUG}/api/admin/bind-device`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                device_signature: signature,
                                exam_session_id: this.selectedSessionId,
                                station_id: this.selectedStationId
                            })
                        });
                        let payloadData = await response.json(); 
                        */

                        // Simulating network delay and payload download
                        await new Promise(resolve => setTimeout(resolve, 1500));

                        // 1. Lock the device signature into the browser's "Black Box"
                        localStorage.setItem('caosce_device_signature', signature);
                        
                        // 2. Mock saving the offline payload (Questions, Roster, etc.)
                        localStorage.setItem('caosce_offline_payload', JSON.stringify({
                            station: this.selectedStation,
                            timestamp: Date.now()
                        }));

                        this.showToast('Device bound successfully. Redirecting to login...', 'success');

                        // 3. Kick the admin out so the laptop is sitting securely on the login screen
                        setTimeout(() => {
                            window.location.href = `/${TENANT_SLUG}/login`;
                        }, 1500);

                    } catch (error) {
                        this.isBinding = false;
                        this.showToast('Failed to bind device. Check server connection.', 'error');
                        console.error(error);
                    }
                }
            }
        }
    </script>
</body>
</html>