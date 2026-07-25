<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Destroy the session if they clicked logout
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    // Redirect to clean the URL
    $redirectUrl = (defined('BASE_PATH') ? BASE_PATH : '') . (defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG ? '/' . CURRENT_TENANT_SLUG . '/admin/login' : '/admin/login');
    header("Location: " . $redirectUrl);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Gateway | CASOCE</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center relative overflow-hidden selection:bg-indigo-500 selection:text-white" x-data="adminLoginController()">

    <!-- Decorative Tech Background -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.05)_1px,transparent_1px)] [background-size:32px_32px] opacity-20"></div>
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 w-[600px] h-[600px] bg-indigo-600/20 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="w-full max-w-md px-6 relative z-10">
        
        <!-- Branding Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-slate-800 border border-slate-700 shadow-lg mb-4">
                <svg class="w-8 h-8 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">System Admin</h1>
            <p class="text-sm text-slate-400 mt-2 uppercase tracking-widest font-bold">CASOCE Command Center</p>
            
            <div class="mt-4">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-800/80 px-3 py-1 text-xs font-bold text-slate-300 ring-1 ring-inset ring-slate-600">
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                    Workspace: <?php echo strtoupper(CURRENT_TENANT_SLUG ?? 'GLOBAL'); ?>
                </span>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-8 shadow-2xl">
            
            <!-- Error Banner -->
            <div x-show="errorMessage" x-transition class="mb-6 bg-red-500/10 border border-red-500/20 rounded-xl p-4 flex items-start" style="display: none;">
                <svg class="h-5 w-5 text-red-400 mr-3 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span class="text-sm text-red-300 font-medium leading-relaxed" x-text="errorMessage"></span>
            </div>

            <form @submit.prevent="submitLogin" class="space-y-5">
                <!-- Username -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Admin ID</label>
                    <input type="text" x-model="formData.username" required autocomplete="username"
                           class="block w-full rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3 text-white placeholder:text-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                           placeholder="Enter your administrator ID">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Security Key</label>
                    <input type="password" x-model="formData.password" required autocomplete="current-password"
                           class="block w-full rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3 text-white placeholder:text-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors"
                           placeholder="••••••••">
                </div>

                <!-- Submit Button -->
                <button type="submit" :disabled="isLoading"
                        class="mt-6 w-full flex items-center justify-center py-3.5 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-[0_0_20px_rgba(79,70,229,0.3)] hover:shadow-[0_0_25px_rgba(79,70,229,0.5)] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed group">
                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isLoading ? 'Authenticating...' : 'Secure Login'"></span>
                    <svg x-show="!isLoading" class="ml-2 w-4 h-4 opacity-70 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>
            
            <div class="mt-8 text-center border-t border-slate-700/50 pt-6">
                <a href="<?php echo defined('CURRENT_TENANT_SLUG') ? '/' . CURRENT_TENANT_SLUG . '/login' : '/landing'; ?>" class="text-sm font-medium text-slate-400 hover:text-white transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Return to Portal
                </a>
            </div>
        </div>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ""; ?>';
        
        function adminLoginController() {
            return {
                formData: {
                    username: '',
                    password: ''
                },
                errorMessage: '',
                isLoading: false,

                getBaseApiUrl() {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return TENANT_SLUG ? `${basePath}/${TENANT_SLUG}` : basePath;
                },

                async submitLogin() {
                    this.isLoading = true;
                    this.errorMessage = '';

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.formData)
                        });
                        
                        let data = await response.json();

                       if (data.success) {
                            // Redirect cleanly to the backend dashboard
                            let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                            // Ensure the redirect URL always starts with a slash, then safely combine
                            let safeRedirect = data.redirect_url.startsWith('/') ? data.redirect_url : '/' + data.redirect_url;
                            
                            window.location.href = basePath + safeRedirect;
                        } else {
                            this.errorMessage = data.message || 'Authentication failed.';
                        }
                    } catch (error) {
                        this.errorMessage = 'Network error connecting to the secure gateway.';
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>