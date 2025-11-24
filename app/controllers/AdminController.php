<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AdminController extends Controller
{
    public function __construct() {
        parent::__construct();
        $this->call->model('RequestModel');
        $this->call->model('ActivityLogModel');
        $this->call->model('UserModel');
    }

    public function dashboard() {
        $requests = $this->RequestModel->getAllRequests();
        $users = $this->UserModel->getAllUsers();
        $residentUsers = array_values(array_filter(
            $users,
            fn($user) => strtolower($user['role'] ?? '') === 'resident'
        ));

        $statusCounter = static function(array $collection, string $status): int {
            return count(array_filter(
                $collection,
                fn($row) => strtolower($row['status'] ?? '') === strtolower($status)
            ));
        };

        $totalRequests = count($requests);
        $pendingRequests = $statusCounter($requests, 'pending');
        
        $approvedRequests = count(array_filter($requests, fn($r) => in_array(strtolower($r['status'] ?? ''), ['approved', 'released'])));
        $releasedRequests = $statusCounter($requests, 'released');
        $rejectedRequests = $statusCounter($requests, 'rejected');

        $totalUsers = count($residentUsers);
        $currentUser = [
            'id' => $_SESSION['user_id'] ?? 0,
            'name' => $_SESSION['user'] ?? 'Admin'
        ];

        $page = $_GET['page'] ?? 'dashboard';

        $this->call->view('dashboard/index', [
            'requests' => $requests,
            'totalRequests' => $totalRequests,
            'pendingRequests' => $pendingRequests,
            'approvedRequests' => $approvedRequests,
            'releasedRequests' => $releasedRequests,
            'rejectedRequests' => $rejectedRequests,
            'totalUsers' => $totalUsers,
            'currentUser' => $currentUser,
            'page' => $page,
        ]);
    }

  public function user_management() {
    $this->call->model('UserModel');
    $this->call->model('RequestModel');

    $users = array_values(array_filter(
        $this->UserModel->getAllUsers(),
        fn($user) => strtolower($user['role'] ?? '') === 'resident'
    ));
    $requests = $this->RequestModel->getAllRequests();

    $requestsByUser = [];
    foreach ($requests as $request) {
        $userId = $request['user_id'] ?? null;
        if ($userId === null) {
            continue;
        }
        $requestsByUser[$userId][] = $request;
    }

    foreach ($users as &$user) {
        $userId = $user['id'] ?? null;
        $userRequests = $userId !== null ? ($requestsByUser[$userId] ?? []) : [];
        $user['requests'] = $userRequests;
        $user['total_requests'] = count($userRequests);
        $user['pending_requests'] = count(array_filter(
            $userRequests,
            fn($r) => ($r['status'] ?? '') === 'pending'
        ));
    }
    unset($user);

    $this->call->view('dashboard/user_management', [
        'users' => $users
    ]);
}

public function view_user($id)
{
    $this->call->model('UserModel');
    $this->call->model('RequestModel');

    $user = $this->UserModel->getUserById($id);
    if (!$user) {
        show_404();
        return;
    }

    $requests = $this->RequestModel->getRequestsByUser($id);

    $this->call->view('dashboard/user_view', [
        'user' => $user,
        'requests' => $requests
    ]);
}

    public function delete_user($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
        }

        $this->call->model('UserModel');
        $this->call->model('RequestModel');
        $this->call->model('AttachmentModel');

        $user = $this->UserModel->getUserById($id);
        if (!$user) {
            show_404();
            return;
        }

        $requests = $this->RequestModel->getRequestsByUser($id);

        foreach ($requests as $request) {
            $requestId = $request['id'];
            $this->AttachmentModel->deleteByRequest($requestId);
            $this->RequestModel->deleteRequest($requestId);
        }

        $this->UserModel->deleteUser($id);

        redirect(BASE_URL . '/admin/users');
    }

    public function activate_user($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
        }

        $this->call->model('UserModel');
        $user = $this->UserModel->getUserById($id);
        
        if (!$user) {
            show_404();
            return;
        }

        $this->UserModel->activateUser($id);
        
        // Log activity
        if (isset($this->ActivityLogModel)) {
            try {
                $this->ActivityLogModel->record([
                    'action' => 'User Activated',
                    'details' => sprintf('Activated user: %s (ID: %s)', $user['fullname'] ?? 'N/A', $id),
                    'request_id' => null,
                    'admin_id' => $_SESSION['user_id'] ?? null,
                ]);
            } catch (Throwable $e) {
                error_log('Failed to record activation activity: ' . $e->getMessage());
            }
        }

        redirect(BASE_URL . '/admin/users');
    }

    public function deactivate_user($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/users');
        }

        $this->call->model('UserModel');
        $user = $this->UserModel->getUserById($id);
        
        if (!$user) {
            show_404();
            return;
        }

        $this->UserModel->deactivateUser($id);
        
        // Log activity
        if (isset($this->ActivityLogModel)) {
            try {
                $this->ActivityLogModel->record([
                    'action' => 'User Deactivated',
                    'details' => sprintf('Deactivated user: %s (ID: %s)', $user['fullname'] ?? 'N/A', $id),
                    'request_id' => null,
                    'admin_id' => $_SESSION['user_id'] ?? null,
                ]);
            } catch (Throwable $e) {
                error_log('Failed to record deactivation activity: ' . $e->getMessage());
            }
        }

        redirect(BASE_URL . '/admin/users');
    }

public function request_details($id)
{
    $this->call->model('RequestModel');

    $request = $this->RequestModel->getRequestById($id);

    if (!$request) {
        show_404(); 
        return;
    }

    $this->call->view('dashboard/request_details', [
        'request' => $request
    ]);
}


public function analytics()
{
    $this->call->model('RequestModel');

        $requests = $this->RequestModel->getAllRequests();

        $total_requests    = count($requests);
        $approved_requests = count(array_filter($requests, fn($r) => strtolower($r['status'] ?? '') === 'approved'));
        $pending_requests  = count(array_filter($requests, fn($r) => strtolower($r['status'] ?? '') === 'pending'));

        // Accurate monthly request counts (last 12 months)
        $months = 12;
        $monthRows = $this->RequestModel->getMonthlyRequestCounts($months);
        $monthly_labels = [];
        $monthly_values = [];

        // $monthRows is an array of ['ym' => 'YYYY-MM', 'total' => n] ordered ASC
        foreach ($monthRows as $r) {
            $ym = $r['ym'] ?? null;
            $total = (int) ($r['total'] ?? 0);
            if ($ym) {
                $dt = DateTime::createFromFormat('!Y-m', $ym);
                if ($dt) {
                    $monthly_labels[] = $dt->format('M');
                } else {
                    $monthly_labels[] = $ym;
                }
            } else {
                $monthly_labels[] = '';
            }
            $monthly_values[] = $total;
        }

        // If the model returned fewer than requested months, pad with zeros on the left
        if (count($monthly_labels) < $months) {
            $pad = $months - count($monthly_labels);
            $monthly_labels = array_merge(array_fill(0, $pad, ''), $monthly_labels);
            $monthly_values = array_merge(array_fill(0, $pad, 0), $monthly_values);
        }

        // Use distribution of RELEASED documents only
        $doc_labels = ['Barangay Clearance','Residency Certificate','Indigency Certificate','Business Permit','Barangay ID'];
        $distribution = $this->RequestModel->getDocumentDistributionByStatus('released', $doc_labels);
        $doc_values = array_map(fn($label) => (int) ($distribution[$label] ?? 0), $doc_labels);

        $this->call->view('admin/analytics', [
            'total_requests' => $total_requests,
            'approved_requests' => $approved_requests,
            'pending_requests' => $pending_requests,
            'monthly_labels' => $monthly_labels,
            'monthly_values' => $monthly_values,
            'doc_labels' => $doc_labels,
            'doc_values' => $doc_values
        ]);
}

    public function activity_logs()
    {
        $search = trim($_GET['search'] ?? '');
        $actionFilter = $_GET['action'] ?? 'all';

        $filters = [
            'search' => $search,
            'action' => $actionFilter,
        ];

        $logs = $this->normalizeLogs(
            $this->ActivityLogModel->getLogs($filters, 10)
        );

        $timeline = $this->normalizeLogs(
            $this->ActivityLogModel->getLogs($filters, 10)
        );

        $actionOptions = $this->ActivityLogModel->getDistinctActions();

        $this->call->view('dashboard/activity_logs', [
            'logs' => $logs,
            'timeline' => $timeline,
            'filters' => [
                'search' => $search,
                'action' => $actionFilter,
            ],
            'actionOptions' => $actionOptions,
        ]);
    }

    private function normalizeLogs(array $logs): array
    {
        return array_map(function($row) {
            $timestamp = $row['timestamp'] ?? $row['created_at'] ?? $row['performed_at'] ?? null;
            return [
                'id' => $row['id'] ?? null,
                'timestamp' => $timestamp,
                'action' => $row['action'] ?? 'Activity',
                'admin_name' => $row['admin_name'] ?? $row['adminName'] ?? $row['admin'] ?? 'System',
                'details' => $row['details'] ?? '',
                'request_id' => $row['request_id'] ?? $row['requestId'] ?? null,
            ];
        }, $logs);
    }

    public function settings()
    {
        // Load barangay information from file or database
        $settingsFile = ROOT_DIR . 'app/config/barangay_settings.json';
        
        if (file_exists($settingsFile)) {
            $barangayInfo = json_decode(file_get_contents($settingsFile), true);
        } else {
            $barangayInfo = [
                'name' => 'Barangay Poblacion',
                'email' => 'barangay@poblacion.gov.ph',
                'address' => 'Barangay Hall, Puerto Galera, Oriental Mindoro',
                'contact' => '+63 912 345 6789',
            ];
        }

        $documentTypes = [
            ['name' => 'Barangay Clearance', 'enabled' => true,  'fee' => 50],
            ['name' => 'Indigency Certificate', 'enabled' => true,  'fee' => 30],
            ['name' => 'Residency Certificate', 'enabled' => true,  'fee' => 40],
            ['name' => 'Business Permit', 'enabled' => false, 'fee' => 200],
            ['name' => 'Barangay ID', 'enabled' => true,  'fee' => 60],
        ];

        $notificationSettings = [
            'email' => true,
            'sms' => false,
        ];

        $this->call->view('dashboard/settings', [
            'barangay' => $barangayInfo,
            'documents' => $documentTypes,
            'notifications' => $notificationSettings,
        ]);
    }

    public function save_barangay_settings()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }

        // Validate required fields
        $required = ['name', 'email', 'address', 'contact'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Field '{$field}' is required"]);
                return;
            }
        }

        // Sanitize input
        $barangayData = [
            'name' => trim($input['name']),
            'email' => filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL),
            'address' => trim($input['address']),
            'contact' => trim($input['contact']),
        ];

        // Validate email
        if (!filter_var($barangayData['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            return;
        }

        // Save to file
        $settingsFile = ROOT_DIR . 'app/config/barangay_settings.json';
        $settingsDir = dirname($settingsFile);
        
        if (!is_dir($settingsDir)) {
            mkdir($settingsDir, 0755, true);
        }

        if (file_put_contents($settingsFile, json_encode($barangayData, JSON_PRETTY_PRINT))) {
            // Log the activity
            if (isset($this->ActivityLogModel)) {
                try {
                    $this->ActivityLogModel->record([
                        'action' => 'Updated Settings',
                        'details' => 'Updated barangay information: ' . $barangayData['name'],
                        'request_id' => null,
                        'admin_id' => $_SESSION['user_id'] ?? null,
                    ]);
                } catch (Throwable $e) {
                    error_log('Failed to record settings update activity: ' . $e->getMessage());
                }
            }

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Barangay information saved successfully!',
                'data' => $barangayData
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to save settings file']);
        }
    }


    public function request_management() {
        $requests = $this->RequestModel->getAllRequests();

        $this->call->view('dashboard/request_management', [
            'requests' => $requests
        ]);
    }
}
?>
