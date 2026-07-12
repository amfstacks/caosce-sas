<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Session Workspace</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar for the massive station modal */
        .modal-scroll::-webkit-scrollbar { width: 8px; }
        .modal-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased" x-data="workspaceController()" x-cloak>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header & Breadcrumbs -->
        <div class="mb-8">
            <nav class="sm:hidden" aria-label="Back">
                <a href="/<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>/admin/sessions" class="flex items-center text-sm font-medium text-slate-500 hover:text-slate-700">
                    &larr; Back to Sessions
                </a>
            </nav>
            <nav class="hidden sm:flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div class="flex">
                            <a href="/<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>/admin/sessions" class="text-sm font-medium text-slate-500 hover:text-slate-700">Sessions Index</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                            <span class="ml-4 text-sm font-medium text-slate-900" x-text="sessionData.title"></span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <div class="mt-4 md:flex md:items-center md:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:truncate sm:text-3xl sm:tracking-tight" x-text="sessionData.title"></h2>
                    <p class="mt-1 text-sm text-slate-500">Scheduled: <span x-text="sessionData.date"></span> | Dept: <span x-text="sessionData.department"></span></p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-slate-200 mb-8">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'roster'" :class="activeTab === 'roster' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'" class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    1. Student Roster (<span x-text="students.length"></span>)
                </button>
                <button @click="activeTab = 'stations'" :class="activeTab === 'stations' ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'" class="whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium">
                    2. Station Configuration
                </button>
            </nav>
        </div>

        <!-- TAB 1: STUDENT ROSTER -->
        <div x-show="activeTab === 'roster'" class="space-y-6">
            
            <!-- Ingestion Header -->
            <!-- Ingestion Header -->
            <div class="bg-white shadow sm:rounded-lg p-6 border border-slate-200">
                <div class="sm:flex sm:items-start sm:justify-between mb-4 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold leading-6 text-slate-900 flex items-center gap-2">
                            Bulk CSV Roster Ingestion
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">Upload candidate roster. <strong class="text-slate-700">Required headers:</strong> <code class="bg-slate-100 px-1 py-0.5 rounded text-xs text-rose-600">matric_no</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-xs text-rose-600">full_name</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-xs text-rose-600">password</code></p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex gap-3">
                        <!-- Instantly generates a clean sample CSV for the user -->
                        <a href="data:text/csv;charset=utf-8,matric_no%2Cfull_name%2Cpassword%0ANS/2026/001%2CJohn%20Doe%2C%0ANS/2026/002%2CJane%20Smith%2C1234" download="sample_roster.csv" class="rounded-md bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-200 transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download Sample
                        </a>
                        <button @click="openStudentModal()" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-blue-600 shadow-sm ring-1 ring-inset ring-blue-300 hover:bg-blue-50 transition-colors">
                            + Add Single Student
                        </button>
                    </div>
                </div>

                <form @submit.prevent="uploadBulkRoster" class="flex flex-col lg:flex-row gap-6 items-end">
                    
                    <div class="flex-grow w-full">
                        <label class="block text-sm font-bold leading-6 text-slate-700">Select .CSV File</label>
                        <input type="file" x-ref="rosterFile" accept=".csv" required class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-lg cursor-pointer">
                    </div>

                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 w-full lg:w-1/3">
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">If Password Column is Empty:</label>
                        <div class="space-y-2">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" value="generate" x-model="passwordStrategy" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-600">
                                <span class="ml-3 text-sm font-medium text-slate-700">Auto-generate 4-digit PIN</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" value="matric" x-model="passwordStrategy" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-600">
                                <span class="ml-3 text-sm font-medium text-slate-700">Use Matric Number</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="w-full lg:w-auto rounded-lg bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-500 transition-colors whitespace-nowrap" :disabled="isUploading">
                        <span x-text="isUploading ? 'Validating & Uploading...' : 'Upload Bulk CSV'"></span>
                    </button>
                </form>
            </div>

            <!-- Student Data Table -->
            <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-slate-300">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-900 sm:pl-6">Matric Number</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Full Name</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-900">Password</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <template x-for="student in students" :key="student.id">
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-slate-900 sm:pl-6" x-text="student.matric"></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500" x-text="student.name"></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 font-mono" x-text="student.password"></td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <button @click="openStudentModal(student)" class="text-blue-600 hover:text-blue-900 mr-4">Edit</button>
                                    <button @click="deleteStudent(student.id)" class="text-red-600 hover:text-red-900">Remove</button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="students.length === 0">
                            <td colspan="4" class="py-10 text-center text-sm text-slate-500">No students enrolled yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: STATION CONFIGURATION -->
        <div x-show="activeTab === 'stations'" class="space-y-6" style="display: none;">
            
            <div class="bg-white shadow sm:rounded-lg p-6 mb-6">
                <h3 class="text-base font-semibold leading-6 text-slate-900">Examination Ring Overview</h3>
                <p class="mt-1 text-sm text-slate-500">Select a station to open its full workspace and configure its parameters.</p>
            </div>

            <!-- Station Status Grid -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <template x-for="station in stations" :key="station.id">
                    <div class="bg-white overflow-hidden shadow rounded-lg border-t-4 hover:shadow-md transition-shadow cursor-pointer" 
                         :class="station.type === 'procedure' ? 'border-indigo-500' : 'border-amber-500'"
                         @click="openStationWorkspace(station)">
                        
                        <div class="p-5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900" x-text="'Station ' + station.sequence"></h3>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium mt-1" 
                                          :class="station.type === 'procedure' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800'" 
                                          x-text="station.type.toUpperCase()"></span>
                                </div>
                                <div x-show="station.confirmed" class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>

                            <p class="mt-4 text-sm font-medium text-slate-900 truncate" x-text="station.title || 'Untitled Station'"></p>
                            
                            <!-- Status Badges -->
                            <div class="mt-4 flex flex-wrap gap-2">
                                <template x-for="status in getStationStatuses(station)">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                          :class="status.active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-slate-50 text-slate-500 ring-slate-500/10'"
                                          x-text="status.label"></span>
                                </template>
                            </div>
                            
                            <div class="mt-5 text-sm text-blue-600 font-medium">Open Workspace &rarr;</div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: ADD/EDIT STUDENT                    -->
        <!-- ========================================== -->
        <div x-show="studentModal.open" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div @click.away="studentModal.open = false" class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                        <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title" x-text="studentModal.isEditing ? 'Edit Student Details' : 'Add New Student'"></h3>
                        
                        <form @submit.prevent="saveStudent" class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Matric Number</label>
                                <input type="text" x-model="studentModal.data.matric" required class="mt-1 block w-full rounded-md border-slate-300 py-2 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Full Name</label>
                                <input type="text" x-model="studentModal.data.name" required class="mt-1 block w-full rounded-md border-slate-300 py-2 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Password</label>
                                <input type="text" x-model="studentModal.data.password" required class="mt-1 block w-full rounded-md border-slate-300 py-2 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                                <p class="text-xs text-slate-500 mt-1" x-show="!studentModal.isEditing">Auto-generated. You can overwrite this now.</p>
                            </div>
                            <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Save Student</button>
                                <button type="button" @click="studentModal.open = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: MASSIVE STATION WORKSPACE           -->
        <!-- ========================================== -->
        <div x-show="stationModal.open" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-90 transition-opacity"></div>
            
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                    <div @click.away="closeStationWorkspace()" class="relative w-full max-w-5xl transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all flex flex-col" style="max-height: 90vh;">
                        
                        <!-- Workspace Header -->
                        <div class="bg-slate-800 px-6 py-4 flex items-center justify-between flex-shrink-0">
                            <div>
                                <h2 class="text-xl font-bold text-white flex items-center gap-3">
                                    <span x-text="'Station ' + (stationModal.station?.sequence || '')"></span> Workspace
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium" 
                                          :class="stationModal.station?.type === 'procedure' ? 'bg-indigo-400/10 text-indigo-400 ring-1 ring-inset ring-indigo-400/30' : 'bg-amber-400/10 text-amber-400 ring-1 ring-inset ring-amber-400/30'" 
                                          x-text="stationModal.station?.type?.toUpperCase()"></span>
                                </h2>
                            </div>
                            <button @click="closeStationWorkspace()" class="text-slate-400 hover:text-white">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- Workspace Body (Scrollable) -->
                        <div class="px-6 py-6 overflow-y-auto modal-scroll flex-grow bg-slate-50">
                            
                            <template x-if="stationModal.station">
                                <div class="space-y-8">
                                    
                                    <!-- 1. Core Parameters -->
                                    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                                        <h3 class="text-lg font-medium text-slate-900 border-b pb-2 mb-4">Core Parameters</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-sm font-medium text-slate-700">Station Title (e.g., Scenario Name)</label>
                                                <input type="text" x-model="stationModal.station.title" class="mt-1 block w-full rounded-md border-slate-300 py-2 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                                            </div>

                                            <div x-show="stationModal.station.type === 'procedure'">
                                                <label class="block text-sm font-medium text-slate-700">Assign Evaluating Examiner</label>
                                                <select x-model="stationModal.station.examiner_id" class="mt-1 block w-full rounded-md border-slate-300 py-2 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                                                    <option value="">Select an Examiner...</option>
                                                    <template x-for="ex in availableExaminers" :key="ex.id">
                                                        <option :value="ex.id" x-text="ex.name"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <div x-show="stationModal.station.type === 'cbt'">
                                                <label class="block text-sm font-medium text-slate-700">Score per Question (Marks)</label>
                                                <input type="number" min="1" x-model="stationModal.station.score_per_question" class="mt-1 block w-full rounded-md border-slate-300 py-2 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border" placeholder="e.g. 2">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Question Bank Manager -->
                                    <div class="bg-white p-6 rounded-lg shadow-sm border border-slate-200">
                                        <div class="flex justify-between items-center border-b pb-2 mb-4">
                                            <h3 class="text-lg font-medium text-slate-900">Question / Scenario Bank</h3>
                                            <div class="flex gap-2">
                                                <button @click="triggerBankUpload" class="inline-flex items-center rounded bg-white px-2.5 py-1.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                                    Bulk Upload (JSON/CSV)
                                                </button>
                                                <button @click="addEmptyQuestion" class="inline-flex items-center rounded bg-blue-50 px-2.5 py-1.5 text-sm font-semibold text-blue-600 shadow-sm hover:bg-blue-100">
                                                    + Add Single Question
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Hidden file input -->
                                        <input type="file" id="bank-upload" class="hidden" accept=".json,.csv" @change="handleBankUpload">

                                        <!-- Question List -->
                                        <div class="space-y-4">
                                            <template x-for="(q, index) in stationModal.station.questions" :key="index">
                                                <div class="bg-slate-50 p-4 rounded-md border border-slate-200 relative transition-colors duration-300" :class="q.saved ? 'border-green-400 bg-green-50/30' : ''">
                                                    <button @click="removeQuestion(index)" class="absolute top-4 right-4 text-red-500 hover:text-red-700" title="Delete Question">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                    
                                                    <div class="pr-10">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide" x-text="'Question ' + (index + 1)"></label>
                                                            
                                                            <!-- Procedure Scoring Field -->
                                                            <div x-show="stationModal.station.type === 'procedure'" class="flex items-center gap-2">
                                                                <span class="text-xs font-medium text-slate-600">Marks:</span>
                                                                <input type="number" min="1" x-model="q.score" class="block w-20 rounded-md border-slate-300 py-1 px-2 text-sm border focus:ring-blue-500 focus:border-blue-500">
                                                            </div>
                                                        </div>
                                                        
                                                        <textarea x-model="q.text" rows="2" class="block w-full rounded-md border-slate-300 py-2 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border mb-3" placeholder="Enter question or procedure text..."></textarea>
                                                        
                                                        <!-- CBT Options & Answer Dropdown -->
                                                        <div x-show="stationModal.station.type === 'cbt'">
                                                            <div class="grid grid-cols-2 gap-3 mb-3">
                                                                <input type="text" x-model="q.optA" placeholder="Option A" class="block w-full rounded-md border-slate-300 py-1.5 px-3 text-sm border">
                                                                <input type="text" x-model="q.optB" placeholder="Option B" class="block w-full rounded-md border-slate-300 py-1.5 px-3 text-sm border">
                                                                <input type="text" x-model="q.optC" placeholder="Option C" class="block w-full rounded-md border-slate-300 py-1.5 px-3 text-sm border">
                                                                <input type="text" x-model="q.optD" placeholder="Option D" class="block w-full rounded-md border-slate-300 py-1.5 px-3 text-sm border">
                                                            </div>
                                                            <div class="w-full md:w-1/2">
                                                                <label class="block text-xs font-medium text-slate-700 mb-1">Correct Answer</label>
                                                                <select x-model="q.correct_answer" class="block w-full rounded-md border-slate-300 py-1.5 px-3 text-sm border bg-white focus:ring-blue-500 focus:border-blue-500">
                                                                    <option value="">Select Correct Option...</option>
                                                                    <option value="A">Option A</option>
                                                                    <option value="B">Option B</option>
                                                                    <option value="C">Option C</option>
                                                                    <option value="D">Option D</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Individual Question Action Bar -->
                                                        <div class="mt-4 flex justify-end">
                                                            <button @click="markQuestionSaved(q)" type="button" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md transition-colors" :class="q.saved ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'">
                                                                <span x-text="q.saved ? '✓ Saved' : 'Save Question'"></span>
                                                            </button>
                                                        </div>

                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <div x-show="stationModal.station.questions.length === 0" class="text-center py-6 text-sm text-slate-500 border-2 border-dashed border-slate-300 rounded-lg">
                                                No questions or scenarios added to this station yet.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>

                        <!-- Workspace Footer Actions -->
                        <div class="bg-slate-100 px-6 py-4 flex items-center justify-between border-t border-slate-200 flex-shrink-0">
                            <div class="text-sm text-slate-500">
                                Status: <span class="font-medium" :class="stationModal.station?.confirmed ? 'text-green-600' : 'text-amber-600'" x-text="stationModal.station?.confirmed ? 'Confirmed & Locked' : 'Draft / Unconfirmed'"></span>
                            </div>
                            <div class="flex gap-3">
                                <button type="button" @click="saveStationDraft()" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                                    Save Draft
                                </button>
                                <button type="button" @click="confirmStation()" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                                    Confirm Station
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Global Toast Notification -->
        <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-[60]">
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
        function workspaceController() {
           return {
                activeTab: 'roster',
                sessionId: new URLSearchParams(window.location.search).get('id'),
                isUploading: false,
                passwordStrategy: 'generate',
                toast: { visible: false, message: '' },
                
                // Initialize as empty/loading states
                sessionData: { title: 'Loading...', date: '...', department: '...' },
                students: [],
                availableExaminers: [],
                stations: [],
                
                studentModal: { open: false, isEditing: false, data: { id: null, matric: '', name: '', password: '' } },
                stationModal: { open: false, station: null },

                init() {
                    if (!this.sessionId) {
                        alert("Critical Error: No Session ID provided.");
                        return;
                    }
                    this.fetchWorkspaceData();
                },

                getBaseApiUrl() {
                    const tenantSlug = '<?php echo CURRENT_TENANT_SLUG ?? ""; ?>';
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return tenantSlug ? `${basePath}/${tenantSlug}` : basePath;
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

                // --- 1. CORE DATA FETCH ---
                async fetchWorkspaceData() {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/data?id=' + this.sessionId);
                        let data = await response.json();
                        
                        if (data.success) {
                            // Format the date nicely before injecting
                            if(data.payload.sessionData.date) {
                                data.payload.sessionData.date = new Date(data.payload.sessionData.date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                            }
                            
                            this.sessionData = data.payload.sessionData;
                            this.students = data.payload.students;
                            this.availableExaminers = data.payload.availableExaminers;
                            this.stations = data.payload.stations;
                        } else {
                            alert(data.message);
                        }
                    } catch (error) {
                        console.error('Failed to load workspace data', error);
                        this.showToast('Network error loading data.');
                    }
                },

                // --- Student Methods ---
               async uploadBulkRoster() {
                    let fileInput = this.$refs.rosterFile;
                    
                    if (!fileInput.files.length) {
                        alert('Please select a CSV file first.');
                        return;
                    }

                    this.isUploading = true;
                    
                    // Native FormData handles multipart/form-data seamlessly
                    let formData = new FormData();
                    formData.append('roster_file', fileInput.files[0]);
                    formData.append('session_id', this.sessionId);
                    formData.append('password_strategy', this.passwordStrategy);

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/student/upload', {
                            method: 'POST',
                            body: formData // Note: We do NOT set 'Content-Type' headers for FormData. The browser sets the multipart boundary automatically.
                        });
                        
                        let data = await response.json();
                        
                        if (data.success) {
                            this.fetchWorkspaceData(); // Refresh the grid to show new students
                            this.showToast('Bulk Roster ingested successfully.');
                            fileInput.value = ''; // Clear the input
                        } else {
                            alert(data.message || 'CSV Upload failed.');
                        }
                    } catch (e) {
                        alert('Network Error during upload.');
                    } finally {
                        this.isUploading = false;
                    }
                },
                openStudentModal(student = null) {
                    if(student) {
                        this.studentModal.isEditing = true;
                        this.studentModal.data = { ...student };
                    } else {
                        this.studentModal.isEditing = false;
                        this.studentModal.data = { id: null, matric: '', name: '', password:  Math.random().toString(36).substr(2, 4) };
                    }
                    this.studentModal.open = true;
                },
              // --- Student API Methods ---
                async saveStudent() {
                    let payload = { ...this.studentModal.data, session_id: this.sessionId };
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/student/save', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.fetchWorkspaceData(); // Refresh roster from DB
                            this.showToast('Student saved successfully.');
                            this.studentModal.open = false;
                        } else {
                            alert(data.message || 'Failed to save student.');
                        }
                    } catch (e) { alert('Network Error'); }
                },

                async deleteStudent(id) {
                    if(confirm('Remove this student from the session?')) {
                        try {
                            let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/student/remove', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ student_id: id, session_id: this.sessionId })
                            });
                            let data = await response.json();
                            if(data.success) {
                                this.students = this.students.filter(s => s.id !== id);
                                this.showToast('Student removed.');
                            }
                        } catch(e) { alert('Network Error'); }
                    }
                },


                getStationStatuses(station) {
                    let statuses = [];
                    statuses.push({ label: 'Title Set', active: !!station.title });
                    statuses.push({ label: 'Question Set', active: station.questions && station.questions.length > 0 });
                    if (station.type === 'procedure') {
                        statuses.push({ label: 'Station Assigned', active: !!station.examiner_id });
                    }
                    statuses.push({ label: 'Station Confirmed', active: station.confirmed });
                    return statuses;
                },
                // --- Station API Methods ---
                async processStationSave(isConfirmed) {
                    // Validation
                    if (isConfirmed) {
                        if(!this.stationModal.station.title) { alert("Cannot confirm: Station Title is required."); return; }
                        if(this.stationModal.station.type === 'procedure' && !this.stationModal.station.examiner_id) { alert("Cannot confirm: Procedure stations must have an assigned examiner."); return; }
                        if(this.stationModal.station.type === 'cbt' && !this.stationModal.station.score_per_question) { alert("Cannot confirm: Please set the Score per Question (Marks)."); return; }
                    }
                    
                    this.stationModal.station.confirmed = isConfirmed;

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/station/save', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ station: this.stationModal.station })
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.fetchWorkspaceData(); // Refresh the grid
                            this.showToast(isConfirmed ? 'Station Configuration Confirmed!' : 'Station draft saved.');
                            this.closeStationWorkspace();
                        } else {
                            alert(data.message || 'Failed to save station configuration.');
                        }
                    } catch(e) { alert('Network Error'); }
                },

                saveStationDraft() {
                    this.processStationSave(false);
                },

                confirmStation() {
                    this.processStationSave(true);
                },

                // --- Station Modal Methods ---
                openStationWorkspace(station) {
                    this.stationModal.station = JSON.parse(JSON.stringify(station));
                    this.stationModal.open = true;
                },
                closeStationWorkspace() {
                    this.stationModal.open = false;
                    this.stationModal.station = null;
                },
                triggerBankUpload() {
                    document.getElementById('bank-upload').click();
                },
                handleBankUpload(e) {
                    let file = e.target.files[0];
                    if(file) {
                        this.showToast('Parsed ' + file.name + ' successfully.');
                        this.addEmptyQuestion();
                        e.target.value = '';
                    }
                },
                addEmptyQuestion() {
                    this.stationModal.station.questions.push({ 
                        text: '', 
                        optA: '', optB: '', optC: '', optD: '', 
                        correct_answer: '', 
                        score: 1, 
                        saved: false 
                    });
                },
                removeQuestion(index) {
                    this.stationModal.station.questions.splice(index, 1);
                },
                markQuestionSaved(q) {
                    q.saved = true;
                    // Reset the visual state after 2 seconds
                    setTimeout(() => { q.saved = false; }, 2000);
                },
                saveStationDraft() {
                    let idx = this.stations.findIndex(s => s.id === this.stationModal.station.id);
                    this.stationModal.station.confirmed = false;
                    this.stations[idx] = JSON.parse(JSON.stringify(this.stationModal.station));
                    this.showToast('Station draft saved safely.');
                    this.closeStationWorkspace();
                },
                confirmStation() {
                    if(!this.stationModal.station.title) {
                        alert("Cannot confirm: Station Title is required.");
                        return;
                    }
                    if(this.stationModal.station.type === 'procedure' && !this.stationModal.station.examiner_id) {
                        alert("Cannot confirm: Procedure stations must have an assigned examiner.");
                        return;
                    }

                    // For CBT, ensure score per question is set
                    if(this.stationModal.station.type === 'cbt' && !this.stationModal.station.score_per_question) {
                        alert("Cannot confirm: Please set the Score per Question (Marks).");
                        return;
                    }

                    let idx = this.stations.findIndex(s => s.id === this.stationModal.station.id);
                    this.stationModal.station.confirmed = true;
                    this.stations[idx] = JSON.parse(JSON.stringify(this.stationModal.station));
                    
                    this.showToast('Station Configuration Confirmed!');
                    this.closeStationWorkspace();
                }
            }
        }
    </script>
</body>
</html>