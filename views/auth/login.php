<?php 
$pageTitle = "Secure Login"; 
include '../views/layouts/header.php'; 
?>

<body class="bg-white font-sans antialiased min-h-screen flex" x-data="loginController()" x-cloak>

    <div x-show="isInitializing" class="fixed inset-0 bg-slate-900 z-50 flex flex-col items-center justify-center transition-opacity duration-300">
        <svg class="animate-spin h-10 w-10 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <p class="text-white font-medium animate-pulse">Loading Workspace...</p>
    </div>

    <div class="hidden md:flex md:w-1/2 lg:w-3/5 relative bg-slate-900 overflow-hidden">
        
        <div class="absolute inset-0 bg-cover bg-center transition-all duration-700 ease-in-out transform scale-105"
             :style="schoolData.cover_image_path 
                ? 'background-image: url(' + schoolData.cover_image_path + ')' 
                : 'background-image: url(https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80)'">
        </div>
        
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/60 to-slate-900/20"></div>
        
        <div class="relative z-10 p-12 flex flex-col justify-end h-full text-white w-full">
            <p class="text-blue-400 font-bold tracking-widest uppercase mb-2"><?php echo APP_NAME; ?> Platform</p>
            <h1 class="text-4xl lg:text-5xl font-bold tracking-tight mb-4 leading-tight" x-text="schoolData.name || '<?php echo APP_CAPTION; ?>'"></h1>
            <p class="text-slate-300 text-lg max-w-xl font-medium">A secure, resilient environment for objective structured clinical examinations.</p>
        </div>
    </div>

    <div class="w-full md:w-1/2 lg:w-2/5 flex flex-col items-center justify-center p-8 sm:p-12 bg-white relative shadow-[-10px_0_30px_rgba(0,0,0,0.05)] z-10">
        
        <div class="w-full max-w-md" x-show="!isInitializing" x-transition.opacity.duration.500ms>
            
            <div class="flex flex-col items-center text-center mb-10">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4 overflow-hidden ring-1 ring-slate-200 shadow-sm">
                    <template x-if="schoolData.logo_path">
                        <img :src="schoolData.logo_path" alt="School Logo" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!schoolData.logo_path">
                        <span class="text-xs text-slate-400 font-bold"><?php echo APP_NAME; ?></span>
                    </template>
                </div>

                <h2 class="text-2xl font-bold text-slate-900">Secure Sign In</h2>
                
                <?php if(defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG !== null): ?>
                    <span class="mt-3 inline-flex items-center rounded-md bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-300 uppercase tracking-widest">
                        Workspace: <?php echo htmlspecialchars(CURRENT_TENANT_SLUG); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div x-show="errorMessage" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md" style="display: none;">
                <p class="text-sm font-medium text-red-800" x-text="errorMessage"></p>
            </div>

            <form @submit.prevent="submitLogin" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-semibold leading-6 text-slate-900">Matric Number / Admin ID</label>
                    <div class="mt-2">
                        <input type="text" id="username" x-model="formData.username" required autocomplete="username" class="block w-full rounded-lg border-slate-300 py-3 px-4 text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 sm:text-sm border bg-slate-50 transition-colors">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold leading-6 text-slate-900">Secure Password</label>
                    <div class="mt-2">
                        <input type="password" id="password" x-model="formData.password" required autocomplete="current-password" class="block w-full rounded-lg border-slate-300 py-3 px-4 text-slate-900 shadow-sm focus:ring-2 focus:ring-blue-600 focus:border-blue-600 sm:text-sm border bg-slate-50 transition-colors">
                    </div>
                </div>

                <button type="submit" :disabled="isLoading" class="flex w-full justify-center items-center rounded-lg bg-blue-600 px-3 py-4 text-sm font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition-all disabled:opacity-70 disabled:cursor-not-allowed mt-4 transform active:scale-[0.98]">
                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isLoading ? 'Authenticating...' : 'Sign In to Workspace'"></span>
                </button>
            </form>
            
        </div>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ''; ?>';

        function loginController() {
            return {
                formData: { username: '', password: '' },
                errorMessage: '',
                isLoading: false,
                isInitializing: true,
                schoolData: { name: '', logo_path: '', cover_image_path: '' }, // Added cover_image_path

                init() {
                    if (!TENANT_SLUG || TENANT_SLUG === 'login') {
                        this.isInitializing = false;
                        return;
                    }
                    this.loadTenantData();
                },

                getBaseApiUrl() {
                    let currentPath = window.location.pathname;
                    if (!TENANT_SLUG || TENANT_SLUG === 'login') return currentPath.split('/login')[0];
                    return currentPath.split(`/${TENANT_SLUG}`)[0] + `/${TENANT_SLUG}`;
                },

                async loadTenantData() {
                    const cacheKey = 'caosce_schoolaaaa' + TENANT_SLUG;
                    const cachedData = localStorage.getItem(cacheKey);

                    if (cachedData) {
                        this.schoolData = JSON.parse(cachedData);
                        setTimeout(() => { this.isInitializing = false; }, 300);
                        return;
                    }

                    try {
                        let apiUrl = this.getBaseApiUrl() + '/api/tenant-info';
                        let response = await fetch(apiUrl);
                        let data = await response.json();

                        if (data.success) {
                            this.schoolData = data.payload;
                            localStorage.setItem(cacheKey, JSON.stringify(data.payload));
                        }
                    } catch (error) {
                        console.error('Could not load tenant branding:', error);
                    } finally {
                        this.isInitializing = false;
                    }
                },

                async submitLogin() {
                    this.isLoading = true;
                    this.errorMessage = '';

                    let deviceSig = localStorage.getItem('caosce_device_signature');
                    let apiUrl = this.getBaseApiUrl() + '/api/login';

                    try {
                        let response = await fetch(apiUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({
                                username: this.formData.username,
                                password: this.formData.password,
                                device_signature: deviceSig
                            })
                        });

                        let data = await response.json();

                        if (data.success) {
                            window.location.href = this.getBaseApiUrl() + data.redirect_url;
                        } else {
                            this.errorMessage = data.message || 'Login failed. Please verify your credentials.';
                        }
                    } catch (error) {
                        this.errorMessage = 'Network error. Could not reach the authentication server.';
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>

<?php include '../views/layouts/footer.php'; ?>