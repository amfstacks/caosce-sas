<?php 
$pageTitle = "Exam Sessions Management"; 
$activeMenu = "sessions"; 
include '../views/layouts/header.php'; 
?>

<body class="bg-slate-50 font-sans antialiased h-screen flex overflow-hidden" x-data="sessionIndexController()" x-cloak>

    <!-- Sidebar -->
    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Topbar -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sm:px-10 flex-shrink-0 z-10">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Exam Sessions</h1>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Manage and provision all objective clinical examinations.</p>
            </div>
            <button @click="openModal()" class="flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-500 hover:shadow-blue-500/40 transition-all active:scale-95">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Session
            </button>
        </header>

        <!-- Scrollable Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50/50 p-8 sm:p-10 z-0">
            
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Session Title</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Department</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        
                        <tr x-show="isLoadingInitial">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium">
                                <svg class="animate-spin h-6 w-6 text-blue-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Loading sessions...
                            </td>
                        </tr>

                        <template x-for="session in sessions" :key="session.id">
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="whitespace-nowrap px-6 py-5 text-sm font-bold text-slate-900" x-text="session.title"></td>
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600 font-medium" x-text="formatDate(session.scheduled_date)"></td>
                                <td class="whitespace-nowrap px-6 py-5 text-sm text-slate-600" x-text="session.department_name"></td>
                                <td class="whitespace-nowrap px-6 py-5 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wide border"
                                          :class="{
                                              'bg-emerald-50 text-emerald-700 border-emerald-200': session.status === 'active',
                                              'bg-amber-50 text-amber-700 border-amber-200': session.status === 'draft',
                                              'bg-slate-100 text-slate-600 border-slate-200': session.status === 'closed'
                                          }"
                                          x-text="session.status">
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-5 text-right text-sm font-medium">
                                    <button @click="openModal(session)" class="text-slate-400 hover:text-blue-600 transition-colors mr-4" title="Edit Parameters">
                                        <svg class="w-5 h-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    
                                    <a :href="getBaseApiUrl() + '/admin/session-control?id=' + session.id" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-900 hover:text-white text-xs font-bold rounded-lg transition-all mr-2">
                                        Summary
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                                    </a>

                                    <a :href="getBaseApiUrl() + '/admin/sessions/manage?id=' + session.id" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white text-xs font-bold rounded-lg transition-all">
                                        Manage
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="sessions.length === 0 && !isLoadingInitial">
                            <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <p class="text-base font-bold text-slate-700 mb-1">No Exam Sessions Found</p>
                                <p class="text-sm">Click "New Session" to provision your first examination.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Slide-over Modal -->
        <div x-show="isModalOpen" class="relative z-50" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            
            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div x-show="isModalOpen" 
                             x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500" 
                             x-transition:enter-start="translate-x-full" 
                             x-transition:enter-end="translate-x-0" 
                             x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500" 
                             x-transition:leave-start="translate-x-0" 
                             x-transition:leave-end="translate-x-full" 
                             class="pointer-events-auto w-screen max-w-md">
                            
                            <form @submit.prevent="saveSession" class="flex h-full flex-col bg-white shadow-2xl border-l border-slate-200">
                                <div class="flex items-center justify-between px-6 py-6 border-b border-slate-100 bg-slate-50/50">
                                    <h2 class="text-xl font-bold text-slate-800" x-text="isEditing ? 'Edit Session' : 'New Session'"></h2>
                                    <button type="button" @click="closeModal()" class="rounded-full p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                                
                                <div class="flex-1 overflow-y-auto px-6 py-8">
                                    <div class="space-y-6">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Session Title</label>
                                            <input type="text" x-model="form.title" required placeholder="e.g., Year 3 OSCE Final" class="block w-full rounded-xl border-slate-300 bg-slate-50 py-3 px-4 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Scheduled Date</label>
                                            <input type="date" x-model="form.scheduled_date" required class="block w-full rounded-xl border-slate-300 bg-slate-50 py-3 px-4 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Department</label>
                                            <select x-model="form.department_id" required class="block w-full rounded-xl border-slate-300 bg-slate-50 py-3 px-4 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors">
                                                <option value="" disabled>Select Department...</option>
                                                <template x-for="dept in departments" :key="dept.id">
                                                    <option :value="dept.id" x-text="dept.name + ' (' + dept.dept_code.toUpperCase() + ')'"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <!-- STATUS DROPDOWN (Only visible when editing an existing session) -->
                                        <div x-show="isEditing">
                                            <label class="block text-sm font-bold text-slate-700 mb-2">Session Status</label>
                                            <select x-model="form.status" required class="block w-full rounded-xl border-slate-300 bg-slate-50 py-3 px-4 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 focus:bg-white transition-colors">
                                                <option value="draft">Draft (Setup Phase)</option>
                                                <option value="active">Active (Exam in Progress)</option>
                                                <option value="closed">Closed (Completed)</option>
                                            </select>
                                        </div>

                                    </div>
                                </div>
                                
                                <div class="border-t border-slate-100 px-6 py-5 bg-slate-50 flex justify-end gap-3">
                                    <button type="button" @click="closeModal()" class="rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors">Cancel</button>
                                    <button type="submit" class="inline-flex justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-500 transition-colors" :disabled="isLoading">
                                        <span x-text="isLoading ? 'Saving...' : 'Save Configuration'"></span>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sessionIndexController() {
            return {
                sessions: [],
                departments: [],
                isModalOpen: false,
                isEditing: false,
                isLoading: false,
                isLoadingInitial: true,
                form: { id: null, title: '', scheduled_date: '', department_id: '', status: 'draft' },

                init() {
                    this.fetchDepartments();
                    this.fetchSessions();
                },

                getBaseApiUrl() {
                    const tenantSlug = '<?php echo CURRENT_TENANT_SLUG ?? ""; ?>';
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return tenantSlug ? `${basePath}/${tenantSlug}` : basePath;
                },

                formatDate(dateString) {
                    if(!dateString) return '';
                    return new Date(dateString).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                },

                async fetchDepartments() {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/departments');
                        let data = await response.json();
                        if (data.success) { this.departments = data.payload; }
                    } catch (e) { console.error('Failed to load departments'); }
                },

                async fetchSessions() {
                    this.isLoadingInitial = true;
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/sessions');
                        let data = await response.json();
                        if (data.success) { this.sessions = data.payload; }
                    } catch (e) { 
                        console.error('Failed to load sessions'); 
                    } finally {
                        this.isLoadingInitial = false;
                    }
                },

                openModal(session = null) {
                    if (session) {
                        this.isEditing = true;
                        this.form = { ...session };
                    } else {
                        this.isEditing = false;
                        this.form = { id: null, title: '', scheduled_date: '', department_id: '', status: 'draft' };
                    }
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                async saveSession() {
                    this.isLoading = true;
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/sessions/save', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.form)
                        });
                        
                        let data = await response.json();
                        if (data.success) {
                            await this.fetchSessions();
                            this.closeModal();
                        } else {
                            alert(data.message || 'Failed to save session');
                        }
                    } catch (error) {
                        alert('Network error while saving.');
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>

<?php include '../views/layouts/footer.php'; ?>