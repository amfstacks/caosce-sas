<?php 
$pageTitle = "Workspace Settings"; 
$activeMenu = "settings"; 
include '../views/layouts/header.php'; 
?>

<body class="bg-slate-50 font-sans antialiased h-screen flex overflow-hidden" x-data="settingsController()" x-cloak>

    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Topbar -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-8 sm:px-10 flex-shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Workspace Settings</h1>
                <p class="text-sm text-slate-500 font-medium mt-0.5">Manage your institution's branding and details.</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-600 font-bold uppercase">
                    <?php 
                    $nameParts = explode(' ', $_SESSION['admin_name'] ?? 'Admin User');
                    echo substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''); 
                    ?>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50/50 p-8 sm:p-10">
            
            <!-- Global Toast Notification -->
            <div x-show="toast.visible" x-transition class="mb-6 rounded-xl p-4 flex items-center shadow-sm" :class="toast.type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-green-50 border border-green-200 text-green-700'" style="display: none;">
                <svg x-show="toast.type === 'success'" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <svg x-show="toast.type === 'error'" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span class="text-sm font-bold" x-text="toast.message"></span>
            </div>

            <!-- Loader -->
            <div x-show="isLoadingData" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>

            <div x-show="!isLoadingData" class="max-w-4xl mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" style="display: none;">
                
                <form @submit.prevent="saveSettings" class="divide-y divide-slate-100">
                    
                    <!-- Cover Image Section -->
                    <div class="p-8">
                        <label class="block text-sm font-bold text-slate-700 mb-4">Portal Cover Image</label>
                        <div class="relative w-full h-48 rounded-xl border-2 border-dashed border-slate-300 overflow-hidden group bg-slate-50 flex items-center justify-center">
                            
                            <!-- Preview Image -->
                            <img x-show="coverPreviewUrl" :src="coverPreviewUrl" class="absolute inset-0 w-full h-full object-cover">
                            
                            <!-- Upload Overlay -->
                            <div class="absolute inset-0 bg-slate-900/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer" :class="!coverPreviewUrl ? 'opacity-100 bg-transparent' : ''" @click="$refs.coverInput.click()">
                                <div class="bg-white text-slate-700 p-2 rounded-full shadow-lg mb-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <span class="text-sm font-bold text-white shadow-sm" :class="!coverPreviewUrl ? 'text-slate-500 shadow-none' : ''">Click to upload cover</span>
                            </div>
                            <input type="file" x-ref="coverInput" @change="previewImage($event, 'cover')" class="hidden" accept="image/jpeg, image/png, image/webp">
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8">
                        
                        <!-- Logo Upload -->
                        <div class="col-span-1 flex flex-col items-center justify-start">
                            <label class="block text-sm font-bold text-slate-700 mb-4 w-full text-center">Institution Logo</label>
                            <div class="relative w-32 h-32 rounded-2xl border-2 border-dashed border-slate-300 overflow-hidden group bg-slate-50 flex items-center justify-center cursor-pointer hover:border-blue-400 transition-colors" @click="$refs.logoInput.click()">
                                <img x-show="logoPreviewUrl" :src="logoPreviewUrl" class="absolute inset-0 w-full h-full object-contain p-2 bg-white">
                                
                                <div class="absolute inset-0 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900/40" :class="!logoPreviewUrl ? 'opacity-100 bg-transparent' : ''">
                                    <svg class="w-8 h-8" :class="!logoPreviewUrl ? 'text-slate-400' : 'text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                </div>
                            </div>
                            <input type="file" x-ref="logoInput" @change="previewImage($event, 'logo')" class="hidden" accept="image/jpeg, image/png, image/webp">
                            <p class="text-xs text-slate-400 mt-3 text-center">Recommended: 400x400px (PNG)</p>
                        </div>

                        <!-- Text Fields -->
                        <div class="col-span-1 md:col-span-2 space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Institution Name</label>
                                <input type="text" x-model="formData.name" required
                                       class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2 flex justify-between">
                                    Workspace Slug 
                                    <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">Fixed Identifier</span>
                                </label>
                                <input type="text" x-model="formData.slug" disabled
                                       class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-slate-500 font-mono font-bold cursor-not-allowed">
                                <p class="text-xs text-slate-500 mt-2">This is the unique URL code for your school (e.g., casoce.com/<strong><span x-text="formData.slug"></span></strong>). It cannot be changed.</p>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Actions -->
                    <div class="bg-slate-50 p-6 flex justify-end gap-4 rounded-b-2xl">
                        <button type="submit" :disabled="isSaving"
                                class="inline-flex justify-center items-center rounded-xl bg-blue-600 px-8 py-3.5 text-sm font-bold text-white shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition-all touch-btn">
                            <svg x-show="isSaving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isSaving ? 'Saving Changes...' : 'Save Workspace Settings'"></span>
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

        function settingsController() {
            return {
                isLoadingData: true,
                isSaving: false,
                toast: { visible: false, message: '', type: 'success' },
                
                formData: { name: '', slug: '' },
                logoFile: null,
                coverFile: null,
                
                logoPreviewUrl: null,
                coverPreviewUrl: null,

                init() {
                    this.fetchSettings();
                },

                getBaseApiUrl() {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return TENANT_SLUG ? `${basePath}/${TENANT_SLUG}` : basePath;
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 4000);
                },

                async fetchSettings() {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/school/details');
                        let data = await response.json();
                        
                        if (data.success) {
                            this.formData.name = data.payload.name;
                            this.formData.slug = data.payload.slug;
                            this.logoPreviewUrl = data.payload.logo_url || null;
                            this.coverPreviewUrl = data.payload.cover_url || null;
                        }
                    } catch (error) {
                        this.showToast('Failed to load settings.', 'error');
                    } finally {
                        this.isLoadingData = false;
                    }
                },

                previewImage(event, type) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Validate size (e.g., max 2MB)
                    if (file.size > 2 * 1024 * 1024) {
                        this.showToast('Image must be less than 2MB', 'error');
                        event.target.value = '';
                        return;
                    }

                    // Create local preview instantly before uploading
                    const objectUrl = URL.createObjectURL(file);
                    
                    if (type === 'logo') {
                        this.logoFile = file;
                        this.logoPreviewUrl = objectUrl;
                    } else {
                        this.coverFile = file;
                        this.coverPreviewUrl = objectUrl;
                    }
                },

                async saveSettings() {
                    this.isSaving = true;
                    
                    // We must use FormData to send files and text together
                    let fd = new FormData();
                    fd.append('name', this.formData.name);
                    
                    if (this.logoFile) fd.append('logo', this.logoFile);
                    if (this.coverFile) fd.append('cover', this.coverFile);

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/school/update', {
                            method: 'POST',
                            body: fd // Do NOT set Content-Type header when sending FormData; the browser handles the boundary automatically
                        });
                        
                        let data = await response.json();

                        if (data.success) {
                            this.showToast(data.message, 'success');
                            // Clear file references since they are now on the server
                            this.logoFile = null;
                            this.coverFile = null;
                        } else {
                            this.showToast(data.message, 'error');
                        }
                    } catch (error) {
                        this.showToast('Network error while saving.', 'error');
                    } finally {
                        this.isSaving = false;
                    }
                }
            }
        }
    </script>
</body>
</html>