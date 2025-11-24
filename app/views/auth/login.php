<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Barangay E-Credentials</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/1041/1041916.png" type="image/png">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(135deg, #EFF6FF 0%, #E0E7FF 50%, #F3E8FF 100%);
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      overflow-x: hidden;
    }

    /* Header */
    .header {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(12px);
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border-bottom: 1px solid rgba(229, 231, 235, 0.8);
      padding: 1rem 0;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .header-content {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .logo-section {
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .logo-icon {
      background: linear-gradient(135deg, #2563EB 0%, #4F46E5 100%);
      padding: 0.5rem;
      border-radius: 0.75rem;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .logo-icon i {
      color: white;
      font-size: 1.5rem;
    }

    .logo-text h1 {
      font-size: 1.25rem;
      font-weight: 700;
      background: linear-gradient(135deg, #2563EB 0%, #4F46E5 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0;
    }

    .logo-text p {
      font-size: 0.75rem;
      color: #6B7280;
      margin: 0;
    }

    .btn-back {
      background: transparent;
      border: none;
      color: #6B7280;
      font-weight: 500;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
    }

    .btn-back:hover {
      background: rgba(255, 255, 255, 0.5);
      color: #374151;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem 1rem;
    }

    .login-container {
      max-width: 450px;
      width: 100%;
    }

    /* Welcome Section */
    .welcome-section {
      text-align: center;
      margin-bottom: 2rem;
    }

    .icon-circle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 5rem;
      height: 5rem;
      background: linear-gradient(135deg, #2563EB 0%, #4F46E5 100%);
      border-radius: 50%;
      box-shadow: 0 10px 30px rgba(37, 99, 235, 0.4);
      margin-bottom: 1rem;
      animation: pulse-icon 2s ease-in-out infinite;
    }

    @keyframes pulse-icon {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    .icon-circle i {
      color: white;
      font-size: 2rem;
    }

    .welcome-section h2 {
      font-size: 2.5rem;
      font-weight: 700;
      background: linear-gradient(135deg, #111827 0%, #374151 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 0.5rem;
    }

    .welcome-section p {
      color: #6B7280;
      font-size: 1rem;
    }

    /* Login Card */
    .login-card {
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(12px);
      border-radius: 1.5rem;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(229, 231, 235, 0.8);
      padding: 2.5rem;
      animation: slideUp 0.6s ease;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Form Styles */
    .form-label {
      color: #374151;
      font-weight: 600;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-label i {
      color: #2563EB;
    }

    .form-control {
      border: 2px solid #E5E7EB;
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      font-size: 0.95rem;
      transition: all 0.2s;
    }

    .form-control:focus {
      border-color: #2563EB;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
      outline: none;
    }

    /* Remember Me & Forgot Password */
    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 1rem;
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .remember-me input[type="checkbox"] {
      width: 1rem;
      height: 1rem;
      cursor: pointer;
    }

    .remember-me label {
      font-size: 0.9rem;
      color: #374151;
      font-weight: 500;
      cursor: pointer;
      margin: 0;
    }

    .forgot-link {
      font-size: 0.9rem;
      color: #2563EB;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.2s;
    }

    .forgot-link:hover {
      color: #1D4ED8;
    }

    /* Buttons */
    .btn-primary {
      background: linear-gradient(135deg, #2563EB 0%, #4F46E5 100%);
      border: none;
      border-radius: 0.75rem;
      padding: 0.875rem 1.5rem;
      font-weight: 600;
      font-size: 1rem;
      color: white;
      width: 100%;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
      margin-top: 1.5rem;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #1D4ED8 0%, #4338CA 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }

    .btn-google {
      background: white;
      border: 2px solid #E5E7EB;
      border-radius: 0.75rem;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      color: #374151;
      width: 100%;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-google:hover {
      background: #F9FAFB;
      border-color: #2563EB;
      color: #2563EB;
      transform: translateY(-1px);
    }

    .btn-google img {
      width: 20px;
      height: 20px;
    }

    /* Divider */
    .divider {
      position: relative;
      text-align: center;
      margin: 2rem 0;
    }

    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background: #D1D5DB;
    }

    .divider span {
      background: rgba(255, 255, 255, 0.8);
      color: #6B7280;
      font-size: 0.9rem;
      font-weight: 500;
      padding: 0 1rem;
      position: relative;
    }

    /* Footer Links */
    .footer-links {
      text-align: center;
      margin-top: 1.5rem;
    }

    .footer-links a {
      color: #2563EB;
      font-weight: 600;
      text-decoration: none;
      transition: color 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .footer-links a:hover {
      color: #1D4ED8;
      text-decoration: underline;
    }

    /* Security Badge */
    .security-badge {
      text-align: center;
      margin-top: 1.5rem;
    }

    .badge-content {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(255, 255, 255, 0.6);
      backdrop-filter: blur(8px);
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-size: 0.9rem;
      color: #6B7280;
    }

    .badge-content i {
      color: #10B981;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .welcome-section h2 {
        font-size: 2rem;
      }

      .login-card {
        padding: 1.5rem;
      }

      .header-content {
        padding: 0 1rem;
      }

      .form-options {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
      }
    }
  </style>
</head>
<body>

  <!-- Header -->
  <header class="header">
    <div class="header-content">
      <div class="logo-section">
        <div class="logo-icon">
          <i class="bi bi-shield-check"></i>
        </div>
        <div class="logo-text">
          <h1>Barangay E-Credentials</h1>
          <p>Document Management System</p>
        </div>
      </div>
      <a href="/" class="btn-back">
        <i class="bi bi-arrow-left me-2"></i>Back to Home
      </a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="main-content">
    <div class="login-container">
      <!-- Welcome Section -->
      <div class="welcome-section">
        <div class="icon-circle">
          <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2>Welcome Back!</h2>
        <p>Sign in to access your barangay credentials dashboard</p>
      </div>

      <!-- Login Card -->
      <div class="login-card">
        <form action="<?= site_url('login') ?>" method="POST" id="loginForm">
          <div>
            <label for="email" class="form-label">
              <i class="bi bi-envelope-fill"></i>
              Email address
            </label>
            <input 
              type="email" 
              name="email" 
              id="email" 
              class="form-control" 
              placeholder="juan@email.com" 
              required 
              autocomplete="email"
            >
          </div>

          <div style="margin-top: 1.5rem;">
            <label for="password" class="form-label">
              <i class="bi bi-lock-fill"></i>
              Password
            </label>
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="form-control" 
              placeholder="••••••••" 
              required 
              autocomplete="current-password"
            >
          </div>

          <div class="form-options">
            <div class="remember-me">
              <input type="checkbox" id="remember-me">
              <label for="remember-me">Remember me</label>
            </div>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>

          <button type="submit" class="btn-primary">
            <i class="bi bi-box-arrow-in-right"></i> Sign in to your account
          </button>
        </form>

        <!-- Divider -->
        <div class="divider">
          <span>Or continue with</span>
        </div>

        <!-- Google Login -->
        <button type="button" class="btn-google" id="googleLoginBtn">
          <img src="https://cdn-icons-png.flaticon.com/512/300/300221.png" alt="Google">
          Continue with Google
        </button>

        <!-- Sign Up Link -->
        <div class="divider" style="margin-top: 2rem;">
          <span>New to our platform?</span>
        </div>

        <div class="footer-links">
          <a href="/register">
            <i class="bi bi-person-plus-fill"></i> Create a new account <i class="bi bi-arrow-right"></i>
          </a>
        </div>
      </div>

      <!-- Security Badge -->
      <div class="security-badge">
        <div class="badge-content">
          <i class="bi bi-shield-check"></i>
          <span>Secure & Encrypted Connection</span>
        </div>
      </div>
    </div>
  </main>

<script>
  // Google OAuth Configuration
  const GOOGLE_CLIENT_ID = '<?= config_item("GOOGLE_CLIENT_ID") ?: "" ?>';
  
  // Disable Google button if not configured
  const googleBtn = document.getElementById('googleLoginBtn');
  if (!GOOGLE_CLIENT_ID) {
    if (googleBtn) {
      googleBtn.disabled = true;
      googleBtn.title = 'Google OAuth not configured. Please contact administrator.';
      googleBtn.style.opacity = '0.5';
      googleBtn.style.cursor = 'not-allowed';
    }
  } else if (googleBtn) {
    // Load Google Sign-In script
    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = function() {
      if (typeof google !== 'undefined' && google.accounts) {
        // Initialize Google Sign-In
        google.accounts.id.initialize({
          client_id: GOOGLE_CLIENT_ID,
          callback: handleGoogleSignIn
        });

        // Add click handler to button
        googleBtn.addEventListener('click', function() {
          const tokenClient = google.accounts.oauth2.initTokenClient({
            client_id: GOOGLE_CLIENT_ID,
            scope: 'email profile',
            callback: function(response) {
              if (response.access_token) {
                // Get user info from Google
                fetch('https://www.googleapis.com/oauth2/v2/userinfo', {
                  headers: { 'Authorization': 'Bearer ' + response.access_token }
                })
                .then(r => r.json())
                .then(data => {
                  if (data.email) {
                    sendOAuthToken('google', response.access_token, data.email, data.name || '', data.picture || null);
                  } else {
                    alert('Unable to retrieve email from Google. Please use email/password login.');
                  }
                })
                .catch(err => {
                  console.error('Google OAuth error:', err);
                  alert('Failed to get user information from Google. Please try again.');
                });
              } else if (response.error) {
                console.error('Google OAuth error:', response.error);
                alert('Google sign-in was cancelled or failed.');
              }
            }
          });
          tokenClient.requestAccessToken();
        });
      }
    };
    document.head.appendChild(script);
  }

  function handleGoogleSignIn(response) {
    if (response.credential) {
      // Decode JWT token to get user info
      try {
        const payload = JSON.parse(atob(response.credential.split('.')[1]));
        sendOAuthToken('google', response.credential, payload.email || '', payload.name || '', payload.picture || null);
      } catch (e) {
        console.error('Error decoding Google token:', e);
        sendOAuthToken('google', response.credential);
      }
    }
  }

  function sendOAuthToken(provider, token, email, name, photo) {
    const oauthUrl = '<?= site_url("oauth/") ?>' + provider;
    fetch(oauthUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        token: token,
        email: email || '',
        name: name || '',
        photo: photo || null
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.href = data.redirect;
      } else {
        alert('Login failed: ' + (data.message || 'Unknown error'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred during login. Please try again.');
    });
  }
</script>
</body>
</html>
