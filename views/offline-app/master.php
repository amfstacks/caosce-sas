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

    <!-- COMPONENTS LAYER -->
    
    <template x-if="currentView === 'login'">
        <div class="w-full h-full absolute inset-0 overflow-y-auto">
            <?php include 'components/login.php'; ?>
        </div>
    </template>

    <template x-if="currentView === 'setup'">
        <div class="w-full h-full absolute inset-0 overflow-y-auto">
            <?php include 'components/setup-device.php'; ?>
        </div>
    </template>

    <template x-if="currentView === 'cbt'">
        <div class="w-full h-full absolute inset-0">
            <?php include 'components/cbt-engine.php'; ?>
        </div>
    </template>

    <template x-if="currentView === 'procedure'">
        <div class="w-full h-full absolute inset-0">
            <?php include 'components/procedure-engine.php'; ?>
        </div>
    </template>

    <template x-if="currentView === 'sync'">
        <div class="w-full h-full absolute inset-0 overflow-y-auto">
            <?php include 'components/admin-sync.php'; ?>
        </div>
    </template>

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
    <!-- Add this right before </body> in master.php -->
<!-- Add this right before </body> in master.php -->
    <!-- <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                // 1. PHP outputs the base path directly into JavaScript
                const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                
                // 2. Construct the exact URL to the Service Worker
                const swPath = basePath + '/sw.js';
                
                // 3. Register it
                navigator.serviceWorker.register(swPath)
                    .then((registration) => {
                        console.log('[CASOCE] Service Worker Active! Scope:', registration.scope);
                    })
                    .catch((error) => {
                        console.error('[CASOCE] SW Registration Failed:', error);
                    });
            });
        }
    </script> -->
    <!-- Add this right before </body> in master.php -->
  <!-- Add this right before </body> in master.php -->
   <!-- Add this right before </body> in master.php -->
    <!-- <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                const swPath = basePath + '/sw.js';
                const CACHE_NAME = 'caosce-offline-shell-v6'; 
                
                try {
                    // 1. Register the Service Worker
                    await navigator.serviceWorker.register(swPath);
                    
                    // 2. Force the current HTML URL into the cache immediately
                    try {
                        const cache = await caches.open(CACHE_NAME);
                        await cache.add(window.location.href);
                    } catch (e) { console.log("Silent cache skip."); }

                    // 3. Instantly tell Alpine we are ready!
                    window.dispatchEvent(new CustomEvent('cache-complete'));
                    
                } catch (error) {
                    console.error('SW Reg Failed:', error);
                }
            });
        }
    </script> -->
    <!-- Add this right before </body> in master.php -->
    <!-- <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                const swPath = basePath + '/sw.js';
                const CACHE_NAME = 'caosce-offline-shell-v7'; 
                
                try {
                    const registration = await navigator.serviceWorker.register(swPath);
                    
                    // ULTIMATE FIX: Fetch the raw HTML and save it under the Universal Key
                    try {
                        const rawHtml = await fetch(window.location.href);
                        const cache = await caches.open(CACHE_NAME);
                        await cache.put('/offline-master-shell', rawHtml);
                    } catch (e) { console.log("Silent cache skip."); }

                    if (!navigator.serviceWorker.controller) {
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed') {
                                    window.dispatchEvent(new CustomEvent('cache-complete'));
                                }
                            });
                        });
                        return;
                    }

                    if (navigator.serviceWorker.controller) {
                        window.dispatchEvent(new CustomEvent('cache-complete'));
                    }
                } catch (error) {
                    console.error('SW Reg Failed:', error);
                }
            });
        }
    </script> -->
    <!-- Add this right before </body> in master.php -->
    <!-- <script>
        console.log("🚨 [DEBUG] 1. Reached the Service Worker script block.");

        if ('serviceWorker' in navigator) {
            console.log("🚨 [DEBUG] 2. Browser supports Service Workers.");
            
            const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const swPath = basePath + '/sw.js';
            console.log("🚨 [DEBUG] 3. Attempting to register path:", swPath);
            
            // Forcing execution NOW. No waiting for window 'load' events.
            navigator.serviceWorker.register(swPath)
                .then((registration) => {
                    console.log("✅ [DEBUG] 4. SUCCESS! Service worker registered with scope:", registration.scope);
                })
                .catch((error) => {
                    console.error("❌ [DEBUG] 4. FAILED to register Service Worker:", error);
                });
                
        } else {
            console.error("❌ [DEBUG] 2. Browser DOES NOT support Service Workers. (Are you using HTTP instead of localhost/HTTPS?)");
        }
    </script> -->
    <!-- Add this right before </body> in master.php -->
    <script>
        if ('serviceWorker' in navigator) {
            const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
            const swPath = basePath + '/sw.js';
            const CACHE_NAME = 'caosce-offline-shell-v8'; // Bumped to v8
            
            // Force execution immediately
            navigator.serviceWorker.register(swPath).then(async (registration) => {
                
                // 1. Force the current HTML URL into the cache under the Universal Key
                try {
                    const rawHtml = await fetch(window.location.href);
                    const cache = await caches.open(CACHE_NAME);
                    await cache.put('/offline-master-shell', rawHtml);
                     window.dispatchEvent(new CustomEvent('cache-complete'));
                } catch (e) { console.log("Silent cache skip."); }

                // 2. Watch for installation to finish so we can trigger the Green Toast
                if (!navigator.serviceWorker.controller) {
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed') {
                                window.dispatchEvent(new CustomEvent('cache-complete'));
                            }
                        });
                    });
                    return;
                }

                if (navigator.serviceWorker.controller) {
                    // window.dispatchEvent(new CustomEvent('cache-complete'));
                }
                
            }).catch((error) => {
                console.error('SW Reg Failed:', error);
            });
        }
    </script>
    <script>
        // if ('serviceWorker' in navigator) {
        //     const basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
        //     const swPath = basePath + '/sw.js';
            
        //     // Force registration
        //     navigator.serviceWorker.register(swPath).then(async (registration) => {
                
        //         // 1. Force the current HTML URL into the cache immediately
        //         try {
        //             const rawHtml = await fetch(window.location.href);
        //             const cache = await caches.open('caosce-offline-shell-v8');
        //             await cache.put('/offline-master-shell', rawHtml);
                    
        //             // 2. NOW we know the shell is cached. Dispatch the event.
        //             window.dispatchEvent(new CustomEvent('cache-complete'));
        //         } catch (e) { console.log("Silent cache skip."); }

        //     }).catch((error) => console.error('SW Reg Failed:', error));
        // }
    </script>
</body>
</body>
</html>