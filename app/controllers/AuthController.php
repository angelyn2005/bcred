
<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('UserModel');
        $this->call->model('ActivityLogModel');
        $this->call->model('EmailVerificationModel');
    }

    public function login()
    {
        $this->call->view('auth/login');
    }

    public function register()
    {
        $this->call->view('auth/register');
    }

    public function login_post()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            echo "<script>alert('Please enter both email and password.'); window.history.back();</script>";
            return;
        }

        // Hardcoded default admin bypass - but get real admin ID from database
        if ($email === 'admin@ph' && $password === 'admin123') {
            // Get actual admin user from database
            $adminUser = $this->UserModel->getUserByEmail('admin@ph');
            if (!$adminUser) {
                // If no admin@ph exists, find any admin user
                $adminUser = $this->UserModel->getAdminUser();
            }
            
            $adminId = $adminUser['id'] ?? 1;
            
            $_SESSION['user'] = $adminUser['fullname'] ?? 'System Administrator';
            $_SESSION['username'] = $adminUser['username'] ?? 'admin';
            $_SESSION['email'] = 'admin@ph';
            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = $adminId; // Use real admin ID from database
            $_SESSION['photo'] = $adminUser['photo'] ?? null;

            // Record login activity
            try {
                if (isset($this->ActivityLogModel)) {
                    $this->ActivityLogModel->record([
                        'action' => 'Admin Login',
                        'details' => sprintf('System Administrator (user_id=%d) logged in', $adminId),
                        'request_id' => null,
                        'admin_id' => $adminId,
                    ]);
                }
            } catch (Throwable $e) {
                error_log('Failed to record login activity: ' . $e->getMessage());
            }

            redirect(BASE_URL . '/dashboard');
            return;
        }

        $user = $this->UserModel->getUserByEmail($email);
        
        // Debug logging
        error_log("Login attempt for email: " . $email);
        error_log("User found: " . ($user ? 'YES (ID: ' . $user['id'] . ', Role: ' . $user['role'] . ')' : 'NO'));
        if ($user) {
            error_log("Password verify result: " . (password_verify($password, $user['password']) ? 'SUCCESS' : 'FAIL'));
        }

        if ($user && password_verify($password, $user['password'])) {
            // Check if account is deactivated
            if (isset($user['is_active']) && $user['is_active'] == 0) {
                echo "<script>alert('Your account is temporarily deactivated. Please contact your barangay administrator.'); window.history.back();</script>";
                return;
            }

            // Only require email verification for non-admin users
            try {
                if (($user['role'] ?? '') !== 'admin') {
                    // Load model instance directly to avoid relying on controller property registration
                    $evModel = null;
                    try {
                        $evModel = load_class('EmailVerificationModel', 'models');
                    } catch (Throwable $le) {
                        $evModel = null;
                    }

                    if ($evModel && !$evModel->isVerified($user['id'])) {
                        $verifyUrl = BASE_URL . '/verify-email?uid=' . urlencode($user['id']);
                        echo "<script>alert('Please verify your email before logging in. Check your inbox for the verification code.'); window.location='{$verifyUrl}';</script>";
                        return;
                    }
                }
            } catch (Throwable $e) {
                error_log('Email verification check failed: ' . $e->getMessage());
            }
            $_SESSION['user'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'] === 'admin' ? 'admin' : 'resident';
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['photo'] = $user['photo'] ?? null;

            // Record login activity
            try {
                if (isset($this->ActivityLogModel)) {
                    $this->ActivityLogModel->record([
                        'action' => ($_SESSION['role'] === 'admin' ? 'Admin Login' : 'User Login'),
                        'details' => sprintf('%s (user_id=%s) logged in', $_SESSION['user'], $_SESSION['user_id']),
                        'request_id' => null,
                        'admin_id' => $_SESSION['role'] === 'admin' ? $_SESSION['user_id'] : null,
                    ]);
                }
            } catch (Throwable $e) {
                error_log('Failed to record login activity: ' . $e->getMessage());
            }

            $redirect = $_SESSION['role'] === 'admin'
                ? BASE_URL . '/dashboard'
                : BASE_URL . '/user_dashboard';

            redirect($redirect);
            return;
        }

        echo "<script>alert('Invalid username or password!'); window.history.back();</script>";
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Record logout activity before destroying session
        try {
            if (!empty($_SESSION['user_id']) && isset($this->ActivityLogModel)) {
                $this->ActivityLogModel->record([
                    'action' => (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Admin Logout' : 'User Logout',
                    'details' => sprintf('%s (user_id=%s) logged out', $_SESSION['user'] ?? 'Unknown', $_SESSION['user_id'] ?? 'unknown'),
                    'request_id' => null,
                    'admin_id' => !empty($_SESSION['role']) && $_SESSION['role'] === 'admin' ? $_SESSION['user_id'] : null,
                ]);
            }
        } catch (Throwable $e) {
            error_log('Failed to record logout activity: ' . $e->getMessage());
        }

        session_destroy();
        redirect('/');
    }

    public function register_post()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/register');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Force resident role only - no admin registration allowed
        $role = 'resident';
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm'] ?? '';

        if ($fullname === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
            echo "<script>alert('Please complete all required fields.'); window.history.back();</script>";
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Please provide a valid email address.'); window.history.back();</script>";
            return;
        }

        // Enforce minimum password length
        if (strlen($password) < 8) {
            echo "<script>alert('Password must be at least 8 characters long.'); window.history.back();</script>";
            return;
        }

        if ($password !== $confirm) {
            echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
            return;
        }

        if ($contact === '' || $address === '') {
            echo "<script>alert('Contact number and address are required.'); window.history.back();</script>";
            return;
        }

        if ($this->UserModel->getUserByUsername($username)) {
            echo "<script>alert('Username already taken.'); window.history.back();</script>";
            return;
        }

        if ($this->UserModel->getUserByEmail($email)) {
            echo "<script>alert('Email already registered.'); window.history.back();</script>";
            return;
        }

        // Do NOT create the user yet. Store registration data in verification.meta and create OTP
        $userData = [
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'contact' => $contact,
            'address' => $address,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $this->call->helper('mail');

            $code = rand(100000, 999999);

            // Ensure we can get an instance of the model
            try {
                $evModel = load_class('EmailVerificationModel', 'models');
            } catch (Throwable $le) {
                $evModel = null;
            }

            if (!$evModel) {
                echo "<script>alert('Verification service unavailable.'); window.history.back();</script>";
                return;
            }

            // Perform a DB-side INSERT using DATE_ADD(NOW(), INTERVAL 45 SECOND)
            // to avoid timezone mismatches between PHP and the DB server.
            $dbConfig = &database_config();
            $cfg = $dbConfig['main'];
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $cfg['hostname'], $cfg['port'], $cfg['database'], $cfg['charset']);
            $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $metaJson = (isset($evModel) && $evModel && $evModel->columnExists('meta')) ? json_encode($userData) : null;
            if ($metaJson !== null) {
                $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, email, code, expires_at, created_at, meta) VALUES (NULL, ?, ?, DATE_ADD(NOW(), INTERVAL 45 SECOND), NOW(), ?)");
                $stmt->execute([$email, (string)$code, $metaJson]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, email, code, expires_at, created_at) VALUES (NULL, ?, ?, DATE_ADD(NOW(), INTERVAL 45 SECOND), NOW())");
                $stmt->execute([$email, (string)$code]);
            }
            $vid = $pdo->lastInsertId();

            $subject = 'Email Verification Code - Barangay E-Credentials';
            $body = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Email Verification</title>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f7fa;'>
    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f7fa; padding: 40px 20px;'>
        <tr>
            <td align='center'>
                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);'>
                    
                    <!-- Header with gradient -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;'>
                            <div style='width: 80px; height: 80px; background-color: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 20px; display: inline-block;'>
                                <img src='https://cdn-icons-png.flaticon.com/512/3059/3059502.png' alt='Shield' style='width: 50px; height: 50px; margin-top: 15px;'>
                            </div>
                            <h1 style='margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;'>Email Verification</h1>
                            <p style='margin: 10px 0 0; color: rgba(255,255,255,0.9); font-size: 16px;'>Barangay E-Credentials System</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style='padding: 40px 30px;'>
                            <p style='margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;'>
                                Hello <strong>{$fullname}</strong>,
                            </p>
                            <p style='margin: 0 0 30px; color: #666666; font-size: 15px; line-height: 1.6;'>
                                Thank you for registering with the Barangay E-Credentials System. To complete your registration, please use the verification code below:
                            </p>
                            
                            <!-- Verification Code Box -->
                            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 30px; text-align: center; margin: 30px 0;'>
                                <p style='margin: 0 0 10px; color: rgba(255,255,255,0.9); font-size: 14px; letter-spacing: 1px; text-transform: uppercase;'>Your Verification Code</p>
                                <div style='background-color: rgba(255,255,255,0.95); border-radius: 8px; padding: 20px; display: inline-block; min-width: 200px;'>
                                    <span style='font-size: 36px; font-weight: 700; color: #667eea; letter-spacing: 8px; font-family: \"Courier New\", monospace;'>{$code}</span>
                                </div>
                            </div>
                            
                            <!-- Warning Box -->
                            <div style='background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px 20px; border-radius: 6px; margin: 20px 0;'>
                                <p style='margin: 0; color: #856404; font-size: 14px;'>
                                    <strong>⏱️ Important:</strong> This code will expire in <strong>45 seconds</strong>. Please enter it immediately.
                                </p>
                            </div>
                            
                            <p style='margin: 20px 0 0; color: #666666; font-size: 14px; line-height: 1.6;'>
                                If you did not register for this account, please ignore this email.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;'>
                            <p style='margin: 0 0 10px; color: #666666; font-size: 14px;'>
                                <strong>Barangay E-Credentials System</strong>
                            </p>
                            <p style='margin: 0; color: #999999; font-size: 12px;'>
                                This is an automated message. Please do not reply to this email.
                            </p>
                            <p style='margin: 15px 0 0; color: #999999; font-size: 12px;'>
                                © 2025 Barangay E-Credentials. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";

            mail_helper($fullname, $email, $subject, $body);

            $verifyUrl = BASE_URL . '/verify-email?vid=' . urlencode($vid);
            echo "<script>alert('Registration initiated. A verification code has been sent to your email.'); window.location='{$verifyUrl}';</script>";
            return;
        } catch (Throwable $e) {
            // Log to PHP error log as before
            error_log('Failed to create verification or send email: ' . $e->getMessage());

            // Also write a detailed debug log to the application's runtime folder so
            // we can inspect exceptions even if the system error_log isn't available.
            try {
                $root = dirname(APP_DIR);
                $logDir = $root . DIRECTORY_SEPARATOR . 'runtime';
                if (!is_dir($logDir)) {
                    @mkdir($logDir, 0777, true);
                }
                $logFile = $logDir . DIRECTORY_SEPARATOR . 'verify_debug.log';
                $payload = '[' . date('Y-m-d H:i:s') . "] Failed to create verification: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n\n";
                @file_put_contents($logFile, $payload, FILE_APPEND | LOCK_EX);
            } catch (Throwable $_e) {
                // Ignore logging failures
            }

            echo "<script>alert('Failed to initiate registration. Please try again later.'); window.history.back();</script>";
            return;
        }
    }

    public function verify_email()
    {
        $vid = $_GET['vid'] ?? null;
        $this->call->view('auth/verify_email', ['vid' => $vid]);
    }

    public function verify_email_post()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/verify-email');
        }

        $vid = $_POST['vid'] ?? null;
        $code = trim($_POST['code'] ?? '');

        if (empty($vid) || empty($code)) {
            echo "<script>alert('Please provide the verification code.'); window.history.back();</script>";
            return;
        }

        try {
            try {
                $evModel = load_class('EmailVerificationModel', 'models');
            } catch (Throwable $le) {
                $evModel = null;
            }

            $row = $evModel ? $evModel->getValidByIdAndCode($vid, $code) : null;
            if ($row) {
                // Create the user from meta if present
                $meta = [];
                if (!empty($row['meta'])) {
                    $meta = json_decode($row['meta'], true) ?: [];
                }

                if (!empty($meta)) {
                    // Prevent duplicate username/email just in case
                    if ($this->UserModel->getUserByUsername($meta['username'] ?? '')) {
                        echo "<script>alert('Username already taken. Please contact admin.'); window.history.back();</script>";
                        return;
                    }
                    if ($this->UserModel->getUserByEmail($meta['email'] ?? '')) {
                        echo "<script>alert('Email already registered. Please contact admin.'); window.history.back();</script>";
                        return;
                    }

                    $newUserId = $this->UserModel->createUser($meta);
                    if ($newUserId) {
                        // mark verification and attach user_id
                        if ($evModel) {
                            $evModel->markVerifiedWithUser($row['id'], $newUserId);
                        }
                        echo "<script>alert('Email verified and account created. You may now login.'); window.location='" . site_url('login') . "';</script>";
                        return;
                    }

                    echo "<script>alert('Failed to create user account.'); window.history.back();</script>";
                    return;
                }

                // If no meta, just mark verified
                if ($evModel) {
                    $evModel->markVerified($row['id']);
                }
                echo "<script>alert('Email verified successfully. You may now login.'); window.location='" . site_url('login') . "';</script>";
                return;
            }

            echo "<script>alert('Invalid or expired verification code.'); window.history.back();</script>";
            return;
        } catch (Throwable $e) {
            error_log('Email verification failed: ' . $e->getMessage());
            echo "<script>alert('Verification failed. Please try again later.'); window.history.back();</script>";
            return;
        }
    }

    public function resend_otp()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/verify-email');
        }
        $vid = $_POST['vid'] ?? null;

        if (empty($vid)) {
            echo "<script>alert('Verification entry not specified.'); window.history.back();</script>";
            return;
        }

        // Fetch the original verification to reuse meta/email
        try {
            $evModel = load_class('EmailVerificationModel', 'models');
        } catch (Throwable $le) {
            $evModel = null;
        }

        $orig = $evModel ? $evModel->getById($vid) : null;

        if (!$orig) {
            echo "<script>alert('Verification entry not found.'); window.history.back();</script>";
            return;
        }

        try {
            $this->call->helper('mail');
            $code = rand(100000, 999999);

            $meta = [];
            if (!empty($orig['meta'])) {
                $meta = json_decode($orig['meta'], true) ?: [];
            }

            $newVid = null;
            if ($evModel) {
                // Use DB-side insert to ensure expiry uses DB server time
                $dbConfig = &database_config();
                $cfg = $dbConfig['main'];
                $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $cfg['hostname'], $cfg['port'], $cfg['database'], $cfg['charset']);
                $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $metaJson = !empty($meta) && $evModel && $evModel->columnExists('meta') ? json_encode($meta) : null;
                if ($metaJson !== null) {
                    $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, email, code, expires_at, created_at, meta) VALUES (NULL, ?, ?, DATE_ADD(NOW(), INTERVAL 45 SECOND), NOW(), ?)");
                    $stmt->execute([$orig['email'] ?? ($meta['email'] ?? ''), (string)$code, $metaJson]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO email_verifications (user_id, email, code, expires_at, created_at) VALUES (NULL, ?, ?, DATE_ADD(NOW(), INTERVAL 45 SECOND), NOW())");
                    $stmt->execute([$orig['email'] ?? ($meta['email'] ?? ''), (string)$code]);
                }
                $newVid = $pdo->lastInsertId();
            }

            $subject = 'Resent Email Verification Code - Barangay E-Credentials';
            $fullnameResend = $meta['fullname'] ?? 'User';
            $body = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Email Verification</title>
</head>
<body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f7fa;'>
    <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='background-color: #f4f7fa; padding: 40px 20px;'>
        <tr>
            <td align='center'>
                <table role='presentation' cellspacing='0' cellpadding='0' border='0' width='100%' style='max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);'>
                    
                    <!-- Header with gradient -->
                    <tr>
                        <td style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;'>
                            <div style='width: 80px; height: 80px; background-color: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 20px; display: inline-block;'>
                                <img src='https://cdn-icons-png.flaticon.com/512/3059/3059502.png' alt='Shield' style='width: 50px; height: 50px; margin-top: 15px;'>
                            </div>
                            <h1 style='margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;'>Email Verification</h1>
                            <p style='margin: 10px 0 0; color: rgba(255,255,255,0.9); font-size: 16px;'>Barangay E-Credentials System</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style='padding: 40px 30px;'>
                            <p style='margin: 0 0 20px; color: #333333; font-size: 16px; line-height: 1.6;'>
                                Hello <strong>{$fullnameResend}</strong>,
                            </p>
                            <p style='margin: 0 0 30px; color: #666666; font-size: 15px; line-height: 1.6;'>
                                You requested a new verification code. Please use the code below to complete your registration:
                            </p>
                            
                            <!-- Verification Code Box -->
                            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 30px; text-align: center; margin: 30px 0;'>
                                <p style='margin: 0 0 10px; color: rgba(255,255,255,0.9); font-size: 14px; letter-spacing: 1px; text-transform: uppercase;'>Your New Verification Code</p>
                                <div style='background-color: rgba(255,255,255,0.95); border-radius: 8px; padding: 20px; display: inline-block; min-width: 200px;'>
                                    <span style='font-size: 36px; font-weight: 700; color: #667eea; letter-spacing: 8px; font-family: \"Courier New\", monospace;'>{$code}</span>
                                </div>
                            </div>
                            
                            <!-- Warning Box -->
                            <div style='background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px 20px; border-radius: 6px; margin: 20px 0;'>
                                <p style='margin: 0; color: #856404; font-size: 14px;'>
                                    <strong>⏱️ Important:</strong> This code will expire in <strong>45 seconds</strong>. Please enter it immediately.
                                </p>
                            </div>
                            
                            <p style='margin: 20px 0 0; color: #666666; font-size: 14px; line-height: 1.6;'>
                                If you did not request this code, please ignore this email.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;'>
                            <p style='margin: 0 0 10px; color: #666666; font-size: 14px;'>
                                <strong>Barangay E-Credentials System</strong>
                            </p>
                            <p style='margin: 0; color: #999999; font-size: 12px;'>
                                This is an automated message. Please do not reply to this email.
                            </p>
                            <p style='margin: 15px 0 0; color: #999999; font-size: 12px;'>
                                © 2025 Barangay E-Credentials. All rights reserved.
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
";

            mail_helper($meta['fullname'] ?? '', $orig['email'] ?? ($meta['email'] ?? ''), $subject, $body);

            $verifyUrl = BASE_URL . '/verify-email?vid=' . urlencode($newVid ?? $vid);
            echo "<script>alert('A new verification code has been sent to your email.'); window.location='{$verifyUrl}';</script>";
            return;
        } catch (Throwable $e) {
            error_log('Failed to resend verification email: ' . $e->getMessage());
            echo "<script>alert('Failed to resend verification email.'); window.history.back();</script>";
            return;
        }
    }

    public function oauth_google()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->call->helper('oauth');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? $_POST['token'] ?? $_GET['token'] ?? '';
        $email = $input['email'] ?? '';
        $name = $input['name'] ?? '';
        $photo = $input['photo'] ?? null;
        
        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            return;
        }

        // Try to verify token, but if it fails and we have email, use that
        $userInfo = verify_google_token($token);
        
        if (!$userInfo && !empty($email)) {
            // Fallback: use provided info (for development/testing)
            $userInfo = [
                'provider' => 'google',
                'provider_id' => $token, // Use token as ID for now
                'email' => $email,
                'fullname' => $name,
                'photo' => $photo,
            ];
        }
        
        if (!$userInfo) {
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            return;
        }

        $this->handleOAuthLogin($userInfo);
    }

    /**
     * Contact form handler (landing page).
     * Accepts POST with: name, email, subject, message
     */
    public function contact()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? 'Message from website');
            $message = trim($_POST['message'] ?? '');

            if ($name === '' || $email === '' || $message === '') {
                echo "<script>alert('Please complete all fields.'); window.history.back();</script>";
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<script>alert('Please provide a valid email address.'); window.history.back();</script>";
                return;
            }

            // Ensure mail helper is available
            $this->call->helper('mail');

            $adminEmail = 'drcts.io@gmail.com';
            $toName = 'Barangay Admin';

            $body = "You have received a new message from the website contact form:\n\n";
            $body .= "Name: {$name}\n";
            $body .= "Email: {$email}\n\n";
            $body .= "Subject: {$subject}\n\n";
            $body .= "Message:\n{$message}\n";

            $sent = mail_helper($toName, $adminEmail, "[Website Contact] " . $subject, $body);

            if ($sent === true) {
                echo "<script>alert('Message sent. Thank you!'); window.location='" . BASE_URL . "';</script>";
                return;
            }

            // If mail_helper returned an error string, show it
            $err = is_string($sent) ? $sent : 'Failed to send message.';
            echo "<script>alert('" . addslashes($err) . "'); window.history.back();</script>";
            return;
        }

        // For GET requests, simply redirect to home
        redirect('/');
    }


    private function handleOAuthLogin($userInfo)
    {
        $email = $userInfo['email'];
        $provider = $userInfo['provider'];
        $providerId = $userInfo['provider_id'];
        
        // Check if user exists by email
        $user = $this->UserModel->getUserByEmail($email);
        
        if (!$user) {
            // Auto-register as resident (only residents can register)
            $username = strtolower(preg_replace('/[^a-z0-9]/', '', $userInfo['fullname'])) . '_' . substr($providerId, 0, 6);
            
            // Ensure username is unique
            $originalUsername = $username;
            $counter = 1;
            while ($this->UserModel->getUserByUsername($username)) {
                $username = $originalUsername . $counter;
                $counter++;
            }
            
            $userData = [
                'fullname' => $userInfo['fullname'],
                'username' => $username,
                'email' => $email,
                'password' => password_hash(uniqid('oauth_', true), PASSWORD_DEFAULT),
                'role' => 'resident', // Always resident for OAuth registration
                'contact' => null,
                'address' => null,
                'photo' => $userInfo['photo'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $userId = $this->UserModel->createUser($userData);
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'Registration failed']);
                return;
            }
            
            $user = $this->UserModel->getUserById($userId);
        }
        
        // Login user
        $_SESSION['user'] = $user['fullname'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'] === 'admin' ? 'admin' : 'resident';
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['photo'] = $user['photo'] ?? null;
        
        $redirect = $_SESSION['role'] === 'admin'
            ? BASE_URL . '/dashboard'
            : BASE_URL . '/user_dashboard';
        
        echo json_encode([
            'success' => true,
            'redirect' => $redirect,
            'role' => $_SESSION['role']
        ]);
    }
}

