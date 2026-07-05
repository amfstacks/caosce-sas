const SyncManager = {
    queueKey: 'caosce_sync_queue',

    // Add a failed request to the queue
    enqueue(endpoint, payload) {
        let queue = JSON.parse(localStorage.getItem(this.queueKey)) || [];
        queue.push({
            id: crypto.randomUUID(), // Unique ID for the request
            endpoint: endpoint,
            payload: payload,
            timestamp: new Date().getTime()
        });
        localStorage.setItem(this.queueKey, JSON.stringify(queue));
        this.processQueue(); // Instantly try to process
    },

    // Process the queue in the background
    async processQueue() {
        if (!navigator.onLine) return; // Stop if browser knows it's offline

        let queue = JSON.parse(localStorage.getItem(this.queueKey)) || [];
        if (queue.length === 0) return;

        let currentRequest = queue[0]; // Grab the oldest request

        try {
            let response = await fetch(currentRequest.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(currentRequest.payload)
            });

            if (response.ok) {
                // Success! Remove from queue and process the next one
                queue.shift();
                localStorage.setItem(this.queueKey, JSON.stringify(queue));
                this.processQueue();
            } else {
                throw new Error('Server returned an error');
            }
        } catch (error) {
            console.warn("Sync failed, retrying in 5 seconds...", error);
            // Wait 5 seconds and try again
            setTimeout(() => this.processQueue(), 5000);
        }
    },

    // Custom fetch wrapper to be used everywhere in the app
    safePost(endpoint, payload) {
        if (navigator.onLine) {
            fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            }).catch(() => {
                // If the fetch fails due to network error, queue it
                this.enqueue(endpoint, payload);
            });
        } else {
            // Instantly queue if offline
            this.enqueue(endpoint, payload);
        }
    }
};

// Listen for the browser coming back online to instantly trigger the queue
window.addEventListener('online', () => SyncManager.processQueue());