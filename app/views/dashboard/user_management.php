<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

// Ensure $users array exists
$users = $users ?? [];

// Search functionality
$searchTerm = $_GET['search'] ?? '';
$filteredUsers = array_filter($users, function($user) use ($searchTerm) {
    $name = $user['fullname'] ?? ($user['name'] ?? '');
    $email = $user['email'] ?? '';
    $contact = $user['contact'] ?? '';

    if ($searchTerm === '') {
        return true;
    }

    return stripos($name, $searchTerm) !== false ||
           stripos($email, $searchTerm) !== false ||
           stripos($contact, $searchTerm) !== false;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: "Poppins", sans-serif; background-color: #f8f9fa; }
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #185adb 0%, #2563EB 50%, #4F46E5 100%);
            min-height: 100vh;
            position: fixed;
            top:0; left:0;
            padding:0;
            box-shadow:6px 0 25px rgba(0,0,0,0.2);
            overflow-y: auto;
        }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.3); border-radius: 10px; }
        .sidebar-header {
            padding: 30px 20px;
            background: rgba(0,0,0,0.2);
            border-bottom: 2px solid rgba(255,255,255,0.15);
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
        }
        .sidebar-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        .sidebar-header h4 {
            color: #fff;
            font-weight: 800;
            font-size: 1.4rem;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .sidebar-header h4 i {
            font-size: 1.8rem;
            animation: iconBounce 2s ease-in-out infinite;
        }
        @keyframes iconBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .sidebar-nav {
            padding: 15px 0;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 15px;
            color: rgba(255,255,255,0.9);
            padding: 16px 25px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            margin: 3px 10px;
            border-radius: 0 10px 10px 0;
            position: relative;
        }
        .sidebar a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 0;
            height: 100%;
            background: rgba(255,255,255,0.1);
            transition: width 0.3s ease;
            border-radius: 0 10px 10px 0;
        }
        .sidebar a:hover::before {
            width: 100%;
        }
        .sidebar a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            transition: transform 0.3s ease;
            position: relative;
            z-index: 1;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border-left-color: #fbbf24;
            padding-left: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .sidebar a:hover i {
            transform: scale(1.2) rotate(5deg);
        }
        .sidebar a.active {
            background: linear-gradient(90deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.1) 100%);
            color: #fff;
            border-left-color: #fbbf24;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(251,191,36,0.3);
        }
        .sidebar a.active i {
            transform: scale(1.15);
        }
        .sidebar-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.2) 50%, transparent 100%);
            margin: 20px 25px;
        }
        .logout-btn {
            color: rgba(255,255,255,0.9);
            margin-top: 20px;
            border-top: 2px solid rgba(255,255,255,0.15);
            padding-top: 20px !important;
        }
        .logout-btn:hover {
            background: linear-gradient(90deg, rgba(220,53,69,0.4) 0%, rgba(220,53,69,0.2) 100%);
            color: #fff;
            border-left-color: #ef4444;
            box-shadow: 0 4px 12px rgba(220,53,69,0.3);
        }
        .logout-btn:hover i {
            animation: slideOut 0.5s ease;
        }
        @keyframes slideOut {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(5px); }
        }
        .main-content { margin-left: 280px; padding: 40px; }
        .topbar { background: #fff; border-bottom: 1px solid #ddd; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        
        /* Profile Picture Styles */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #dee2e6;
        }
        .user-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            border: 2px solid #dee2e6;
        }
        .user-name-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        /* Uniform Button Styles */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: nowrap;
            align-items: center;
        }
        .action-buttons .btn {
            min-width: 95px;
            padding: 6px 12px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
        }
        .action-buttons form {
            margin: 0;
            display: inline-block;
        }
        .btn-view {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-view:hover {
            background: linear-gradient(135deg, #5568d3 0%, #63408a 100%);
            color: white;
        }
        .btn-activate {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            color: white;
        }
        .btn-activate:hover {
            background: linear-gradient(135deg, #0e8070 0%, #2dd368 100%);
            color: white;
        }
        .btn-deactivate {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            border: none;
            color: white;
        }
        .btn-deactivate:hover {
            background: linear-gradient(135deg, #d00866 0%, #e55e00 100%);
            color: white;
        }
        
        /* Modal Styles */
        .modal-backdrop-custom {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 1040;
            display: none;
        }
        .modal-backdrop-custom.show {
            display: block;
        }
        .modal-custom {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            z-index: 1050;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            display: none;
        }
        .modal-custom.show {
            display: block;
            animation: slideDown 0.3s ease-out;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translate(-50%, -60%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }
        .modal-header-custom {
            padding: 20px 25px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body-custom {
            padding: 25px;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }
        .modal-close:hover {
            background: #f0f0f0;
            color: #333;
        }
        .user-detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .user-detail-row:last-child {
            border-bottom: none;
        }
        .user-detail-label {
            font-weight: 600;
            color: #555;
            min-width: 150px;
        }
        .user-detail-value {
            color: #333;
            flex: 1;
        }
        .user-profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            color: white;
        }
        .user-profile-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        .user-profile-initial {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            color: #764ba2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 32px;
            border: 3px solid white;
        }
        .request-item {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #667eea;
        }
        .request-item:last-child {
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; padding: 20px; }
            .modal-custom { width: 95%; max-height: 85vh; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="bi bi-shield-fill-check"></i> Admin Portal</h4>
    </div>
    <div class="sidebar-nav">
        <a href="<?= BASE_URL ?>/dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/requests"><i class="bi bi-file-earmark-text-fill"></i> Request Management</a>
        <a href="<?= BASE_URL ?>/admin/users" class="active"><i class="bi bi-people-fill"></i> User Management</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/admin/analytics"><i class="bi bi-bar-chart-line-fill"></i> Analytics & Reports</a>
        <a href="<?= BASE_URL ?>/admin/activity-logs"><i class="bi bi-clock-history"></i> Activity Logs</a>
        <a href="<?= BASE_URL ?>/admin/settings"><i class="bi bi-gear-fill"></i> Settings</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/logout" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="topbar">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-people me-2"></i>User Management</h5>
        <div class="username"><i class="bi bi-person-circle me-1 text-secondary"></i>Admin</div>
    </div>

    <div class="container-fluid">
        <!-- Search -->
        <form method="get" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by name, email or contact..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Search</button>
            </div>
        </form>

        <!-- User Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Registered Users</h6>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Total Requests</th>
                            <th>Pending</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($filteredUsers)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-1 text-secondary mb-2"></i><br>No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($filteredUsers as $user): ?>
                                <?php
                                    $displayName = $user['fullname'] ?? ($user['name'] ?? 'N/A');
                                    $totalRequests = $user['total_requests'] ?? count($user['requests'] ?? []);
                                    $pendingRequests = $user['pending_requests'] ?? count(array_filter($user['requests'] ?? [], fn($r) => ($r['status'] ?? '') === 'pending'));
                                    $isActive = isset($user['is_active']) ? ($user['is_active'] == 1) : true;
                                    $photoPath = !empty($user['photo']) ? BASE_URL . '/' . $user['photo'] : null;
                                    $initial = strtoupper(substr($displayName, 0, 1));
                                ?>
                                <tr>
                                    <td>
                                        <div class="user-name-cell">
                                            <?php if($photoPath): ?>
                                                <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($displayName) ?>" class="user-avatar">
                                            <?php else: ?>
                                                <div class="user-initial"><?= $initial ?></div>
                                            <?php endif; ?>
                                            <span class="fw-semibold"><?= htmlspecialchars($displayName) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($user['email'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($user['contact'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if($isActive): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Deactivated</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $totalRequests ?></td>
                                    <td><?= $pendingRequests ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button onclick="viewUserModal(<?= htmlspecialchars(json_encode($user)) ?>)" class="btn btn-view">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                            <?php if($isActive): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/user/deactivate/<?= $user['id'] ?? '' ?>" class="m-0" onsubmit="return confirm('Deactivate this user account? They will not be able to login or request documents.')">
                                                    <button type="submit" class="btn btn-deactivate">
                                                        <i class="bi bi-x-circle"></i> Deactivate
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/user/activate/<?= $user['id'] ?? '' ?>" class="m-0" onsubmit="return confirm('Activate this user account? They will be able to login and request documents.')">
                                                    <button type="submit" class="btn btn-activate">
                                                        <i class="bi bi-check-circle"></i> Activate
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- User Details Modal -->
<div class="modal-backdrop-custom" id="userModalBackdrop" onclick="closeUserModal()"></div>
<div class="modal-custom" id="userModal">
    <div class="modal-header-custom">
        <h5 class="mb-0 fw-bold"><i class="bi bi-person-circle me-2"></i>User Details</h5>
        <button class="modal-close" onclick="closeUserModal()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="modal-body-custom" id="userModalContent">
        <!-- Content will be populated by JavaScript -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewUserModal(user) {
    const modal = document.getElementById('userModal');
    const backdrop = document.getElementById('userModalBackdrop');
    const content = document.getElementById('userModalContent');
    
    const photoPath = user.photo ? '<?= BASE_URL ?>/' + user.photo : null;
    const initial = user.fullname ? user.fullname.charAt(0).toUpperCase() : 'U';
    const isActive = user.is_active == 1;
    
    // Build requests list
    let requestsHtml = '';
    if (user.requests && user.requests.length > 0) {
        requestsHtml = '<div class="mt-3"><h6 class="fw-bold mb-3">Request History</h6>';
        user.requests.forEach(req => {
            const statusClass = req.status === 'approved' ? 'success' : 
                              req.status === 'pending' ? 'warning' : 
                              req.status === 'rejected' ? 'danger' : 'secondary';
            requestsHtml += `
                <div class="request-item">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong>${req.document_type || 'N/A'}</strong>
                        <span class="badge bg-${statusClass}">${req.status || 'N/A'}</span>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-calendar"></i> ${req.created_at || 'N/A'}
                    </small>
                    ${req.purpose ? '<div class="mt-1"><small><strong>Purpose:</strong> ' + req.purpose + '</small></div>' : ''}
                </div>
            `;
        });
        requestsHtml += '</div>';
    } else {
        requestsHtml = '<div class="mt-3 text-center text-muted"><i class="bi bi-inbox"></i> No requests yet</div>';
    }
    
    content.innerHTML = `
        <div class="user-profile-header">
            ${photoPath ? 
                '<img src="' + photoPath + '" alt="Profile" class="user-profile-photo">' :
                '<div class="user-profile-initial">' + initial + '</div>'
            }
            <div>
                <h4 class="mb-1">${user.fullname || 'N/A'}</h4>
                <div>
                    ${isActive ? 
                        '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Active</span>' :
                        '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Deactivated</span>'
                    }
                </div>
            </div>
        </div>
        
        <div class="user-detail-row">
            <div class="user-detail-label"><i class="bi bi-envelope me-2"></i>Email</div>
            <div class="user-detail-value">${user.email || 'N/A'}</div>
        </div>
        <div class="user-detail-row">
            <div class="user-detail-label"><i class="bi bi-telephone me-2"></i>Contact</div>
            <div class="user-detail-value">${user.contact || 'N/A'}</div>
        </div>
        <div class="user-detail-row">
            <div class="user-detail-label"><i class="bi bi-house me-2"></i>Address</div>
            <div class="user-detail-value">${user.address || 'N/A'}</div>
        </div>
        <div class="user-detail-row">
            <div class="user-detail-label"><i class="bi bi-person-badge me-2"></i>Username</div>
            <div class="user-detail-value">${user.username || 'N/A'}</div>
        </div>
        <div class="user-detail-row">
            <div class="user-detail-label"><i class="bi bi-calendar-check me-2"></i>Member Since</div>
            <div class="user-detail-value">${user.created_at || 'N/A'}</div>
        </div>
        <div class="user-detail-row">
            <div class="user-detail-label"><i class="bi bi-file-earmark-text me-2"></i>Total Requests</div>
            <div class="user-detail-value">${user.total_requests || 0}</div>
        </div>
        <div class="user-detail-row">
            <div class="user-detail-label"><i class="bi bi-hourglass-split me-2"></i>Pending Requests</div>
            <div class="user-detail-value">${user.pending_requests || 0}</div>
        </div>
        
        ${requestsHtml}
    `;
    
    modal.classList.add('show');
    backdrop.classList.add('show');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('show');
    document.getElementById('userModalBackdrop').classList.remove('show');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUserModal();
    }
});

document.querySelectorAll('.delete-user-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const name = this.dataset.userName || 'this user';
        const message = `Are you sure you want to delete ${name}'s account? This action cannot be undone. All user data and request history will be permanently removed.`;
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
