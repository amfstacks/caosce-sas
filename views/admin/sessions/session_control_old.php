<?php 
$pageTitle = "Session Control Room"; 
$activeMenu = "sessions"; 
include '../views/layouts/header.php'; 
?>

<body class="bg-slate-50 font-sans antialiased h-screen flex overflow-hidden" x-data="sessionControl()" x-cloak>

    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Topbar -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sm:px-10 flex-shrink-0">
            <div class="flex items-center gap-4">
                <a :href="getBaseApiUrl() + '/admin/dashboard'" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors">
                    <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Session Control Room</h1>
                    <p class="text-sm text-slate-500 font-medium mt-0.5" x-text="session.title || 'Loading...'"></p>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50/50 p-8 sm:p-10">
            
            <!-- Glance Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Enrolled Candidates</p>
                        <p class="text-3xl font-extrabold text-slate-900 mt-1" x-text="summary.total_students"></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Active Stations</p>
                        <p class="text-3xl font-extrabold text-slate-900 mt-1" x-text="summary.total_stations"></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Assigned Lecturers</p>
                        <p class="text-3xl font-extrabold text-slate-900 mt-1">
                            <span x-text="summary.assigned_lecturers"></span> <span class="text-lg text-slate-400">/</span> <span class="text-lg text-slate-400" x-text="summary.total_stations"></span>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Station Configuration Setup -->
            <h2 class="text-lg font-bold text-slate-800 mb-4">Station Configuration Setup</h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <template x-for="station in stations" :key="station.id">
                    <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex items-start gap-4">
                        <div class="w-10 h-10 rounded-lg font-black text-lg flex items-center justify-center flex-shrink-0" :class="station.station_type === 'cbt' ? 'bg-blue-100 text-blue-700' : 'bg-rose-100 text-rose-700'" x-text="station.order_sequence"></div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1 block" x-text="station.station_type + ' MODULE'"></span>
                                    <h3 class="text-lg font-bold text-slate-900 leading-tight" x-text="station.title"></h3>
                                </div>
                                <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-1 rounded" x-text="station.time_limit_minutes + ' min'"></span>
                            </div>
                            
                            <!-- Setup Confirmation Status -->
                            <div class="mt-4 flex items-center gap-2" :class="station.examiner_name ? 'text-emerald-600' : 'text-amber-500'">
                                <svg x-show="station.examiner_name" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg x-show="!station.examiner_name" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                <span class="text-sm font-semibold" x-text="station.examiner_name ? 'Assigned: ' + station.examiner_name : 'No examiner assigned'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </main>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

        function sessionControl() {
            return {
                sessionId: new URLSearchParams(window.location.search).get('id'),
                session: {},
                summary: { total_students: 0, total_stations: 0, assigned_lecturers: 0 },
                stations: [],

                init() {
                    if(this.sessionId) {
                        this.fetchDetails();
                    } else {
                        alert("No session ID provided.");
                    }
                },

                getBaseApiUrl() {
                    let currentPath = window.location.pathname;
                    if (!TENANT_SLUG) return currentPath;
                    return currentPath.split(`/${TENANT_SLUG}`)[0] + `/${TENANT_SLUG}`;
                },

                async fetchDetails() {
                    try {
                        let apiUrl = this.getBaseApiUrl() + '/api/admin/session-details?id=' + this.sessionId;
                        let response = await fetch(apiUrl);
                        let data = await response.json();
                        
                        if(data.success) {
                            this.session = data.payload.session;
                            this.summary = data.payload.summary;
                            this.stations = data.payload.stations;
                        }
                    } catch(e) {
                        console.error("Failed to fetch session details", e);
                    }
                }
            }
        }
    </script>

<?php include '../views/layouts/footer.php'; ?>