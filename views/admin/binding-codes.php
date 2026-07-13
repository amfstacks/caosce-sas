<?php 
$activeMenu = 'hardware'; // Ensure this highlights correctly in your sidebar
$pageTitle = 'Hardware Provisioning PINs';
// include '../views/layouts/admin_sidebar.php'; 
include '../views/layouts/header.php'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased flex h-screen overflow-hidden" x-data="bindingCodeController()" x-cloak>

    <!-- Sidebar Inclusion -->
    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Professional Header -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-6 sm:px-10 flex-shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-4 sm:gap-6">
                <button @click="goBack()" type="button" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
                    <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </button>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Hardware Provisioning</h1>
                    <p class="text-sm text-slate-500 font-medium mt-0.5">Manage laptop binding PINs scoped to specific exam stations.</p>
                </div>
            </div>
        </header>

        <!-- Scrollable Page Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 p-6 sm:p-10">
            <div class="max-w-7xl mx-auto space-y-8">

                <!-- Generator Section -->
                <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-slate-200">
                    <h2 class="text-base font-bold leading-6 text-slate-900 mb-2">Create New Scoped PIN</h2>
                    <p class="text-sm text-slate-500 mb-6">Generated PINs lock a physical device to a specific exam session and station.</p>
                    
                    <form @submit.prevent="generateNewCode" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Session Select -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Target Session</label>
                                <select x-model="selectedSession" required class="block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border bg-slate-50" @change="selectedStation = ''">
                                    <option value="">-- Select Session --</option>
                                    <template x-for="session in sessions" :key="session.id">
                                        <option :value="session.id" x-text="session.title"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Station Select (Filtered by Session) -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Target Station</label>
                                <select x-model="selectedStation" required class="block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border bg-slate-50" :disabled="!selectedSession">
                                    <option value="">-- Select Station --</option>
                                    <template x-for="station in availableStations" :key="station.id">
                                        <option :value="station.id" x-text="'Station ' + station.sequence + ' - ' + station.title"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Label -->
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1">Assignment Label</label>
                                <input type="text" x-model="newLabel" placeholder="e.g., Room 4 Setup" class="block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border bg-slate-50">
                            </div>
                        </div>
                        
                        <div class="pt-2">
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-500 transition-colors" :disabled="isGenerating || !selectedSession || !selectedStation">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span x-text="isGenerating ? 'Generating...' : 'Generate 6-Char PIN'"></span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Codes Table -->
                <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                        <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Active & Inactive PINs (<span x-text="codes.length"></span>)</h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Binding PIN</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Target Scope</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Label</th>
                                    <th scope="col" class="px-3 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <template x-for="item in codes" :key="item.id">
                                    <tr class="hover:bg-slate-50/80 transition-colors" :class="!item.active ? 'opacity-60 bg-slate-50/50' : ''">
                                        
                                        <!-- PIN -->
                                        <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                            <div class="flex items-center gap-3">
                                                <span class="font-mono text-lg font-bold tracking-wider px-3 py-1 rounded-md border shadow-sm"
                                                      :class="item.active ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-500 border-slate-200 line-through'"
                                                      x-text="item.code"></span>
                                                <button @click="copyCode(item.code)" class="text-slate-400 hover:text-blue-600 transition-colors" title="Copy PIN">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Scope (Session/Station) -->
                                        <td class="whitespace-nowrap px-3 py-4">
                                            <div class="text-sm font-bold text-slate-800 truncate max-w-[200px]" x-text="item.session_title"></div>
                                            <div class="text-xs text-slate-500 mt-0.5">
                                                Station <span x-text="item.station_sequence"></span>: <span x-text="item.station_title"></span>
                                            </div>
                                        </td>

                                        <!-- Label -->
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-600">
                                            <span x-text="item.label"></span>
                                        </td>

                                        <!-- Status Toggle -->
                                        <td class="whitespace-nowrap px-3 py-4 text-center">
                                            <button type="button" @click="toggleStatus(item)"
                                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-inner"
                                                    :class="item.active ? 'bg-green-500' : 'bg-slate-300'">
                                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                      :class="item.active ? 'translate-x-5' : 'translate-x-0'"></span>
                                            </button>
                                            <div class="text-[10px] font-bold mt-1 tracking-wider uppercase" :class="item.active ? 'text-green-600' : 'text-slate-400'" x-text="item.active ? 'Active' : 'Revoked'"></div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                            <button @click="deleteCode(item.id)" class="text-red-500 hover:text-red-700 font-bold p-1.5 rounded hover:bg-red-50 transition-colors">
                                                Delete
                                            </button>
                                        </td>

                                    </tr>
                                </template>
                                
                                <tr x-show="codes.length === 0">
                                    <td colspan="5" class="py-12 text-center text-sm text-slate-500">
                                        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                        No provisioning PINs created yet. Generate one above to begin hardware setup.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Toast Notification -->
        <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
            <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
                <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl bg-slate-800 shadow-xl ring-1 ring-black ring-opacity-5" style="display: none;">
                    <div class="p-4 flex items-start">
                        <svg class="h-6 w-6 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="ml-3 text-sm font-bold text-white pt-0.5" x-text="toast.message"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function bindingCodeController() {
            return {
                sessions: [],
                stations: [],
                codes: [],
                
                selectedSession: '',
                selectedStation: '',
                newLabel: '',
                
                isGenerating: false,
                toast: { visible: false, message: '' },
                
                init() {
                    this.fetchData();
                },

                getBaseApiUrl() {
                    const tenantSlug = '<?php echo CURRENT_TENANT_SLUG ?? ""; ?>';
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return tenantSlug ? `${basePath}/${tenantSlug}` : basePath;
                },

                goBack() {
                    if (document.referrer && document.referrer.includes(window.location.host)) {
                        window.history.back();
                    } else {
                        window.location.href = this.getBaseApiUrl() + '/admin/dashboard';
                    }
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

                // Computed property to filter stations based on selected session
                get availableStations() {
                    if (!this.selectedSession) return [];
                    return this.stations.filter(st => st.session_id === this.selectedSession);
                },

                async fetchData() {
                    try {
                        let res = await fetch(this.getBaseApiUrl() + '/api/admin/hardware/data');
                        let data = await res.json();
                        if(data.success) {
                            this.sessions = data.payload.sessions;
                            this.stations = data.payload.stations;
                            // Ensure booleans are typed correctly for Alpine toggle
                            this.codes = data.payload.codes.map(c => ({...c, active: !!c.active}));
                        }
                    } catch(e) { console.error("Error fetching hardware data"); }
                },

                generateRandomPIN() {
                    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; 
                    let result = '';
                    for (let i = 0; i < 6; i++) { result += chars.charAt(Math.floor(Math.random() * chars.length)); }
                    return `${result.slice(0, 2)}-${result.slice(2, 4)}-${result.slice(4, 6)}`;
                },

                async generateNewCode() {
                    if(!this.selectedSession || !this.selectedStation) return;
                    this.isGenerating = true;
                    
                    const payload = {
                        session_id: this.selectedSession,
                        station_id: this.selectedStation,
                        label: this.newLabel,
                        pin_code: this.generateRandomPIN()
                    };

                    try {
                        let res = await fetch(this.getBaseApiUrl() + '/api/admin/hardware/generate', {
                            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
                        });
                        let data = await res.json();
                        if(data.success) {
                            this.newLabel = '';
                            this.selectedSession = '';
                            this.selectedStation = '';
                            this.showToast(`PIN generated successfully!`);
                            await this.fetchData(); // Refresh list to get joined titles
                        } else {
                            alert(data.message || 'Failed to generate PIN');
                        }
                    } catch(e) { alert('Network Error'); } finally {
                        this.isGenerating = false;
                    }
                },

                async toggleStatus(item) {
                    item.active = !item.active;
                    try {
                        await fetch(this.getBaseApiUrl() + '/api/admin/hardware/toggle', {
                            method: 'POST', headers: { 'Content-Type': 'application/json' }, 
                            body: JSON.stringify({ id: item.id, active: item.active })
                        });
                        this.showToast(`PIN ${item.code} is now ${item.active ? 'Activated' : 'Revoked'}.`);
                    } catch(e) { console.error('Error toggling status'); }
                },

                async deleteCode(id) {
                    if (confirm(`Are you sure you want to permanently delete this PIN?`)) {
                        try {
                            let res = await fetch(this.getBaseApiUrl() + '/api/admin/hardware/delete', {
                                method: 'POST', headers: { 'Content-Type': 'application/json' }, 
                                body: JSON.stringify({ id: id })
                            });
                            let data = await res.json();
                            if(data.success) {
                                this.codes = this.codes.filter(c => c.id !== id);
                                this.showToast('Provisioning PIN deleted.');
                            }
                        } catch(e) { console.error('Error deleting PIN'); }
                    }
                },

                copyCode(codeString) {
                    navigator.clipboard.writeText(codeString).then(() => {
                        this.showToast(`PIN ${codeString} copied to clipboard!`);
                    }).catch(err => { console.error('Failed to copy', err); });
                }
            }
        }
    </script>
</body>
</html>