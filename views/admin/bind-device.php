<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Device Binding & Sync</title>
    
    <!-- localforage for massive offline database capacity -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/localforage/1.10.0/localforage.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        .modal-scroll::-webkit-scrollbar { width: 6px; }
        .modal-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="deviceBinderController()" x-cloak>

    <!-- Header -->
    <header class="bg-slate-900 shadow-md flex-shrink-0">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-blue-600/20 rounded-lg flex items-center justify-center border border-blue-500/30 shadow-inner">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">Offline Sync Center</h1>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Device Provisioning & Payload Management</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2 bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700 shadow-inner">
                    <span class="relative flex h-2.5 w-2.5">
                      <span x-show="isOnline" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="isOnline ? 'bg-green-500' : 'bg-red-500'"></span>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider" :class="isOnline ? 'text-green-400' : 'text-red-400'" x-text="isOnline ? 'System Online' : 'System Offline'"></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-4xl space-y-6">
            
            <!-- SECTION 1: Mini Stats Dashboard -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-800 px-6 py-4 flex justify-between items-center border-b border-slate-700">
                    <h2 class="text-base font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Target Assignment Overview
                    </h2>
                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold uppercase tracking-widest shadow-inner"
                          :class="target.station_type === 'procedure' ? 'bg-indigo-500/10 text-indigo-400 ring-1 ring-inset ring-indigo-500/20' : 'bg-blue-500/10 text-blue-400 ring-1 ring-inset ring-blue-500/20'" 
                          x-text="target.station_type"></span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100 bg-slate-50">
                    <div class="p-6">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Exam Session</p>
                        <p class="text-base font-bold text-slate-800 leading-tight" x-text="target.session_title"></p>
                    </div>
                    <div class="p-6">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Station Title</p>
                        <p class="text-base font-bold text-slate-800 leading-tight" x-text="target.station_title"></p>
                    </div>
                    <div class="p-6 flex items-center">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sequence</p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-blue-600 leading-none" x-text="target.station_sequence"></span>
                                <span class="text-sm font-bold text-slate-500">Ring Position</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Offline Data Synchronization -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 relative overflow-hidden">
                
                <div x-show="localStats.isSynced" class="absolute inset-0 bg-green-50/50 pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 border-b border-slate-100 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">IndexedDB Payload</h3>
                            <p class="text-sm text-slate-500 mt-1">Download questions, rosters, and examiner profiles for offline execution.</p>
                        </div>
                        
                        <div class="mt-4 sm:mt-0" x-show="localStats.isSynced">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                Payload Cached
                            </span>
                        </div>
                    </div>

                    <!-- Sync Metrics -->
                    <div class="grid grid-cols-1 gap-4 mb-8" :class="target.station_type === 'procedure' ? 'sm:grid-cols-3' : 'sm:grid-cols-2'">
                        
                        <!-- Roster Metric -->
                        <div class="bg-white border rounded-xl flex flex-col transition-colors overflow-hidden" :class="localStats.isSynced ? 'border-green-200 shadow-sm' : 'border-slate-200'">
                            <div class="p-5 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-600 mb-1">Students</p>
                                    <p class="text-2xl font-black text-slate-900" x-text="localStats.studentCount"></p>
                                </div>
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="localStats.isSynced ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </div>
                            </div>
                            <button x-show="localStats.isSynced" @click="openDataViewer('students')" class="w-full bg-green-50 hover:bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wider py-2.5 transition-colors border-t border-green-100 flex items-center justify-center gap-1">
                                View Roster
                            </button>
                        </div>

                        <!-- Questions Metric -->
                        <div class="bg-white border rounded-xl flex flex-col transition-colors overflow-hidden" :class="localStats.isSynced ? 'border-green-200 shadow-sm' : 'border-slate-200'">
                            <div class="p-5 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-600 mb-1">Questions</p>
                                    <p class="text-2xl font-black text-slate-900" x-text="localStats.questionCount"></p>
                                </div>
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="localStats.isSynced ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </div>
                            </div>
                            <button x-show="localStats.isSynced" @click="openDataViewer('questions')" class="w-full bg-green-50 hover:bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wider py-2.5 transition-colors border-t border-green-100 flex items-center justify-center gap-1">
                                View Bank
                            </button>
                        </div>

                        <!-- Examiner Metric (Only for Procedure) -->
                        <div x-show="target.station_type === 'procedure'" class="bg-white border rounded-xl flex flex-col transition-colors overflow-hidden" :class="localStats.isSynced ? 'border-green-200 shadow-sm' : 'border-slate-200'">
                            <div class="p-5 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-600 mb-1">Examiner</p>
                                    <p class="text-lg font-black text-slate-900 truncate max-w-[100px]" x-text="cachedExaminer ? cachedExaminer.full_name : 'None'"></p>
                                </div>
                                <div class="w-10 h-10 rounded-full flex items-center justify-center" :class="localStats.isSynced ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                            </div>
                            <button x-show="localStats.isSynced" @click="openDataViewer('examiner')" class="w-full bg-green-50 hover:bg-green-100 text-green-700 text-xs font-bold uppercase tracking-wider py-2.5 transition-colors border-t border-green-100 flex items-center justify-center gap-1">
                                View Credentials
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        
                        <button x-show="!localStats.isSynced" @click="downloadOfflineData" type="button" class="flex-1 flex justify-center items-center gap-2 rounded-xl bg-blue-600 px-6 py-4 text-sm font-bold text-white shadow-lg shadow-blue-600/30 hover:bg-blue-500 transition-colors disabled:opacity-50" :disabled="isSyncing || !isOnline">
                            <svg x-show="isSyncing" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <svg x-show="!isSyncing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span x-text="isSyncing ? 'Downloading Payload...' : 'Fetch Offline Data'"></span>
                        </button>

                        <div x-show="localStats.isSynced" class="w-full flex flex-col sm:flex-row gap-3">
                            <button @click="downloadOfflineData" type="button" class="flex-1 rounded-xl bg-white border border-slate-300 px-4 py-3 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors disabled:opacity-50" :disabled="isSyncing || !isOnline">
                                <span x-text="isSyncing ? 'Refreshing...' : '↻ Reload Fresh Data'"></span>
                            </button>
                            
                            <button @click="unbindDevice" type="button" class="flex-1 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm font-bold text-red-700 shadow-sm hover:bg-red-100 transition-colors">
                                ⚠ Clear & Unbind Device
                            </button>

                            <button @click="launchExamMode" type="button" class="flex-[2] rounded-xl bg-green-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-green-600/20 hover:bg-green-500 transition-colors flex items-center justify-center gap-2">
                                <span>Launch Exam Screen</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                    </div>
                    
                    <p x-show="!isOnline" class="mt-4 text-xs font-bold text-red-500 text-center bg-red-50 py-2 rounded-lg border border-red-100">
                        Internet connection required to download or refresh data.
                    </p>

                </div>
            </div>
        </div>
    </main>

    <!-- ========================================== -->
    <!-- MODAL: DATA VIEWER                         -->
    <!-- ========================================== -->
    <div x-show="viewerModal.open" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.away="viewerModal.open = false" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl flex flex-col max-h-[85vh]">
                    
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50 flex-shrink-0">
                        <h3 class="text-lg font-bold text-slate-900" x-text="viewerModal.type === 'students' ? 'Offline Roster Data' : (viewerModal.type === 'questions' ? 'Offline Question Bank' : 'Examiner Credentials')"></h3>
                        <button @click="viewerModal.open = false" class="text-slate-400 hover:text-slate-600 transition-colors bg-white rounded-md p-1 border border-slate-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="overflow-y-auto modal-scroll p-6 flex-grow bg-white">
                        
                        <!-- Roster View -->
                        <div x-show="viewerModal.type === 'students'">
                            <table class="min-w-full divide-y divide-slate-200 border border-slate-200 rounded-lg overflow-hidden block">
                                <thead class="bg-slate-50 w-full table table-fixed">
                                    <tr>
                                        <th class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Matric No.</th>
                                        <th class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Full Name</th>
                                        <th class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-1/3">Login PIN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 w-full block max-h-[50vh] overflow-y-auto modal-scroll">
                                    <template x-for="student in cachedStudents" :key="student.id">
                                        <tr class="hover:bg-slate-50 table table-fixed w-full">
                                            <td class="py-3 px-4 text-sm font-bold text-slate-900 w-1/3" x-text="student.matric_number"></td>
                                            <td class="py-3 px-4 text-sm text-slate-600 w-1/3" x-text="student.full_name"></td>
                                            <td class="py-3 px-4 text-sm font-mono font-bold text-blue-600 w-1/3" x-text="student.raw_password"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                            <div x-show="cachedStudents.length === 0" class="text-center py-8 text-sm text-slate-500">No student records found in cache.</div>
                        </div>

                        <!-- Question Bank View -->
                        <div x-show="viewerModal.type === 'questions'" class="space-y-4">
                            <template x-for="(q, index) in cachedQuestions" :key="q.id">
                                <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="text-xs font-black text-slate-500 uppercase tracking-widest bg-slate-200 px-2 py-0.5 rounded" x-text="'Question ' + (index + 1)"></span>
                                        <span x-show="target.station_type === 'procedure' && q.score" class="text-xs font-bold text-indigo-700 bg-indigo-100 border border-indigo-200 px-2 py-0.5 rounded" x-text="q.score + ' Marks'"></span>
                                    </div>
                                    <p class="text-base font-semibold text-slate-800 mb-4 whitespace-pre-wrap" x-text="q.question_text"></p>
                                    
                                    <div x-show="target.station_type === 'cbt' && (q.opt_a || q.opt_b || q.opt_c || q.opt_d)" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-slate-600">
                                        <div class="flex items-start" :class="q.correct_answer === 'A' ? 'font-bold text-green-700 bg-green-50 p-1.5 -m-1.5 rounded' : ''"><span class="w-6 shrink-0 font-bold">A.</span> <span x-text="q.opt_a"></span></div>
                                        <div class="flex items-start" :class="q.correct_answer === 'B' ? 'font-bold text-green-700 bg-green-50 p-1.5 -m-1.5 rounded' : ''"><span class="w-6 shrink-0 font-bold">B.</span> <span x-text="q.opt_b"></span></div>
                                        <div class="flex items-start" :class="q.correct_answer === 'C' ? 'font-bold text-green-700 bg-green-50 p-1.5 -m-1.5 rounded' : ''"><span class="w-6 shrink-0 font-bold">C.</span> <span x-text="q.opt_c"></span></div>
                                        <div class="flex items-start" :class="q.correct_answer === 'D' ? 'font-bold text-green-700 bg-green-50 p-1.5 -m-1.5 rounded' : ''"><span class="w-6 shrink-0 font-bold">D.</span> <span x-text="q.opt_d"></span></div>
                                    </div>
                                </div>
                            </template>
                            <div x-show="cachedQuestions.length === 0" class="text-center py-8 text-sm text-slate-500">No questions found in cache.</div>
                        </div>

                        <!-- Examiner View -->
                        <div x-show="viewerModal.type === 'examiner'">
                            <div x-show="cachedExaminer" class="bg-indigo-50 border border-indigo-100 rounded-xl p-6">
                                <h4 class="text-indigo-800 font-bold mb-4">Assigned Evaluator Credentials</h4>
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Full Name</p>
                                        <p class="text-base font-bold text-indigo-900" x-text="cachedExaminer?.full_name"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Admin ID (Username)</p>
                                        <p class="text-base font-bold text-indigo-900" x-text="cachedExaminer?.username"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Offline Password</p>
                                        <p class="text-base font-mono font-bold text-indigo-600 bg-white inline-block px-3 py-1 rounded border border-indigo-200 mt-1" x-text="cachedExaminer?.raw_password"></p>
                                    </div>
                                </div>
                                <p class="text-xs text-indigo-600/70 mt-6 italic">The examiner must use these exact credentials to authenticate offline before grading candidates.</p>
                            </div>
                            <div x-show="!cachedExaminer" class="text-center py-8 text-sm text-slate-500">No examiner assigned to this station.</div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Toast -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl shadow-xl ring-1 ring-black ring-opacity-5" :class="toast.type === 'error' ? 'bg-red-900' : 'bg-slate-800'" style="display: none;">
                <div class="p-4 flex items-start">
                    <svg x-show="toast.type === 'success'" class="h-6 w-6 text-green-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg x-show="toast.type === 'error'" class="h-6 w-6 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <p class="ml-3 text-sm font-bold text-white pt-0.5" x-text="toast.message"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>';

        function deviceBinderController() {
            return {
                isOnline: navigator.onLine,
                isSyncing: false,
                toast: { visible: false, message: '', type: 'success' },
                
                // Modal State
                viewerModal: { open: false, type: 'students' },
                cachedStudents: [],
                cachedQuestions: [],
                cachedExaminer: null,

                target: {
                    session_id: '', session_title: '',
                    station_id: '', station_title: '', station_sequence: '', station_type: ''
                },

                localStats: {
                    isSynced: false,
                    studentCount: 0,
                    questionCount: 0
                },

                async init() {
                    window.addEventListener('online', () => this.isOnline = true);
                    window.addEventListener('offline', () => this.isOnline = false);

                    const payloadStr = sessionStorage.getItem('caosce_binding_payload');
                    if (!payloadStr) {
                        alert("Unauthorized access. Redirecting to Gatekeeper.");
                        window.location.href = `/${TENANT_SLUG}/setup/device`; 
                        return;
                    }
                    this.target = JSON.parse(payloadStr);

                    // Await the localforage read
                    await this.checkLocalPayload();
                },

                getBaseApiUrl() {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return TENANT_SLUG ? `${basePath}/${TENANT_SLUG}` : basePath;
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 4000);
                },

                async checkLocalPayload() {
                    try {
                        const parsed = await localforage.getItem('caosce_offline_data');
                        if (parsed && parsed.station_id === this.target.station_id) {
                            this.cachedStudents = parsed.students || [];
                            this.cachedQuestions = parsed.questions || [];
                            this.cachedExaminer = parsed.examiner || null;
                            
                            this.localStats.studentCount = this.cachedStudents.length;
                            this.localStats.questionCount = this.cachedQuestions.length;
                            this.localStats.isSynced = true;
                        }
                    } catch (e) {
                        console.error("Corrupted offline payload found.", e);
                    }
                },

                openDataViewer(type) {
                    this.viewerModal.type = type;
                    this.viewerModal.open = true;
                },

                async downloadOfflineData() {
                    if (!this.isOnline) return;
                    this.isSyncing = true;

                    try {
                        let response = await fetch(this.getBaseApiUrl() + `/api/setup/download-payload?session_id=${this.target.session_id}&station_id=${this.target.station_id}`);
                        let data = await response.json();

                        if (data.success) {
                            const offlinePayload = {
                                session_id: this.target.session_id,
                                station_id: this.target.station_id,
                                sync_timestamp: Date.now(),
                                students: data.payload.students,
                                questions: data.payload.questions,
                                station_settings: data.payload.station_settings,
                                examiner: data.payload.examiner // Include examiner securely
                            };

                            // Save to IndexedDB using localforage
                            await localforage.setItem('caosce_offline_data', offlinePayload);
                            
                            // Synchronous ID for fast auth checks
                            if (!localStorage.getItem('caosce_device_signature')) {
                                localStorage.setItem('caosce_device_signature', this.generateUUID());
                            }

                            await this.checkLocalPayload();
                            this.showToast('Data synchronized and secured locally!', 'success');
                        } else {
                            this.showToast(data.message || 'Failed to fetch payload.', 'error');
                        }
                    } catch (error) {
                        this.showToast('Network error during synchronization.', 'error');
                    } finally {
                        this.isSyncing = false;
                    }
                },

                async unbindDevice() {
                    if(confirm("DANGER: This will delete all locally cached data and credentials. Proceed?")) {
                        await localforage.removeItem('caosce_offline_data');
                        localStorage.removeItem('caosce_device_signature');
                        
                        sessionStorage.removeItem('caosce_binding_payload');
                        sessionStorage.removeItem('caosce_provision_pin');
                        
                        window.location.href = this.getBaseApiUrl() + '/setup/device'; 
                    }
                },

                launchExamMode() {
                    if (!this.localStats.isSynced) {
                        this.showToast("Cannot launch: Payload is missing.", "error");
                        return;
                    }
                    window.location.href = this.getBaseApiUrl() + '';
                },

                generateUUID() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                }
            }
        }
    </script>
</body>
</html>