<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Hardware Provisioning PINs</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased" x-data="bindingCodeController()" x-cloak>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header & Breadcrumbs -->
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol role="list" class="flex items-center space-x-2 text-sm text-slate-500">
                        <li><a href="/<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>/admin/dashboard" class="hover:text-slate-700">Dashboard</a></li>
                        <li>&rarr;</li>
                        <li class="font-medium text-slate-900">Hardware Provisioning</li>
                    </ol>
                </nav>
                <h1 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Device Binding PINs
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Generate secure, revocable 6-character access codes. Assistants can use these to bind exam laptops without requiring your master admin password.
                </p>
            </div>
            
            <div class="flex-shrink-0">
                <a href="/<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>/admin/dashboard" class="inline-flex items-center justify-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Generator Section -->
        <div class="bg-white shadow sm:rounded-lg p-6 mb-8 border border-slate-200">
            <h2 class="text-base font-semibold leading-6 text-slate-900 mb-2">Create New Provisioning PIN</h2>
            <p class="text-sm text-slate-500 mb-6">Generated PINs allow access exclusively to the laptop setup gatekeeper. They grant zero administrative access to this dashboard.</p>
            
            <form @submit.prevent="generateNewCode" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-4">
                <div class="flex-grow">
                    <label for="label" class="block text-sm font-medium text-slate-700 mb-1">Assignment Label (Optional)</label>
                    <input type="text" id="label" x-model="newLabel" placeholder="e.g., Hall A Setup Team, IT Assistant John, Room 4" class="block w-full rounded-md border-slate-300 py-2.5 px-3.5 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border bg-slate-50">
                </div>
                
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors" :disabled="isGenerating">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span x-text="isGenerating ? 'Generating...' : 'Generate 6-Char PIN'"></span>
                </button>
            </form>
        </div>

        <!-- Codes Table -->
        <div class="bg-white shadow sm:rounded-lg overflow-hidden border border-slate-200">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Active & Inactive PINs (<span x-text="codes.length"></span>)</h3>
                <span class="text-xs text-slate-500">Format: XX-XX-XX</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-100/50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Binding PIN</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Assigned Label</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Created Date</th>
                            <th scope="col" class="px-3 py-3.5 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status (Toggle)</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="item in codes" :key="item.id">
                            <tr class="hover:bg-slate-50/80 transition-colors" :class="!item.active ? 'opacity-60 bg-slate-50/50' : ''">
                                
                                <!-- Monospace PIN Display with Copy Button -->
                                <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                    <div class="flex items-center gap-3">
                                        <span class="font-mono text-lg font-bold tracking-wider px-3 py-1 rounded border shadow-sm"
                                              :class="item.active ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-500 border-slate-200 line-through'"
                                              x-text="item.code"></span>
                                        
                                        <button @click="copyCode(item.code)" class="text-slate-400 hover:text-blue-600 transition-colors" title="Copy PIN to Clipboard">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- Assigned Label -->
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-800">
                                    <span x-text="item.label || 'General Setup'"></span>
                                </td>

                                <!-- Created Timestamp -->
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                                    <span x-text="item.created_at"></span>
                                </td>

                                <!-- Alpine Toggle Switch for Active/Inactive -->
                                <td class="whitespace-nowrap px-3 py-4 text-center">
                                    <button type="button" 
                                            @click="toggleStatus(item)"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-inner"
                                            :class="item.active ? 'bg-green-500' : 'bg-slate-300'"
                                            role="switch" 
                                            :aria-checked="item.active">
                                        <span class="sr-only">Toggle PIN Status</span>
                                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                              :class="item.active ? 'translate-x-5' : 'translate-x-0'"></span>
                                    </button>
                                    <div class="text-[11px] font-bold mt-1 tracking-wider uppercase" 
                                         :class="item.active ? 'text-green-600' : 'text-slate-400'" 
                                         x-text="item.active ? 'Active' : 'Revoked'"></div>
                                </td>

                                <!-- Actions -->
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                                    <button @click="deleteCode(item.id)" class="text-red-600 hover:text-red-900 font-semibold p-1.5 rounded hover:bg-red-50 transition-colors">
                                        Delete
                                    </button>
                                </td>

                            </tr>
                        </template>
                        
                        <!-- Empty State -->
                        <tr x-show="codes.length === 0">
                            <td colspan="5" class="py-12 text-center text-sm text-slate-500">
                                <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                No provisioning PINs created yet. Generate one above to begin hardware setup.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Global Toast Notification -->
        <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
            <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
                <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-slate-800 shadow-lg ring-1 ring-black ring-opacity-5" style="display: none;">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-white" x-text="toast.message"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function bindingCodeController() {
            return {
                newLabel: '',
                isGenerating: false,
                toast: { visible: false, message: '' },
                
                // Pre-populated with simulated data
                codes: [
                    { id: 1, code: 'A4-9K-2M', label: 'Hall A Setup Team', active: true, created_at: '2026-07-06 14:15' },
                    { id: 2, code: '8X-3B-9Y', label: 'Room 3 Assistant', active: false, created_at: '2026-07-05 09:30' }
                ],

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

                // Generates a random 6-character alphanumeric code formatted as XX-XX-XX
                generateRandomPIN() {
                    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Excluded confusing chars like I, O, 0, 1
                    let result = '';
                    for (let i = 0; i < 6; i++) {
                        result += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    // Format as XX-XX-XX
                    return `${result.slice(0, 2)}-${result.slice(2, 4)}-${result.slice(4, 6)}`;
                },

                async generateNewCode() {
                    this.isGenerating = true;
                    
                    // Simulate API call delay
                    setTimeout(() => {
                        const newPIN = {
                            id: Date.now(),
                            code: this.generateRandomPIN(),
                            label: this.newLabel.trim() || 'General Provisioning',
                            active: true,
                            created_at: new Date().toISOString().slice(0, 16).replace('T', ' ')
                        };

                        // Unshift puts the newest code at the very top of the list
                        this.codes.unshift(newPIN);
                        this.newLabel = '';
                        this.isGenerating = false;
                        this.showToast(`PIN ${newPIN.code} generated successfully!`);
                    }, 500);
                },

                toggleStatus(item) {
                    item.active = !item.active;
                    // In production, trigger an asynchronous fetch/axios request here to update DB status
                    const statusText = item.active ? 'Activated' : 'Revoked / Deactivated';
                    this.showToast(`PIN ${item.code} is now ${statusText}.`);
                },

                deleteCode(id) {
                    const item = this.codes.find(c => c.id === id);
                    if (confirm(`Are you sure you want to permanently delete PIN ${item?.code}? Assistants using this code will immediately lose setup access.`)) {
                        this.codes = this.codes.filter(c => c.id !== id);
                        this.showToast('Provisioning PIN deleted.');
                    }
                },

                copyCode(codeString) {
                    navigator.clipboard.writeText(codeString).then(() => {
                        this.showToast(`PIN ${codeString} copied to clipboard!`);
                    }).catch(err => {
                        console.error('Failed to copy text: ', err);
                    });
                }
            }
        }
    </script>
</body>
</html>