<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Admin Control Center</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        :root { --primary: #2563eb; --sidebar: #1e293b; --bg: #f8fafc; --text: #334155; --border: #e2e8f0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background-color: var(--bg); color: var(--text); margin: 0; display: flex; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; background-color: var(--sidebar); color: white; height: 100vh; position: fixed; display: flex; flex-direction: column; }
        .sidebar-header { padding: 1.5rem; text-align: center; border-bottom: 1px solid #334155; }
        .sidebar-header h2 { margin: 0; color: #60a5fa; font-size: 1.5rem; letter-spacing: 1px; }
        .tenant-tag { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; margin-top: 0.5rem; display: block; }
        
        .nav-links { flex-grow: 1; padding: 1rem 0; }
        .nav-links a { display: block; padding: 1rem 1.5rem; color: #cbd5e1; text-decoration: none; border-left: 3px solid transparent; transition: all 0.2s; }
        .nav-links a:hover, .nav-links a.active { background-color: #334155; color: white; border-left-color: var(--primary); }
        .nav-links a svg { width: 18px; height: 18px; margin-right: 10px; vertical-align: text-bottom; }
        
        /* Main Content */
        .main-content { margin-left: 260px; padding: 2rem; width: 100%; box-sizing: border-box; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
        .topbar h1 { margin: 0; font-size: 1.8rem; color: #0f172a; }
        .logout-btn { background: #ef4444; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 0.875rem; }
        
        /* Stats Grid */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .stat-card h3 { margin: 0 0 0.5rem 0; font-size: 0.875rem; color: #64748b; text-transform: uppercase; }
        .stat-card .value { font-size: 2.5rem; font-weight: bold; color: #0f172a; margin: 0; }
        .stat-card .desc { font-size: 0.875rem; color: #10b981; margin-top: 0.5rem; }
    </style>
</head>
<body x-data="dashboardController()">

    <nav class="sidebar">
        <div class="sidebar-header">
            <h2>CASOCE</h2>
            <span class="tenant-tag">Workspace: <?php echo htmlspecialchars(CURRENT_TENANT_SLUG ?? 'GLOBAL'); ?></span>
        </div>
        <div class="nav-links">
            <a href="#" class="active">Dashboard Overview</a>
            <a href="/<?php echo CURRENT_TENANT_SLUG; ?>/admin/sessions">Exam Sessions</a>
            <a href="/<?php echo CURRENT_TENANT_SLUG; ?>/admin/roster">Student Roster</a>
            <a href="/<?php echo CURRENT_TENANT_SLUG; ?>/admin/bind-device">Device Binding</a>
            <a href="/<?php echo CURRENT_TENANT_SLUG; ?>/admin/sync">Bulk Sync Monitor</a>
        </div>
    </nav>

    <main class="main-content">
        <header class="topbar">
            <h1>Admin Control Center</h1>
            <a href="/<?php echo CURRENT_TENANT_SLUG; ?>/login" class="logout-btn">Log Out</a>
        </header>

        <div class="stat-grid">
            <div class="stat-card">
                <h3>Active Exam Sessions</h3>
                <p class="value" x-text="stats.activeSessions">...</p>
                <p class="desc">Currently running</p>
            </div>
            <div class="stat-card">
                <h3>Bound Devices</h3>
                <p class="value" x-text="stats.boundDevices">...</p>
                <p class="desc">Ready for offline mode</p>
            </div>
            <div class="stat-card">
                <h3>Pending Offline Syncs</h3>
                <p class="value" x-text="stats.pendingSyncs">...</p>
                <p class="desc" style="color: #ef4444;">Requires network connection</p>
            </div>
            <div class="stat-card">
                <h3>Total Enrolled Students</h3>
                <p class="value" x-text="stats.totalStudents">...</p>
                <p class="desc">Across all sessions</p>
            </div>
        </div>
    </main>

    <script>
        function dashboardController() {
            return {
                stats: {
                    activeSessions: 0,
                    boundDevices: 0,
                    pendingSyncs: 0,
                    totalStudents: 0
                },
                init() {
                    this.fetchDashboardStats();
                },
                async fetchDashboardStats() {
                    // We will build the backend endpoint for this later. 
                    // For now, we simulate pulling the data.
                    try {
                        /* 
                        let response = await fetch('/<?php echo CURRENT_TENANT_SLUG; ?>/api/admin/stats');
                        let data = await response.json();
                        if(data.success) { this.stats = data.payload; }
                        */
                        
                        // Placeholder data until we write the PHP controller method
                        this.stats = {
                            activeSessions: 2,
                            boundDevices: 5,
                            pendingSyncs: 12,
                            totalStudents: 145
                        };
                    } catch (error) {
                        console.error("Failed to load stats", error);
                    }
                }
            }
        }
    </script>
</body>
</html>