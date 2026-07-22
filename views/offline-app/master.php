<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ECHO "YES";
// exit;
$pageTitle = "CASOCE - Offline Examination Portal"; 
// include '../layouts/header.php'; // Only include if it doesn't break the full-screen layout
include __DIR__ . '/../layouts/header.php';
?>

   
    <style>
        [x-cloak] { display: none !important; }
        .touch-btn { transition: transform 0.1s, background-color 0.2s; }
        .touch-btn:active { transform: scale(0.98); }
        .no-select { user-select: none; -webkit-user-select: none; }
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: transparent; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>

<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen overflow-hidden" 
      x-data="masterAppController()" 
      x-init="bootUp()"
      @navigate.window="switchView($event.detail)"
      @cache-complete.window="markAsCached()"
      x-cloak>

    <!-- Global Preloader -->
    <div x-show="isBooting" class="fixed inset-0 z-[9999] bg-slate-900 flex flex-col items-center justify-center p-4">
        <svg class="animate-spin h-10 w-10 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <p class="text-white font-medium animate-pulse">Initializing Secure Environment...</p>
    </div>
    <!-- Offline Ready Indicator (Floating at bottom right) -->
    <div class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none">
        
        <!-- Downloading State -->
        <div x-show="!isAppCached" x-transition class="bg-slate-800 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 border border-slate-700">
            <svg class="animate-spin h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span class="text-sm font-bold">Caching Exam Engine...</span>
        </div>

        <!-- Success State Toast -->
        <div x-show="showCacheToast" x-transition class="bg-green-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 border border-green-500">
            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-sm font-bold">Engine Ready for Offline Use!</span>
        </div>

    </div>

   
    
    <div x-show="currentView === 'login'">
        <div class="w-full h-full absolute inset-0 overflow-y-auto">
            <?php include 'components/login.php'; ?>
        </div>
    </div>

    <div x-show="currentView === 'setup'">
        <div class="w-full h-full absolute inset-0 overflow-y-auto">
            <?php include 'components/setup-device.php'; ?>
        </div>
    </div>

    <div x-show="currentView === 'cbt'">
        <div class="w-full h-full absolute inset-0">
            <?php include 'components/cbt-engine.php'; ?>
        </div>
    </div>

    <div x-show="currentView === 'procedure'">
        <div class="w-full h-full absolute inset-0">
            <?php include 'components/procedure-engine.php'; ?>
        </div>
    </div>

    <div x-show="currentView === 'sync'">
        <div class="w-full h-full absolute inset-0 overflow-y-auto">
            <?php include 'components/admin-sync.php'; ?>
        </div>
    </div>

    <script>
        function masterAppController() {
            return {
                isBooting: true,
                currentView: '', // 'login', 'setup', 'cbt', 'procedure', 'sync'
                payloadMeta: null,
                isAppCached: false, 
                showCacheToast: false,

                async bootUp() {
                    // 1. Check if device has an offline payload
                    try {
                        const payload = await localforage.getItem('caosce_offline_data');
                        if (payload && payload.station_settings) {
                            this.payloadMeta = payload.station_settings;
                        }
                    } catch(e) { console.error("DB Read Error"); }

                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        this.isAppCached = true;
                    }

                    // 2. Check Auth State
                    const authStr = sessionStorage.getItem('caosce_offline_auth');
                    
                    if (authStr) {
                        // Someone is logged in! Route them based on role.
                        const auth = JSON.parse(authStr);
                        
                        if (auth.role === 'admin') {
                            this.currentView = 'sync';
                        } 
                        else if (auth.role === 'examiner') {
                            this.currentView = 'procedure';
                        } 
                        else if (auth.role === 'student') {
                            // Route based on what type of station this laptop is bound to
                            this.currentView = (this.payloadMeta && this.payloadMeta.station_type === 'cbt') ? 'cbt' : 'procedure';
                        }
                    } else {
                        // Nobody is logged in. Default to Login screen.
                        // (The Login screen itself will check if the device is unbound and show the Setup button).
                        this.currentView = 'login';
                    }

                    // Remove preloader
                    setTimeout(() => { this.isBooting = false; }, 400);
                },

                // Listens for $dispatch('navigate', 'view_name') from any child component
                switchView(viewName) {
                    this.isBooting = true;
                    setTimeout(() => {
                        this.currentView = viewName;
                        this.isBooting = false;
                    }, 300); // Tiny artificial delay to unmount/mount cleanly
                },
                markAsCached() {
            this.isAppCached = true;
            this.showCacheToast = true;
            setTimeout(() => { this.showCacheToast = false; }, 5000); // Hide toast after 5 seconds
        }
            }
        }
    </script>
<!-- <script>
        if ('serviceWorker' in navigator) {
            // 1. Get dynamic path and strip '/public' so it correctly targets the root!
            let rawBasePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const basePath = rawBasePath.replace(/\/public\/?$/, '');
            const swPath = basePath + '/sw.js';
            // const CACHE_NAME = 'caosce-offline-shell-v8'; 
            const CACHE_NAME = 'caosce-offline-shell-v9';

            console.log("⏳ [SW] Attempting to register at:", swPath);
            
            navigator.serviceWorker.register(swPath).then(async (registration) => {
                // NOW you will see this in the console!
                console.log("✅ [SW] Registered Successfully! Scope:", registration.scope);
                
                try {
                    const rawHtml = await fetch(window.location.href);
                    const cache = await caches.open(CACHE_NAME);
                    await cache.put('/offline-master-shell', rawHtml);
                    
                    // If the SW is already controlling the page, trigger the UI success instantly
                    if (navigator.serviceWorker.controller) {
                        window.dispatchEvent(new CustomEvent('cache-complete'));
                    }
                } catch (e) { console.log("Silent cache skip."); }

                // Watch for a brand new installation to finish
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        // This only fires when sw.js completely finishes downloading all files
                        if (newWorker.state === 'installed') {
                            window.dispatchEvent(new CustomEvent('cache-complete'));
                        }
                    });
                });
                
            }).catch((error) => {
                console.error('❌ [SW] Registration Failed:', error);
            });
        } else {
            console.warn('⚠️ [SW] Service Workers are not supported in this browser environment.');
        }
    </script> -->

    <script>
        if ('serviceWorker' in navigator) {
            let rawBasePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const basePath = rawBasePath.replace(/\/public\/?$/, '');
            const swPath = basePath + '/sw.js';
            const CACHE_NAME = 'caosce-offline-shell-v9';

            console.log("⏳ [SW] Attempting to register at:", swPath);
            
            navigator.serviceWorker.register(swPath).then(async (registration) => {
                console.log("✅ [SW] Registered Successfully! Scope:", registration.scope);
                
                // Force the master HTML into the cache
                try {
                    const rawHtml = await fetch(window.location.href);
                    const cache = await caches.open(CACHE_NAME);
                    await cache.put('/offline-master-shell', rawHtml);
                } catch (e) { console.log("Silent cache skip."); }

                // Trigger UI update if the app is ALREADY cached (page reloads)
                if (navigator.serviceWorker.controller) {
                    window.dispatchEvent(new CustomEvent('cache-complete'));
                }
                
            }).catch((error) => {
                console.error('❌ [SW] Registration Failed:', error);
            });

            // THE BULLETPROOF FIX FOR FIRST LOAD:
            // This fires exactly when sw.js executes `self.clients.claim()`!
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                window.dispatchEvent(new CustomEvent('cache-complete'));
            });
            
        } else {
            console.warn('⚠️ [SW] Service Workers are not supported in this browser environment.');
        }
    </script>



</body>
</html>