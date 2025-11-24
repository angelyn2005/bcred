<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class DashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->model('RequestModel');
    }

    public function index()
    {
        // Only allow admin
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
            redirect('/login');
        }

        // Get all requests
        $data['requests'] = $this->RequestModel->getAllRequests();

        $this->call->view('dashboard/index', $data);
    }
}
