<div x-data="deviceBinder()" x-init="fetchExamData()">
    <h2>Configure This Exam Device</h2>
    
    <form @submit.prevent="bindDevice">
        <!-- Select Active Exam Session -->
        <select x-model="selectedSession" required>
            <option value="">Select Exam Session...</option>
            <template x-for="session in sessions" :key="session.id">
                <option :value="session.id" x-text="session.title"></option>
            </template>
        </select>

        <!-- Select Station (1-6) -->
        <select x-model="selectedStation" required>
            <option value="">Select Station...</option>
            <template x-for="station in stations" :key="station.id">
                <option :value="station.id" x-text="'Station ' + station.order_sequence + ' - ' + station.station_type.toUpperCase()"></option>
            </template>
        </select>

        <!-- Select Examiner (Hidden if CBT Station) -->
        <select x-model="selectedExaminer" x-show="isProcedureStation()">
            <option value="">Select Assigned Examiner...</option>
            <template x-for="examiner in examiners" :key="examiner.id">
                <option :value="examiner.id" x-text="examiner.full_name"></option>
            </template>
        </select>

        <button type="submit">Lock & Bind Device</button>
    </form>
</div>

<script>
function deviceBinder() {
    return {
        sessions: [], stations: [], examiners: [],
        selectedSession: '', selectedStation: '', selectedExaminer: '',
        
        // Mock fetch function (In reality, this pulls from your PHP API)
        fetchExamData() {
            // Fetch dropdown data...
        },

        isProcedureStation() {
            let station = this.stations.find(s => s.id === this.selectedStation);
            return station ? station.station_type === 'procedure' : false;
        },

        // Cryptographic UUID v4 Generator for JS
        generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        },

        bindDevice() {
            let signature = this.generateUUID();
            
            fetch('/api/admin/bind-device', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    device_signature: signature,
                    exam_session_id: this.selectedSession,
                    station_id: this.selectedStation,
                    examiner_id: this.isProcedureStation() ? this.selectedExaminer : null
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    // Lock it into the browser's Black Box storage
                    localStorage.setItem('caosce_device_signature', signature);
                    alert('Device successfully bound! You may now log out and leave this laptop for the exam.');
                    window.location.href = '/login'; // Kick subadmin out to the login screen
                }
            });
        }
    }
}
</script>