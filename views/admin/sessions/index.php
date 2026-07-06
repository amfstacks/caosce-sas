<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Session Management</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased" x-data="sessionIndexController()">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Exam Sessions
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Manage all your objective structured clinical examinations across departments.
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <button @click="openModal()" type="button" class="block rounded-md bg-blue-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    + Create New Session
                </button>
            </div>
        </div>

        <!-- Data Table -->
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-slate-300">
                            <thead class="bg-slate-100">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">Session Title</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Scheduled Date</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Department</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Status</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <template x-for="session in sessions" :key="session.id">
                                    <tr>
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-slate-900 sm:pl-6" x-text="session.title"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500" x-text="formatDate(session.scheduled_date)"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500" x-text="session.department_name"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                                  :class="session.status === 'active' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-slate-50 text-slate-600 ring-slate-500/10'"
                                                  x-text="session.status.toUpperCase()">
                                            </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button @click="openModal(session)" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit Parameters</button>
                                            
                                            <!-- The deep-dive link into the specific session workspace -->
                                            <a :href="'/<?php echo CURRENT_TENANT_SLUG; ?>/admin/sessions/manage?id=' + session.id" class="text-blue-600 hover:text-blue-900 font-bold">Open Workspace &rarr;</a>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="sessions.length === 0">
                                    <td colspan="5" class="py-10 text-center text-sm text-slate-500">No exam sessions found. Create one to get started.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide-over Modal for Create/Edit -->
        <div x-show="isModalOpen" class="relative z-10" aria-labelledby="slide-over-title" role="dialog" aria-modal="true" style="display: none;">
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>
            
            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div x-show="isModalOpen" 
                             x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500" 
                             x-transition:enter-start="translate-x-full" 
                             x-transition:enter-end="translate-x-0" 
                             x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500" 
                             x-transition:leave-start="translate-x-0" 
                             x-transition:leave-end="translate-x-full" 
                             class="pointer-events-auto w-screen max-w-md">
                            
                            <form @submit.prevent="saveSession" class="flex h-full flex-col divide-y divide-slate-200 bg-white shadow-xl">
                                <div class="flex min-h-0 flex-1 flex-col overflow-y-scroll py-6 px-4 sm:px-6">
                                    <div class="flex items-start justify-between">
                                        <h2 class="text-lg font-semibold leading-6 text-slate-900" x-text="isEditing ? 'Edit Session Parameters' : 'Create New Session'"></h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" @click="closeModal()" class="rounded-md bg-white text-slate-400 hover:text-slate-500 focus:outline-none">
                                                <span class="sr-only">Close panel</span>
                                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6 flex flex-col gap-5">
                                        <div>
                                            <label for="title" class="block text-sm font-medium leading-6 text-slate-900">Session Title</label>
                                            <div class="mt-2">
                                                <input type="text" x-model="form.title" id="title" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label for="date" class="block text-sm font-medium leading-6 text-slate-900">Scheduled Date</label>
                                            <div class="mt-2">
                                                <input type="date" x-model="form.scheduled_date" id="date" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <label for="department" class="block text-sm font-medium leading-6 text-slate-900">Department</label>
                                            <div class="mt-2">
                                                <select x-model="form.department_id" id="department" required class="block w-full rounded-md border-0 py-2 px-3 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                                    <option value="">Select Department...</option>
                                                    <option value="dept-uuid-1">General Nursing (NS)</option>
                                                    <option value="dept-uuid-2">Midwifery (MW)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-shrink-0 justify-end px-4 py-4">
                                    <button type="button" @click="closeModal()" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">Cancel</button>
                                    <button type="submit" class="ml-4 inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500" :disabled="isLoading">
                                        <span x-text="isLoading ? 'Saving...' : 'Save Session'"></span>
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        const API_BASE = '/<?php echo CURRENT_TENANT_SLUG; ?>/api/admin/sessions';

        function sessionIndexController() {
            return {
                sessions: [],
                isModalOpen: false,
                isEditing: false,
                isLoading: false,
                form: { id: null, title: '', scheduled_date: '', department_id: '' },

                init() {
                    this.fetchSessions();
                },

                formatDate(dateString) {
                    if(!dateString) return '';
                    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                },

                async fetchSessions() {
                    // Placeholder for API fetch. In production: await fetch(API_BASE)
                    this.sessions = [
                        { id: '1111-2222', title: 'Maternal Health Practical', scheduled_date: '2026-08-15', department_name: 'Midwifery (MW)', status: 'active' },
                        { id: '3333-4444', title: 'Anatomy Baseline Exam', scheduled_date: '2026-09-01', department_name: 'General Nursing (NS)', status: 'pending' }
                    ];
                },

                openModal(session = null) {
                    if (session) {
                        this.isEditing = true;
                        this.form = { ...session }; // Clone the data
                    } else {
                        this.isEditing = false;
                        this.form = { id: null, title: '', scheduled_date: '', department_id: '' };
                    }
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                },

                async saveSession() {
                    this.isLoading = true;
                    try {
                        // In production: send this.form to your PHP backend via POST/PUT
                        console.log('Saving payload:', this.form);
                        
                        // Simulate network delay
                        await new Promise(resolve => setTimeout(resolve, 500));
                        
                        // Optimistic UI update for mockup purposes
                        if(!this.isEditing) {
                            this.form.id = 'new-uuid-' + Date.now();
                            this.form.status = 'pending';
                            this.form.department_name = 'Selected Dept';
                            this.sessions.unshift({...this.form});
                        } else {
                            let index = this.sessions.findIndex(s => s.id === this.form.id);
                            if(index !== -1) this.sessions[index] = {...this.form};
                        }
                        
                        this.closeModal();
                    } catch (error) {
                        console.error('Error saving session', error);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>