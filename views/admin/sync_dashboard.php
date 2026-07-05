<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CAOSCE - Bulk Sync Dashboard</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="/assets/css/admin.css">
    <style>
        .status-pending { color: #f59e0b; font-weight: bold; }
        .status-syncing { color: #3b82f6; font-weight: bold; animation: pulse 1s infinite; }
        .status-success { color: #10b981; font-weight: bold; }
        .status-failed { color: #ef4444; font-weight: bold; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
        
        .progress-bar-container { width: 100%; background-color: #e5e7eb; border-radius: 4px; margin: 20px 0; height: 20px; overflow: hidden;}
        .progress-bar { height: 100%; background-color: #10b981; transition: width 0.3s ease; }
    </style>
</head>
<body>

<div x-data="bulkSyncManager()" x-init="loadQueue()" class="container">
    <header>
        <h1>Device Sync Dashboard</h1>
        <p>Review and upload offline data to the central server.</p>
    </header>

    <!-- Network Warning -->
    <div x-show="!isOnline" class="callout danger">
        <strong>No Internet Connection:</strong> You must connect this laptop to Wi-Fi before you can push these records.
    </div>

    <!-- Overall Progress -->
    <div class="summary-card">
        <h2>Unsynced Records: <span x-text="queue.length"></span></h2>
        
        <div class="progress-bar-container" x-show="isSyncing || progress > 0">
            <div class="progress-bar" :style="'width: ' + progress + '%'"></div>
        </div>

        <button class="btn-primary" 
                @click="startBulkSync()" 
                :disabled="isSyncing || queue.length === 0 || !isOnline">
            <span x-text="isSyncing ? 'Syncing Data...' : 'Sync All Records'"></span>
        </button>
    </div>

    <!-- The Queue Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Endpoint (Action Type)</th>
                <th>Candidate ID</th>
                <th>Timestamp</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="(item, index) in queue" :key="item.id">
                <tr>
                    <td x-text="index + 1"></td>
                    <td x-text="item.endpoint.split('/').pop().toUpperCase()"></td>
                    <td x-text="item.payload.student_id || 'N/A'"></td>
                    <td x-text="formatTime(item.timestamp)"></td>
                    <td>
                        <span :class="'status-' + item.ui_status" x-text="item.ui_status.toUpperCase()"></span>
                    </td>
                </tr>
            </template>
            <tr x-show="queue.length === 0">
                <td colspan="5" style="text-align: center; color: gray;">All data on this device is fully synced.</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
function bulkSyncManager() {
    return {
        isOnline: navigator.onLine,
        isSyncing: false,
        queue: [],
        currentIndex: 0,
        successfulSyncs: 0,

        loadQueue() {
            let rawQueue = localStorage.getItem('caosce_sync_queue');
            if (rawQueue) {
                this.queue = JSON.parse(rawQueue).map(item => {
                    // Inject a UI status property that isn't saved to the DB
                    item.ui_status = 'pending'; 
                    return item;
                });
            }

            window.addEventListener('online', () => this.isOnline = true);
            window.addEventListener('offline', () => this.isOnline = false);
        },

        get progress() {
            if (this.queue.length === 0) return 0;
            return Math.round((this.successfulSyncs / this.queue.length) * 100);
        },

        formatTime(ms) {
            return new Date(ms).toLocaleTimeString();
        },

        startBulkSync() {
            if (!this.isOnline || this.queue.length === 0) return;
            
            if(confirm("Ready to push " + this.queue.length + " records to the central server? Do not close this window.")) {
                this.isSyncing = true;
                this.currentIndex = 0;
                this.successfulSyncs = 0;
                this.processNextItem();
            }
        },

        async processNextItem() {
            // Stop condition: We reached the end of the queue
            if (this.currentIndex >= this.queue.length) {
                this.finishSync();
                return;
            }

            let item = this.queue[this.currentIndex];
            item.ui_status = 'syncing';

            try {
                let response = await fetch(item.endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(item.payload)
                });

                if (response.ok) {
                    item.ui_status = 'success';
                    this.successfulSyncs++;
                } else {
                    item.ui_status = 'failed';
                    this.isSyncing = false;
                    alert("Server rejected record #" + (this.currentIndex + 1) + ". Sync paused.");
                    return; // Halt the loop if the server explicitly rejects it
                }
            } catch (error) {
                item.ui_status = 'failed';
                this.isSyncing = false;
                alert("Network connection lost during sync. Please reconnect and try again.");
                return; // Halt the loop if network drops mid-sync
            }

            // Move to the next item
            this.currentIndex++;
            
            // Add a slight 200ms delay. 
            // 1. Gives the Admin visual satisfaction of watching them check off one by one.
            // 2. Protects the Shared Hosting server from concurrent connection limits.
            setTimeout(() => {
                this.processNextItem();
            }, 200);
        },

        finishSync() {
            this.isSyncing = false;
            
            // Filter out the successful ones, keep any that failed so they can try again
            let remainingQueue = this.queue.filter(item => item.ui_status !== 'success');
            
            // Remove our injected UI properties before saving back to localStorage
            let cleanQueue = remainingQueue.map(item => {
                let { ui_status, ...rest } = item;
                return rest;
            });

            localStorage.setItem('caosce_sync_queue', JSON.stringify(cleanQueue));
            
            alert("Sync Complete! " + this.successfulSyncs + " records successfully pushed to the server.");
            
            // Reload UI
            this.loadQueue();
        }
    }
}
</script>
</body>
</html>