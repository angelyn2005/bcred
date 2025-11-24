<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$router->get('/', 'LandingController::index');
$router->get('/login', 'AuthController::login');
$router->post('/login', 'AuthController::login_post');
$router->get('/register', 'AuthController::register');
$router->post('/register', 'AuthController::register_post');
$router->post('/oauth/google', 'AuthController::oauth_google');
$router->get('/logout', 'AuthController::logout');
$router->match('/contact', 'AuthController::contact', ['GET','POST']);

$router->get('/verify-email', 'AuthController::verify_email');
$router->post('/verify-email', 'AuthController::verify_email_post');
$router->post('/resend-otp', 'AuthController::resend_otp');
$router->get('/setup/seed-admins', 'SetupController@seed_admins');
$router->get('/setup/fix-admin-password', 'SetupController@fix_admin_password');

$router->get('/dashboard', 'AdminController::dashboard');
$router->get('/admin/dashboard', 'AdminController::dashboard');
$router->get('/admin/request-management', 'AdminController::request_management');

$router->get('/user_dashboard', 'UserDashboard::index');
$router->post('/request/submit', 'RequestController::submit');
$router->post('/user/profile/update', 'UserDashboard::updateProfile');
$router->post('/user/profile/photo', 'UserDashboard::updatePhoto');
$router->get('/user/requests', 'UserRequestController::list');
$router->get('/user/request/create', 'UserRequestController::create');
$router->post('/user/request/create', 'UserRequestController::create');
$router->get('/admin/request/{id}', 'AdminController::request_details');

$router->get('/admin/requests', 'AdminRequestController::list');
$router->post('/admin/request/update/{id}', 'AdminRequestController::updateRequestAjax');
$router->get('/admin/request/view/{id}', 'AdminRequestController::view');

$router->post('/admin/request/release/{id}', 'AdminRequestController::markReleased'); // Optional: mark as released

// Admin signature and document management
$router->post('/admin/signature/upload', 'AdminRequestController::upload_signature');
$router->get('/admin/signature/current', 'AdminRequestController::get_current_signature');
$router->get('/admin/document/preview/{id}', 'AdminRequestController::preview_document');
$router->post('/admin/document/confirm-release', 'AdminRequestController::confirm_release');

$router->get('/admin/users', 'AdminController::user_management');
$router->get('/admin/user/view/{id}', 'AdminController::view_user');
$router->post('/admin/user/delete/{id}', 'AdminController::delete_user');
$router->post('/admin/user/activate/{id}', 'AdminController::activate_user');
$router->post('/admin/user/deactivate/{id}', 'AdminController::deactivate_user');
$router->get('/admin/analytics', 'AdminController::analytics');
$router->get('/admin/activity-logs', 'AdminController::activity_logs');
$router->get('/admin/settings', 'AdminController::settings');
$router->post('/admin/settings/save-barangay', 'AdminController::save_barangay_settings');

$router->get('/admin/analytics', 'AdminController::analytics');


