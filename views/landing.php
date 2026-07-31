<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAOSCE | Unbreakable Offline Clinical Examinations</title>

    <meta name="google-site-verification" content="FahBVZQWUuCOuvqXtuIgZVu_knZYN1SiKAl76dj_MKM" />
    <meta name="description" content="CAOSCE is the enterprise-grade, offline-first examination portal built for Nursing, Midwifery, and high-stakes clinical OSCE procedures. Conduct flawless exams with zero downtime.">
    <meta name="keywords" content="OSCE software, clinical examination platform, nursing exam software, offline CBT, objective structured clinical examination, medical testing portal">
    <meta name="author" content="CAOSCE Systems">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://caosce.com/">

    <!-- Open Graph (Facebook, LinkedIn, Slack) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://caosce.com/">
    <meta property="og:title" content="CAOSCE | Unbreakable Offline Clinical Examinations">
    <meta property="og:description" content="The enterprise-grade offline examination portal built for Nursing and Midwifery schools. Conduct flawless exams with zero internet required.">
    <meta property="og:image" content="https://caosce.com/public/assets/social-preview.jpg">
    <meta property="og:site_name" content="CAOSCE">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://caosce.com/">
    <meta name="twitter:title" content="CAOSCE | Unbreakable Offline Clinical Examinations">
    <meta name="twitter:description" content="The enterprise-grade offline examination portal built for Nursing and Midwifery schools. Conduct flawless exams with zero internet required.">
    <meta name="twitter:image" content="https://caosce.com/public/assets/social-preview.jpg">

    <!-- Favicon Suite -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/public/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/public/assets/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/public/assets/apple-touch-icon.png">
    <!-- Alpine.js & Tailwind -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel { 
            background: rgba(15, 23, 42, 0.6); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
        }
        .hero-bg {
            /* High-quality Unsplash image of medical/nursing training */
            /* background-image: url('https://images.unsplash.com/photo-1576091160550-2173ff9e2832?q=80&w=2070&auto=format&fit=crop'); */
            background-image: url('<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>/public/assets/osce_image.jpg');
            background-size: cover;
            background-position: center;
        }
        [x-cloak] { display: none !important; }
    </style>

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "CAOSCE",
      "operatingSystem": "Windows, Web",
      "applicationCategory": "EducationalApplication",
      "description": "An offline-first clinical examination and OSCE portal designed for Nursing and Midwifery schools to conduct secure, tamper-proof assessments.",
      "url": "https://caosce.com",
      "publisher": {
        "@type": "Organization",
        "name": "Ajala Mayowa Felix Amfstacks"
      },
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "NGN",
        "availability": "https://schema.org/OnlineOnly"
      }
    }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 antialiased selection:bg-blue-600 selection:text-white" x-data="landingController()">

    <!-- ========================================== -->
    <!-- NAVBAR                                     -->
    <!-- ========================================== -->
    <nav class="absolute top-0 w-full z-50 transition-all duration-300 border-b border-white/10" :class="{'bg-slate-900/90 backdrop-blur-md fixed': scrolled, 'absolute': !scrolled}" @scroll.window="scrolled = (window.pageYOffset > 50)">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo Placeholder -->
                <div class="flex-shrink-0 flex items-center gap-3 cursor-pointer">
                    <!-- Professional Geometric Logo Placeholder -->
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-extrabold text-white tracking-tight">CAOSCE</span>
                </div>
                
                <!-- Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#how-it-works" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">How it Works</a>
                    <a href="#features" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Features</a>
                    <button @click="modals.contact = true" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Contact Us</button>
                    
                    <div class="h-5 w-px bg-slate-700"></div>

                    <button @click="modals.request = true" class="text-sm font-bold text-blue-400 hover:text-blue-300 transition-colors">Request Access</button>
                    
                    <!-- Admin Login Link -->
                    <a href="admin/login" class="inline-flex items-center justify-center px-5 py-2 text-sm font-bold text-white transition-all bg-white/10 hover:bg-white/20 rounded-full ring-1 ring-white/20">
                        Admin Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ========================================== -->
    <!-- HERO SECTION (FULL HEIGHT)                 -->
    <!-- ========================================== -->
    <div class="relative min-h-screen flex items-center justify-center hero-bg">
        <!-- Deep Gradient Overlay for text readability -->
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/90 via-slate-900/80 to-slate-900/95"></div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8 w-full pt-32 pb-20 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <!-- Left: Hero Copy -->
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold uppercase tracking-wider mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    The Standard in Clinical Assessments
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.1] mb-6">
                    Flawless  Exams. <br/>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-white">Zero Downtime. 100% Offline-First.</span>
                </h1>
                <p class="text-lg text-slate-300 font-medium mb-8 leading-relaxed max-w-lg">
                    The enterprise-grade offline examination portal built specifically for Nursing, Midwifery, and high-stakes clinical OSCE procedures. Secure, tamper-proof, and seamlessly synchronized.
                </p>
                <div class="flex flex-wrap gap-4">
                    <button @click="modals.request = true" class="px-8 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg shadow-blue-600/30 transition-all active:scale-95">
                        Register Your Institution
                    </button>
                    <a href="#how-it-works" class="px-8 py-3.5 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl ring-1 ring-white/20 transition-all backdrop-blur-sm">
                        See How it Works
                    </a>
                </div>
            </div>

            <!-- Right: Workspace Gateway Card -->
            <div class="lg:justify-self-end w-full max-w-md">
                <div class="glass-panel rounded-3xl p-8 shadow-2xl relative z-10 transform transition-all hover:shadow-blue-900/40">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mb-6 ring-1 ring-blue-500/30">
                        <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-white mb-2">Student & Examiner Portal</h3>
                    <p class="text-sm text-slate-400 mb-8">Enter your institution's unique workspace code below to download your offline exam payload.</p>

                    <!-- Error Toast -->
                    <div x-show="errorMessage" x-transition.opacity x-cloak class="mb-6 bg-red-500/10 border border-red-500/30 rounded-lg p-3 flex items-start text-left">
                        <svg class="h-5 w-5 text-red-400 mr-2 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <span class="text-sm text-red-400 font-medium" x-text="errorMessage"></span>
                    </div>

                    <form @submit.prevent="validateWorkspace" class="space-y-4">
                        <input type="text" 
                               x-model="workspaceSlug" 
                               @input="formatSlug"
                               placeholder="ENTER SCHOOL CODE" 
                               maxlength="12"
                               class="w-full bg-slate-900/80 border border-slate-600 rounded-xl px-5 py-4 text-center text-xl font-black text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-500 placeholder:font-semibold placeholder:tracking-wider placeholder:text-sm shadow-inner">

                        <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-[0_0_20px_rgba(37,99,235,0.2)] hover:shadow-[0_0_30px_rgba(37,99,235,0.4)] flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="workspaceSlug.length < 3 || isChecking">
                            <svg x-show="isChecking" x-cloak class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-text="isChecking ? 'Locating...' : 'Access Workspace'"></span>
                            <svg x-show="!isChecking" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- HOW IT WORKS SECTION                       -->
    <!-- ========================================== -->
    <div id="how-it-works" class="py-24 bg-slate-50 relative border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-sm font-black text-blue-600 tracking-widest uppercase mb-3">Architecture</h2>
                <h3 class="text-3xl md:text-4xl font-extrabold text-slate-900">How CAOSCE Works</h3>
                <p class="mt-4 text-slate-600 text-lg">A robust four-step clinical testing framework designed to survive any network failure.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 relative group hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 font-black text-xl">1</div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">Provision</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">System Admins create exam sessions, upload CBT questions, and assign examiners to specific practical procedures.</p>
                </div>
                <!-- Step 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 relative group hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 font-black text-xl">2</div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">Bind & Cache</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">Devices connect to the workspace once, using a 6-digit PIN to securely download the encrypted offline payload.</p>
                </div>
                <!-- Step 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 relative group hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mb-6 font-black text-xl">3</div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">Execute Offline</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">Exams run 100% offline. Zero latency, no server crashes, immune to Wi-Fi drops.</p>
                </div>
                <!-- Step 4 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 relative group hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6 font-black text-xl">4</div>
                    <h4 class="text-xl font-bold text-slate-900 mb-3">Sync & Analyze</h4>
                    <p class="text-slate-600 text-sm leading-relaxed">When internet is restored, the Admin Control Center bulk-syncs all offline results instantly for grading and CSV export.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FEATURES SECTION                           -->
    <!-- ========================================== -->
    <div id="features" class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Text Content -->
                <div>
                    <h2 class="text-sm font-black text-blue-600 tracking-widest uppercase mb-3">Enterprise Resilience</h2>
                    <p class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight mb-6">
                        Say goodbye to server crashes during high-stakes exams.
                    </p>
                    <p class="text-lg text-slate-600 mb-8 leading-relaxed">
                        CAOSCE turns any regular browser or Windows desktop into a fortified, offline-first exam engine. Pre-load clinical scenarios, isolate the testing environment, and grade practical procedures without worrying about connectivity.
                    </p>
                    
                    <ul class="space-y-6">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 border border-blue-100">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-slate-900">Standardized Clinical Scoring</h4>
                                <p class="mt-1 text-slate-500 text-sm">Empower examiners to grade practical Nursing and Midwifery skills objectively using pre-defined rubrics.</p>
                            </div>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-700 border border-slate-200">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-slate-900">Cryptographic Device Binding</h4>
                                <p class="mt-1 text-slate-500 text-sm">Devices are locked to specific exam stations. Only authorized students and examiners can access the active payload.</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Image Side -->
                <div class="relative">
                    <div class="rounded-3xl overflow-hidden shadow-2xl ring-1 ring-slate-900/5 aspect-[4/3] relative bg-slate-100">
                        <!-- Clinical setting photo -->
                        <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=2070&auto=format&fit=crop" alt="Clinical examination dashboard" class="object-cover w-full h-full">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 to-transparent"></div>
                    </div>
                    
                    <!-- Floating Stat Card -->
                    <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl ring-1 ring-slate-900/5 border border-slate-100 animate-bounce" style="animation-duration: 3s;">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">System Latency</p>
                                <p class="text-2xl font-black text-slate-900">0.0ms</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- CTA SECTION                                -->
    <!-- ========================================== -->
    <div class="bg-blue-600 py-16">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-extrabold text-white mb-4">Ready to modernize your institution's exams?</h2>
            <p class="text-blue-100 mb-8 text-lg">Join leading nursing and midwifery schools using CAOSCE to deliver flawless practical assessments.</p>
            <button @click="modals.request = true" class="bg-white text-blue-600 font-bold px-8 py-4 rounded-xl shadow-lg hover:bg-slate-50 transition-colors active:scale-95">
                Request a Demo & Access
            </button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- FOOTER                                     -->
    <!-- ========================================== -->
    <footer class="bg-slate-900 py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2 opacity-50 hover:opacity-100 transition-opacity cursor-pointer">
                <div class="w-8 h-8 bg-slate-700 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <span class="text-xl font-extrabold text-white tracking-tight">CAOSCE</span>
            </div>
            <div class="flex gap-6 text-sm text-slate-500 font-medium">
                <button @click="modals.contact = true" class="hover:text-slate-300 transition-colors">Contact Support</button>
                <a href="#" class="hover:text-slate-300 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-slate-300 transition-colors">Terms of Service</a>
            </div>
            <p class="text-slate-600 text-sm">© <?php echo date('Y'); ?> CAOSCE Systems. All rights reserved.</p>
        </div>
    </footer>


    <!-- ========================================== -->
    <!-- MODALS (Teleported to body)                -->
    <!-- ========================================== -->

    <!-- Contact Us Modal -->
    <div x-show="modals.contact" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.outside="modals.contact = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-800">Contact Support</h3>
                <button @click="modals.contact = false" class="text-slate-400 hover:text-slate-600 bg-white p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form @submit.prevent="submitContact" class="p-6 space-y-4">
                <div x-show="formStatus.contact === 'success'" class="bg-green-50 text-green-700 p-4 rounded-xl text-sm font-bold border border-green-200">
                    Message sent successfully! Our team will reach out shortly.
                </div>
                <div x-show="formStatus.contact !== 'success'">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Full Name</label>
                        <input type="text" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Email Address</label>
                        <input type="email" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Message</label>
                        <textarea required rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all resize-none"></textarea>
                    </div>
                    <button type="submit" :disabled="formStatus.contact === 'loading'" class="w-full mt-6 bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-xl transition-all disabled:opacity-70 flex justify-center items-center gap-2">
                        <span x-text="formStatus.contact === 'loading' ? 'Sending...' : 'Send Message'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Access Modal -->
    <div x-show="modals.request" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.outside="modals.request = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="px-6 py-6 border-b border-slate-100 flex justify-between items-center bg-blue-600 text-white">
                <div>
                    <h3 class="text-xl font-bold">Request Institution Access</h3>
                    <p class="text-blue-100 text-xs mt-1">Get your school set up on CAOSCE</p>
                </div>
                <button @click="modals.request = false" class="text-blue-100 hover:text-white bg-blue-700 p-2 rounded-full"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form @submit.prevent="submitRequest" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <div x-show="formStatus.request === 'success'" class="bg-blue-50 text-blue-700 p-4 rounded-xl text-sm font-bold border border-blue-200">
                    Application received! Our onboarding team will review your institution details and contact you to provision your workspace.
                </div>
                <div x-show="formStatus.request !== 'success'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Institution Name</label>
                        <input type="text" required placeholder="e.g. College of Nursing Sciences" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Contact Person</label>
                            <input type="text" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Official Role</label>
                            <select required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all bg-white">
                                <option value="">Select role...</option>
                                <option>Provost / Dean</option>
                                <option>Head of Department</option>
                                <option>Chief Examiner</option>
                                <option>IT Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Official Email</label>
                        <input type="email" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Estimated Student Capacity</label>
                        <select required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all bg-white">
                            <option value="">Select size...</option>
                            <option>Under 100 students</option>
                            <option>100 - 500 students</option>
                            <option>500+ students</option>
                        </select>
                    </div>
                    <button type="submit" :disabled="formStatus.request === 'loading'" class="w-full mt-6 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3.5 px-6 rounded-xl transition-all disabled:opacity-70 flex justify-center items-center gap-2 shadow-lg shadow-blue-600/20">
                        <span x-text="formStatus.request === 'loading' ? 'Submitting Application...' : 'Submit Request'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- ========================================== -->
    <!-- VUE / ALPINE JS CONTROLLER                 -->
    <!-- ========================================== -->
    <script>
        function landingController() {
            return {
                workspaceSlug: '',
                isChecking: false,
                errorMessage: '',
                scrolled: false,
                
                // UI State for Modals
                modals: {
                    contact: false,
                    request: false
                },
                
                formStatus: {
                    contact: 'idle', // idle, loading, success
                    request: 'idle'
                },

                formatSlug() {
                    this.workspaceSlug = this.workspaceSlug.toUpperCase().replace(/\s+/g, '');
                    this.errorMessage = '';
                },

                // --- EXISTING WORKSPACE VALIDATION ---
                async validateWorkspace() {
                    if (this.workspaceSlug.length < 3) return;
                    this.isChecking = true;
                    this.errorMessage = '';
                    try {
                        let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                        let url = `${basePath}/api/workspace/validate?slug=${encodeURIComponent(this.workspaceSlug)}`;
                        
                        let response = await fetch(url);
                        let data = await response.json();
                        
                        if (data.success) {
                            localStorage.setItem('caosce_last_workspace', data.payload.slug);
                            window.location.href = `${basePath}/${data.payload.slug}/exam`;
                        } else {
                            this.errorMessage = data.message;
                        }
                    } catch (error) {
                        this.errorMessage = 'Network error. Please check your connection and try again.';
                    } finally {
                        this.isChecking = false;
                    }
                },

                // --- MOCK FORM SUBMISSIONS ---
                submitContact() {
                    this.formStatus.contact = 'loading';
                    // Mock API call delay
                    setTimeout(() => {
                        this.formStatus.contact = 'success';
                        setTimeout(() => { 
                            this.modals.contact = false; 
                            this.formStatus.contact = 'idle'; // Reset for next time
                        }, 3000);
                    }, 1500);
                },

                submitRequest() {
                    this.formStatus.request = 'loading';
                    // Mock API call delay
                    setTimeout(() => {
                        this.formStatus.request = 'success';
                        setTimeout(() => { 
                            this.modals.request = false;
                            this.formStatus.request = 'idle';
                        }, 4000);
                    }, 1500);
                }
            }
        }
    </script>
</body>
</html>