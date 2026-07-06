/**
 * CASOCE Global Background Sync Engine
 * Runs invisibly on all pages to push offline data to the server.
 */
(function() {
    const SYNC_INTERVAL_MS = 15000; // Check the queue every 15 seconds
    let isSyncing = false;

    async function processSyncQueue() {
        // 1. Halt if offline
        if (!navigator.onLine) return;

        // 2. Halt if the Admin is on the manual Bulk Sync page to prevent conflicts
        if (window.location.pathname.includes('/admin/sync')) {
            console.log("Background Sync paused: User is on the manual sync dashboard.");
            return;
        }

        // 3. Halt if a sync is already actively running
        if (isSyncing) return;
        
        try {
            isSyncing = true;
            let queue = JSON.parse(localStorage.getItem('caosce_sync_queue') || '[]');
            
            if (queue.length === 0) {
                isSyncing = false;
                return; 
            }

            let remainingQueue = [];
            
            // Process items one by one
            for (let i = 0; i < queue.length; i++) {
                let item = queue[i];
                try {
                    // Attempt to push to server
                    let response = await fetch(item.endpoint, {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(item.payload)
                    });

                    // If the server explicitly rejects it (e.g. 500 error), keep it in the queue
                    if (!response.ok) {
                        remainingQueue.push(item);
                    }
                } catch (e) {
                    // If network fails mid-request, keep it in the queue
                    remainingQueue.push(item);
                }
            }

            // Overwrite the local storage queue with ONLY the items that failed to sync
            localStorage.setItem('caosce_sync_queue', JSON.stringify(remainingQueue));

        } finally {
            isSyncing = false; // Release the lock
        }
    }

    // Run automatically every 15 seconds
    setInterval(processSyncQueue, SYNC_INTERVAL_MS);
    
    // Run immediately when the browser detects the internet has returned
    window.addEventListener('online', processSyncQueue);
    
    // Expose globally so UIs can trigger a sync instantly on form submit
    window.CAOSCE_BackgroundSync = processSyncQueue;
})();