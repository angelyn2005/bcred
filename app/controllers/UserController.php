<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserController extends Controller
{
    public function __construct() {
        parent::__construct();
        $this->call->model('RequestModel'); // Load RequestModel
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Dashboard page — show user's submitted requests
    public function dashboard() {
        $user_id = $_SESSION['user_id'] ?? 1; // fallback to 1 if no login session (for dev)
        $requests = $this->RequestModel->getRequestsByUser($user_id);
        $this->call->view('user_dashboard/index', ['requests' => $requests]);
    }

    // Handle form submit (Submit Request)
    public function submit_request() {
    // Basic validation
    $doc_type = $this->io->post('doc_type') ?? '';
    $details  = $this->io->post('details') ?? '';

    if (empty($doc_type) || empty($details)) {
        echo "<script>alert('Please choose a document type and enter details.'); window.history.back();</script>";
        return;
    }

    $user_id = $_SESSION['user_id'] ?? 1;

    // Prepare data - map to DB
    $data = [
        'user_id'       => $user_id,
        'document_type' => $doc_type,
        'details'       => $details,
        'status'        => 'Pending',
        'created_at'    => date('Y-m-d H:i:s'),
        'updated_at'    => date('Y-m-d H:i:s')
    ];

    try {
        $result = $this->RequestModel->addRequest($data);

        if ($result) {
            redirect('/user/dashboard');
        } else {
            echo "<script>alert('Failed to submit request.'); window.history.back();</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Database error while submitting request.'); window.history.back();</script>";
        // error_log($e->getMessage());
    } catch (Exception $e) {
        echo "<script>alert('Unexpected error.'); window.history.back();</script>";
        // error_log($e->getMessage());
    }
}


    // Profile
    public function profile() {
        $userData = [
            'name'    => 'Juan Dela Cruz',
            'email'   => 'juan@example.com',
            'contact' => '09123456789'
        ];
        $this->call->view('user/profile', $userData);
    }
}
