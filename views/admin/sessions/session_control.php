<?php 
$pageTitle = "Session Control Room"; 
$activeMenu = "sessions"; 
include '../views/layouts/header.php'; 
?>
<style>
    .tab-enter { transition: opacity 0.3s ease-out, transform 0.3s ease-out; }
    .tab-enter-start { opacity: 0; transform: translateY(5px); }
    .tab-enter-end { opacity: 1; transform: translateY(0); }
</style>

<body class="bg-slate-50 font-sans antialiased h-screen flex overflow-hidden" x-data="sessionControl()" x-cloak>

    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Topbar -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sm:px-10 flex-shrink-0 z-10">
            <div class="flex items-center gap-4">
                <a :href="getBaseApiUrl() + '/admin/dashboard'" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors">
                    <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Session Control Room</h1>
                    <p class="text-sm text-slate-500 font-medium mt-0.5" x-text="session.title || 'Loading...'"></p>
                </div>
            </div>
            
            <div x-show="activeTab === 'results'" class="flex gap-3">
                <button @click="fetchResults()" class="flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh Data
                </button>
                <button @click="downloadMasterCSV()" class="flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-500 transition-all">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Master Result (CSV)
                </button>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50/50 p-8 sm:p-10 z-0">
            
            <div class="max-w-7xl mx-auto space-y-8">
                
                <!-- Tab Navigation -->
                <div class="border-b border-slate-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="activeTab = 'overview'" 
                            :class="activeTab === 'overview' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'" 
                            class="group whitespace-nowrap border-b-2 py-4 px-1 text-sm font-bold transition-colors">
                            1. Configuration Overview
                        </button>
                        <button @click="activeTab = 'results'" 
                            :class="activeTab === 'results' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'" 
                            class="group whitespace-nowrap border-b-2 py-4 px-1 text-sm font-bold transition-colors flex items-center gap-2">
                            2. Live Exam Results
                            <span class="bg-blue-100 text-blue-700 py-0.5 px-2 rounded-full text-xs font-semibold ml-1">Live</span>
                        </button>
                    </nav>
                </div>

                <!-- TAB 1: OVERVIEW -->
                <div x-show="activeTab === 'overview'" class="tab-enter" style="display: none;">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Enrolled Candidates</p><p class="text-3xl font-extrabold text-slate-900 mt-1" x-text="summary.total_students"></p></div>
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
                        </div>
                        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Active Stations</p><p class="text-3xl font-extrabold text-slate-900 mt-1" x-text="summary.total_stations"></p></div>
                            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg></div>
                        </div>
                        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                            <div><p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Assigned Lecturers</p><p class="text-3xl font-extrabold text-slate-900 mt-1"><span x-text="summary.assigned_lecturers"></span> <span class="text-lg text-slate-400">/</span> <span class="text-lg text-slate-400" x-text="summary.total_stations"></span></p></div>
                            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center"><svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        </div>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4">Station Configuration Setup</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <template x-for="station in stations" :key="station.id">
                            <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg font-black text-lg flex items-center justify-center flex-shrink-0" :class="station.station_type === 'cbt' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700'" x-text="station.order_sequence"></div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <div><span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1 block" x-text="station.station_type + ' MODULE'"></span><h3 class="text-lg font-bold text-slate-900 leading-tight" x-text="station.title"></h3></div>
                                        <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-1 rounded" x-text="station.time_limit_minutes + ' min'"></span>
                                    </div>
                                    <div class="mt-4 flex items-center gap-2" :class="station.examiner_name ? 'text-emerald-600' : 'text-amber-500'">
                                        <svg x-show="station.examiner_name" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <svg x-show="!station.examiner_name" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <span class="text-sm font-semibold" x-text="station.examiner_name ? 'Assigned: ' + station.examiner_name : 'No examiner assigned'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- TAB 2: LIVE RESULTS -->
                <div x-show="activeTab === 'results'" class="tab-enter" style="display: none;">
                    
                    <!-- Download Station Cards -->
                    <div class="flex flex-wrap gap-3 mb-6">
                        <template x-for="st in resultStations" :key="st.id">
                            <button @click="downloadStationCSV(st)" class="inline-flex items-center gap-2 bg-white border border-slate-200 hover:border-blue-300 shadow-sm hover:shadow text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold transition-all group">
                                <span class="bg-slate-100 text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600 w-6 h-6 rounded flex items-center justify-center text-xs transition-colors" x-text="st.order_sequence"></span>
                                Station <span x-text="st.order_sequence"></span>
                                <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-500 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </button>
                        </template>
                    </div>

                    <!-- Master Data Table -->
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-bold text-slate-700 uppercase tracking-wider sticky left-0 bg-slate-50 z-10 border-r border-slate-100">Candidate Info</th>
                                        <template x-for="st in resultStations" :key="st.id">
                                            <th class="px-4 py-4 text-center font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100">St <span x-text="st.order_sequence"></span></th>
                                        </template>
                                        <th class="px-6 py-4 text-center font-black text-blue-700 uppercase tracking-wider bg-blue-50/50">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <tr x-show="isLoadingResults">
                                        <td :colspan="resultStations.length + 2" class="px-6 py-12 text-center text-slate-400 font-medium">
                                            <svg class="animate-spin h-6 w-6 text-blue-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            Compiling results...
                                        </td>
                                    </tr>
                                    <template x-for="student in studentResults" :key="student.student_id">
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-3 whitespace-nowrap sticky left-0 bg-white border-r border-slate-100 group-hover:bg-slate-50 transition-colors">
                                                <div class="font-bold text-slate-900 font-mono" x-text="student.matric"></div>
                                                <div class="text-xs text-slate-500" x-text="student.name"></div>
                                            </td>
                                            <template x-for="st in resultStations" :key="st.id">
                                                <td class="px-4 py-3 text-center whitespace-nowrap border-r border-slate-100">
                                                    <span x-show="student.scores[st.id] !== undefined" class="font-semibold text-slate-700" x-text="student.scores[st.id]"></span>
                                                    <span x-show="student.scores[st.id] === undefined" class="text-slate-300">-</span>
                                                </td>
                                            </template>
                                            <td class="px-6 py-3 text-center whitespace-nowrap bg-blue-50/30">
                                                <span class="font-black text-lg text-blue-700" x-text="student.total"></span>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="studentResults.length === 0 && !isLoadingResults">
                                        <td :colspan="resultStations.length + 2" class="px-6 py-12 text-center text-slate-500">No synchronized results found for this session yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

        function sessionControl() {
            return {
                activeTab: 'overview',
                sessionId: new URLSearchParams(window.location.search).get('id'),
                
                // Overview Data
                session: {},
                summary: { total_students: 0, total_stations: 0, assigned_lecturers: 0 },
                stations: [],
                
                // Results Data
                isLoadingResults: true,
                resultStations: [],
                studentResults: [],

                init() {
                    if(this.sessionId) {
                        this.fetchDetails();
                        // Auto-fetch results to have them ready
                        this.fetchResults();
                    } else { alert("No session ID provided."); }
                },

                getBaseApiUrl() {
                    let currentPath = window.location.pathname;
                    if (!TENANT_SLUG) return currentPath;
                    return currentPath.split(`/${TENANT_SLUG}`)[0] + `/${TENANT_SLUG}`;
                },

                async fetchDetails() {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/session-details?id=' + this.sessionId);
                        let data = await response.json();
                        if(data.success) {
                            this.session = data.payload.session;
                            this.summary = data.payload.summary;
                            this.stations = data.payload.stations;
                        }
                    } catch(e) { console.error("Failed to fetch session details", e); }
                },

                async fetchResults() {
                    this.isLoadingResults = true;
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/session-results?id=' + this.sessionId);
                        let data = await response.json();
                        if(data.success) {
                            this.resultStations = data.payload.stations;
                            this.studentResults = data.payload.results;
                        }
                    } catch(e) { console.error("Failed to fetch results", e); }
                    finally { this.isLoadingResults = false; }
                },

                // --- DYNAMIC CSV EXPORTERS ---
                triggerDownload(csvContent, fileName) {
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    const url = URL.createObjectURL(blob);
                    link.setAttribute('href', url);
                    link.setAttribute('download', fileName);
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                downloadMasterCSV() {
                    if(this.studentResults.length === 0) return alert("No results to download.");
                    
                    // 1. Build Headers
                    let headers = ['Matric Number', 'Student Name'];
                    this.resultStations.forEach(st => headers.push(`Station ${st.order_sequence}`));
                    headers.push('Total Score');
                    
                    let csvRows = [headers.join(',')];

                    // 2. Build Rows
                    this.studentResults.forEach(student => {
                        let row = [`"${student.matric}"`, `"${student.name}"`];
                        
                        this.resultStations.forEach(st => {
                            let score = student.scores[st.id] !== undefined ? student.scores[st.id] : '0';
                            row.push(score);
                        });
                        
                        row.push(student.total);
                        csvRows.push(row.join(','));
                    });

                    // 3. Download
                    let safeTitle = (this.session.title || 'Session').replace(/[^a-z0-9]/gi, '_').toLowerCase();
                    this.triggerDownload(csvRows.join('\n'), `${safeTitle}_master_results.csv`);
                },

                downloadStationCSV(station) {
                    if(this.studentResults.length === 0) return alert("No results to download.");

                    let headers = ['Matric Number', 'Student Name', `Station ${station.order_sequence} Score`];
                    let csvRows = [headers.join(',')];

                    this.studentResults.forEach(student => {
                        let score = student.scores[station.id] !== undefined ? student.scores[station.id] : '0';
                        csvRows.push([`"${student.matric}"`, `"${student.name}"`, score].join(','));
                    });

                    let safeTitle = (this.session.title || 'Session').replace(/[^a-z0-9]/gi, '_').toLowerCase();
                    this.triggerDownload(csvRows.join('\n'), `${safeTitle}_station_${station.order_sequence}_results.csv`);
                }
            }
        }
    </script>
</body>
</html>