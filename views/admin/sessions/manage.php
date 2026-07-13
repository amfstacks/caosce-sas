<?php 
$pageTitle = "Manage Session "; 
$activeMenu = "sessions"; 
include '../views/layouts/header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
   
    <style>
        [x-cloak] { display: none !important; }
        .modal-scroll::-webkit-scrollbar { width: 8px; }
        .modal-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Smooth fade transition for tabs */
        .tab-enter { transition: opacity 0.3s ease-out; }
        .tab-enter-start { opacity: 0; }
        .tab-enter-end { opacity: 1; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased flex h-screen overflow-hidden" x-data="workspaceController()" x-cloak>

    <!-- Sidebar Inclusion -->
    <?php include '../views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative">
        
        <!-- NEW Professional Header -->
        <header class="bg-white h-20 border-b border-slate-200 flex items-center justify-between px-6 sm:px-10 flex-shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-4 sm:gap-6">
                <!-- Smart Back Button -->
                <button @click="goBack()" type="button" class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center hover:bg-slate-200 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2">
                    <svg class="w-5 h-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </button>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Session Configuration</h1>
                    <div class="flex items-center gap-2 text-sm text-slate-500 font-medium mt-0.5">
                        <span x-text="sessionData.title || 'Loading...'"></span>
                        <span x-show="sessionData.date" class="text-slate-300">•</span>
                        <span x-show="sessionData.date" x-text="sessionData.date"></span>
                        <span x-show="sessionData.department" class="text-slate-300 hidden sm:inline">•</span>
                        <span x-show="sessionData.department" x-text="sessionData.department" class="hidden sm:inline bg-slate-100 px-2 py-0.5 rounded text-xs"></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Scrollable Page Content -->
        <main class="flex-1 overflow-y-auto bg-slate-50 p-6 sm:p-10">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <!-- Polished Tab Navigation -->
                <div class="border-b border-slate-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="activeTab = 'roster'" 
                            :class="activeTab === 'roster' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'" 
                            class="group whitespace-nowrap border-b-2 py-4 px-1 text-sm font-bold transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" :class="activeTab === 'roster' ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            1. Student Roster 
                            <span class="bg-slate-100 text-slate-600 py-0.5 px-2.5 rounded-full text-xs font-semibold ml-1" :class="activeTab === 'roster' ? 'bg-blue-100 text-blue-700' : ''" x-text="students.length"></span>
                        </button>
                        <button @click="activeTab = 'stations'" 
                            :class="activeTab === 'stations' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700'" 
                            class="group whitespace-nowrap border-b-2 py-4 px-1 text-sm font-bold transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" :class="activeTab === 'stations' ? 'text-blue-600' : 'text-slate-400 group-hover:text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            2. Station Configuration
                        </button>
                    </nav>
                </div>

                <!-- TAB 1: STUDENT ROSTER -->
                <div x-show="activeTab === 'roster'" class="space-y-6 tab-enter" style="display: none;">
                    <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-slate-200">
                        <div class="sm:flex sm:items-start sm:justify-between mb-4 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-base font-bold leading-6 text-slate-900 flex items-center gap-2">Bulk CSV Roster Ingestion</h3>
                                <p class="mt-1 text-sm text-slate-500">Upload candidate roster. <strong class="text-slate-700">Required headers:</strong> <code class="bg-slate-100 px-1 py-0.5 rounded text-xs text-rose-600">matric_no</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-xs text-rose-600">full_name</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-xs text-rose-600">password</code></p>
                            </div>
                            <div class="mt-4 sm:mt-0 flex gap-3">
                                <a href="data:text/csv;charset=utf-8,matric_no%2Cfull_name%2Cpassword%0ANS/2026/001%2CJohn%20Doe%2C%0ANS/2026/002%2CJane%20Smith%2C1234" download="sample_roster.csv" class="rounded-lg bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-100 transition-colors flex items-center gap-2">
                                    Download Sample
                                </a>
                                <button @click="openStudentModal()" class="rounded-lg bg-blue-50 px-3 py-2 text-sm font-bold text-blue-700 shadow-sm hover:bg-blue-100 transition-colors border border-blue-200">
                                    + Add Single Student
                                </button>
                            </div>
                        </div>

                        <form @submit.prevent="uploadBulkRoster" class="flex flex-col lg:flex-row gap-6 items-end">
                            <div class="flex-grow w-full">
                                <label class="block text-sm font-bold leading-6 text-slate-700">Select .CSV File</label>
                                <input type="file" x-ref="rosterFile" accept=".csv" required class="mt-2 block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 w-full lg:w-1/3">
                                <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">If Password Column is Empty:</label>
                                <div class="space-y-2">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" value="generate" x-model="passwordStrategy" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-600">
                                        <span class="ml-3 text-sm font-semibold text-slate-700">Auto-generate 4-digit PIN</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" value="matric" x-model="passwordStrategy" class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-600">
                                        <span class="ml-3 text-sm font-semibold text-slate-700">Use Matric Number</span>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="w-full lg:w-auto rounded-lg bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-500 transition-all active:scale-95 whitespace-nowrap" :disabled="isUploading">
                                <span x-text="isUploading ? 'Validating & Uploading...' : 'Upload Bulk CSV'"></span>
                            </button>
                        </form>
                    </div>

                    <!-- Student Data Table -->
                    <div class="bg-white shadow-sm sm:rounded-xl border border-slate-200 overflow-hidden">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th scope="col" class="py-4 pl-4 pr-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500 sm:pl-6">Matric Number</th>
                                    <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Full Name</th>
                                    <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Password</th>
                                    <th scope="col" class="relative py-4 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <template x-for="student in students" :key="student.id">
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-slate-900 sm:pl-6" x-text="student.matric"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-600" x-text="student.name"></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 font-mono" x-text="student.password"></td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button @click="openStudentModal(student)" class="text-blue-600 hover:text-blue-900 mr-4 font-semibold">Edit</button>
                                            <button @click="deleteStudent(student.id)" class="text-red-500 hover:text-red-700 font-semibold">Remove</button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="students.length === 0">
                                    <td colspan="4" class="py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        <h3 class="mt-2 text-sm font-medium text-slate-900">No students enrolled</h3>
                                        <p class="mt-1 text-sm text-slate-500">Get started by uploading a CSV or adding a student manually.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: STATION CONFIGURATION -->
                <div x-show="activeTab === 'stations'" class="space-y-6 tab-enter" style="display: none;">
                    
                    <div class="bg-white shadow-sm sm:rounded-xl p-6 mb-6 border border-slate-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold leading-6 text-slate-900">Examination Ring Overview</h3>
                            <p class="mt-1 text-sm text-slate-500">Select a station below to open its full workspace and configure its parameters.</p>
                        </div>
                        <div class="hidden md:flex gap-2">
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">Procedure Stations</span>
                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20">CBT Stations</span>
                        </div>
                    </div>

                    <!-- Station Status Grid -->
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <template x-for="station in stations" :key="station.id">
                            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-slate-200 border-t-4 hover:shadow-md hover:-translate-y-0.5 transition-all cursor-pointer group" 
                                 :class="station.type === 'procedure' ? 'border-t-indigo-500' : 'border-t-amber-500'"
                                 @click="openStationWorkspace(station)">
                                
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="text-xl font-black text-slate-900 tracking-tight" x-text="'Station ' + station.sequence"></h3>
                                            <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold mt-1.5" 
                                                  :class="station.type === 'procedure' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800'" 
                                                  x-text="station.type.toUpperCase()"></span>
                                        </div>
                                        <div x-show="station.confirmed" class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center shadow-inner">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    </div>

                                    <p class="mt-4 text-base font-semibold text-slate-700 truncate" x-text="station.title || 'Untitled Station'"></p>
                                    
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <template x-for="status in getStationStatuses(station)">
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-wider ring-1 ring-inset"
                                                  :class="status.active ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-slate-50 text-slate-400 ring-slate-500/10'"
                                                  x-text="status.label"></span>
                                        </template>
                                    </div>
                                    
                                    <div class="mt-6 flex items-center text-sm text-blue-600 font-bold group-hover:text-blue-700 transition-colors">
                                        Open Workspace 
                                        <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ========================================== -->
    <!-- MODALS REMAIN EXACTLY THE SAME BELOW       -->
    <!-- ========================================== -->

    <!-- MODAL: ADD/EDIT STUDENT -->
    <div x-show="studentModal.open" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.away="studentModal.open = false" class="relative transform overflow-hidden rounded-2xl bg-white px-4 pb-4 pt-5 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <h3 class="text-xl font-bold leading-6 text-slate-900" x-text="studentModal.isEditing ? 'Edit Student Details' : 'Add New Student'"></h3>
                    
                    <form @submit.prevent="saveStudent" class="mt-6 space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Matric Number</label>
                            <input type="text" x-model="studentModal.data.matric" required class="mt-1 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Full Name</label>
                            <input type="text" x-model="studentModal.data.name" required class="mt-1 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700">Password</label>
                            <input type="text" x-model="studentModal.data.password" required class="mt-1 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border font-mono">
                            <p class="text-xs text-slate-500 mt-1 font-medium" x-show="!studentModal.isEditing">Auto-generated. You can overwrite this now.</p>
                        </div>
                        <div class="mt-6 sm:flex sm:flex-row-reverse gap-3">
                            <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-500 sm:w-auto transition-colors">Save Student</button>
                            <button type="button" @click="studentModal.open = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: MASSIVE STATION WORKSPACE -->
    <div x-show="stationModal.open" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/90 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div @click.away="closeStationWorkspace()" class="relative w-full max-w-5xl transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all flex flex-col" style="max-height: 90vh;">
                    
                    <div class="bg-slate-800 px-6 py-4 flex items-center justify-between flex-shrink-0">
                        <div>
                            <h2 class="text-xl font-bold text-white flex items-center gap-3">
                                <span x-text="'Station ' + (stationModal.station?.sequence || '')"></span> Workspace
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-black tracking-wider" 
                                      :class="stationModal.station?.type === 'procedure' ? 'bg-indigo-400/20 text-indigo-300 ring-1 ring-inset ring-indigo-400/30' : 'bg-amber-400/20 text-amber-300 ring-1 ring-inset ring-amber-400/30'" 
                                      x-text="stationModal.station?.type?.toUpperCase()"></span>
                            </h2>
                        </div>
                        <button @click="closeStationWorkspace()" class="text-slate-400 hover:text-white transition-colors bg-white/5 p-1.5 rounded-lg hover:bg-white/10">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <div class="px-6 py-6 overflow-y-auto modal-scroll flex-grow bg-slate-50">
                        <template x-if="stationModal.station">
                            <div class="space-y-8">
                                
                                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                                    <h3 class="text-lg font-bold text-slate-900 border-b pb-3 mb-5">Core Parameters</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-700">Station Title (e.g., Scenario Name)</label>
                                            <input type="text" x-model="stationModal.station.title" class="mt-1.5 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                                        </div>

                                        <div x-show="stationModal.station.type === 'procedure'">
                                            <label class="block text-sm font-bold text-slate-700">Assign Evaluating Examiner</label>
                                            <select x-model="stationModal.station.examiner_id" class="mt-1.5 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                                                <option value="">Select an Examiner...</option>
                                                <template x-for="ex in availableExaminers" :key="ex.id">
                                                    <option :value="ex.id" x-text="ex.name" :selected="stationModal.station.examiner_id == ex.id"></option>
                                                </template>
                                            </select>
                                        </div>

                                        <div x-show="stationModal.station.type === 'cbt'">
                                            <label class="block text-sm font-bold text-slate-700">Score per Question (Marks)</label>
                                            <input type="number" min="1" x-model="stationModal.station.score_per_question" class="mt-1.5 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border" placeholder="e.g. 2">
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center border-b pb-4 mb-5 gap-4">
                                        <h3 class="text-lg font-bold text-slate-900" x-text="isEditingInlineQuestion ? (questionForm.isEditing ? 'Edit Question' : 'Add New Question') : 'Question / Scenario Bank'"></h3>
                                        
                                        <div class="flex flex-wrap gap-2" x-show="!isEditingInlineQuestion">
                                            <a :href="stationModal.station.type === 'cbt' 
                                                ? 'data:text/csv;charset=utf-8,question%2Coption_a%2Coption_b%2Coption_c%2Coption_d%2Ccorrect_answer%0AWhat%20is%20the%20normal%20heart%20rate%3F%2C60-100%2C100-120%2C40-60%2C120-140%2CA' 
                                                : 'data:text/csv;charset=utf-8,question%2Cscore%0AWash%20hands%20before%20procedure%2C2'" 
                                            :download="stationModal.station.type === 'cbt' ? 'sample_cbt_questions.csv' : 'sample_procedure_questions.csv'" 
                                            class="inline-flex items-center text-xs font-bold text-slate-500 hover:text-slate-800 mr-2 transition-colors">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                Template
                                            </a>

                                            <input type="file" x-ref="questionFile" accept=".csv" class="hidden" @change="uploadBulkQuestions">
                                            <button @click="$refs.questionFile.click()" class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-200 transition-colors" :disabled="isUploadingQuestions">
                                                <span x-text="isUploadingQuestions ? 'Uploading...' : 'Bulk CSV Upload'"></span>
                                            </button>
                                            <button @click="openInlineQuestionForm()" class="inline-flex items-center rounded-lg bg-blue-50 border border-blue-200 px-3 py-2 text-sm font-bold text-blue-700 shadow-sm hover:bg-blue-100 transition-colors">
                                                + Add Question
                                            </button>
                                        </div>
                                        
                                        <div x-show="isEditingInlineQuestion">
                                            <button type="button" @click="cancelInlineQuestionForm()" class="inline-flex items-center text-sm font-bold text-slate-500 hover:text-slate-700 bg-slate-100 px-3 py-1.5 rounded-lg transition-colors">
                                                &larr; Back to Bank
                                            </button>
                                        </div>
                                    </div>

                                    <!-- VIEW A: THE READ ONLY LIST -->
                                    <div class="space-y-4" x-show="!isEditingInlineQuestion">
                                        <template x-for="(q, index) in stationModal.station.questions" :key="q.id || index">
                                            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex justify-between items-start group">
                                                <div class="pr-6 flex-grow">
                                                    <div class="flex items-center gap-3 mb-2">
                                                        <span class="text-[11px] font-black text-slate-500 uppercase tracking-widest bg-slate-100 px-2 py-0.5 rounded" x-text="'Question ' + (index + 1)"></span>
                                                        <span x-show="stationModal.station.type === 'procedure' && q.score" class="inline-flex items-center rounded px-2 py-0.5 text-[11px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100" x-text="q.score + ' Marks'"></span>
                                                    </div>
                                                    <p class="text-base text-slate-800 font-semibold whitespace-pre-wrap leading-relaxed" x-text="q.text"></p>
                                                    
                                                    <div x-show="stationModal.station.type === 'cbt' && (q.optA || q.optB || q.optC || q.optD)" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-slate-600 bg-slate-50 p-4 rounded-lg border border-slate-100">
                                                        <div class="flex items-start" :class="q.correct_answer === 'A' ? 'font-bold text-green-700 bg-green-50 p-1.5 -m-1.5 rounded' : ''"><span class="w-6 shrink-0 font-bold">A.</span> <span x-text="q.optA"></span></div>
                                                        <div class="flex items-start" :class="q.correct_answer === 'B' ? 'font-bold text-green-700 bg-green-50 p-1.5 -m-1.5 rounded' : ''"><span class="w-6 shrink-0 font-bold">B.</span> <span x-text="q.optB"></span></div>
                                                        <div class="flex items-start" :class="q.correct_answer === 'C' ? 'font-bold text-green-700 bg-green-50 p-1.5 -m-1.5 rounded' : ''"><span class="w-6 shrink-0 font-bold">C.</span> <span x-text="q.optC"></span></div>
                                                        <div class="flex items-start" :class="q.correct_answer === 'D' ? 'font-bold text-green-700 bg-green-50 p-1.5 -m-1.5 rounded' : ''"><span class="w-6 shrink-0 font-bold">D.</span> <span x-text="q.optD"></span></div>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col sm:flex-row gap-2 flex-shrink-0 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <button @click="openInlineQuestionForm(q)" class="text-blue-600 hover:text-blue-800 p-2 bg-blue-50 rounded-lg transition-colors" title="Edit Question">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    </button>
                                                    <button @click="deleteSingleQuestion(q.id, index)" class="text-red-500 hover:text-red-700 p-2 bg-red-50 rounded-lg transition-colors" title="Delete Question">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                        
                                        <div x-show="!stationModal.station.questions || stationModal.station.questions.length === 0" class="text-center py-12 text-sm text-slate-500 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50/50">
                                            <svg class="mx-auto h-10 w-10 text-slate-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            No questions or scenarios added to this station yet.
                                        </div>
                                    </div>

                                    <!-- VIEW B: THE INLINE FORM -->
                                    <form @submit.prevent="saveSingleQuestion" x-show="isEditingInlineQuestion" style="display: none;" class="space-y-5 bg-slate-50 p-6 rounded-xl border border-slate-200">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-800 mb-1.5">Question / Scenario Text</label>
                                            <textarea x-model="questionForm.data.text" rows="3" required class="block w-full rounded-lg border-slate-300 py-3 px-4 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border" placeholder="Enter full question or clinical scenario..."></textarea>
                                        </div>

                                        <div x-show="stationModal.station?.type === 'procedure'">
                                            <label class="block text-sm font-bold text-slate-800 mb-1.5">Marks for this step</label>
                                            <input type="number" min="1" step="0.5" x-model="questionForm.data.score" class="block w-full sm:w-1/3 rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border">
                                        </div>

                                        <div x-show="stationModal.station?.type === 'cbt'" class="space-y-5">
                                            <h4 class="text-sm font-bold text-slate-800 border-t border-slate-200 pt-4 mt-2">Multiple Choice Options</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Option A</label>
                                                    <input type="text" x-model="questionForm.data.optA" class="mt-1.5 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm sm:text-sm border focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Option B</label>
                                                    <input type="text" x-model="questionForm.data.optB" class="mt-1.5 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm sm:text-sm border focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Option C</label>
                                                    <input type="text" x-model="questionForm.data.optC" class="mt-1.5 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm sm:text-sm border focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Option D</label>
                                                    <input type="text" x-model="questionForm.data.optD" class="mt-1.5 block w-full rounded-lg border-slate-300 py-2.5 px-3 shadow-sm sm:text-sm border focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                            </div>
                                            <div class="pt-2">
                                                <label class="block text-sm font-bold text-slate-800 mb-1.5">Correct Answer</label>
                                                <select x-model="questionForm.data.correct_answer" class="block w-full md:w-1/2 rounded-lg border-slate-300 py-2.5 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border bg-white">
                                                    <option value="">-- Select Correct Option --</option>
                                                    <option value="A">Option A</option>
                                                    <option value="B">Option B</option>
                                                    <option value="C">Option C</option>
                                                    <option value="D">Option D</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mt-6 flex flex-row-reverse gap-3 border-t border-slate-200 pt-5">
                                            <button type="submit" :disabled="questionForm.isSaving" class="inline-flex justify-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-500 transition-colors">
                                                <span x-text="questionForm.isSaving ? 'Saving...' : 'Save Question'"></span>
                                            </button>
                                            <button type="button" @click="cancelInlineQuestionForm()" class="inline-flex justify-center rounded-lg bg-white px-6 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors">
                                                Cancel
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="bg-slate-100 px-6 py-4 flex items-center justify-between border-t border-slate-200 flex-shrink-0">
                        <div class="text-sm text-slate-500">
                            Status: <span class="font-bold" :class="stationModal.station?.confirmed ? 'text-green-600' : 'text-amber-600'" x-text="stationModal.station?.confirmed ? 'Confirmed & Locked' : 'Draft / Unconfirmed'"></span>
                        </div>
                        <div class="flex gap-3">
                            <button type="button" @click="saveStationDraft()" class="rounded-lg bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors">
                                Save Settings
                            </button>
                            <button type="button" @click="confirmStation()" class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-green-500 transition-colors">
                                Confirm Station
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Global Toast Notification -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-[70]">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            <div x-show="toast.visible" x-transition.opacity class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl bg-slate-800 shadow-xl ring-1 ring-black ring-opacity-5" style="display: none;">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-bold text-white" x-text="toast.message"></p>
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
                isUploadingQuestions: false,
                passwordStrategy: 'generate',
                toast: { visible: false, message: '' },
                
                sessionData: { title: 'Loading...', date: '...', department: '...' },
                students: [],
                availableExaminers: [],
                stations: [],
                
                studentModal: { open: false, isEditing: false, data: { id: null, matric: '', name: '', password: '' } },
                stationModal: { open: false, station: null },
                
                isEditingInlineQuestion: false,
                questionForm: { 
                    isEditing: false, 
                    isSaving: false,
                    data: { id: null, text: '', optA: '', optB: '', optC: '', optD: '', correct_answer: '', score: 1 } 
                },

                init() {
                    if (!this.sessionId) { alert("Critical Error: No Session ID provided."); return; }
                    this.fetchWorkspaceData();
                },

                getBaseApiUrl() {
                    const tenantSlug = '<?php echo CURRENT_TENANT_SLUG ?? ""; ?>';
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return tenantSlug ? `${basePath}/${tenantSlug}` : basePath;
                },

                // --- NEW Smart Back Behavior ---
                goBack() {
                    // Check if the user navigated from within our app. If not, default to the index page.
                    if (document.referrer && document.referrer.includes(window.location.host)) {
                        window.history.back();
                    } else {
                        window.location.href = this.getBaseApiUrl() + '/admin/sessions';
                    }
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    setTimeout(() => { this.toast.visible = false; }, 3000);
                },

                async fetchWorkspaceData() {
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/data?id=' + this.sessionId);
                        let data = await response.json();
                        if (data.success) {
                            if(data.payload.sessionData.date) {
                                data.payload.sessionData.date = new Date(data.payload.sessionData.date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                            }
                            this.sessionData = data.payload.sessionData;
                            this.students = data.payload.students;
                            this.availableExaminers = data.payload.availableExaminers;
                            this.stations = data.payload.stations;
                        } else { alert(data.message); }
                    } catch (error) { this.showToast('Network error loading data.'); }
                },

               async uploadBulkRoster() {
                    let fileInput = this.$refs.rosterFile;
                    if (!fileInput.files.length) { alert('Please select a CSV file first.'); return; }

                    this.isUploading = true;
                    let formData = new FormData();
                    formData.append('roster_file', fileInput.files[0]);
                    formData.append('session_id', this.sessionId);
                    formData.append('password_strategy', this.passwordStrategy);

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/student/upload', { method: 'POST', body: formData });
                        let data = await response.json();
                        if (data.success) {
                            this.fetchWorkspaceData();
                            this.showToast('Bulk Roster ingested successfully.');
                            fileInput.value = ''; 
                        } else { alert(data.message || 'CSV Upload failed.'); }
                    } catch (e) { alert('Network Error during upload.'); } finally { this.isUploading = false; }
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
                
                async saveStudent() {
                    let payload = { ...this.studentModal.data, session_id: this.sessionId };
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/student/save', {
                            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.fetchWorkspaceData(); 
                            this.showToast('Student saved successfully.');
                            this.studentModal.open = false;
                        } else { alert(data.message || 'Failed to save student.'); }
                    } catch (e) { alert('Network Error'); }
                },

                async deleteStudent(id) {
                    if(confirm('Remove this student from the session?')) {
                        try {
                            let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/student/remove', {
                                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ student_id: id, session_id: this.sessionId })
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
                    if (station.type === 'procedure') { statuses.push({ label: 'Station Assigned', active: !!station.examiner_id }); }
                    statuses.push({ label: 'Station Confirmed', active: station.confirmed });
                    return statuses;
                },

                async processStationSave(isConfirmed) {
                    if (isConfirmed) {
                        if(!this.stationModal.station.title || this.stationModal.station.title.trim() === '') { alert("Cannot confirm: Station Title is required."); return; }
                        if(this.stationModal.station.type === 'procedure' && (!this.stationModal.station.examiner_id || this.stationModal.station.examiner_id === '')) { alert("Cannot confirm: Procedure stations must have an assigned examiner."); return; }
                        if(this.stationModal.station.type === 'cbt' && !this.stationModal.station.score_per_question) { alert("Cannot confirm: Please set the Score per Question (Marks)."); return; }
                        
                        let hasValidQuestion = this.stationModal.station.questions && this.stationModal.station.questions.length > 0;
                        if(!hasValidQuestion) { alert("Cannot confirm: You must add at least one question or scenario."); return; }
                    }
                    
                    this.stationModal.station.confirmed = isConfirmed;

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/station/save', {
                            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ station: this.stationModal.station })
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.fetchWorkspaceData(); 
                            this.showToast(isConfirmed ? 'Station Configuration Confirmed!' : 'Station draft settings saved.');
                            this.closeStationWorkspace();
                        } else { alert(data.message || 'Failed to save station configuration.'); }
                    } catch(e) { alert('Network Error during save.'); }
                },

                saveStationDraft() { this.processStationSave(false); },
                confirmStation() { this.processStationSave(true); },

                openStationWorkspace(station) {
                    let st = JSON.parse(JSON.stringify(station));
                    st.examiner_id = st.examiner_id || ''; 
                    this.stationModal.station = st;
                    this.isEditingInlineQuestion = false;
                    this.stationModal.open = true;
                },
                closeStationWorkspace() {
                    this.stationModal.open = false;
                    this.stationModal.station = null;
                },

                openInlineQuestionForm(question = null) {
                    if(question) {
                        this.questionForm.isEditing = true;
                        this.questionForm.data = { ...question };
                    } else {
                        this.questionForm.isEditing = false;
                        this.questionForm.data = { id: null, text: '', optA: '', optB: '', optC: '', optD: '', correct_answer: '', score: 1 };
                    }
                    this.isEditingInlineQuestion = true;
                },

                cancelInlineQuestionForm() {
                    this.isEditingInlineQuestion = false;
                },

                async saveSingleQuestion() {
                    this.questionForm.isSaving = true;
                    let payload = { ...this.questionForm.data, station_id: this.stationModal.station.id };
                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/question/save', {
                            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload)
                        });
                        let data = await response.json();
                        if(data.success) {
                            this.showToast('Question saved to bank!');
                            this.isEditingInlineQuestion = false;
                            
                            await this.fetchWorkspaceData(); 
                            let updatedStation = this.stations.find(s => s.id === this.stationModal.station.id);
                            if(updatedStation) { this.stationModal.station.questions = updatedStation.questions; }
                        } else { alert(data.message || 'Failed to save question.'); }
                    } catch(e) { alert('Network error saving question.'); } finally { this.questionForm.isSaving = false; }
                },

                async deleteSingleQuestion(id, index) {
                    if(confirm('Are you sure you want to delete this question?')) {
                        try {
                            let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/question/delete', {
                                method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id })
                            });
                            let data = await response.json();
                            if(data.success) {
                                this.stationModal.station.questions.splice(index, 1);
                                this.showToast('Question deleted.');
                                await this.fetchWorkspaceData();
                            } else { alert(data.message || 'Failed to delete question.'); }
                        } catch(e) { alert('Network error deleting question.'); }
                    }
                },

                async uploadBulkQuestions(e) {
                    let file = e.target.files[0];
                    if(!file) return;

                    this.isUploadingQuestions = true;
                    let formData = new FormData();
                    formData.append('question_file', file);
                    formData.append('station_id', this.stationModal.station.id);

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/admin/workspace/question/upload', {
                            method: 'POST', body: formData
                        });
                        let data = await response.json();
                        if (data.success) {
                            this.showToast('Questions uploaded successfully.');
                            await this.fetchWorkspaceData(); 
                            let updatedStation = this.stations.find(s => s.id === this.stationModal.station.id);
                            if(updatedStation) { this.stationModal.station.questions = updatedStation.questions; }
                        } else { alert(data.message || 'Bulk Upload failed.'); }
                    } catch (err) { alert('Network Error during upload.'); } finally { 
                        this.isUploadingQuestions = false; 
                        e.target.value = ''; 
                    }
                }
            }
        }
    </script>
</body>
</html>