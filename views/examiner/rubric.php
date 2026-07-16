<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Examiner Station</title>
    <!-- localforage for massive offline database capacity -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .touch-btn { transition: transform 0.1s; }
        .touch-btn:active { transform: scale(0.95); }
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: transparent; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .modal-scroll { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen flex flex-col overflow-hidden" x-data="examinerController()" x-cloak>

    <!-- Preloader -->
    <div x-show="isLoading" class="fixed inset-0 z-[300] bg-slate-900 flex flex-col items-center justify-center p-4">
        <svg class="animate-spin h-10 w-10 text-indigo-500 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <p class="text-white font-medium animate-pulse">Loading Examiner Profile & Roster...</p>
    </div>

    <!-- Top Navigation Bar -->
    <header class="bg-slate-900 text-white flex-shrink-0 z-20 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-indigo-500/20 p-2 rounded-lg border border-indigo-500/30">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold leading-tight" x-text="station.title || 'Loading Station...'"></h1>
                    <p class="text-xs text-slate-400">Station <span x-text="station.sequence"></span> &bull; Evaluator: <span x-text="examiner.name"></span></p>
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
        <div x-show="!activeStudent && !isLoading" x-transition.opacity class="absolute inset-0 flex flex-col items-center justify-center p-4 overflow-y-auto">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-8 border border-slate-200">
                <div class="text-center mb-8">
                    <div class="mx-auto w-16 h-16 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">Candidate Roster</h2>
                    <p class="text-slate-500 mt-2">Select a student to begin grading, or click a graded student to review/edit their score.</p>
                </div>

                <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 modal-scroll">
                    <div class="relative mb-4">
                        <input type="text" x-model="searchQuery" placeholder="Search by Matric or Name..." class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 shadow-sm text-slate-800">
                        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>

                    <template x-for="student in filteredStudents" :key="student.id">
                        <button @click="startExam(student)" 
                                class="w-full text-left p-4 rounded-xl border transition-all touch-btn flex justify-between items-center"
                                :class="{
                                    'bg-green-50 border-green-200': student.grading_status === 'completed',
                                    'bg-amber-50 border-amber-200': student.grading_status === 'partial',
                                    'bg-white border-slate-300 hover:border-indigo-500 hover:shadow-md': student.grading_status === 'unattempted'
                                }">
                            <div>
                                <p class="font-bold text-slate-900 text-lg" x-text="student.matric"></p>
                                <p class="text-slate-500 text-sm" x-text="student.name"></p>
                            </div>
                            
                            <!-- Status Badges -->
                            <div x-show="student.grading_status === 'completed'" class="flex items-center text-green-700 bg-green-200/50 px-3 py-1 rounded-full text-xs font-bold ring-1 ring-green-600/20">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 
                                Graded (Edit)
                            </div>
                            
                            <div x-show="student.grading_status === 'partial'" class="flex items-center text-amber-700 bg-amber-200/50 px-3 py-1 rounded-full text-xs font-bold ring-1 ring-amber-600/20">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Draft (Resume)
                            </div>

                            <div x-show="student.grading_status === 'unattempted'" class="text-indigo-600 bg-indigo-50 px-4 py-2 rounded-lg text-sm font-bold">
                                Grade &rarr;
                            </div>
                        </button>
                    </template>
                    <div x-show="filteredStudents.length === 0" class="text-center py-6 text-slate-500 text-sm">
                        No students found matching your search.
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- STATE 2: ACTIVE GRADING VIEW               -->
        <!-- ========================================== -->
        <div x-show="activeStudent" x-transition.opacity class="absolute inset-0 flex flex-col" style="display: none;">
            
            <div class="bg-white border-b border-slate-200 shadow-sm flex-shrink-0 z-10 p-4 lg:px-8 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <button @click="backToList()" :disabled="isSubmitting" class="text-slate-500 hover:text-slate-800 p-2 touch-btn flex items-center gap-2 disabled:opacity-50 font-bold text-sm bg-slate-100 rounded-lg" title="Back to Roster">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Back to Roster
                    </button>
                    <div class="pl-4 border-l border-slate-200">
                        <div class="flex items-center gap-2">
                            <p class="text-xs font-bold text-indigo-600 uppercase tracking-wide">Evaluating</p>
                            <span x-show="isSavingLocally" class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                            </span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 leading-none mt-1" x-text="activeStudent?.matric"></h2>
                        <p class="text-sm text-slate-500" x-text="activeStudent?.name"></p>
                    </div>
                </div>
            </div>

            <!-- Rubric Grid -->
            <div id="rubric-container" class="flex-grow overflow-y-auto p-4 lg:p-8 bg-slate-50 modal-scroll">
                <div class="max-w-5xl mx-auto space-y-4 pb-48"> 
                    
                    <template x-for="(item, index) in rubric" :key="item.id">
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
                                                :disabled="isSubmitting"
                                                class="w-14 h-14 rounded-lg font-bold text-lg transition-all touch-btn flex items-center justify-center flex-shrink-0 disabled:opacity-50"
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
                        class="px-8 py-4 rounded-xl font-bold text-lg shadow-lg touch-btn flex items-center gap-3 transition-colors disabled:opacity-50"
                        :class="isRubricComplete ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                        :disabled="!isRubricComplete || isSubmitting">
                    <span x-text="isSubmitting ? 'Finalizing Sync...' : (isRubricComplete ? 'Finalize & Submit Score' : 'Complete All Steps to Finalize')"></span>
                    <svg x-show="isRubricComplete && !isSubmitting" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>
            </div>

        </div>
    </main>

    <!-- Global Toast Notification -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-start justify-center px-4 py-6 z-[60]">
        <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto overflow-hidden rounded-full shadow-lg px-6 py-3 flex items-center" :class="toast.type === 'error' ? 'bg-red-900' : 'bg-slate-900'" style="display: none;">
            <svg x-show="toast.type !== 'error'" class="h-5 w-5 text-green-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <svg x-show="toast.type === 'error'" class="h-5 w-5 text-red-400 mr-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
            <p class="text-sm font-bold text-white" x-text="toast.message"></p>
        </div>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? "global"; ?>';

        function examinerController() {
            return {
                isLoading: true,
                isOnline: navigator.onLine,
                toast: { visible: false, message: '', type: 'success' },
                
                examiner: {},
                station: {},
                session_id: '',
                
                rawQuestions: [],
                students: [],
                searchQuery: '',
                
                activeStudent: null,
                rubric: [],
                isSubmitting: false,
                isSavingLocally: false,

                fractions: [
                    { label: '0', val: 0 },
                    { label: '<sup>1</sup>&frasl;<sub>4</sub>', val: 0.25 },
                    { label: '<sup>1</sup>&frasl;<sub>2</sub>', val: 0.5 },
                    { label: '<sup>3</sup>&frasl;<sub>4</sub>', val: 0.75 },
                    { label: '1', val: 1 }
                ],

                async init() {
                    window.addEventListener('online', () => this.isOnline = true);
                    window.addEventListener('offline', () => this.isOnline = false);
                    
                    const authStr = sessionStorage.getItem('caosce_offline_auth');
                    if (!authStr) {
                        alert("Unauthorized. Please log in.");
                        window.location.href = `/${TENANT_SLUG}/login`;
                        return;
                    }
                    const parsedAuth = JSON.parse(authStr);
                    if (parsedAuth.role !== 'examiner') {
                        alert("Access Denied: This station requires Examiner credentials.");
                        window.location.href = `/${TENANT_SLUG}/login`;
                        return;
                    }
                    this.examiner = parsedAuth;

                    await this.loadOfflinePayload();
                },

                getBaseApiUrl() {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return TENANT_SLUG ? `${basePath}/${TENANT_SLUG}` : basePath;
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

                async loadOfflinePayload() {
                    try {
                        const payload = await localforage.getItem('caosce_offline_data');
                        if (!payload) throw new Error("Offline database is empty.");

                        this.station = payload.station_settings;
                        this.session_id = payload.session_id;
                        this.rawQuestions = payload.questions;

                        await this.refreshStudentListStatus(payload.students);
                        this.isLoading = false;

                    } catch (error) {
                        console.error("Failed to load procedure data:", error);
                        alert("Critical Error: Missing offline data. Please ask the administrator to bind this device.");
                        window.location.href = `/${TENANT_SLUG}/login`;
                    }
                },

                // Recalculates who is graded, partially graded, or untouched
                async refreshStudentListStatus(baseStudents) {
                    const existingRecords = await localforage.getItem('caosce_exam_records') || [];
                    
                    // If we pass no arguments, just use the existing array
                    let sourceArray = baseStudents || this.students;
                    
                    this.students = sourceArray.map(s => {
                        let record = existingRecords.find(r => r.student_id === s.id && r.station_id === this.station.id);
                        let status = 'unattempted';
                        if (record) {
                            status = record.completion_status === 'completed' ? 'completed' : 'partial';
                        }
                        return {
                            id: s.id,
                            matric: s.matric_number || s.matric,
                            name: s.full_name || s.name,
                            grading_status: status
                        };
                    });
                },

                get filteredStudents() {
                    if (!this.searchQuery) return this.students;
                    let q = this.searchQuery.toLowerCase();
                    return this.students.filter(s => s.matric.toLowerCase().includes(q) || s.name.toLowerCase().includes(q));
                },

                async startExam(student) {
                    this.activeStudent = student;
                    
                    // Check if they already have saved data in the offline DB (Draft or Completed)
                    const existingRecords = await localforage.getItem('caosce_exam_records') || [];
                    let savedRecord = existingRecords.find(r => r.student_id === student.id && r.station_id === this.station.id);

                    this.rubric = this.rawQuestions.map(q => {
                        let existingMultiplier = null;
                        
                        if (savedRecord && savedRecord.breakdown) {
                            let ans = savedRecord.breakdown.find(b => b.question_id === q.id);
                            if (ans && ans.answer_chosen !== null) {
                                existingMultiplier = parseFloat(ans.answer_chosen);
                            }
                        }

                        return {
                            id: q.id,
                            text: q.question_text,
                            score: parseFloat(q.score) || 1, 
                            multiplier: existingMultiplier
                        };
                    });
                    
                    setTimeout(() => {
                        let container = document.getElementById('rubric-container');
                        if(container) container.scrollTop = 0;
                    }, 50);
                },

                backToList() {
                    this.activeStudent = null;
                },

                async awardFraction(index, fractionValue) {
                    this.rubric[index].multiplier = fractionValue;

                    // 1. Visually Scroll down
                    if (index < this.rubric.length - 1) {
                        setTimeout(() => {
                            let nextStepEl = document.getElementById('question-step-' + (index + 1));
                            if (nextStepEl) {
                                nextStepEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, 300); 
                    }

                    // 2. Instantly Auto-Save to Master Offline Array (as Draft or Final)
                    await this.autoSaveRecord();

                    // 3. Send Single Tick to Server in background (Fire & Forget)
                    this.syncTickOnline(this.rubric[index].id, fractionValue);
                },

                async autoSaveRecord() {
                    this.isSavingLocally = true;
                    const isComplete = this.isRubricComplete;

                    const currentRecord = {
                        record_id: this.generateUUID(), // Will be overwritten below if it already exists
                        student_id: this.activeStudent.id,
                        matric: this.activeStudent.matric,
                        student_name: this.activeStudent.name,
                        session_id: this.session_id,
                        station_id: this.station.id,
                        station_title: this.station.title,
                        total_score: this.totalScore,
                        max_possible: this.maxPossibleScore,
                        completion_status: isComplete ? 'completed' : 'partial', // Tells Admin UI if it's a draft
                        breakdown: this.rubric.map(r => ({ 
                            question_id: r.id, 
                            answer_chosen: r.multiplier !== null ? r.multiplier.toString() : null,
                            earned: r.multiplier !== null ? (r.score * r.multiplier) : 0,
                            is_correct: r.multiplier === 1 
                        })),
                        timestamp: Date.now(),
                        sync_status: 'pending' 
                    };

                    try {
                        let records = await localforage.getItem('caosce_exam_records') || [];
                        let existingIdx = records.findIndex(r => r.student_id === currentRecord.student_id && r.station_id === currentRecord.station_id);
                        
                        if (existingIdx > -1) {
                            // Keep the same record ID to prevent server duplication
                            currentRecord.record_id = records[existingIdx].record_id;
                            records[existingIdx] = currentRecord;
                        } else {
                            records.push(currentRecord);
                        }
                        
                        await localforage.setItem('caosce_exam_records', records);
                        
                        // Update UI status instantly
                        let studentIndex = this.students.findIndex(s => s.id === this.activeStudent.id);
                        if (studentIndex > -1) {
                            this.students[studentIndex].grading_status = isComplete ? 'completed' : 'partial';
                        }
                        
                    } catch(e) {
                        console.error("Auto-save failed", e);
                    } finally {
                        setTimeout(() => { this.isSavingLocally = false; }, 400);
                    }

                    return currentRecord;
                },

                async syncTickOnline(questionId, answer) {
                    if (!navigator.onLine) return;
                    try {
                        await fetch(this.getBaseApiUrl() + '/api/sync/tick', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                student_id: this.activeStudent.id,
                                exam_session_id: this.session_id,
                                question_id: questionId,
                                answer: answer.toString()
                            })
                        });
                    } catch(e) { /* Silent fail */ }
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

                async submitGrade() {
                    if (!this.isRubricComplete || this.isSubmitting) return;
                    this.isSubmitting = true;

                    try {
                        // Ensure final state is saved
                        let finalRecord = await this.autoSaveRecord();

                        this.activeStudent = null;
                        this.searchQuery = '';
                        this.showToast(`Score Finalized & Ready for Master Sync.`);

                        // Attempt final payload sync
                        await this.attemptFinalSync(finalRecord);

                    } catch(e) {
                        console.error(e);
                        this.showToast("Critical Error saving final score.", "error");
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                async attemptFinalSync(record) {
                    if (!navigator.onLine) return;
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/sync/cbt-score', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(record)
                        });
                        let data = await response.json();
                        
                        if (data.success) {
                            let records = await localforage.getItem('caosce_exam_records');
                            let idx = records.findIndex(r => r.record_id === record.record_id);
                            if (idx > -1) {
                                records[idx].sync_status = 'synced';
                                await localforage.setItem('caosce_exam_records', records);
                            }
                        }
                    } catch(e) { console.log("Live sync failed, will remain pending."); }
                },

                logout() {
                    if(confirm("Sign out of the Examiner Interface? You will need your PIN to re-enter.")) {
                        sessionStorage.removeItem('caosce_offline_auth');
                        window.location.href = `/${TENANT_SLUG}/login`;
                    }
                },
                
                generateUUID() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                }
            }
        }
    </script>
</body>
</html>