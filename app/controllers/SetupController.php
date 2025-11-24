<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class SetupController extends Controller
{
    public function seed_admins()
    {
        // Only allow this in development or with proper authentication
        $env = config_item('ENVIRONMENT') ?? 'development';
        if ($env !== 'development' && empty($_SESSION['admin_setup_key'])) {
            show_404();
            return;
        }

        $this->call->model('UserModel');
        $this->call->helper('admin_seeder');

        try {
            seed_admin_accounts($this->UserModel);
            
            // Return HTML response for easier viewing
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Admin Seeder</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #28a745; }
        .success { color: #28a745; font-weight: bold; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .credentials { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .credentials strong { color: #0d6efd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Admin Accounts Seeded Successfully!</h1>
        <div class="info">
            <p class="success">Admin accounts have been created/updated in the database.</p>
        </div>
        <div class="credentials">
            <h3>Account 1:</h3>
            <p><strong>Email:</strong> admin@ph</p>
            <p><strong>Password:</strong> admin123</p>
            <p><strong>Username:</strong> admin</p>
        </div>
        <div class="credentials">
            <h3>Account 2:</h3>
            <p><strong>Email:</strong> staff@barangay.gov.ph</p>
            <p><strong>Password:</strong> staff123</p>
            <p><strong>Username:</strong> barangay_staff</p>
        </div>
        <p style="margin-top: 30px;">
            <a href="' . BASE_URL . '/login" style="background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Go to Login Page</a>
        </p>
    </div>
</body>
</html>';
        } catch (Exception $e) {
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Admin Seeder Error</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="error">❌ Error Seeding Admin Accounts</h1>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <p>Please check your database connection and try again.</p>
    </div>
</body>
</html>';
        }
    }

    public function fix_admin_password()
    {
        // Allow this in development mode
        $env = config_item('ENVIRONMENT') ?? 'development';
        if ($env !== 'development') {
            show_404();
            return;
        }

        $this->call->model('UserModel');

        try {
            // Get admin user
            $admin = $this->UserModel->getUserByUsername('admin');
            
            if (!$admin) {
                echo '<!DOCTYPE html><html><head><title>Fix Admin Password</title></head><body>';
                echo '<h1>❌ Admin account not found</h1>';
                echo '<p>Please run the seeder first: <a href="' . BASE_URL . '/setup/seed-admins">Seed Admin Accounts</a></p>';
                echo '</body></html>';
                return;
            }

            // Update password with correct hash
            $newPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
            $updated = $this->UserModel->updateUser($admin['id'], [
                'password' => $newPasswordHash
            ]);

            if ($updated) {
                echo '<!DOCTYPE html>
<html>
<head>
    <title>Password Fixed</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="success">✅ Admin Password Fixed!</h1>
        <p>The admin password has been updated with the correct hash.</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Email:</strong> admin@ph</p>
            <p><strong>Password:</strong> admin123</p>
        </div>
        <p><a href="' . BASE_URL . '/login" style="background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Go to Login Page</a></p>
    </div>
</body>
</html>';
            } else {
                echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
                echo '<h1>❌ Failed to update password</h1>';
                echo '<p>Please check your database connection.</p>';
                echo '</body></html>';
            }
        } catch (Exception $e) {
            echo '<!DOCTYPE html><html><head><title>Error</title></head><body>';
            echo '<h1>❌ Error: ' . htmlspecialchars($e->getMessage()) . '</h1>';
            echo '</body></html>';
        }
    }
}
