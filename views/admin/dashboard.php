<?php 
$pageTitle = "Admin Control Center"; 
$activeMenu = "dashboard"; // This tells the sidebar which link to highlight
include '../views/layouts/header.php'; 
?>

<body class="bg-slate-50 font-sans antialiased h-screen flex overflow-hidden" x-data="dashboardController()" x-cloak>

    <!-- Inject the Premium Sidebar -->
    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Topbar -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sm:px-10 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Dashboard Overview</h1>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Welcome back, Admin. Here is today's summary.</p>
            </div>
            
            <div class="flex items-center gap-6">
                <!-- User Profile & Action -->
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-600 font-bold">
                        AD
                    </div>
                    <button @click="logout" class="text-sm font-bold text-red-600 hover:text-red-700 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-lg transition-colors">
                        Sign Out
                    </button>
                </div>
            </div>
        </header>

        <!-- Scrollable Dashboard Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50/50 p-8 sm:p-10">
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Card 1 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:scale-110 transition-transform duration-500 text-emerald-500">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Active Sessions</h3>
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <p class="text-4xl font-extrabold text-slate-900 mb-1">
                            <span x-show="isLoading" class="text-slate-200 animate-pulse">...</span>
                            <span x-show="!isLoading" x-text="stats.activeSessions"></span>
                        </p>
                        <p class="text-sm font-medium text-emerald-600">Currently running exams</p>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:scale-110 transition-transform duration-500 text-blue-500">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Bound Devices</h3>
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <p class="text-4xl font-extrabold text-slate-900 mb-1">
                            <span x-show="isLoading" class="text-slate-200 animate-pulse">...</span>
                            <span x-show="!isLoading" x-text="stats.boundDevices"></span>
                        </p>
                        <p class="text-sm font-medium text-blue-600">Locked for offline mode</p>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:scale-110 transition-transform duration-500 text-amber-500">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Pending Syncs</h3>
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <p class="text-4xl font-extrabold text-slate-900 mb-1" x-text="stats.pendingSyncs"></p>
                        <p class="text-sm font-medium text-amber-600">Awaiting local upload</p>
                    </div>
                </div>

                <!-- Card 4 -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:scale-110 transition-transform duration-500 text-purple-500">
                        <svg class="w-24 h-24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Students</h3>
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                    </div>
                    <div class="relative z-10">
                        <p class="text-4xl font-extrabold text-slate-900 mb-1">
                            <span x-show="isLoading" class="text-slate-200 animate-pulse">...</span>
                            <span x-show="!isLoading" x-text="stats.totalStudents"></span>
                        </p>
                        <p class="text-sm font-medium text-purple-600">Across all records</p>
                    </div>
                </div>

            </div>
            <!-- Active Sessions List -->
            <div class="mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-slate-800 tracking-tight">Currently Active Sessions</h2>
                    <a href="/<?php echo CURRENT_TENANT_SLUG; ?>/admin/sessions" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View All Archive &rarr;</a>
                </div>
                
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                            <tr>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Session Title</th>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Date</th>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Status</th>
                                <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- We will populate this via Alpine -->
                            <template x-for="session in activeSessionsList" :key="session.id">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-slate-800" x-text="session.title"></td>
                                    <td class="px-6 py-4 text-slate-600" x-text="session.scheduled_date"></td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <!-- THE DRILL-DOWN LINK -->
                                        <a :href="getBaseApiUrl() + '/admin/session-control?id=' + session.id" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg transition-colors">
                                            Control Room
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            </template>
                            
                            <!-- Empty State -->
                            <tr x-show="activeSessionsList.length === 0 && !isLoading">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">No active sessions found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Alpine.js Controller -->
    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

        function dashboardController() {
            return {
                isLoading: true,
                stats: {
                    activeSessions: 0,
                    boundDevices: 0,
                    pendingSyncs: 0,
                    totalStudents: 0
                },
                activeSessionsList: [],

                init() {
                    this.checkLocalSyncs();
                    this.fetchDashboardStats();
                    setInterval(() => { this.checkLocalSyncs(); }, 3000);
                },

                getBaseApiUrl() {
                    let currentPath = window.location.pathname;
                    if (!TENANT_SLUG) return currentPath;
                    return currentPath.split(`/${TENANT_SLUG}`)[0] + `/${TENANT_SLUG}`;
                },

                checkLocalSyncs() {
                    let queue = JSON.parse(localStorage.getItem('caosce_sync_queue') || '[]');
                    this.stats.pendingSyncs = queue.length;
                },

                async fetchDashboardStats() {
                    try {
                        let apiUrl = this.getBaseApiUrl() + '/api/admin/stats';
                        let response = await fetch(apiUrl);
                        let data = await response.json();
                        
                        if(data.success) {
                            this.stats.activeSessions = data.payload.activeSessions;
                            this.stats.boundDevices = data.payload.boundDevices;
                            this.stats.totalStudents = data.payload.totalStudents;
                            this.activeSessionsList = data.payload.activeSessionsList;
                        }
                        
                    } catch (error) {
                        console.error("Failed to load database stats", error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                logout() {
                    if(confirm("Securely sign out of the Admin panel?")) {
                        window.location.href = this.getBaseApiUrl() + '/login';
                    }
                }
            }
        }
    </script>

<?php include '../views/layouts/footer.php'; ?>