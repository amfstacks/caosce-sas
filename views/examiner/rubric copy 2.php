<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Examiner Station</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Include the Global Sync Engine -->
    <!-- <script src="/public/js/sync-engine.js"></script> -->
    
    <style>
        .touch-btn { transition: transform 0.1s; }
        .touch-btn:active { transform: scale(0.95); }
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: transparent; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        /* Smooth scrolling for the whole container */
        .modal-scroll { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="examinerController()" x-cloak>

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 text-white flex-shrink-0 z-20 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-indigo-500/20 p-2 rounded-lg border border-indigo-500/30">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold leading-tight" x-text="station.title || 'Loading Station...'"></h1>
                    <p class="text-xs text-slate-400">Station <span x-text="station.sequence"></span> &bull; <span x-text="station.examiner_name"></span></p>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 bg-slate-800 rounded-full px-3 py-1 border border-slate-700">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="isOnline ? 'bg-green-400' : 'bg-amber-400'"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="isOnline ? 'bg-green-500' : 'bg-amber-500'"></span>
                    </span>
                    <span class="text-xs font-medium text-slate-300" x-text="isOnline ? 'Connected' : 'Offline Mode'"></span>
                </div>
                <button @click="logout" class="text-slate-400 hover:text-white text-sm">Sign Out</button>
            </div>
        </div>
    </header>

    <main class="flex-grow relative overflow-hidden bg-slate-100 flex flex-col">
        
        <!-- ========================================== -->
        <!-- STATE 1: STANDBY / SELECT CANDIDATE        -->
        <!-- ========================================== -->
        <div x-show="!activeStudent" x-transition.opacity class="absolute inset-0 flex flex-col items-center justify-center p-4 overflow-y-auto">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-8 border border-slate-200">
                <div class="text-center mb-8">
                    <div class="mx-auto w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">Next Candidate</h2>
                    <p class="text-slate-500 mt-2">Select the student who has approached the station.</p>
                </div>

                <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 modal-scroll">
                    <div class="relative mb-4">
                        <input type="text" x-model="searchQuery" placeholder="Search by Matric or Name..." class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 shadow-sm text-slate-800">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <template x-for="student in filteredStudents" :key="student.id">
                        <button @click="startExam(student)" 
                                :disabled="student.graded"
                                class="w-full text-left p-4 rounded-xl border transition-all touch-btn flex justify-between items-center"
                                :class="student.graded ? 'bg-slate-50 border-slate-200 opacity-60 cursor-not-allowed' : 'bg-white border-slate-300 hover:border-indigo-500 hover:shadow-md'">
                            <div>
                                <p class="font-bold text-slate-900 text-lg" x-text="student.matric"></p>
                                <p class="text-slate-500 text-sm" x-text="student.name"></p>
                            </div>
                            <div x-show="student.graded" class="flex items-center text-green-600 bg-green-50 px-3 py-1 rounded-full text-xs font-bold">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Graded
                            </div>
                            <div x-show="!student.graded" class="text-indigo-600 bg-indigo-50 px-4 py-2 rounded-lg text-sm font-bold">
                                Select &rarr;
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- STATE 2: ACTIVE GRADING VIEW               -->
        <!-- ========================================== -->
        <div x-show="activeStudent" x-transition.opacity class="absolute inset-0 flex flex-col" style="display: none;">
            
            <div class="bg-white border-b border-slate-200 shadow-sm flex-shrink-0 z-10 p-4 lg:px-8 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <button @click="cancelExam()" class="text-slate-400 hover:text-slate-600 p-2 touch-btn" title="Cancel">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="pl-4 border-l border-slate-200">
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-wide">Currently Evaluating</p>
                        <h2 class="text-xl font-bold text-slate-900 leading-none mt-1" x-text="activeStudent?.matric"></h2>
                        <p class="text-sm text-slate-500" x-text="activeStudent?.name"></p>
                    </div>
                </div>
            </div>

            <!-- Rubric Grid -->
            <div id="rubric-container" class="flex-grow overflow-y-auto p-4 lg:p-8 bg-slate-50 modal-scroll">
                <div class="max-w-5xl mx-auto space-y-4 pb-48"> <!-- Extra bottom padding so last item can scroll up -->
                    
                    <template x-for="(item, index) in rubric" :key="index">
                        <!-- Dynamic ID assigned here for auto-scrolling target -->
                        <div :id="'question-step-' + index" class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 transition-colors duration-300" :class="item.multiplier !== null ? 'border-l-4 border-l-indigo-500 bg-indigo-50/10 opacity-75' : 'border-l-4 border-l-transparent'">
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                                
                                <div class="flex-grow">
                                    <div class="flex items-center justify-between lg:justify-start gap-4 mb-2">
                                        <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-1 rounded-md" x-text="'Step ' + (index + 1)"></span>
                                        <span class="text-slate-500 text-sm font-semibold" x-text="'Question Score: ' + item.score + ' marks'"></span>
                                    </div>
                                    <p class="text-lg text-slate-800 font-medium leading-snug" x-text="item.text"></p>
                                </div>
                                
                                <!-- Fractional Scoring Buttons -->
                                <div class="flex-shrink-0 flex items-center gap-2 bg-slate-100 p-2 rounded-xl border border-slate-200 overflow-x-auto">
                                    <template x-for="fraction in fractions" :key="fraction.val">
                                        <button @click="awardFraction(index, fraction.val)"
                                                class="w-14 h-14 rounded-lg font-bold text-lg transition-all touch-btn flex items-center justify-center flex-shrink-0"
                                                :class="item.multiplier === fraction.val ? 'bg-indigo-600 text-white shadow-md ring-2 ring-indigo-300 ring-offset-1' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-50'"
                                                x-html="fraction.label">
                                        </button>
                                    </template>
                                </div>

                                <!-- Earned Points Display -->
                                <div class="hidden lg:flex flex-col items-center justify-center w-24 border-l border-slate-200 pl-4">
                                    <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Earned</span>
                                    <span class="text-2xl font-black" :class="item.multiplier !== null ? 'text-indigo-600' : 'text-slate-300'" x-text="item.multiplier !== null ? (item.score * item.multiplier) : '-'"></span>
                                </div>

                            </div>
                        </div>
                    </template>
                    
                </div>
            </div>

            <!-- Sticky Submission Footer -->
            <div class="absolute bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-[0_-10px_20px_rgba(0,0,0,0.05)] p-4 lg:px-8 flex justify-between items-center z-20">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase">Calculated Total Score</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl font-black text-indigo-600" x-text="totalScore"></span>
                        <span class="text-lg font-bold text-slate-400" x-text="'/ ' + maxPossibleScore"></span>
                    </div>
                </div>
                
                <button @click="submitGrade()" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg touch-btn flex items-center gap-3 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!isRubricComplete">
                    <span x-text="isRubricComplete ? 'Finalize & Submit Score' : 'Complete All Steps to Submit'"></span>
                    <svg x-show="isRubricComplete" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>
            </div>

        </div>
    </main>

    <!-- Global Toast Notification -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-start justify-center px-4 py-6 z-[60]">
        <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto overflow-hidden rounded-full bg-slate-900 shadow-lg px-6 py-3 flex items-center" style="display: none;">
            <svg class="h-5 w-5 text-green-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm font-bold text-white" x-text="toast.message"></p>
        </div>
    </div>

    <script>
        function examinerController() {
            return {
                isOnline: navigator.onLine,
                toast: { visible: false, message: '' },
                
                station: {},
                students: [],
                searchQuery: '',
                
                activeStudent: null,
                rubric: [],

                // Fractional Multiplier Definition using HTML strict formatting
                fractions: [
                    { label: '0', val: 0 },
                    { label: '<sup>1</sup>&frasl;<sub>4</sub>', val: 0.25 },
                    { label: '<sup>1</sup>&frasl;<sub>2</sub>', val: 0.5 },
                    { label: '<sup>3</sup>&frasl;<sub>4</sub>', val: 0.75 },
                    { label: '1', val: 1 }
                ],

                init() {
                    window.addEventListener('online', () => this.isOnline = true);
                    window.addEventListener('offline', () => this.isOnline = false);
                    this.loadOfflinePayload();
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

                loadOfflinePayload() {
                    this.station = {
                        id: 'st-1',
                        sequence: 1,
                        title: 'Comprehensive Intravenous Cannulation Procedure',
                        examiner_name: 'Dr. Sarah Samson',
                        questions: [
                            { id: 101, text: 'Introduces self to the patient, confirms patient identity using two identifiers, and explains the procedure clearly.', score: 2 },
                            { id: 102, text: 'Obtains informed verbal consent from the patient before proceeding.', score: 1 },
                            { id: 103, text: 'Washes hands thoroughly following WHO guidelines and dons non-sterile gloves.', score: 2 },
                            { id: 104, text: 'Assembles all necessary equipment (cannula, alcohol wipes, tourniquet, flush, dressing) on a clean tray.', score: 2 },
                            { id: 105, text: 'Applies tourniquet correctly (not too tight) 5-10cm above the intended insertion site.', score: 2 },
                            { id: 106, text: 'Identifies a suitable, prominent vein by palpation.', score: 2 },
                            { id: 107, text: 'Cleans the insertion site with an alcohol swab using 30 seconds of friction and allows it to dry completely.', score: 3 },
                            { id: 108, text: 'Anchors the vein by gently pulling the skin taut below the insertion site.', score: 2 },
                            { id: 109, text: 'Inserts the cannula bevel-up at a 15-30 degree angle and observes for primary flashback of blood.', score: 5 },
                            { id: 110, text: 'Lowers the angle, advances the catheter smoothly over the needle into the vein, and observes secondary flashback.', score: 4 },
                            { id: 111, text: 'Releases the tourniquet before completely removing the needle to prevent bleeding.', score: 3 },
                            { id: 112, text: 'Disposes of the sharp needle immediately into the designated sharps bin and applies the sterile dressing.', score: 2 }
                        ]
                    };

                    this.students = [
                        { id: 1, matric: 'NS/2026/001', name: 'Ayomide Balogun', graded: false },
                        { id: 2, matric: 'NS/2026/002', name: 'Chioma Eze', graded: false },
                        { id: 3, matric: 'NS/2026/003', name: 'Obinna Okafor', graded: false }
                    ];
                },

                get filteredStudents() {
                    if (!this.searchQuery) return this.students;
                    let q = this.searchQuery.toLowerCase();
                    return this.students.filter(s => s.matric.toLowerCase().includes(q) || s.name.toLowerCase().includes(q));
                },

                startExam(student) {
                    this.activeStudent = student;
                    this.rubric = this.station.questions.map(q => ({
                        ...q,
                        multiplier: null // null means the examiner has not tapped a button yet
                    }));
                    
                    // Reset scroll position to top when a new exam starts
                    setTimeout(() => {
                        let container = document.getElementById('rubric-container');
                        if(container) container.scrollTop = 0;
                    }, 50);
                },

                cancelExam() {
                    if(confirm('Cancel grading for this student? All currently entered marks will be lost.')) {
                        this.activeStudent = null;
                    }
                },

                // Apply the tapped fraction and trigger auto-scroll
                awardFraction(index, fractionValue) {
                    this.rubric[index].multiplier = fractionValue;

                    // Auto-scroll logic: If there is a next question, scroll to it smoothly
                    if (index < this.rubric.length - 1) {
                        // 300ms delay gives the UI time to show the button press before moving the screen
                        setTimeout(() => {
                            let nextStepEl = document.getElementById('question-step-' + (index + 1));
                            if (nextStepEl) {
                                nextStepEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, 300); 
                    }
                },

                get totalScore() {
                    let total = this.rubric.reduce((sum, item) => {
                        let earned = item.multiplier !== null ? (item.score * item.multiplier) : 0;
                        return sum + earned;
                    }, 0);
                    return parseFloat(total.toFixed(2));
                },

                get maxPossibleScore() {
                    return this.rubric.reduce((total, item) => total + item.score, 0);
                },

                get isRubricComplete() {
                    return this.rubric.length > 0 && this.rubric.every(item => item.multiplier !== null);
                },

                submitGrade() {
                    if (!this.isRubricComplete) return;

                    let resultPayload = {
                        student_id: this.activeStudent.id,
                        matric: this.activeStudent.matric,
                        station_id: this.station.id,
                        total_score: this.totalScore,
                        max_possible: this.maxPossibleScore,
                        breakdown: this.rubric.map(r => ({ id: r.id, earned: (r.score * r.multiplier) })),
                        timestamp: Date.now()
                    };

                    // Push to the offline syncing queue
                    let syncQueue = JSON.parse(localStorage.getItem('caosce_sync_queue') || '[]');
                    syncQueue.push({
                        endpoint: '/api/sync/procedure-score',
                        payload: resultPayload,
                        timestamp: Date.now()
                    });
                    localStorage.setItem('caosce_sync_queue', JSON.stringify(syncQueue));

                    // Immediately tell the global background worker to attempt a sync right now
                    if (typeof window.CAOSCE_BackgroundSync === 'function') {
                        window.CAOSCE_BackgroundSync();
                    }

                    // Reset UI
                    let studentIndex = this.students.findIndex(s => s.id === this.activeStudent.id);
                    if (studentIndex > -1) this.students[studentIndex].graded = true;

                    this.activeStudent = null;
                    this.showToast(`Score securely saved and queued for sync.`);
                },

                logout() {
                    if(confirm("Sign out of the Examiner Interface?")) {
                        window.location.href = '/<?php echo CURRENT_TENANT_SLUG ?? "global"; ?>/login';
                    }
                }
            }
        }
    </script>
</body>
</html>