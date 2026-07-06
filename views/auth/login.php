<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAOSCE - Login</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; }
        .login-card h2 { text-align: center; color: #1f2937; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #4b5563; font-weight: 500; font-size: 0.875rem; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #3b82f6; ring: 2px; }
        .btn-submit { width: 100%; background-color: #3b82f6; color: white; padding: 0.75rem; border: none; border-radius: 4px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: background-color 0.2s; }
        .btn-submit:hover { background-color: #2563eb; }
        .btn-submit:disabled { background-color: #9ca3af; cursor: not-allowed; }
        .error-message { background-color: #fee2e2; color: #b91c1c; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.875rem; text-align: center; }
        .tenant-badge { text-align: center; margin-bottom: 1rem; color: #6b7280; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>

<div class="login-card" x-data="loginController()">
    
    <!-- Show the current tenant slug if we routed through one -->
    <?php if(defined('CURRENT_TENANT_SLUG') && CURRENT_TENANT_SLUG !== null): ?>
        <div class="tenant-badge">Workspace: <strong><?php echo htmlspecialchars(CURRENT_TENANT_SLUG); ?></strong></div>
    <?php endif; ?>

    <h2>CAOSCE Login</h2>

    <div x-show="errorMessage" x-text="errorMessage" class="error-message" style="display: none;"></div>

    <form @submit.prevent="submitLogin">
        <div class="form-group">
            <label for="username">Username / Matric Number</label>
            <input type="text" id="username" x-model="formData.username" required placeholder="Enter your ID">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" x-model="formData.password" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn-submit" :disabled="isLoading">
            <span x-text="isLoading ? 'Authenticating...' : 'Sign In'"></span>
        </button>
    </form>
</div>

<script>
function loginController() {
    return {
        formData: {
            username: '',
            password: ''
        },
        errorMessage: '',
        isLoading: false,

        async submitLogin() {
            this.isLoading = true;
            this.errorMessage = '';

            // Grab the device signature if it was previously set by an Admin
            let deviceSig = localStorage.getItem('caosce_device_signature');

            // 1. Grab the current URL (e.g., /pro/caosce_app/yag/login)
            let currentPath = window.location.pathname;

            // 2. Swap the word 'login' for 'api/login' to dynamically build the right path
            let apiUrl = currentPath.replace('/login', '/api/login');

            try {
                // 3. Make the fetch request using the dynamic apiUrl
                let response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        username: this.formData.username,
                        password: this.formData.password,
                        device_signature: deviceSig
                    })
                });

                let data = await response.json();

                if (data.success) {
                    // If login is successful, redirect to the URL provided by the controller
                    // Ensure the redirect also respects the local subfolder
                    let basePath = currentPath.split('/login')[0]; 
                    window.location.href = basePath + data.redirect_url.replace('/admin', '/admin'); // Simplistic handling, adjust if needed based on controller output
                    
                    // A safer redirect that appends the backend's redirect path to your base path:
                    window.location.href = currentPath.replace('/login', data.redirect_url);
                } else {
                    // Show error from backend (e.g., "Invalid credentials" or "Not bound")
                    this.errorMessage = data.message || 'Login failed. Please try again.';
                }
            } catch (error) {
                this.errorMessage = 'Network error. Could not reach the server.';
                console.error(error);
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
</body>
</html>