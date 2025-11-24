<?php defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Barangay E-Credentials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/1041/1041916.png" type="image/png">
    <style>
        body {
            background: linear-gradient(135deg, #e8f0ff, #f3f8ff, #eaf3ff);
            background-size: 300% 300%;
            animation: moveGradient 12s ease infinite;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }

        @keyframes moveGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .verify-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
            width: 100%;
            max-width: 500px;
            padding: 2.5rem;
            animation: fadeInUp 0.6s ease;
            position: relative;
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .icon-box {
            height: 90px;
            width: 90px;
            background: linear-gradient(135deg, #0d6efd 0%, #084298 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
            animation: pulse 2s ease infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .icon-box i {
            font-size: 45px;
            color: #ffffff;
        }

        h3 {
            text-align: center;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 0.5rem;
            font-size: 28px;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
            font-size: 15px;
            line-height: 1.6;
        }

        .code-input {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 12px;
            text-align: center;
            padding: 18px;
            border: 2px solid #e0e7ff;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-family: 'Courier New', monospace;
            background: #f8f9ff;
        }

        .code-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            outline: none;
            background: #fff;
        }

        .btn-verify {
            background: linear-gradient(90deg, #0d6efd, #084298);
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }

        .btn-verify:hover {
            background: linear-gradient(90deg, #0a58ca, #052c65);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        }

        .btn-resend {
            background: transparent;
            border: 2px solid #e0e7ff;
            border-radius: 12px;
            padding: 14px;
            font-weight: 600;
            color: #0d6efd;
            transition: all 0.3s ease;
        }

        .btn-resend:hover {
            background: #f8f9ff;
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .timer-box {
            background: linear-gradient(135deg, #fff3cd 0%, #fff8e1 100%);
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .timer-box i {
            font-size: 24px;
            color: #f59e0b;
        }

        .timer-text {
            flex: 1;
            color: #92400e;
            font-size: 14px;
            font-weight: 500;
        }

        .timer-countdown {
            font-size: 20px;
            font-weight: 700;
            color: #b45309;
            font-family: 'Courier New', monospace;
        }

        .footer-text {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
            margin-top: 1.5rem;
        }

        .footer-text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #f3f4f6;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
            font-size: 20px;
        }

        .close-btn:hover {
            background: #e5e7eb;
            color: #374151;
            transform: rotate(90deg);
        }

        .email-sent-icon {
            display: inline-block;
            animation: bounce 1s ease infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @media (max-width: 576px) {
            .verify-card {
                padding: 2rem 1.5rem;
            }
            
            h3 {
                font-size: 24px;
            }
            
            .code-input {
                font-size: 20px;
                letter-spacing: 8px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="verify-card">
    <button type="button" class="close-btn" id="closeBtn" title="Close">
        <i class="bi bi-x"></i>
    </button>

    <div class="icon-box">
        <i class="bi bi-envelope-check email-sent-icon"></i>
    </div>

    <h3>Verify Your Email</h3>
    <p class="subtitle">
        We've sent a 6-digit verification code to your email.<br>
        Please enter it below to complete your registration.
    </p>

    <div class="timer-box" id="timerBox">
        <i class="bi bi-clock-history"></i>
        <span class="timer-text">Code expires in:</span>
        <span class="timer-countdown" id="countdown">45</span>
    </div>

    <form method="post" action="<?= site_url('verify-email') ?>" id="verifyForm">
        <input type="hidden" name="vid" value="<?= htmlspecialchars($vid ?? '') ?>">

        <div class="mb-4">
            <label for="codeInput" class="form-label fw-semibold">Verification Code</label>
            <input 
                id="codeInput" 
                name="code" 
                type="text" 
                inputmode="numeric" 
                pattern="\d{6}" 
                maxlength="6" 
                required 
                class="form-control code-input" 
                placeholder="000000"
                autocomplete="off"
            >
        </div>

        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-primary btn-verify">
                <i class="bi bi-check-circle me-2"></i>Verify Email
            </button>
        </div>

        <div class="d-grid">
            <button type="button" class="btn btn-resend" id="resendBtn">
                <i class="bi bi-arrow-clockwise me-2"></i>Resend Code
            </button>
        </div>
    </form>

    <div class="footer-text">
        Changed your mind? <a href="<?= site_url('login') ?>">Back to Login</a>
    </div>
</div>

<script>
(function() {
    const closeBtn = document.getElementById('closeBtn');
    const resendBtn = document.getElementById('resendBtn');
    const codeInput = document.getElementById('codeInput');
    const verifyForm = document.getElementById('verifyForm');
    const countdownEl = document.getElementById('countdown');
    const timerBox = document.getElementById('timerBox');
    const vid = '<?= htmlspecialchars($vid ?? '') ?>';

    // Auto-focus on input
    codeInput.focus();

    // Countdown timer (45 seconds)
    let timeLeft = 45;
    const timer = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;
        
        if (timeLeft <= 10) {
            countdownEl.style.color = '#dc2626';
            timerBox.style.background = 'linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)';
            timerBox.style.borderColor = '#dc2626';
        }
        
        if (timeLeft <= 0) {
            clearInterval(timer);
            countdownEl.textContent = 'EXPIRED';
            timerBox.innerHTML = '<i class="bi bi-exclamation-triangle" style="color: #dc2626; font-size: 24px;"></i><span class="timer-text" style="color: #991b1b;">Code has expired. Please request a new one.</span>';
        }
    }, 1000);

    // Close button
    closeBtn.addEventListener('click', () => {
        if (confirm('Are you sure you want to cancel verification?')) {
            window.location.href = '<?= site_url('login') ?>';
        }
    });

    // Resend code
    resendBtn.addEventListener('click', () => {
        const form = document.createElement('form');
        form.method = 'post';
        form.action = '<?= site_url('resend-otp') ?>';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'vid';
        input.value = vid;
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    });

    // Allow only numbers in code input
    codeInput.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });

    // Auto-submit when 6 digits are entered
    codeInput.addEventListener('input', (e) => {
        if (e.target.value.length === 6) {
            setTimeout(() => {
                verifyForm.submit();
            }, 300);
        }
    });

    // Prevent form submission if expired
    verifyForm.addEventListener('submit', (e) => {
        if (timeLeft <= 0) {
            e.preventDefault();
            alert('Verification code has expired. Please request a new code.');
        }
    });
})();
</script>

</body>
</html>
