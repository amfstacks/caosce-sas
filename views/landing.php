<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE | Unbreakable Offline Examinations</title>
    <!-- Alpine.js & Tailwind -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .bg-grid-pattern { background-image: radial-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px); background-size: 32px 32px; }
        .glass-panel { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.1); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased selection:bg-blue-600 selection:text-white" x-data="landingController()">

    <!-- ========================================== -->
    <!-- NAVBAR                                     -->
    <!-- ========================================== -->
    <nav class="absolute top-0 w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-24">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-600/30">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <span class="text-2xl font-extrabold text-white tracking-tight">CASOCE</span>
                </div>
                
                <!-- Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Features</a>
                    <a href="#how-it-works" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">How it Works</a>
                    <!-- Admin Login Link -->
                    <a href="admin/login" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-white transition-all bg-white/10 hover:bg-white/20 rounded-full ring-1 ring-white/20">
                        System Admin
                        <svg class="w-4 h-4 ml-2 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- HERO SECTION & WORKSPACE GATEWAY           -->
    <!-- ========================================== -->
    <div class="relative bg-slate-900 pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <!-- Decorative Background Gradients -->
        <div class="absolute inset-0 bg-grid-pattern opacity-30 pointer-events-none"></div>
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 w-[800px] h-[800px] bg-blue-600/30 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/3 -translate-x-1/3 w-[600px] h-[600px] bg-indigo-600/20 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-white tracking-tight leading-[1.1] mb-6">
                Conduct Flawless Exams. <br/>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Zero Internet Required.</span>
            </h1>
            <p class="mt-4 text-lg md:text-xl text-slate-400 max-w-2xl mx-auto font-medium mb-12">
                The enterprise-grade offline examination portal built for medical, clinical, and high-stakes practical assessments. Secure, tamper-proof, and lightning fast.
            </p>

            <!-- Workspace Gateway Card -->
            <div class="max-w-md mx-auto glass-panel rounded-3xl p-8 shadow-2xl relative z-10 transform transition-all hover:shadow-blue-900/20">
                <h3 class="text-xl font-bold text-white mb-2">Join Your Exam Session</h3>
                <p class="text-sm text-slate-400 mb-6">Enter your institution's 6-digit workspace code below to access your offline portal.</p>

                <!-- Error Toast -->
                <div x-show="errorMessage" x-transition.opacity style="display: none;" class="mb-4 bg-red-500/10 border border-red-500/30 rounded-lg p-3 flex items-start text-left">
                    <svg class="h-5 w-5 text-red-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span class="text-sm text-red-400 font-medium" x-text="errorMessage"></span>
                </div>

                <form @submit.prevent="validateWorkspace" class="space-y-4">
                    <div class="relative">
                        <input type="text" 
                               x-model="workspaceSlug" 
                               @input="formatSlug"
                               placeholder="e.g. MED-2026" 
                               maxlength="12"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-5 py-4 text-center text-2xl font-black text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all placeholder:text-slate-600 placeholder:font-medium placeholder:tracking-normal placeholder:text-lg">
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-[0_0_20px_rgba(37,99,235,0.3)] hover:shadow-[0_0_30px_rgba(37,99,235,0.5)] flex justify-center items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                            :disabled="workspaceSlug.length < 3 || isChecking">
                        <svg x-show="isChecking" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-text="isChecking ? 'Locating Workspace...' : 'Enter Portal'"></span>
                        <svg x-show="!isChecking" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FEATURE IMAGES SECTION                     -->
    <!-- ========================================== -->
    <div id="features" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Text Content -->
                <div>
                    <h2 class="text-base font-black text-blue-600 tracking-wide uppercase mb-3">Enterprise Resilience</h2>
                    <p class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight mb-6">
                        Say goodbye to server crashes during exams.
                    </p>
                    <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                        CASOCE turns any regular browser into a fortified, offline-first exam engine. Pre-load questions, isolate the testing environment, and grade clinical procedures without worrying about Wi-Fi drops.
                    </p>
                    
                    <ul class="space-y-6">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 border border-blue-100">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-slate-900">100% Offline Capability</h4>
                                <p class="mt-1 text-slate-500">Exams continue seamlessly even if the master server goes down or the local network fails.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 border border-indigo-100">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-slate-900">Tamper-Proof Gatekeeper</h4>
                                <p class="mt-1 text-slate-500">Devices are cryptographically bound to specific stations via PIN, preventing unauthorized access.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Image Side -->
                <div class="relative">
                    <!-- Main Image (Placeholder using reliable Unsplash URL) -->
                    <div class="rounded-3xl overflow-hidden shadow-2xl ring-1 ring-slate-900/5 aspect-[4/3] relative">
                        <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=2070&auto=format&fit=crop" alt="Students taking digital examination" class="object-cover w-full h-full transform hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                    </div>
                    
                    <!-- Floating Stat Card -->
                    <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl ring-1 ring-slate-900/5 animate-bounce" style="animation-duration: 3s;">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-500 uppercase">Data Synced</p>
                                <p class="text-2xl font-black text-slate-900">0ms Latency</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FOOTER                                     -->
    <!-- ========================================== -->
    <footer class="bg-slate-900 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <span class="text-xl font-extrabold text-white tracking-tight">CASOCE</span>
            </div>
            <p class="text-slate-500 text-sm">© <?php echo date('Y'); ?> CASOCE Systems. All rights reserved.</p>
        </div>
    </footer>

    <!-- ========================================== -->
    <!-- VUE / ALPINE JS CONTROLLER                 -->
    <!-- ========================================== -->
    <script>
        function landingController() {
            return {
                workspaceSlug: '',
                isChecking: false,
                errorMessage: '',

                formatSlug() {
                    // Auto-uppercase and clean out spaces
                    this.workspaceSlug = this.workspaceSlug.toUpperCase().replace(/\s+/g, '');
                    this.errorMessage = ''; // Clear error when typing
                },

               async validateWorkspace() {
                    if (this.workspaceSlug.length < 3) return;
                    
                    this.isChecking = true;
                    this.errorMessage = '';

                    try {
                        let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                        // Point exactly to your new Router endpoint
                        let url = `${basePath}/api/workspace/validate?slug=${encodeURIComponent(this.workspaceSlug)}`;
                        
                        let response = await fetch(url);
                        let data = await response.json();
                        
                        if (data.success) {
                            // Remember it for next time!
                            localStorage.setItem('caosce_last_workspace', data.payload.slug);
                            
                            // Redirect to the tenant's exact login path
                            window.location.href = `${basePath}/${data.payload.slug}/exam`;
                        } else {
                            this.errorMessage = data.message;
                        }
                    } catch (error) {
                        this.errorMessage = 'Network error. Please check your connection and try again.';
                    } finally {
                        this.isChecking = false;
                    }
                }
            }
        }
    </script>
</body>
</html>