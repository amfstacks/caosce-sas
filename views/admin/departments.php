<?php 
$pageTitle = "Departments Management"; 
$activeMenu = "departments"; // Change to your actual sidebar active key
include '../views/layouts/header.php'; 
?>

<body class="bg-slate-50 font-sans antialiased h-screen flex overflow-hidden" x-data="departmentController()" x-cloak>

    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Topbar -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sm:px-10 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Departments</h1>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Provision academic departments for examination mapping.</p>
            </div>
            
            <button @click="openAddModal" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Department
            </button>
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

            <!-- Departments Table -->
            <div x-show="!isLoading" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Department Name</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Access Code</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Date Added</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Status</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="dept in departments" :key="dept.id">
                            <tr class="hover:bg-slate-50 transition-colors" :class="dept.status === 'disabled' ? 'opacity-50 bg-slate-50' : ''">
                                <td class="px-6 py-4 font-bold text-slate-800" x-text="dept.name"></td>
                                <td class="px-6 py-4 text-slate-600">
                                    <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded font-mono text-xs font-bold tracking-wider" x-text="dept.dept_code.toUpperCase()"></span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 font-medium" x-text="dept.formatted_date"></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold" 
                                          :class="dept.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'" 
                                          x-text="dept.status.toUpperCase()">
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="toggleStatus(dept.id)" class="px-4 py-2 rounded-lg text-xs font-bold transition-colors shadow-sm" :class="dept.status === 'active' ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'" x-text="dept.status === 'active' ? 'Disable Access' : 'Enable Access'"></button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="departments.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                No departments provisioned yet.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- ADD MODAL -->
        <div x-show="modals.form" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.outside="modals.form = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200">
                <div class="bg-slate-50 border-b border-slate-100 p-6">
                    <h3 class="text-xl font-bold text-slate-800">Add New Department</h3>
                    <p class="text-xs text-amber-600 mt-1 font-medium bg-amber-50 inline-block px-2 py-0.5 rounded">Note: Departments cannot be edited or deleted once created.</p>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Department Name</label>
                        <input type="text" x-model="formData.name" required placeholder="e.g. Nursing Science" class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">System Access Code</label>
                        <select x-model="formData.dept_code" required class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm text-sm bg-white">
                            <option value="" disabled>Select Department Code...</option>
                            <option value="ns">Nursing Science (NS)</option>
                            <option value="mw">Midwifery (MW)</option>
                        </select>
                    </div>
                    <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="modals.form = false" class="px-5 py-2.5 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-100 transition-colors">Cancel</button>
                        <button type="submit" :disabled="isSaving" class="px-6 py-2.5 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 transition-colors shadow-sm inline-flex items-center gap-2">
                            <svg x-show="isSaving" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isSaving ? 'Provisioning...' : 'Provision Department'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

        function departmentController() {
            return {
                isLoading: true,
                isSaving: false,
                departments: [],
                toast: { visible: false, message: '', type: 'success' },
                
                modals: { form: false },
                formData: { name: '', dept_code: '' },

                init() {
                    this.fetchData();
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

                async fetchData() {
                    this.isLoading = true;
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/departments/list');
                        let data = await response.json();
                        if (data.success) this.departments = data.payload;
                    } catch (e) {
                        this.showToast('Failed to load data', 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                openAddModal() {
                    this.formData = { name: '', dept_code: '' };
                    this.modals.form = true;
                },

                async submitForm() {
                    this.isSaving = true;
                    
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/departments/add', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.formData)
                        });
                        let data = await response.json();
                        
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.modals.form = false;
                            this.fetchData();
                        } else {
                            this.showToast(data.message, 'error');
                        }
                    } catch (e) {
                        this.showToast('Network error', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                },

                async toggleStatus(id) {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/departments/toggle', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id })
                        });
                        if ((await response.json()).success) this.fetchData();
                    } catch (e) {}
                }
            }
        }
    </script>
</body>
</html>