<div x-data="examSetupManager()" class="setup-container">
    <h2>CAOSCE Session Management</h2>

    <!-- Tab Navigation -->
    <div class="tabs">
        <button @click="activeTab = 'create'" :class="{'active': activeTab === 'create'}">1. Create Session</button>
        <button @click="activeTab = 'import'" :class="{'active': activeTab === 'import'}" x-show="currentSessionId">2. Import Students</button>
        <button @click="activeTab = 'allocate'" :class="{'active': activeTab === 'allocate'}" x-show="currentSessionId">3. Allocate Stations</button>
    </div>

    <!-- Step 1: Create Session -->
    <div x-show="activeTab === 'create'" class="tab-content">
        <form @submit.prevent="createSession">
            <input type="text" x-model="newSession.title" placeholder="e.g., Maternal Health CAOSCE - July Intake" required>
            <input type="date" x-model="newSession.scheduled_date" required>
            
            <select x-model="newSession.department_id" required>
                <option value="">Select Department...</option>
                <option value="dept-uuid-nursing">General Nursing (NS)</option>
                <option value="dept-uuid-midwifery">Midwifery (MW)</option>
            </select>
            
            <button type="submit">Initialize Exam Session</button>
        </form>
    </div>

    <!-- Step 2: Import Candidates -->
    <div x-show="activeTab === 'import'" class="tab-content">
        <div class="callout">
            <strong>CSV Format:</strong> Column A: Matric Number, Column B: Full Name. (Passwords will be auto-generated).
        </div>
        <form @submit.prevent="uploadCsv" enctype="multipart/form-data">
            <input type="file" x-ref="csvFile" accept=".csv" required>
            <button type="submit">Upload & Enroll Students</button>
        </form>
    </div>

    <!-- Step 3: Allocate Stations -->
    <div x-show="activeTab === 'allocate'" class="tab-content">
        <table>
            <thead>
                <tr>
                    <th>Station</th>
                    <th>Type</th>
                    <th>Assign Examiner</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="station in stations" :key="station.id">
                    <tr>
                        <td x-text="station.title"></td>
                        <td>
                            <span x-text="station.station_type.toUpperCase()"></span>
                        </td>
                        <td>
                            <select x-model="station.examiner_id" @change="assignExaminer(station.id, $event.target.value)" x-show="station.station_type === 'procedure'">
                                <option value="">Select Examiner...</option>
                                <template x-for="examiner in examiners" :key="examiner.id">
                                    <option :value="examiner.id" x-text="examiner.full_name"></option>
                                </template>
                            </select>
                            <span x-show="station.station_type === 'cbt'" style="color: gray;">Auto-Managed (CBT)</span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>

<script>
function examSetupManager() {
    return {
        activeTab: 'create',
        currentSessionId: null,
        newSession: { title: '', scheduled_date: '', department_id: '', school_id: 'current-school-uuid' },
        stations: [],
        examiners: [], // Pre-loaded from DB

        createSession() {
            fetch('/api/admin/session/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(this.newSession)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    this.currentSessionId = data.session_id;
                    alert(data.message);
                    this.activeTab = 'import';
                    this.loadStations(); // Fetches the newly generated 6 stations
                }
            });
        },

        uploadCsv() {
            let formData = new FormData();
            formData.append('student_csv', this.$refs.csvFile.files[0]);
            formData.append('exam_session_id', this.currentSessionId);
            formData.append('department_id', this.newSession.department_id);
            formData.append('school_id', this.newSession.school_id);

            fetch('/api/admin/session/import', {
                method: 'POST',
                body: formData // Note: Content-Type is set automatically by the browser for FormData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    alert('Candidates successfully ingested. Passwords have been generated.');
                    this.activeTab = 'allocate';
                }
            });
        },

        assignExaminer(stationId, examinerId) {
            fetch('/api/admin/station/assign', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ station_id: stationId, examiner_id: examinerId })
            });
        },
        
        loadStations() {
            // AJAX call to fetch stations where exam_session_id = this.currentSessionId
        }
    }
}
</script>