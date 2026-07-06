<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CASOCE - Device Setup</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased h-screen flex flex-col justify-center items-center" x-data="gatekeeperController()" x-cloak>

    <div class="w-full max-w-md px-6">
        
        <!-- Branding -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-600/20 text-blue-500 mb-4 ring-1 ring-blue-500/50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">CASOCE Provisioning</h1>
            <p class="text-sm text-slate-400 mt-2">Enter the 6-character binding PIN to authorize and configure this device.</p>
        </div>

        <!-- Gatekeeper Form -->
        <div class="bg-slate-800 rounded-xl shadow-2xl border border-slate-700 p-8">
            
            <div x-show="errorMessage" x-transition class="mb-6 bg-red-500/10 border border-red-500/50 rounded-md p-3 flex items-start" style="display: none;">
                <svg class="h-5 w-5 text-red-400 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span class="text-sm text-red-400 font-medium" x-text="errorMessage"></span>
            </div>

            <form @submit.prevent="verifyPin" class="space-y-6">
                <div>
                    <label for="pin" class="sr-only">Binding PIN</label>
                    <input type="text" 
                           id="pin" 
                           x-model="pin" 
                           @input="formatPin"
                           maxlength="8" 
                           autocomplete="off"
                           placeholder="XX-XX-XX" 
                           class="block w-full rounded-lg border-0 bg-slate-900/50 py-4 px-4 text-center text-3xl font-mono font-bold text-white uppercase tracking-widest shadow-inner ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-blue-500 placeholder:text-slate-600">
                </div>

                <button type="submit" 
                        class="flex w-full justify-center items-center rounded-lg bg-blue-600 px-3 py-4 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 disabled:opacity-50 transition-colors" 
                        :disabled="pin.length !== 8 || isVerifying">
                    
                    <svg x-show="isVerifying" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span x-text="isVerifying ? 'Verifying PIN...' : 'Unlock Device Setup'"></span>
                </button>
            </form>
        </div>

        <div class="text-center mt-8">
            <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Tenant Workspace: <?php echo htmlspecialchars(CURRENT_TENANT_SLUG ?? 'GLOBAL'); ?></span>
        </div>
    </div>

    <script>
        const TENANT_SLUG = '<?php echo CURRENT_TENANT_SLUG ?? 'global'; ?>';

        function gatekeeperController() {
            return {
                pin: '',
                isVerifying: false,
                errorMessage: '',

                // Forces uppercase and automatically injects hyphens (e.g. A4X9M2 -> A4-X9-M2)
                formatPin(e) {
                    // Strip all non-alphanumeric characters
                    let clean = e.target.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
                    
                    // Rebuild with hyphens
                    let formatted = '';
                    for (let i = 0; i < clean.length; i++) {
                        if (i === 2 || i === 4) formatted += '-';
                        formatted += clean[i];
                    }
                    
                    this.pin = formatted;
                },

                async verifyPin() {
                    this.errorMessage = '';
                    this.isVerifying = true;

                    try {
                        /*
                        // IN PRODUCTION: Call your PHP backend to verify the PIN
                        let response = await fetch(`/${TENANT_SLUG}/api/setup/verify-pin`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ pin: this.pin })
                        });
                        let data = await response.json();
                        */

                        // Simulating network validation delay
                        await new Promise(resolve => setTimeout(resolve, 800));

                        // Mock Validation Logic
                        if (this.pin === 'A4-X9-M2') {
                            
                            // Generate a temporary auth token to prove this browser entered the PIN
                            // The Device Binding Wizard will check for this token before allowing setup
                            let sessionToken = btoa(Date.now() + this.pin); 
                            sessionStorage.setItem('caosce_provision_token', sessionToken);
                            
                            // Redirect to the binding wizard
                            window.location.href = `/${TENANT_SLUG}/admin/bind-device`;
                        } else {
                            this.errorMessage = 'Invalid or deactivated PIN. Please check with the administrator.';
                        }
                    } catch (error) {
                        this.errorMessage = 'Network error. Could not verify PIN.';
                    } finally {
                        this.isVerifying = false;
                    }
                }
            }
        }
    </script>
</body>
</html>