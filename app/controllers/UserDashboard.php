<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserDashboard extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->call->model('RequestModel');
        $this->call->model('NotificationModel');
        $this->call->model('UserModel');
    }

    public function index()
    {
        if (empty($_SESSION['user_id'])) {
            redirect('/login');
            return;
        }

        $page = $_GET['page'] ?? 'dashboard';
        $userRecord = $this->UserModel->getUserById($_SESSION['user_id']) ?? [];

        $photoPath = $userRecord['photo'] ?? ($_SESSION['photo'] ?? null);
        $currentUser = [
            'id' => $userRecord['id'] ?? $_SESSION['user_id'],
            'fullname' => $userRecord['fullname'] ?? ($_SESSION['user'] ?? 'Guest'),
            'username' => $userRecord['username'] ?? ($_SESSION['username'] ?? ''),
            'email' => $userRecord['email'] ?? ($_SESSION['email'] ?? ''),
            'contact' => $userRecord['contact'] ?? '',
            'address' => $userRecord['address'] ?? '',
            'role' => $userRecord['role'] ?? ($_SESSION['role'] ?? 'resident'),
            'photo' => $photoPath,
        ];
        $currentUser['name'] = $currentUser['fullname'];
        $currentUser['photo_url'] = $this->buildPhotoUrl($photoPath);

        $requests = $this->RequestModel->getRequestsByUser($currentUser['id']);
        $notifications = $this->NotificationModel->getNotificationsByUser($currentUser['id']);

        $totalRequests = count($requests);
        $pendingRequests = count(array_filter($requests, fn($r) => ($r['status'] ?? '') === 'pending'));
        $completedRequests = count(array_filter(
            $requests,
            fn($r) => in_array($r['status'] ?? '', ['approved', 'released'], true)
        ));

        $flash = $_SESSION['flash'] ?? null;
        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }

        $data = [
            'currentUser' => $currentUser,
            'page' => $page,
            'requests' => $requests,
            'notifications' => $notifications,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'completedRequests' => $completedRequests,
            'flash' => $flash,
        ];

        $this->call->view('user_dashboard/index', $data);
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/user_dashboard?page=profile');
            return;
        }

        if (empty($_SESSION['user_id'])) {
            redirect('/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $userRecord = $this->UserModel->getUserById($userId);

        if (!$userRecord) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Unable to load your profile. Please try again.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($fullname === '' || $username === '' || $email === '') {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Full name, username, and email are required.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Please provide a valid email address.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        if (($userRecord['role'] ?? 'resident') === 'resident') {
            if ($contact === '' || $address === '') {
                $_SESSION['flash'] = [
                    'type' => 'danger',
                    'message' => 'Contact number and address are required for residents.',
                ];
                redirect('/user_dashboard?page=profile');
                return;
            }
        }

        $existingUsername = $this->UserModel->getUserByUsername($username);
        if ($existingUsername && (int) $existingUsername['id'] !== (int) $userId) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Username is already taken.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $existingEmail = $this->UserModel->getUserByEmail($email);
        if ($existingEmail && (int) $existingEmail['id'] !== (int) $userId) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Email is already registered.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $updateData = [
            'fullname' => $fullname,
            'username' => $username,
            'email' => $email,
            'contact' => $contact !== '' ? $contact : null,
            'address' => $address !== '' ? $address : null,
        ];

        $updated = $this->UserModel->updateUser($userId, $updateData);

        if ($updated) {
            $_SESSION['user'] = $fullname;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Profile updated successfully.',
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'No changes detected or update failed.',
            ];
        }

        redirect('/user_dashboard?page=profile');
    }

    public function updatePhoto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/user_dashboard?page=profile');
            return;
        }

        if (empty($_SESSION['user_id'])) {
            redirect('/login');
            return;
        }

        if (empty($_FILES['photo']['name'] ?? '')) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Please choose an image to upload.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $file = $_FILES['photo'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Failed to upload the image. Please try again.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $detectedType = mime_content_type($file['tmp_name']);
        if (!in_array($detectedType, $allowedTypes, true)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Please upload a valid image (JPG, PNG, GIF, or WEBP).',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $maxSize = 2 * 1024 * 1024; // 2MB
        if (($file['size'] ?? 0) > $maxSize) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Image is too large. Maximum size is 2MB.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $userId = $_SESSION['user_id'];
        $uploadRoot = ROOT_DIR . 'public/uploads/profile_photos/';
        if (!is_dir($uploadRoot) && !mkdir($uploadRoot, 0777, true) && !is_dir($uploadRoot)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Unable to create the upload directory.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
        $filename = sprintf('user_%d_%s.%s', $userId, uniqid(), $extension);
        $absolutePath = $uploadRoot . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Failed to save the uploaded image.',
            ];
            redirect('/user_dashboard?page=profile');
            return;
        }

        $relativePath = $this->sanitizePhotoPath('uploads/profile_photos/' . $filename);

        $userRecord = $this->UserModel->getUserById($userId);
        $previousPhoto = $userRecord['photo'] ?? null;

        $updated = $this->UserModel->updateUser($userId, ['photo' => $relativePath]);

        if ($updated) {
            $_SESSION['photo'] = $relativePath;
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Profile photo updated successfully.',
            ];

            $previousAbsolute = $this->buildPhotoAbsolutePath($previousPhoto);
            $uploadRootReal = realpath($uploadRoot);
            $previousReal = $previousAbsolute && file_exists($previousAbsolute)
                ? realpath($previousAbsolute)
                : null;

            if ($previousReal && $uploadRootReal && strpos($previousReal, $uploadRootReal) === 0) {
                @unlink($previousAbsolute);
            }
        } else {
            $_SESSION['flash'] = [
                'type' => 'warning',
                'message' => 'Unable to update your profile photo. Please try again.',
            ];
        }

        redirect('/user_dashboard?page=profile');
    }

    private function sanitizePhotoPath($path)
    {
        if (empty($path)) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', trim((string) $path)), '/');
        if ($normalized === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $normalized)) {
            return $normalized;
        }

        if (stripos($normalized, 'public/') !== 0) {
            if (stripos($normalized, 'uploads/') === 0) {
                $normalized = 'public/' . $normalized;
            } else {
                $normalized = 'public/uploads/profile_photos/' . basename($normalized);
            }
        }

        return $normalized;
    }

    private function buildPhotoAbsolutePath($path)
    {
        if (empty($path) || preg_match('#^https?://#i', (string) $path)) {
            return null;
        }

        $relative = $this->sanitizePhotoPath($path);
        if (!$relative) {
            return null;
        }

        return ROOT_DIR . $relative;
    }

    private function buildPhotoUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $relative = $this->sanitizePhotoPath($path);
        if (!$relative) {
            return null;
        }

        $baseUrl = defined('BASE_URL') && BASE_URL !== null
            ? BASE_URL
            : (config_item('BASE_URL') ?? '');

        $baseUrl = $baseUrl !== '' ? rtrim((string) $baseUrl, '/') : '';

        return ($baseUrl !== '' ? $baseUrl . '/' : '/') . ltrim($relative, '/');
    }
}
