<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CAOSCE - Examiner Panel</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="/assets/js/sync_manager.js"></script>
    <script src="/assets/js/exam_store.js"></script>
    <link rel="stylesheet" href="/assets/css/exam.css">
</head>
<body>

<div x-data="examinerInterface()" class="container">
    <!-- Wait for Alpine Store to load the payload from Phase 4 -->
    <template x-if="$store.exam.sessionData">
        <div>
            <header>
                <h1 x-text="$store.exam.sessionData.station_title"></h1>
                <p class="status-indicator" :class="navigator.onLine ? 'online' : 'offline'">
                    <span x-text="navigator.onLine ? '🟢 Online Sync Active' : '🔴 OFFLINE (Saving to Black Box)'"></span>
                </p>
            </header>

            <!-- Step 1: Select the candidate standing in front of them -->
            <div x-show="!activeStudent" class="candidate-selection">
                <h2>Select Next Candidate</h2>
                <select x-model="selectedStudentId">
                    <option value="">-- Choose Candidate by Matric Number --</option>
                    <template x-for="student in $store.exam.target" :key="student.id">
                        <option :value="student.id" x-text="student.matric_number + ' - ' + student.full_name"></option>
                    </template>
                </select>
                <button @click="startAssessment" :disabled="!selectedStudentId">Begin Assessment</button>
            </div>

            <!-- Step 2: The Assessment Rubric -->
            <div x-show="activeStudent" class="rubric-panel">
                <div class="candidate-info">
                    <h3>Assessing: <span x-text="activeStudent?.full_name"></span></h3>
                    <button @click="cancelAssessment" class="btn-danger">Cancel & Pick Another</button>
                </div>

                <table class="rubric-table">
                    <thead>
                        <tr>
                            <th>Step sequence</th>
                            <th>Procedure/Action</th>
                            <th>Max Score</th>
                            <th>Awarded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="rubric in $store.exam.sessionData.evaluation_content" :key="rubric.id">
                            <tr :class="{'critical-step': rubric.is_critical_step}">
                                <td x-text="rubric.sequence_order"></td>
                                <td x-text="rubric.task_description"></td>
                                <td x-text="rubric.max_score"></td>
                                <td>
                                    <!-- Log the score instantly to localStorage and sync queue -->
                                    <input type="number" 
                                           min="0" 
                                           :max="rubric.max_score" 
                                           step="0.5"
                                           x-model.number="$store.exam.currentAnswers[rubric.id]"
                                           @change="$store.exam.logAction(rubric.id, $event.target.value)">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div class="score-summary">
                    <h2>Total Score: <span x-text="calculateTotal()"></span></h2>
                    <button @click="submitFinalScore" class="btn-success">Finalize & Submit Score</button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function examinerInterface() {
    return {
        selectedStudentId: '',
        
        get activeStudent() {
            if (!this.selectedStudentId || !Alpine.store('exam').target) return null;
            return Alpine.store('exam').target.find(s => s.id === this.selectedStudentId);
        },

        startAssessment() {
            // Reset the answers for the new student
            Alpine.store('exam').currentAnswers = {};
            Alpine.store('exam').updateBlackBox();
        },

        cancelAssessment() {
            this.selectedStudentId = '';
            Alpine.store('exam').currentAnswers = {};
        },

        calculateTotal() {
            let total = 0;
            let answers = Alpine.store('exam').currentAnswers;
            for (let key in answers) {
                total += parseFloat(answers[key] || 0);
            }
            return total;
        },

        submitFinalScore() {
            if(confirm('Are you sure you want to finalize this score? This candidate will be marked as completed for this station.')) {
                SyncManager.safePost('/api/sync/finalize-score', {
                    student_id: this.selectedStudentId,
                    exam_session_id: Alpine.store('exam').sessionData.id,
                    station_id: Alpine.store('exam').sessionData.station_id,
                    total_score: this.calculateTotal()
                });
                
                // Remove student from local pending list and reset
                Alpine.store('exam').target = Alpine.store('exam').target.filter(s => s.id !== this.selectedStudentId);
                this.cancelAssessment();
                alert('Score safely recorded.');
            }
        }
    }
}
</script>
</body>
</html>