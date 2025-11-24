<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

if (!function_exists('seed_admin_accounts')) {
    /**
     * Seed hardcoded admin accounts
     * Call this function during installation or setup
     *
     * @param object $userModel
     * @return void
     */
    function seed_admin_accounts($userModel)
    {
        $admins = [
            [
                'username' => 'admin',
                'fullname' => 'System Administrator',
                'email' => 'admin@ph',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'contact' => null,
                'address' => null,
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'username' => 'barangay_staff',
                'fullname' => 'Barangay Staff',
                'email' => 'staff@barangay.gov.ph',
                'password' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'admin',
                'contact' => null,
                'address' => null,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        foreach ($admins as $admin) {
            // Check if admin already exists by email
            $existing = $userModel->getUserByEmail($admin['email']);
            if (!$existing) {
                $userModel->createUser($admin);
            } else {
                // Update password if admin exists (for password reset)
                $userModel->updateUser($existing['id'], [
                    'password' => $admin['password'],
                    'fullname' => $admin['fullname'],
                    'email' => $admin['email']
                ]);
            }
        }
    }
}

