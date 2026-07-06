<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAOSCE - Exam Session Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <nav class="bg-slate-900 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <span class="font-bold text-xl tracking-wider text-blue-400">CAOSCE</span>
                    <span class="text-slate-400">|</span>
                    <span class="font-medium">Exam Session Manager</span>
                </div>
                <div>
                    <a href="/<?php echo CURRENT_TENANT_SLUG; ?>/admin/dashboard" class="text-sm bg-slate-800 hover:bg-slate-700 px-3 py-2 rounded-md transition">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8" x-data="examSessionManager()">
        
        <!-- Header -->
        <div class="mb-8 border-b border-slate-200 pb-4">
            <h1 class="text-3xl font-bold text-slate-900">Setup New Exam</h1>
            <p class="text-slate-500 mt-2">Workspace: <span class="uppercase font-semibold text-blue-600"><?php echo CURRENT_TENANT_SLUG; ?></span></p>
        </div>

        <!-- Step Progress Indicator -->
        <div class="mb-8 flex space-x-4">
            <div :class="step === 1 ? 'border-blue-600 text-blue-600' : (step > 1 ? 'border-emerald-500 text-emerald-500' : 'border-slate-300 text-slate-400')" class="flex-1 border-b-4 pb-2 text-center font-medium transition-colors duration-300">
                1. Create Session
            </div>
            <div :class="step === 2 ? 'border-blue-600 text-blue-600' : (step > 2 ? 'border-emerald-500 text-emerald-500' : 'border-slate-300 text-slate-400')" class="flex-1 border-b-4 pb-2 text-center font-medium transition-colors duration-300">
                2. Import Students
            </div>
            <div :class="step === 3 ? 'border-blue-600 text-blue-600' : 'border-slate-300 text-slate-400'" class="flex-1 border-b-4 pb-2 text-center font-medium transition-colors duration-300">
                3. Allocate Stations
            </div>
        </div>

        <!-- STEP 1: Create Session -->
        <div x-show="step === 1" x-transition x-cloak class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-6">Session Details</h2>
            <form @submit.prevent="createSession" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Exam Title</label>
                    <input type="text" x-model="sessionData.title" required placeholder="e.g., Maternal Health CAOSCE - July Intake" class="w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Date</label>
                        <input type="date" x-model="sessionData.date" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                        <select x-model="sessionData.department_id" required class="w-full px-4 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="">Select Department...</option>
                            <option value="1">General Nursing (NS)</option>
                            <option value="2">Midwifery (MW)</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" :disabled="isProcessing" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition disabled:opacity-50">
                        <span x-text="isProcessing ? 'Saving...' : 'Initialize Exam & Continue'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- STEP 2: Import Candidates -->
        <div x-show="step === 2" x-transition x-cloak class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Enroll Candidates via CSV</h2>
            
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Formatting Instructions</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Upload a CSV file without headers. <strong>Column A:</strong> Matric Number, <strong>Column B:</strong> Full Name. Passwords will be auto-generated securely by the system.</p>
                        </div>
                    </div>
                </div>
            </div>

            <form @submit.prevent="uploadCsv" class="space-y-6">
                <div class="border-2 border-dashed border-slate-300 rounded-lg p-8 text-center hover:bg-slate-50 transition cursor-pointer" @click="$refs.csvInput.click()">
                    <input type="file" x-ref="csvInput" accept=".csv" class="hidden" @change="handleFileChange">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex justify-center text-sm text-slate-600">
                            <span class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500">
                                <span x-text="fileName ? fileName : 'Upload a CSV file'"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" @click="step = 1" class="text-slate-600 hover:text-slate-900 font-medium py-2 px-4">Back</button>
                    <button type="submit" :disabled="!fileName || isProcessing" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition disabled:opacity-50">
                        <span x-text="isProcessing ? 'Processing CSV...' : 'Upload & Enroll Students'"></span>
                    </button>
                </div>
            </form>
        </div>

        <!-- STEP 3: Allocate Stations -->
        <div x-show="step === 3" x-transition x-cloak class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-xl font-semibold">Allocate Examiners to Stations</h2>
                <p class="text-sm text-slate-500 mt-1">Assign clinical examiners to procedure stations. CBT stations are fully automated.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Station Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Assigned Examiner</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <template x-for="station in stations" :key="station.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900" x-text="station.title"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    <span :class="station.station_type === 'cbt' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full uppercase" x-text="station.station_type"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    
                                    <!-- Examiner Dropdown for Procedure Stations -->
                                    <select x-show="station.station_type === 'procedure'" 
                                            x-model="station.examiner_id" 
                                            @change="assignExaminer(station.id, $event.target.value)"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border">
                                        <option value="">-- Unassigned --</option>
                                        <template x-for="examiner in examiners" :key="examiner.id">
                                            <option :value="examiner.id" x-text="examiner.full_name"></option>
                                        </template>
                                    </select>
                                    
                                    <!-- Badge for automated CBT Stations -->
                                    <div x-show="station.station_type === 'cbt'" class="text-slate-400 italic flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        Automated (No Examiner)
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button @click="finishSetup" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-6 rounded-md transition">
                    Complete Setup
                </button>
            </div>
        </div>

        <!-- Custom Message Box (Replaces alert) -->
        <div x-show="toast.visible" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="fixed bottom-6 right-6 max-w-sm w-full bg-slate-800 text-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden" style="z-index: 50;">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium" x-text="toast.message"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="toast.visible = false" class="bg-slate-800 rounded-md inline-flex text-slate-400 hover:text-white focus:outline-none">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script>
        function examSessionManager() {
            // Grab the tenant slug safely from PHP
            const tenantSlug = '<?php echo CURRENT_TENANT_SLUG; ?>';
            const apiBase = `/${tenantSlug}/api/admin`;

            return {
                step: 1,
                isProcessing: false,
                currentSessionId: null,
                fileName: '',
                
                sessionData: { 
                    title: '', 
                    date: '', 
                    department_id: ''
                },
                
                stations: [],
                examiners: [], 
                
                toast: {
                    visible: false,
                    message: ''
                },

                init() {
                    // Preload examiners for Step 3
                    this.loadExaminers();
                },

                showToast(message) {
                    this.toast.message = message;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 4000);
                },

                handleFileChange(e) {
                    if (e.target.files.length > 0) {
                        this.fileName = e.target.files[0].name;
                    }
                },

                async createSession() {
                    this.isProcessing = true;
                    try {
                        // Mocking the API response since backend isn't built yet
                        /*
                        let response = await fetch(`${apiBase}/session/create`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.sessionData)
                        });
                        let data = await response.json();
                        */
                        
                        // MOCK DELAY & SUCCESS
                        await new Promise(r => setTimeout(r, 800));
                        this.currentSessionId = 'mock-session-uuid-123';
                        
                        this.showToast('Exam Session initialized successfully.');
                        this.step = 2; // Move to CSV upload
                    } catch (error) {
                        this.showToast('Failed to connect to server.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async uploadCsv() {
                    let fileInput = this.$refs.csvInput;
                    if (fileInput.files.length === 0) {
                        this.showToast('Please select a CSV file first.');
                        return;
                    }

                    this.isProcessing = true;
                    let formData = new FormData();
                    formData.append('student_csv', fileInput.files[0]);
                    formData.append('exam_session_id', this.currentSessionId);

                    try {
                        // Mock API upload
                        /*
                        let response = await fetch(`${apiBase}/session/import`, {
                            method: 'POST',
                            body: formData 
                        });
                        let data = await response.json();
                        */

                        // MOCK DELAY & SUCCESS
                        await new Promise(r => setTimeout(r, 1500));
                        
                        this.showToast('Candidates successfully ingested. Passwords generated.');
                        this.loadStations(); // Load generated stations
                        this.step = 3;
                    } catch (error) {
                        this.showToast('Failed to upload file.');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async loadExaminers() {
                    // Mock data - In reality, fetch from API
                    this.examiners = [
                        { id: 'ex-1', full_name: 'Dr. Sarah Jenkins' },
                        { id: 'ex-2', full_name: 'Prof. Michael Okeke' },
                        { id: 'ex-3', full_name: 'Dr. Aisha Bello' }
                    ];
                },

                async loadStations() {
                    // Mock data - In reality, the backend creates these when the session is initialized
                    this.stations = [
                        { id: 'st-1', title: 'Station 1 - Vitals', station_type: 'procedure', examiner_id: '' },
                        { id: 'st-2', title: 'Station 2 - History', station_type: 'procedure', examiner_id: '' },
                        { id: 'st-3', title: 'Station 3 - Assessment', station_type: 'cbt', examiner_id: null },
                        { id: 'st-4', title: 'Station 4 - Delivery', station_type: 'procedure', examiner_id: '' },
                        { id: 'st-5', title: 'Station 5 - Postpartum', station_type: 'procedure', examiner_id: '' },
                        { id: 'st-6', title: 'Station 6 - Ethics', station_type: 'cbt', examiner_id: null },
                    ];
                },

                async assignExaminer(stationId, examinerId) {
                    if(!examinerId) return;
                    
                    // Mock API call to save assignment immediately
                    /*
                    await fetch(`${apiBase}/station/assign`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ station_id: stationId, examiner_id: examinerId })
                    });
                    */
                    this.showToast('Examiner assigned to station.');
                },

                finishSetup() {
                    this.showToast('Exam setup complete! Redirecting...');
                    setTimeout(() => {
                        window.location.href = `/${tenantSlug}/admin/dashboard`;
                    }, 1500);
                }
            }
        }
    </script>
</body>
</html>