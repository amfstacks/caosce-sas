<!-- START OF COMPONENT: cbt.php -->
<div class="h-screen w-full flex flex-col overflow-hidden no-select" x-data="cbtController()" @keydown.window="handleKeydown($event)" x-init="initCbt()" x-cloak>

    <!-- Preloader -->
    <div x-show="isLoading" class="absolute inset-0 z-[300] bg-slate-900 flex flex-col items-center justify-center p-4">
        <svg class="animate-spin h-10 w-10 text-blue-500 mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <p class="text-white font-medium animate-pulse">Loading Exam Payload...</p>
    </div>

    <!-- Start Screen -->
    <div x-show="!examStarted && !isLoading" class="absolute inset-0 z-[100] bg-slate-900 flex flex-col items-center justify-center p-4">
        <div class="bg-white p-8 sm:p-12 rounded-2xl shadow-2xl max-w-xl w-full text-center relative overflow-hidden">
            <div x-show="restoredSession" class="absolute top-0 left-0 w-full bg-amber-100 text-amber-800 py-2 text-xs font-bold uppercase tracking-wider">
                Previous Session Recovered
            </div>

            <div class="mx-auto w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-6 mt-4">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Ready to Begin?</h1>
            <p class="text-slate-600 mb-8" x-text="'Welcome, ' + student.name + '. You have ' + examDurationMinutes + ' minutes to complete this module.'"></p>
            
            <button @click="startExamAction()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl text-lg shadow-lg transition-colors flex items-center justify-center gap-3 touch-btn">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                <span x-text="restoredSession ? 'Resume Exam (Fullscreen)' : 'Start Exam (Fullscreen)'"></span>
            </button>
        </div>
    </div>

    <!-- Lockdown Screen -->
    <div x-show="isLocked" style="display: none;" class="absolute inset-0 z-[200] bg-red-900 flex flex-col items-center justify-center p-4 backdrop-blur-md">
        <div class="bg-white p-8 sm:p-12 rounded-2xl shadow-2xl max-w-xl w-full text-center border-t-8 border-red-600">
            <div class="mx-auto w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-6 animate-pulse">
                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h1 class="text-3xl font-black text-slate-900 mb-2 uppercase tracking-wide">Exam Paused</h1>
            <p class="text-slate-600 mb-8 font-medium">You have exited fullscreen mode. For security reasons, your exam environment has been locked and questions are hidden.</p>
            
            <button @click="resumeFullscreen()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-xl text-lg shadow-lg transition-colors flex items-center justify-center gap-3 touch-btn">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                Return to Fullscreen & Resume
            </button>
        </div>
    </div>

    <!-- Header -->
    <header class="bg-slate-900 text-white flex-shrink-0 z-20 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-blue-500/20 p-2 rounded-lg border border-blue-500/30">
                    <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold leading-tight" x-text="station.title || 'Computer Based Test'"></h1>
                    <p class="text-xs text-slate-400">Station <span x-text="station.sequence"></span> &bull; <span x-text="station.score_per_question + ' marks per question'"></span></p>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-white" x-text="student.name"></p>
                    <p class="text-xs text-blue-300 font-mono" x-text="student.matric"></p>
                </div>
                
                <div class="flex items-center gap-2 bg-slate-800 rounded-lg px-4 py-2 border border-slate-700 shadow-inner">
                    <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xl font-mono font-bold tracking-wider" :class="timeRemaining <= 60 ? 'text-red-400 animate-pulse' : 'text-slate-100'" x-text="formattedTime"></span>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow relative overflow-hidden flex flex-col md:flex-row">
        
        <!-- Sidebar Navigator -->
        <aside class="w-full md:w-64 bg-white border-r border-slate-200 flex-shrink-0 flex flex-col shadow-[4px_0_10px_rgba(0,0,0,0.02)] z-10 hidden md:flex">
            <div class="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <div>
                    <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Navigator</h3>
                    <p class="text-xs text-slate-500 mt-1"><span x-text="answeredCount"></span> of <span x-text="questions.length"></span> answered</p>
                </div>
                <div title="Saving progress locally..." class="w-2 h-2 rounded-full" :class="isSaving ? 'bg-amber-400 animate-pulse' : 'bg-green-400'"></div>
            </div>
            
            <div class="flex-grow overflow-y-auto p-4">
                <div class="grid grid-cols-4 gap-2 mb-8">
                    <template x-for="(q, idx) in questions" :key="q.id">
                        <button @click="jumpToQuestion(idx)" 
                                class="h-10 rounded-md font-bold text-sm flex items-center justify-center transition-all touch-btn border"
                                :class="{
                                    'bg-blue-600 text-white border-blue-700 shadow-md ring-2 ring-blue-300 ring-offset-1': currentIndex === idx,
                                    'bg-green-100 text-green-700 border-green-300': q.selected && currentIndex !== idx,
                                    'bg-white text-slate-500 border-slate-200 hover:bg-slate-50': !q.selected && currentIndex !== idx
                                }">
                            <span x-text="idx + 1"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="mt-auto border-t border-slate-200 bg-slate-50 p-4">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-medium text-slate-700">Auto-Advance</span>
                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <!-- Note: Since we removed the <style> block, we must rely on inline styling or ensure the toggle CSS is in master.php -->
                        <input type="checkbox" name="toggle" id="autoNextToggle" x-model="autoNext" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer" style="right: 0; z-index: 1; border-color: #e2e8f0; transition: all 0.3s;"/>
                        <label for="autoNextToggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-300 cursor-pointer" style="width: 3rem; height: 1.5rem; transition: all 0.3s;"></label>
                    </div>
                </div>

                <div class="text-xs text-slate-500 bg-slate-200/50 p-3 rounded-lg border border-slate-200">
                    <p class="font-bold text-slate-700 mb-2 uppercase tracking-wider">Keyboard Shortcuts</p>
                    <ul class="space-y-1">
                        <li><kbd class="bg-white border border-slate-300 rounded px-1 font-mono">A-D</kbd> Select Option</li>
                        <li><kbd class="bg-white border border-slate-300 rounded px-1 font-mono">N</kbd> Next Question</li>
                        <li><kbd class="bg-white border border-slate-300 rounded px-1 font-mono">P</kbd> Previous Question</li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Question Area -->
        <div class="flex-grow flex flex-col relative overflow-hidden bg-slate-50/50">
            
            <div class="h-1 bg-slate-200 w-full md:hidden">
                <div class="h-full bg-blue-500 transition-all duration-300" :style="'width: ' + ((answeredCount / questions.length) * 100) + '%'"></div>
            </div>

            <div class="flex-grow overflow-y-auto p-4 sm:p-8 flex items-center justify-center">
                <div class="w-full max-w-3xl">
                    <template x-if="currentQuestion && examStarted && !isLocked">
                        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden transform transition-all duration-300">
                            
                            <div class="p-6 sm:p-10 border-b border-slate-100 bg-slate-50 flex justify-between items-start">
                                <div>
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-700/10 mb-4" x-text="'Question ' + (currentIndex + 1) + ' of ' + questions.length"></span>
                                    <h2 class="text-2xl sm:text-3xl font-medium text-slate-900 leading-snug whitespace-pre-wrap" x-text="currentQuestion.question_text"></h2>
                                </div>
                            </div>
                            
                            <div class="p-6 sm:p-10 bg-white">
                                <div class="space-y-4">
                                    <template x-for="opt in ['A', 'B', 'C', 'D']" :key="opt">
                                        <button x-show="currentQuestion['opt_' + opt.toLowerCase()]"
                                                @click="selectOption(opt)" 
                                                class="w-full flex items-center p-4 rounded-xl border-2 text-left transition-all touch-btn group focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                                :class="currentQuestion.selected === opt ? 'border-blue-500 bg-blue-50 shadow-sm' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50'">
                                            
                                            <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center font-bold text-lg mr-4 transition-colors"
                                                 :class="currentQuestion.selected === opt ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-100 group-hover:text-blue-600'">
                                                <span x-text="opt"></span>
                                            </div>
                                            
                                            <span class="text-lg font-medium text-slate-800" x-text="currentQuestion['opt_' + opt.toLowerCase()]"></span>
                                            
                                            <div class="ml-auto" x-show="currentQuestion.selected === opt">
                                                <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-white border-t border-slate-200 p-4 sm:px-8 flex justify-between items-center shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
                <button @click="prevQuestion" 
                        class="px-6 py-3 rounded-lg font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors disabled:opacity-50 touch-btn flex items-center gap-2"
                        :disabled="currentIndex === 0 || !examStarted || isLocked">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Previous <span class="hidden sm:inline text-xs text-slate-400 font-mono ml-1">[P]</span>
                </button>

                <button x-show="currentIndex < questions.length - 1" 
                        @click="nextQuestion" 
                        class="px-6 py-3 rounded-lg font-bold text-white bg-slate-800 hover:bg-slate-900 transition-colors touch-btn flex items-center gap-2"
                        :disabled="!examStarted || isLocked">
                    Next <span class="hidden sm:inline text-xs text-slate-400 font-mono ml-1">[N]</span>
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>

                <button x-show="currentIndex === questions.length - 1" 
                        @click="confirmSubmit" 
                        class="px-8 py-3 rounded-lg font-bold text-white bg-green-600 hover:bg-green-700 transition-colors shadow-lg touch-btn flex items-center gap-2 animate-bounce-once"
                        :disabled="!examStarted || isLocked || isSubmitting">
                    Finish Exam
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        </div>
    </main>

    <!-- Submission Modal -->
    <div x-show="showConfirmModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white px-4 pb-4 pt-5 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md sm:p-6">
                    <div>
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full" :class="answeredCount === questions.length ? 'bg-green-100' : 'bg-amber-100'">
                            <svg x-show="answeredCount === questions.length" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <svg x-show="answeredCount < questions.length" class="h-8 w-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-xl font-bold leading-6 text-slate-900" id="modal-title">Submit Examination?</h3>
                            <div class="mt-4">
                                <p class="text-sm text-slate-500 mb-2">You have answered <span class="font-bold text-slate-800" x-text="answeredCount"></span> out of <span class="font-bold text-slate-800" x-text="questions.length"></span> questions.</p>
                                
                                <div x-show="answeredCount < questions.length" class="bg-amber-50 border border-amber-200 rounded-md p-3 mt-2">
                                    <p class="text-sm font-medium text-amber-800">Warning: You have unanswered questions. Are you sure you want to finish?</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 sm:mt-8 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" @click="finalizeExam" :disabled="isSubmitting" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-3 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-500 sm:w-auto focus:outline-none disabled:opacity-50">
                            <span x-text="isSubmitting ? 'Saving Offline Record...' : 'Yes, Submit Final Answers'"></span>
                        </button>
                        <button type="button" @click="showConfirmModal = false" :disabled="isSubmitting" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-3 text-sm font-bold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto focus:outline-none disabled:opacity-50">
                            Return to Exam
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Ensure TENANT_SLUG is declared globally outside if not already present in master
    if(typeof TENANT_SLUG === 'undefined') {
        window.TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? ""; ?>';
    }

    function cbtController() {
        return {
            isLoading: true,
            station: {},
            student: {},
            questions: [],
            
            examStarted: false,
            autoNext: true,
            isLocked: false,     
            isSubmitting: false, 
            isSaving: false,
            
            currentIndex: 0,
            showConfirmModal: false,
            
            examDurationMinutes: 15,
            timeRemaining: 0,
            timerInterval: null,
            restoredSession: false,
            session_id: '',

            async initCbt() {
                const authStr = sessionStorage.getItem('caosce_offline_auth');
                if (!authStr) {
                    this.$dispatch('navigate', 'login');
                    return;
                }
                this.student = JSON.parse(authStr);

                await this.loadOfflinePayload();
                
                // Add specific event listener for this component
                document.addEventListener('fullscreenchange', this.handleFullscreenChange.bind(this));
            },

            handleFullscreenChange() {
                // Ensure we only lock if this component is active
                if (this.examStarted && !this.isSubmitting && !document.fullscreenElement) {
                    this.isLocked = true;
                }
            },

            getBaseApiUrl() {
                let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                return window.TENANT_SLUG ? `${basePath}/${window.TENANT_SLUG}` : basePath;
            },

            async loadOfflinePayload() {
                try {
                    const payload = await localforage.getItem('caosce_offline_data');
                    if (!payload) throw new Error("Offline database is empty.");

                    this.station = payload.station_settings;
                    this.session_id = payload.session_id;
                    this.examDurationMinutes = this.station.time_limit_minutes || 15;
                    
                    const progressKey = `caosce_progress_${this.student.id}_${this.station.id}`;
                    const savedProgressStr = localStorage.getItem(progressKey);
                    
                    if (savedProgressStr) {
                        const savedProgress = JSON.parse(savedProgressStr);
                        this.questions = savedProgress.questions;
                        this.timeRemaining = savedProgress.timeRemaining;
                        this.restoredSession = true;
                    } else {
                        this.questions = payload.questions.map(q => ({ ...q, selected: null }));
                        this.timeRemaining = this.examDurationMinutes * 60;
                    }

                    this.isLoading = false;

                } catch (error) {
                    console.error("Failed to load exam data:", error);
                    alert("Critical Error: Missing offline exam data.");
                    this.$dispatch('navigate', 'login');
                }
            },

            startExamAction() {
                this.examStarted = true;
                this.startTimer();
                
                let elem = document.documentElement;
                if (elem.requestFullscreen) {
                    elem.requestFullscreen().catch(err => console.log("Fullscreen request failed:", err));
                }
            },

            resumeFullscreen() {
                let elem = document.documentElement;
                if (elem.requestFullscreen) {
                    elem.requestFullscreen().then(() => {
                        this.isLocked = false;
                    }).catch(err => {
                        alert("Cannot resume. Please ensure your browser allows fullscreen mode.");
                    });
                }
            },

            handleKeydown(e) {
                if (!this.examStarted || this.showConfirmModal || this.isLocked) return;
                const key = e.key.toUpperCase();
                if (key === 'N') { this.nextQuestion(); e.preventDefault(); }
                if (key === 'P') { this.prevQuestion(); e.preventDefault(); }
                if (['A', 'B', 'C', 'D'].includes(key)) { this.selectOption(key); e.preventDefault(); }
            },

            get currentQuestion() { return this.questions[this.currentIndex]; },
            get answeredCount() { return this.questions.filter(q => q.selected !== null).length; },

            jumpToQuestion(index) { this.currentIndex = index; },
            nextQuestion() { if (this.currentIndex < this.questions.length - 1) this.currentIndex++; },
            prevQuestion() { if (this.currentIndex > 0) this.currentIndex--; },

            async selectOption(opt) {
                this.questions[this.currentIndex].selected = opt;
                this.autoSaveProgress();
                this.syncTickOnline(this.questions[this.currentIndex].id, opt);

                if (this.autoNext) {
                    setTimeout(() => {
                        if (this.currentIndex < this.questions.length - 1) {
                            this.nextQuestion();
                        } else if (this.answeredCount === this.questions.length) {
                            this.confirmSubmit();
                        }
                    }, 350);
                }
            },

            autoSaveProgress() {
                this.isSaving = true;
                const progressKey = `caosce_progress_${this.student.id}_${this.station.id}`;
                const progressData = {
                    questions: this.questions,
                    timeRemaining: this.timeRemaining,
                    last_saved: Date.now()
                };
                localStorage.setItem(progressKey, JSON.stringify(progressData));
                setTimeout(() => { this.isSaving = false; }, 500);
            },

            async syncTickOnline(questionId, answer) {
                if (!navigator.onLine) return;
                try {
                    await fetch(this.getBaseApiUrl() + '/api/sync/tick', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            student_id: this.student.id,
                            exam_session_id: this.session_id,
                            question_id: questionId,
                            answer: answer
                        })
                    });
                } catch(e) {}
            },

            confirmSubmit() { this.showConfirmModal = true; },

            async finalizeExam() {
                this.stopTimer();
                this.isSubmitting = true;
                
                // Remove fullscreen listener to prevent locking on exit
                document.removeEventListener('fullscreenchange', this.handleFullscreenChange);
                
                if (document.fullscreenElement) {
                    document.exitFullscreen().catch(err => console.log(err));
                }

                let calculatedScore = 0;
                let breakdown = this.questions.map(q => {
                    let scoreMultiplier = parseFloat(q.score) || 1; 
                    let isCorrect = (q.selected === q.correct_answer);
                    let earned = isCorrect ? scoreMultiplier : 0;
                    calculatedScore += earned;
                    
                    return { question_id: q.id, answer_chosen: q.selected, earned: earned, is_correct: isCorrect };
                });

                let maxPossible = this.questions.reduce((sum, q) => sum + (parseFloat(q.score) || 1), 0);

                const finalRecord = {
                    record_id: this.generateUUID(),
                    student_id: this.student.id,
                    matric: this.student.matric,
                    student_name: this.student.name,
                    session_id: this.session_id,
                    station_id: this.station.id,
                    station_title: this.station.title,
                    total_score: calculatedScore,
                    max_possible: maxPossible,
                    breakdown: breakdown,
                    timestamp: Date.now(),
                    sync_status: 'pending' 
                };

                try {
                    let records = await localforage.getItem('caosce_exam_records') || [];
                    records = records.filter(r => !(r.student_id === finalRecord.student_id && r.station_id === finalRecord.station_id));
                    records.push(finalRecord);
                    await localforage.setItem('caosce_exam_records', records);

                    localStorage.removeItem(`caosce_progress_${this.student.id}_${this.station.id}`);
                    sessionStorage.removeItem('caosce_offline_auth');

                    await this.attemptFinalSync(finalRecord);

                    // Switch back to login screen smoothly
                    this.$dispatch('navigate', 'login');

                } catch(e) {
                    console.error(e);
                    alert("Critical Error saving exam. Do NOT close browser. Call Administrator immediately.");
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
                } catch(e) { console.log("Final sync failed. Will remain pending in offline DB."); }
            },

            startTimer() {
                this.timerInterval = setInterval(() => {
                    if (this.timeRemaining > 0) {
                        this.timeRemaining--;
                        if(this.timeRemaining % 30 === 0) this.autoSaveProgress();
                    } else {
                        this.stopTimer();
                        this.showConfirmModal = false;
                        alert("Time is up! Your exam is being automatically submitted.");
                        this.finalizeExam();
                    }
                }, 1000);
            },

            stopTimer() { clearInterval(this.timerInterval); },
            
            get formattedTime() {
                let m = Math.floor(this.timeRemaining / 60).toString().padStart(2, '0');
                let s = (this.timeRemaining % 60).toString().padStart(2, '0');
                return `${m}:${s}`;
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
<!-- END OF COMPONENT: cbt.php -->