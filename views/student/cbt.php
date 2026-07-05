<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CAOSCE - CBT Examination</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="/assets/js/sync_manager.js"></script>
    <script src="/assets/js/exam_store.js"></script>
    <link rel="stylesheet" href="/assets/css/exam.css">
    <style>
        /* UI Lockdown */
        body { user-select: none; }
        .fullscreen-lock { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #fff; overflow-y: auto; z-index: 9999; }
    </style>
</head>
<body>

<div x-data="cbtInterface()" x-init="initTimer()" class="fullscreen-lock">
    <template x-if="$store.exam.sessionData">
        <div class="exam-wrapper">
            
            <!-- Sticky Header with Timer -->
            <header class="cbt-header">
                <div>
                    <h2 x-text="$store.exam.sessionData.station_title"></h2>
                    <p>Candidate: <span x-text="$store.exam.target.matric_number + ' - ' + $store.exam.target.full_name"></span></p>
                </div>
                <div class="timer-display" :class="{'danger': timeRemaining <= 60}">
                    <h2>Time Remaining: <span x-text="formattedTime"></span></h2>
                </div>
            </header>

            <main class="question-container">
                <template x-for="(question, index) in $store.exam.sessionData.evaluation_content" :key="question.id">
                    <div class="question-card">
                        <h3><span x-text="(index + 1) + '. '"></span><span x-text="question.question_text"></span></h3>
                        <p class="marks" x-text="'[' + question.marks + ' Marks]'"></p>
                        
                        <div class="options-list">
                            <template x-for="option in question.options" :key="option.id">
                                <label class="option-label">
                                    <input type="radio" 
                                           :name="'q_' + question.id" 
                                           :value="option.id"
                                           :checked="$store.exam.currentAnswers[question.id] === option.id"
                                           @change="$store.exam.logAction(question.id, option.id)">
                                    <span x-text="option.option_text"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </main>

            <footer class="cbt-footer">
                <button @click="manualSubmit" class="btn-submit">Submit Station Exam</button>
            </footer>

        </div>
    </template>
</div>

<script>
function cbtInterface() {
    return {
        timeRemaining: 0,
        timerInterval: null,

        initTimer() {
            // Wait for Alpine store to populate
            let checkStore = setInterval(() => {
                let session = Alpine.store('exam').sessionData;
                if (session) {
                    clearInterval(checkStore);
                    
                    // Check if timer state already exists in Black Box
                    let savedState = JSON.parse(localStorage.getItem('caosce_timer_state'));
                    if (savedState && savedState.station_id === session.station_id) {
                        this.timeRemaining = savedState.time_remaining;
                    } else {
                        this.timeRemaining = session.time_limit_minutes * 60;
                    }
                    
                    this.startCountdown();
                }
            }, 100);
        },

        get formattedTime() {
            let minutes = Math.floor(this.timeRemaining / 60);
            let seconds = this.timeRemaining % 60;
            return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        },

        startCountdown() {
            this.timerInterval = setInterval(() => {
                if (this.timeRemaining > 0) {
                    this.timeRemaining--;
                    // Persist timer state to survive accidental refreshes
                    localStorage.setItem('caosce_timer_state', JSON.stringify({
                        station_id: Alpine.store('exam').sessionData.station_id,
                        time_remaining: this.timeRemaining
                    }));
                } else {
                    clearInterval(this.timerInterval);
                    this.autoSubmit();
                }
            }, 1000);
        },

        autoSubmit() {
            alert("Time is up! Your answers are being submitted automatically. Please move to the transit area.");
            this.processSubmission();
        },

        manualSubmit() {
            if(confirm("Are you sure you want to submit? You cannot undo this action.")) {
                clearInterval(this.timerInterval);
                this.processSubmission();
            }
        },

        processSubmission() {
            // Calculate final score purely client-side based on the payload (or let server do it on sync)
            SyncManager.safePost('/api/sync/finalize-cbt', {
                student_id: Alpine.store('exam').target.id,
                exam_session_id: Alpine.store('exam').sessionData.id,
                station_id: Alpine.store('exam').sessionData.station_id,
                answers: Alpine.store('exam').currentAnswers
            });
            
            // Clear timer state and redirect to a safe "Standby" screen
            localStorage.removeItem('caosce_timer_state');
            window.location.href = '/student/procedure_standby?completed=true';
        }
    }
}
</script>
</body>
</html>