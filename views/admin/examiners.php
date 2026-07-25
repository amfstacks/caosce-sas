<?php 
$pageTitle = "Examiners Management"; 
$activeMenu = "examiners"; // Adjust active menu logic as needed
include '../views/layouts/header.php'; 
?>

<body class="bg-slate-50 font-sans antialiased h-screen flex overflow-hidden" x-data="examinerController()" x-cloak>

    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- Topbar -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sm:px-10 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Examiners</h1>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Manage access for clinical supervisors and invigilators.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <button @click="modals.import = true" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Bulk Import
                </button>
                <button @click="openAddModal" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Add Examiner
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

            <!-- Examiners Table -->
            <div x-show="!isLoading" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden" style="display: none;">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Full Name</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Username</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Raw PIN/Pass</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs">Status</th>
                            <th class="px-6 py-4 font-bold uppercase tracking-wider text-xs text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="user in examiners" :key="user.id">
                            <tr class="hover:bg-slate-50 transition-colors" :class="user.status === 'disabled' ? 'opacity-50' : ''">
                                <td class="px-6 py-4 font-bold text-slate-800" x-text="user.full_name"></td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-xs" x-text="user.username"></td>
                                <td class="px-6 py-4 text-slate-600 font-mono text-xs" x-text="user.raw_password"></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold" 
                                          :class="user.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'" 
                                          x-text="user.status.toUpperCase()">
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <button @click="toggleStatus(user.id)" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors" :class="user.status === 'active' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200'" x-text="user.status === 'active' ? 'Disable' : 'Enable'"></button>
                                    <button @click="openEditModal(user)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Edit</button>
                                    <button @click="deleteUser(user.id)" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 transition-colors">Delete</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="examiners.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No examiners found. Add one to get started.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- ADD/EDIT MODAL -->
        <div x-show="modals.form" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.outside="modals.form = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200">
                <div class="bg-slate-50 border-b border-slate-100 p-6">
                    <h3 class="text-xl font-bold text-slate-800" x-text="isEditing ? 'Edit Examiner' : 'Add New Examiner'"></h3>
                </div>
                <form @submit.prevent="submitForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Full Name</label>
                        <input type="text" x-model="formData.full_name" required class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Username</label>
                        <input type="text" x-model="formData.username" required class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">
                            Password 
                            <span x-show="isEditing" class="text-xs text-amber-600 font-normal ml-2">(Leave blank to keep current)</span>
                        </label>
                        <input type="text" x-model="formData.password" :required="!isEditing" class="w-full rounded-lg border border-slate-300 px-4 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-mono text-sm">
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="modals.form = false" class="px-5 py-2.5 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-100 transition-colors">Cancel</button>
                        <button type="submit" :disabled="isSaving" class="px-5 py-2.5 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 transition-colors inline-flex items-center gap-2">
                            <span x-text="isSaving ? 'Saving...' : 'Save Record'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- BULK IMPORT MODAL -->
        <div x-show="modals.import" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" style="display: none;">
            <div @click.outside="modals.import = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-200">
                <div class="bg-slate-50 border-b border-slate-100 p-6">
                    <h3 class="text-xl font-bold text-slate-800">Bulk Import Examiners</h3>

                    <p class="text-xs text-slate-500 mt-1">Upload a CSV file with columns: <code class="bg-slate-200 px-1 rounded">full_name</code>, <code class="bg-slate-200 px-1 rounded">username</code>, <code class="bg-slate-200 px-1 rounded">password</code>.</p>
                    <a href="data:text/csv;charset=utf-8,full_name,username,password%0AJohn%20Doe,johndoe1,Pass123%0AJane%20Smith,janesmith2,Pass456" 
                       download="examiners_template.csv" 
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Sample Template
                    </a>
                </div>
                <form @submit.prevent="submitImport" class="p-6 space-y-4">
                    <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center bg-slate-50">
                        <input type="file" x-ref="csvFile" required accept=".csv" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" @click="modals.import = false" class="px-5 py-2.5 rounded-lg text-sm font-bold text-slate-600 hover:bg-slate-100 transition-colors">Cancel</button>
                        <button type="submit" :disabled="isSaving" class="px-5 py-2.5 rounded-lg text-sm font-bold text-white bg-slate-800 hover:bg-slate-900 disabled:opacity-50 transition-colors">
                            <span x-text="isSaving ? 'Uploading...' : 'Import CSV'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

        function examinerController() {
            return {
                isLoading: true,
                isSaving: false,
                examiners: [],
                toast: { visible: false, message: '', type: 'success' },
                
                modals: { form: false, import: false },
                isEditing: false,
                formData: { id: '', full_name: '', username: '', password: '' },

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
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/examiners/list');
                        let data = await response.json();
                        if (data.success) this.examiners = data.payload;
                    } catch (e) {
                        this.showToast('Failed to load data', 'error');
                    } finally {
                        this.isLoading = false;
                    }
                },

                openAddModal() {
                    this.isEditing = false;
                    this.formData = { id: '', full_name: '', username: '', password: '' };
                    this.modals.form = true;
                },

                openEditModal(user) {
                    this.isEditing = true;
                    this.formData = { id: user.id, full_name: user.full_name, username: user.username, password: '' };
                    this.modals.form = true;
                },

                async submitForm() {
                    this.isSaving = true;
                    let endpoint = this.isEditing ? '/api/admin/examiners/update' : '/api/admin/examiners/add';
                    
                    try {
                        let response = await fetch(this.getBaseApiUrl() + endpoint, {
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

                async submitImport() {
                    const file = this.$refs.csvFile.files[0];
                    if (!file) return;

                    this.isSaving = true;
                    let fd = new FormData();
                    fd.append('csv_file', file);

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/examiners/import', {
                            method: 'POST',
                            body: fd
                        });
                        let data = await response.json();
                        
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.modals.import = false;
                            this.$refs.csvFile.value = '';
                            this.fetchData();
                        } else {
                            this.showToast(data.message, 'error');
                        }
                    } catch (e) {
                        this.showToast('Upload failed', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                },

                async toggleStatus(id) {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/examiners/toggle', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id })
                        });
                        if ((await response.json()).success) this.fetchData();
                    } catch (e) {}
                },

                async deleteUser(id) {
                    if (!confirm('Are you sure? This will move the examiner to the deleted archive.')) return;
                    
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/examiners/delete', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id })
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.fetchData();
                        }
                    } catch (e) {}
                }
            }
        }
    </script>
</body>
</html>