<!-- views/partials/modals.php -->
<div x-data="universalModals()" 
     @open-contact.window="modals.contact = true" 
     @open-request.window="modals.request = true">

    <!-- Contact Us Modal -->
    <div x-show="modals.contact" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.outside="modals.contact = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-800">Contact Support</h3>
                <button @click="modals.contact = false" class="text-slate-400 hover:text-slate-600 bg-white p-2 rounded-full shadow-sm"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form @submit.prevent="submitContact" class="p-6 space-y-4">
                <div x-show="formStatus.contact === 'success'" class="bg-green-50 text-green-700 p-4 rounded-xl text-sm font-bold border border-green-200">
                    Message sent successfully! Our team will reach out shortly.
                </div>
                <div x-show="formStatus.contact !== 'success'">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Full Name</label>
                        <input type="text" x-model="contactForm.name" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Email Address</label>
                        <input type="email" x-model="contactForm.email" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Phone Number</label>
                        <input type="tel" x-model="contactForm.phone" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Message</label>
                        <textarea x-model="contactForm.message" required rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all resize-none"></textarea>
                    </div>
                    <button type="submit" :disabled="formStatus.contact === 'loading'" class="w-full mt-6 bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-xl transition-all disabled:opacity-70 flex justify-center items-center gap-2">
                        <span x-text="formStatus.contact === 'loading' ? 'Sending...' : 'Send Message'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Access Modal -->
    <div x-show="modals.request" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.outside="modals.request = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <div class="px-6 py-6 border-b border-slate-100 flex justify-between items-center bg-blue-600 text-white">
                <div>
                    <h3 class="text-xl font-bold">Request Institution Access</h3>
                    <p class="text-blue-100 text-xs mt-1">Get your school set up on CAOSCE</p>
                </div>
                <button @click="modals.request = false" class="text-blue-100 hover:text-white bg-blue-700 p-2 rounded-full"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            <form @submit.prevent="submitRequest" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <div x-show="formStatus.request === 'success'" class="bg-blue-50 text-blue-700 p-4 rounded-xl text-sm font-bold border border-blue-200">
                    Application received! Our onboarding team will review your institution details and contact you to provision your workspace.
                </div>
                <div x-show="formStatus.request !== 'success'" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Institution Name</label>
                        <input type="text" x-model="requestForm.institution_name" required placeholder="e.g. College of Nursing Sciences" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Contact Person</label>
                            <input type="text" x-model="requestForm.contact_person" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Official Role</label>
                            <select x-model="requestForm.role" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all bg-white">
                                <option value="">Select role...</option>
                                <option value="Provost / Dean">Provost / Dean</option>
                                <option value="Head of Department">Head of Department</option>
                                <option value="Chief Examiner">Chief Examiner</option>
                                <option value="IT Administrator">IT Administrator</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Official Email</label>
                            <input type="email" x-model="requestForm.email" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1.5">Phone Number</label>
                            <input type="tel" x-model="requestForm.phone" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Estimated Student Capacity</label>
                        <select x-model="requestForm.capacity" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm outline-none transition-all bg-white">
                            <option value="">Select size...</option>
                            <option value="Under 100 students">Under 100 students</option>
                            <option value="100 - 500 students">100 - 500 students</option>
                            <option value="500+ students">500+ students</option>
                        </select>
                    </div>
                    <button type="submit" :disabled="formStatus.request === 'loading'" class="w-full mt-6 bg-blue-600 hover:bg-blue-500 text-white font-bold py-3.5 px-6 rounded-xl transition-all disabled:opacity-70 flex justify-center items-center gap-2 shadow-lg shadow-blue-600/20">
                        <span x-text="formStatus.request === 'loading' ? 'Submitting Application...' : 'Submit Request'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function universalModals() {
        return {
            modals: { contact: false, request: false },
            formStatus: { contact: 'idle', request: 'idle' },
            
            // Data models bound to the form inputs via x-model
            contactForm: { name: '', email: '', phone: '', message: '' },
            requestForm: { institution_name: '', contact_person: '', role: '', email: '', phone: '', capacity: '' },

            async submitContact() {
                this.formStatus.contact = 'loading';
                try {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    let response = await fetch(`${basePath}/api/web/contact`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.contactForm)
                    });
                    
                    let data = await response.json();
                    if (data.success) {
                        this.formStatus.contact = 'success';
                        // Reset form
                        this.contactForm = { name: '', email: '', phone: '', message: '' };
                        setTimeout(() => { 
                            this.modals.contact = false; 
                            this.formStatus.contact = 'idle';
                        }, 3000);
                    } else {
                        alert(data.message);
                        this.formStatus.contact = 'idle';
                    }
                } catch (error) {
                    alert("Network error. Please try again.");
                    this.formStatus.contact = 'idle';
                }
            },

            async submitRequest() {
                this.formStatus.request = 'loading';
                try {
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    let response = await fetch(`${basePath}/api/web/request-demo`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.requestForm)
                    });
                    
                    let data = await response.json();
                    if (data.success) {
                        this.formStatus.request = 'success';
                        // Reset form
                        this.requestForm = { institution_name: '', contact_person: '', role: '', email: '', phone: '', capacity: '' };
                        setTimeout(() => { 
                            this.modals.request = false;
                            this.formStatus.request = 'idle';
                        }, 4000);
                    } else {
                        alert(data.message);
                        this.formStatus.request = 'idle';
                    }
                } catch (error) {
                    alert("Network error. Please try again.");
                    this.formStatus.request = 'idle';
                }
            }
        }
    }
</script>