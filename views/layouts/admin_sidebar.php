<?php
// Default to dashboard if no active menu is defined
$activeMenu = $activeMenu ?? 'dashboard';
// $tenantUrl = '/' . (defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG ? CURRENT_TENANT_SLUG : '');
$tenantUrl = BASE_PATH . '/' . (defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG ? CURRENT_TENANT_SLUG : '');
?>

<aside class="w-72 bg-[#0B1120] text-slate-300 flex-shrink-0 hidden md:flex flex-col h-screen border-r border-slate-800 transition-all duration-300">
    
    <!-- Brand Header -->
    <div class="h-20 flex items-center px-8 border-b border-slate-800/60 bg-[#0B1120] z-10 sticky top-0">
        <div class="flex flex-col">
            <h2 class="text-xl font-extrabold text-white tracking-wide flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <?php echo defined('APP_NAME') ? APP_NAME : 'CASOCE'; ?>
            </h2>
            <span class="text-[10px] uppercase tracking-[0.2em] text-slate-500 font-bold mt-1 pl-10">
                Wksp: <span class="text-blue-400"><?php echo htmlspecialchars(CURRENT_TENANT_SLUG ?? 'GLOBAL'); ?></span>
            </span>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="flex-grow overflow-y-auto py-6 px-4 space-y-1 modal-scroll">
        
        <p class="px-4 text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 mt-2">Core Operations</p>

        <a href="<?php echo $tenantUrl; ?>/admin/dashboard" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo $activeMenu === 'dashboard' ? 'bg-blue-600/10 text-blue-400 font-semibold' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard Overview
        </a>

        <a href="<?php echo $tenantUrl; ?>/admin/sessions" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo $activeMenu === 'sessions' ? 'bg-blue-600/10 text-blue-400 font-semibold' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            Exam Sessions
        </a>

        <a href="<?php echo $tenantUrl; ?>/admin/roster" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo $activeMenu === 'roster' ? 'bg-blue-600/10 text-blue-400 font-semibold' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Student Roster
        </a>

        <div class="pt-6 pb-2">
            <p class="px-4 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Hardware & Sync</p>
        </div>

        <a href="<?php echo $tenantUrl; ?>/admin/bind-device" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo $activeMenu === 'devices' ? 'bg-blue-600/10 text-blue-400 font-semibold' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            Device Binding
        </a>

        <a href="<?php echo $tenantUrl; ?>/admin/sync" 
           class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 <?php echo $activeMenu === 'sync' ? 'bg-blue-600/10 text-blue-400 font-semibold' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Bulk Sync Monitor
            </div>
            <!-- Sync indicator badge (Managed by Alpine later, static for now) -->
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        </a>

        <div class="pt-6 pb-2">
            <p class="px-4 text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Configuration</p>
        </div>

        <a href="<?php echo $tenantUrl; ?>/admin/settings" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 <?php echo $activeMenu === 'settings' ? 'bg-blue-600/10 text-blue-400 font-semibold' : 'hover:bg-slate-800/50 hover:text-white'; ?>">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            School Settings
        </a>
    </div>
</aside>