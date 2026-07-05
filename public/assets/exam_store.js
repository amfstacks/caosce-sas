document.addEventListener('alpine:init', () => {
    Alpine.store('exam', {
        sessionData: null,   // Holds questions, rubrics, timers
        studentData: null,   // Who is taking the exam
        currentAnswers: {},  // The live state of what they have clicked
        blackBoxKey: 'caosce_black_box', // The failsafe key

        async init() {
            // 1. Check if we already have data in the Black Box (e.g., page refreshed mid-exam)
            let savedState = localStorage.getItem(this.blackBoxKey);
            if (savedState) {
                let parsed = JSON.parse(savedState);
                this.sessionData = parsed.session;
                this.studentData = parsed.student;
                this.currentAnswers = parsed.answers;
            } else {
                // 2. Initial Payload Pull: Fetch from PHP API
                await this.fetchPayload();
            }
        },

        async fetchPayload() {
            // Grab the device signature from phase 2
            let deviceSig = localStorage.getItem('caosce_device_signature');
            
            try {
                let res = await fetch('/api/exam/payload', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ device_signature: deviceSig })
                });
                let data = await res.json();
                
                if (data.success) {
                    this.sessionData = data.payload.session;
                    this.studentData = data.payload.student;
                    this.updateBlackBox(); // Save initial state instantly
                }
            } catch (e) {
                alert("Critical Error: Unable to fetch exam payload. Check internet connection before starting.");
            }
        },

        // 3. The Local Failsafe (Runs on EVERY click)
        updateBlackBox() {
            const stateToSave = {
                session: this.sessionData,
                student: this.studentData,
                answers: this.currentAnswers,
                last_updated: new Date().getTime()
            };
            // Persistently log to the physical laptop's local storage
            localStorage.setItem(this.blackBoxKey, JSON.stringify(stateToSave));
        },

        // 4. Handle a candidate answering a CBT question or Examiner ticking a rubric
        logAction(questionId, selectedValue) {
            // Update Alpine State
            this.currentAnswers[questionId] = selectedValue;
            
            // Save to Black Box instantly so data is never lost
            this.updateBlackBox();

            // Attempt to sync to the Shared Host via our custom Retry Loop
            SyncManager.safePost('/api/sync/log-action', {
                student_id: this.studentData.id,
                exam_session_id: this.sessionData.id,
                question_id: questionId,
                answer: selectedValue,
                device_signature: localStorage.getItem('caosce_device_signature')
            });
        }
    });
});