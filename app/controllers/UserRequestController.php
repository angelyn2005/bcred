<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserRequestController {
    private $requestModel;

    public function __construct() {
        // Use framework's model loader for consistency
        $this->call->model('RequestModel');
        $this->requestModel = $this->RequestModel; // Assuming $this->RequestModel is set by $this->call->model()
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id']; // assuming user session exists
            $document_type = $_POST['document_type'];
            $details = $_POST['details'];
            $type_id = $_POST['type_id'] ?? null;

            $this->requestModel->createRequest($user_id, $document_type, $details, $type_id);

            // Redirect to user dashboard
            header('Location: ' . base_url('user/dashboard'));
            exit();
        }
        include app_view('user/request_form');
    }

    public function list() {
        $user_id = $_SESSION['user_id'];
        // Fix: Use the correct method name
        $requests = $this->requestModel->getRequestsByUser($user_id);
        if (!$requests) {
            $requests = []; // Fallback if query fails
        }
        include app_view('user/dashboard');
    }
}