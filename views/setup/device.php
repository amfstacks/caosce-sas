<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Device Provisioning</title>
    <!-- Use local assets if available, fallback to CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased h-screen flex flex-col justify-center items-center relative overflow-hidden" x-data="gatekeeperController()" x-cloak>

    <!-- Background Ambient Glow -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-3xl h-64 bg-blue-600/10 blur-[100px] rounded-full pointer-events-none"></div>

    <div class="w-full max-w-md px-6 relative z-10">
        
        <!-- Branding -->
        <div class="text-center mb-8 transition-all duration-500" :class="step === 'verified' ? 'scale-95 opacity-80' : 'scale-100 opacity-100'">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-500/10 text-blue-500 mb-4 ring-1 ring-blue-500/30 shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">CASOCE Provisioning</h1>
            <p class="text-sm text-slate-400 mt-2" x-text="step === 'input' ? 'Enter the 6-character binding PIN to authorize and configure this device.' : 'Verify target assignment before proceeding.'"></p>
        </div>

        <!-- Gatekeeper Card -->
        <div class="bg-slate-800 rounded-2xl shadow-2xl border border-slate-700/60 p-8 backdrop-blur-sm relative overflow-hidden">
            
            <!-- Error Banner -->
            <div x-show="errorMessage" x-transition.opacity class="mb-6 bg-red-500/10 border border-red-500/30 rounded-lg p-3 flex items-start">
                <svg class="h-5 w-5 text-red-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span class="text-sm text-red-400 font-medium leading-tight" x-text="errorMessage"></span>
            </div>

            <!-- STEP 1: PIN INPUT FORM -->
            <form @submit.prevent="verifyPin" x-show="step === 'input'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
                <div>
                    <label for="pin" class="sr-only">Binding PIN</label>
                    <input type="text" 
                           id="pin" 
                           x-model="pin" 
                           @input="formatPin"
                           maxlength="8" 
                           autocomplete="off"
                           placeholder="XX-XX-XX" 
                           class="block w-full rounded-xl border-0 bg-slate-900/60 py-4 px-4 text-center text-3xl font-mono font-bold text-white uppercase tracking-[0.3em] shadow-inner ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 placeholder:text-slate-700 transition-all">
                </div>

                <button type="submit" 
                        class="flex w-full justify-center items-center rounded-xl bg-blue-600 px-3 py-4 text-sm font-bold text-white shadow-lg shadow-blue-900/20 hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all" 
                        :disabled="pin.length !== 8 || isVerifying">
                    <svg x-show="isVerifying" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isVerifying ? 'Authenticating...' : 'Unlock Device Setup'"></span>
                </button>
            </form>

            <!-- STEP 2: VERIFIED DETAILS & PROCEED -->
            <div x-show="step === 'verified'" style="display: none;" x-transition:enter="transition ease-out duration-300 delay-150" x-transition:enter-start="opacity-0 -translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">
                
                <div class="flex items-center justify-center gap-3 text-green-400 mb-2">
                    <div class="rounded-full bg-green-400/10 p-1.5 ring-1 ring-green-400/30">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold">PIN Validated</h3>
                </div>

                <div class="bg-slate-900/60 rounded-xl p-5 border border-slate-700/50 space-y-4 shadow-inner">
                    <!-- Session -->
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Target Exam Session</p>
                        <p class="text-base font-semibold text-white leading-tight" x-text="payload?.session_title"></p>
                    </div>
                    
                    <div class="h-px w-full bg-slate-700/50"></div>

                    <!-- Station -->
                    <div>
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Assigned Station</p>
                        <div class="flex items-start gap-2">
                            <span class="inline-flex items-center rounded bg-blue-500/10 px-2 py-0.5 text-xs font-bold text-blue-400 ring-1 ring-inset ring-blue-500/20 whitespace-nowrap" x-text="'Station ' + payload?.station_sequence"></span>
                            <p class="text-sm font-medium text-slate-300 leading-snug" x-text="payload?.station_title"></p>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="step = 'input'; pin = '';" type="button" class="flex-1 rounded-xl bg-slate-700 px-3 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-slate-600 transition-colors">
                        Cancel
                    </button>
                    <button @click="proceedToBind" type="button" class="flex-[2] rounded-xl bg-green-600 px-3 py-3.5 text-sm font-bold text-white shadow-lg shadow-green-900/20 hover:bg-green-500 transition-colors flex justify-center items-center gap-2">
                        <span>Proceed to Bind</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </div>

        </div>

        <div class="text-center mt-8">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-widest">Workspace: <?php echo htmlspecialchars(CURRENT_TENANT_SLUG ?? 'GLOBAL'); ?></span>
        </div>
    </div>

    <script>
        function gatekeeperController() {
            return {
                step: 'input', // 'input' or 'verified'
                pin: '',
                isVerifying: false,
                errorMessage: '',
                payload: null,

                getBaseApiUrl() {
                    const tenantSlug = '<?php echo CURRENT_TENANT_SLUG ?? ""; ?>';
                    let basePath = '<?php echo defined("BASE_PATH") ? BASE_PATH : ""; ?>';
                    return tenantSlug ? `${basePath}/${tenantSlug}` : basePath;
                },

                formatPin(e) {
                    let clean = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                    let formatted = '';
                    for (let i = 0; i < clean.length; i++) {
                        if (i === 2 || i === 4) formatted += '-';
                        formatted += clean[i];
                    }
                    this.pin = formatted;
                    this.errorMessage = ''; // Clear error on typing
                },

                async verifyPin() {
                    this.errorMessage = '';
                    this.isVerifying = true;

                    try {
                        let response = await fetch(this.getBaseApiUrl() + '/api/setup/verify-pin', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ pin: this.pin })
                        });
                        
                        let data = await response.json();

                        if (data.success) {
                            this.payload = data.payload;
                            // print(data.payload);
                            // print('data.payload');
                            // Smooth transition
                            setTimeout(() => {
                                this.step = 'verified';
                            }, 300);
                        } else {
                            this.errorMessage = data.message || 'Invalid or revoked PIN code.';
                        }
                    } catch (error) {
                        this.errorMessage = 'Network error. Could not connect to server.';
                    } finally {
                        this.isVerifying = false;
                    }
                },

                proceedToBind() {
                    // Save the valid PIN or session token so the next page knows we are authorized
                    sessionStorage.setItem('caosce_provision_pin', this.pin);
                    // sessionStorage.setItem('caosce_target_station', this.payload.station_id);
                    // Save the ENTIRE payload as a JSON string so the next screen can display the titles!
                    sessionStorage.setItem('caosce_binding_payload', JSON.stringify(this.payload));
                    
                    // Redirect to the actual binding wizard
                    window.location.href = this.getBaseApiUrl() + '/admin/bind-device';
                }
            }
        }
    </script>
</body>
</html>