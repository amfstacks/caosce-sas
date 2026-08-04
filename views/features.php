<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Clinical Examination Software | Fast, Offline OSCE Platform</title>
    <meta name="description" content="Discover how CAOSCE delivers zero-latency clinical exams and seamless database reconciliation without internet access. The ultimate platform for nursing and midwifery schools.">
    <link rel="canonical" href="https://caosce.com/clinical-osce-software">

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-panel { background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.05); }
        .bg-grid { background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 30px 30px; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data class="bg-slate-900 text-slate-200 antialiased min-h-screen flex flex-col relative overflow-x-hidden"> 
    <div class="fixed inset-0 bg-grid z-0 opacity-20 pointer-events-none"></div>
    <div class="fixed top-[-20%] left-[-10%] w-[50%] h-[50%] rounded-full bg-blue-600/20 blur-[120px] z-0 pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="relative z-20 px-6 py-4 flex justify-between items-center max-w-7xl mx-auto w-full border-b border-white/10 mb-12">
        <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/" class="text-2xl font-extrabold tracking-tight text-white flex items-center gap-2">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-sm">C</div>
            CAOSCE
        </a>
        <div class="flex gap-4">
            <button @click="$dispatch('open-contact')" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors">Contact Us</button>
            <button @click="$dispatch('open-request')" class="px-5 py-2.5 rounded-lg text-sm font-bold text-white bg-blue-600 hover:bg-blue-500 transition-colors">Request Demo</button>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 flex-grow px-6 max-w-6xl mx-auto w-full pb-24">
        
        <header class="text-center mb-20 max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-6xl font-extrabold text-white leading-tight mb-6 tracking-tight">
                Flawless Nursing School Exams.<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-300">Zero Internet Required.</span>
            </h1>
            <p class="text-lg md:text-xl text-slate-400 leading-relaxed">
                Traditional exam platforms fail when the internet drops. CAOSCE is engineered differently. We provide nursing and midwifery schools with an unbreakable, offline-first environment for both theoretical CBTs and Objective Structured Clinical Examinations (OSCE).
            </p>
            <div class="mt-8 flex justify-center gap-4">
                <button @click="$dispatch('open-request')" class="px-8 py-4 bg-white text-slate-900 font-bold rounded-xl hover:bg-slate-100 transition-colors">Schedule a Demo</button>
            </div>
        </header>

        <!-- CBT vs OSCE Section -->
        <div class="mb-24">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-white">One Platform. Two Specialized Exam Modes.</h2>
                <p class="text-slate-400 mt-3">Built specifically for the dual nature of clinical assessments.</p>
            </div>
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- CBT Block -->
                <div class="glass-panel p-8 rounded-3xl border-t-4 border-t-cyan-400">
                    <div class="w-12 h-12 bg-cyan-500/20 text-cyan-400 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Computer Based Testing (CBT)</h3>
                    <p class="text-slate-400 leading-relaxed mb-6">
                        Designed for theoretical nursing exams. Thousands of students can answer multiple-choice questions simultaneously. The software caches all questions and images directly to the local device.
                    </p>
                    <ul class="space-y-3 text-sm text-slate-300 font-medium">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Instant page loads (0.0ms latency)</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Automated grading and timing</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Cryptographic cheating prevention</li>
                    </ul>
                </div>

                <!-- OSCE Block -->
                <div class="glass-panel p-8 rounded-3xl border-t-4 border-t-blue-500">
                    <div class="w-12 h-12 bg-blue-500/20 text-blue-400 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Examiner Rubric Stations</h3>
                    <p class="text-slate-400 leading-relaxed mb-6">
                        Designed for practical procedure stations. Instead of paper forms, examiners use laptops or tablets to objectively grade students performing CPR, vital checks, and clinical duties using pre-set rubrics.
                    </p>
                    <ul class="space-y-3 text-sm text-slate-300 font-medium">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Replaces messy paper assessment sheets</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> One-tap standardized scoring</li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg> Reconciles automatically with CBT scores</li>
                    </ul>
                </div>
            </div>
        </div>

    </main>

    <!-- LOAD THE UNIVERSAL MODALS -->

    <!-- LOAD THE UNIVERSAL MODALS -->
    <?php include __DIR__ . '/partials/modals.php'; ?>

</body>
</html>