<?php 
$pageTitle = "Sync Codes Management"; 
$activeMenu = "sync-codes"; 
include '../views/layouts/header.php'; 
?>

<body class="bg-slate-50 font-sans antialiased h-screen flex overflow-hidden" x-data="syncCodeController()" x-cloak>

    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Topbar -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sm:px-10 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Access Sync Codes</h1>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Generate 6-digit PINs for station data retrieval.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button @click="generateCode" :disabled="isGenerating" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-all disabled:opacity-70">
                    <svg x-show="isGenerating" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <svg x-show="!isGenerating" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span x-text="isGenerating ? 'Generating...' : 'Generate New Code'"></span>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50/50 p-8 sm:p-10">
            
            <!-- Global Toast -->
            <div x-show="toast.visible" x-transition class="mb-6 rounded-xl p-4 flex items-center shadow-sm" :class="toast.type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700'" style="display: none;">
                <span class="text-sm font-bold" x-text="toast.message"></span>
            </div>

            <!-- Loader -->
            <div x-show="isLoading" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>

            <!-- Codes Table -->
            <div x-show="!isLoading" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Sync Code</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Status</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Created On</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="item in codes" :key="item.id">
                            <tr class="hover:bg-slate-50 transition-colors" :class="item.status === 'disabled' ? 'opacity-60 bg-slate-50' : ''">
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-lg bg-slate-100 border border-slate-200 font-mono text-lg font-black tracking-[0.2em] text-slate-800" 
                                          :class="item.status === 'disabled' ? 'line-through text-slate-400' : ''"
                                          x-text="item.code"></span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold" 
                                          :class="item.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'" 
                                          x-text="item.status.toUpperCase()">
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 font-medium text-xs" x-text="item.formatted_date"></td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2 items-center h-full mt-2">
                                    <button @click="toggleStatus(item.id)" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" :class="item.status === 'active' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'" x-text="item.status === 'active' ? 'Disable' : 'Enable'"></button>
                                    <button @click="deleteCode(item.id)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 transition-colors">Delete</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="codes.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                No sync codes generated yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

        function syncCodeController() {
            return {
                isLoading: true,
                isGenerating: false,
                codes: [],
                toast: { visible: false, message: '', type: 'success' },

                init() {
                    this.fetchCodes();
                },

                getBaseApiUrl() {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return TENANT_SLUG ? `${basePath}/${TENANT_SLUG}` : basePath;
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

                async fetchCodes() {
                    this.isLoading = true;
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/sync-codes/list');
                        let data = await response.json();
                        if (data.success) this.codes = data.payload;
                    } catch (e) {
                        this.showToast('Failed to load codes', 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                async generateCode() {
                    this.isGenerating = true;
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/sync-codes/create', { method: 'POST' });
                        let data = await response.json();
                        
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.fetchCodes();
                        } else {
                            this.showToast(data.message, 'error');
                        }
                    } catch (e) {
                        this.showToast('Network error', 'error');
                    } finally {
                        this.isGenerating = false;
                    }
                },

                async toggleStatus(id) {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/sync-codes/toggle', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id })
                        });
                        if ((await response.json()).success) this.fetchCodes();
                    } catch (e) {}
                },

                async deleteCode(id) {
                    if (!confirm('Are you sure you want to permanently delete this sync code?')) return;
                    
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/sync-codes/delete', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id })
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.fetchCodes();
                        }
                    } catch (e) {}
                }
            }
        }
    </script>
</body>
</html>