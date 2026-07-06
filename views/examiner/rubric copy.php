<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Examiner Station</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Hide number input arrows for clean partial marking */
        input[type="number"]::-webkit-inner-spin-button, 
        input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        input[type="number"] { -moz-appearance: textfield; }
        
        .touch-btn { transition: transform 0.1s; }
        .touch-btn:active { transform: scale(0.95); }
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
                <!-- Offline Badge Indicator -->
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

    <!-- Main Content Area -->
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
                    <!-- Search Filter -->
                    <div class="relative mb-4">
                        <input type="text" x-model="searchQuery" placeholder="Search by Matric or Name..." class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm text-slate-800">
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
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Graded
                            </div>
                            <div x-show="!student.graded" class="text-indigo-600 bg-indigo-50 px-4 py-2 rounded-lg text-sm font-bold">
                                Select &rarr;
                            </div>
                        </button>
                    </template>
                    
                    <div x-show="filteredStudents.length === 0" class="text-center py-6 text-slate-500">
                        No matching candidates found.
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- STATE 2: ACTIVE GRADING VIEW               -->
        <!-- ========================================== -->
        <div x-show="activeStudent" x-transition.opacity class="absolute inset-0 flex flex-col" style="display: none;">
            
            <!-- Context Header (Student & Timer) -->
            <div class="bg-white border-b border-slate-200 shadow-sm flex-shrink-0 z-10 p-4 lg:px-8 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <button @click="cancelExam()" class="text-slate-400 hover:text-slate-600 p-2 touch-btn" title="Cancel and go back">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="pl-4 border-l border-slate-200">
                        <p class="text-xs font-bold text-indigo-600 uppercase tracking-wide">Currently Evaluating</p>
                        <h2 class="text-xl font-bold text-slate-900 leading-none mt-1" x-text="activeStudent?.matric"></h2>
                        <p class="text-sm text-slate-500" x-text="activeStudent?.name"></p>
                    </div>
                </div>
                
                <!-- Timer -->
                <div class="flex items-center gap-3 bg-slate-100 rounded-lg p-3 border border-slate-200">
                    <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-2xl font-mono font-bold tracking-wider" :class="timeRemaining <= 60 ? 'text-red-600' : 'text-slate-700'" x-text="formattedTime"></span>
                </div>
            </div>

            <!-- Rubric Scrollable Area -->
            <div class="flex-grow overflow-y-auto p-4 lg:p-8 bg-slate-50 modal-scroll">
                <div class="max-w-4xl mx-auto space-y-4 pb-32">
                    
                    <template x-for="(item, index) in rubric" :key="index">
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 transition-colors" :class="item.awarded !== null ? 'border-l-4 border-l-indigo-500' : ''">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                
                                <!-- Question Text -->
                                <div class="flex-grow">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="bg-slate-100 text-slate-600 text-xs font-bold px-2 py-1 rounded-md" x-text="'Step ' + (index + 1)"></span>
                                        <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-2 py-1 rounded-md" x-text="'Max: ' + item.score + ' pts'"></span>
                                    </div>
                                    <p class="text-lg text-slate-800 font-medium leading-snug" x-text="item.text"></p>
                                </div>
                                
                                <!-- Scoring Controls -->
                                <div class="flex-shrink-0 flex items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                    <!-- Quick Zero -->
                                    <button @click="awardScore(index, 0)" 
                                            class="w-14 h-14 rounded-lg font-bold text-lg transition-all touch-btn"
                                            :class="item.awarded === 0 ? 'bg-red-500 text-white shadow-inner' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100'">
                                        0
                                    </button>
                                    
                                    <!-- Partial Mark Input -->
                                    <div class="relative w-16 h-14">
                                        <input type="number" 
                                               x-model.number="item.awarded" 
                                               @input="validateScore(index)"
                                               min="0" :max="item.score" 
                                               class="w-full h-full text-center text-xl font-bold rounded-lg border focus:ring-2 focus:ring-indigo-500 outline-none transition-colors"
                                               :class="(item.awarded !== null && item.awarded > 0 && item.awarded < item.score) ? 'bg-amber-100 border-amber-300 text-amber-800' : 'bg-white border-slate-300 text-slate-900'">
                                    </div>

                                    <!-- Quick Full Marks -->
                                    <button @click="awardScore(index, item.score)" 
                                            class="w-14 h-14 rounded-lg font-bold text-lg transition-all touch-btn"
                                            :class="item.awarded === item.score ? 'bg-green-500 text-white shadow-inner' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100'">
                                        <span x-text="item.score"></span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </template>
                    
                </div>
            </div>

            <!-- Sticky Submission Footer -->
            <div class="absolute bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-[0_-10px_20px_rgba(0,0,0,0.05)] p-4 lg:px-8 flex justify-between items-center z-20">
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-500 uppercase">Total Score</span>
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
                
                // Data pulled from Black Box (localStorage)
                station: {},
                students: [],
                searchQuery: '',
                
                // Exam State
                activeStudent: null,
                rubric: [],
                
                // Timer State
                examDurationMinutes: 10, // Admin configured time
                timeRemaining: 0,
                timerInterval: null,

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
                    // In a real app, this parses localStorage.getItem('caosce_offline_payload')
                    // For the preview, we mock the secure payload injected by the Binding Wizard
                    this.station = {
                        id: 'st-1',
                        sequence: 1,
                        title: 'Intravenous Cannulation Procedure',
                        examiner_name: 'Dr. Sarah Samson',
                        questions: [
                            { id: 101, text: 'Introduces self, confirms patient ID, and explains the procedure clearly.', score: 2 },
                            { id: 102, text: 'Washes hands thoroughly and dons non-sterile gloves.', score: 2 },
                            { id: 103, text: 'Applies tourniquet correctly and identifies a suitable vein.', score: 3 },
                            { id: 104, text: 'Cleans the site with alcohol swab (30s friction, allows to dry).', score: 2 },
                            { id: 105, text: 'Inserts cannula at 15-30 degree angle, observing for primary flashback.', score: 5 },
                            { id: 106, text: 'Advances catheter, releases tourniquet, and disposes of sharps safely.', score: 4 }
                        ]
                    };

                    this.students = [
                        { id: 1, matric: 'NS/2026/001', name: 'Ayomide Balogun', graded: false },
                        { id: 2, matric: 'NS/2026/002', name: 'Chioma Eze', graded: false },
                        { id: 3, matric: 'NS/2026/003', name: 'Obinna Okafor', graded: false },
                        { id: 4, matric: 'NS/2026/004', name: 'Fatima Bello', graded: false }
                    ];
                },

                get filteredStudents() {
                    if (!this.searchQuery) return this.students;
                    let q = this.searchQuery.toLowerCase();
                    return this.students.filter(s => 
                        s.matric.toLowerCase().includes(q) || 
                        s.name.toLowerCase().includes(q)
                    );
                },

                startExam(student) {
                    this.activeStudent = student;
                    
                    // Clone the station questions into an active rubric state
                    this.rubric = this.station.questions.map(q => ({
                        ...q,
                        awarded: null // null means not yet graded
                    }));

                    this.startTimer();
                },

                cancelExam() {
                    if(confirm('Cancel grading for this student? All currently entered marks will be lost.')) {
                        this.activeStudent = null;
                        this.stopTimer();
                    }
                },

                // --- Scoring Logic ---

                awardScore(index, value) {
                    this.rubric[index].awarded = value;
                },

                validateScore(index) {
                    let item = this.rubric[index];
                    // Prevent typing numbers higher than max score
                    if (item.awarded > item.score) {
                        item.awarded = item.score;
                    }
                    // Prevent negative numbers
                    if (item.awarded < 0) {
                        item.awarded = 0;
                    }
                },

                get totalScore() {
                    return this.rubric.reduce((total, item) => {
                        return total + (item.awarded || 0);
                    }, 0);
                },

                get maxPossibleScore() {
                    return this.rubric.reduce((total, item) => total + item.score, 0);
                },

                get isRubricComplete() {
                    // Check if every item has an awarded value that is not null
                    return this.rubric.length > 0 && this.rubric.every(item => item.awarded !== null);
                },

                // --- Timer Logic ---

                startTimer() {
                    this.timeRemaining = this.examDurationMinutes * 60;
                    this.timerInterval = setInterval(() => {
                        if (this.timeRemaining > 0) {
                            this.timeRemaining--;
                        } else {
                            this.stopTimer();
                            // In high stakes exams, time up usually auto-submits or locks the UI.
                            alert("Time is up for this station!");
                        }
                    }, 1000);
                },

                stopTimer() {
                    clearInterval(this.timerInterval);
                },

                get formattedTime() {
                    let m = Math.floor(this.timeRemaining / 60).toString().padStart(2, '0');
                    let s = (this.timeRemaining % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                // --- Submission & Offline Sync Logic ---

                submitGrade() {
                    if (!this.isRubricComplete) return;

                    // 1. Build the Data Payload
                    let resultPayload = {
                        student_id: this.activeStudent.id,
                        matric: this.activeStudent.matric,
                        station_id: this.station.id,
                        total_score: this.totalScore,
                        max_possible: this.maxPossibleScore,
                        breakdown: this.rubric.map(r => ({ id: r.id, awarded: r.awarded })),
                        timestamp: Date.now()
                    };

                    // 2. Save to Offline Queue (Black Box)
                    let syncQueue = JSON.parse(localStorage.getItem('caosce_sync_queue') || '[]');
                    syncQueue.push({
                        endpoint: '/api/sync/procedure-score',
                        payload: resultPayload,
                        timestamp: Date.now()
                    });
                    localStorage.setItem('caosce_sync_queue', JSON.stringify(syncQueue));

                    // 3. Mark student as graded so they can't be selected again locally
                    let studentIndex = this.students.findIndex(s => s.id === this.activeStudent.id);
                    if (studentIndex > -1) {
                        this.students[studentIndex].graded = true;
                    }

                    // 4. Reset UI
                    this.stopTimer();
                    this.activeStudent = null;
                    
                    this.showToast(`Score of ${resultPayload.total_score} securely saved and queued for sync.`);
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