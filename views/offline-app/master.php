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
      x-cloak>

    <!-- Global Preloader -->
    <div x-show="isBooting" class="fixed inset-0 z-[9999] bg-slate-900 flex flex-col items-center justify-center p-4">
        <svg class="animate-spin h-10 w-10 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <p class="text-white font-medium animate-pulse">Initializing Secure Environment...</p>
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
                }
            }
        }
    </script>
</body>
</html>