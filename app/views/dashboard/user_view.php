<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$user = $user ?? [];
$requests = $requests ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - <?= htmlspecialchars($user['fullname'] ?? 'User') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: "Poppins", sans-serif; background-color: #f8f9fa; }
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #0d6efd 0%, #084298 100%);
            min-height: 100vh;
            position: fixed;
            top:0; left:0;
            padding:0;
            box-shadow:4px 0 15px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            padding: 25px 20px;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
            margin-bottom: 10px;
        }
        .sidebar-header h4 {
            color: #fff;
            font-weight: 700;
            font-size: 1.3rem;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .sidebar-header h4 i {
            font-size: 1.5rem;
        }
        .sidebar-nav {
            padding: 10px 0;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.85);
            padding: 14px 25px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            margin: 2px 0;
        }
        .sidebar a i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border-left-color: #fff;
            padding-left: 30px;
        }
        .sidebar a.active {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border-left-color: #fff;
            font-weight: 600;
        }
        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 15px 20px;
        }
        .logout-btn {
            color: rgba(255,255,255,0.85);
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 15px !important;
        }
        .logout-btn:hover {
            background-color: rgba(220,53,69,0.3);
            color: #fff;
            border-left-color: #dc3545;
        }
        .main-content { margin-left: 260px; padding: 40px; }
        .topbar { background: #fff; border-bottom: 1px solid #ddd; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

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

<div class="main-content">
    <div class="topbar">
        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-lines-fill me-2"></i>User Profile</h5>
        <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold text-primary mb-3"><?= htmlspecialchars($user['fullname'] ?? 'User') ?></h5>
                    <p class="mb-2"><i class="bi bi-envelope me-2 text-secondary"></i><?= htmlspecialchars($user['email'] ?? 'N/A') ?></p>
                    <p class="mb-2"><i class="bi bi-telephone me-2 text-secondary"></i><?= htmlspecialchars($user['contact'] ?? 'N/A') ?></p>
                    <p class="mb-2"><i class="bi bi-geo-alt me-2 text-secondary"></i><?= htmlspecialchars($user['address'] ?? 'N/A') ?></p>
                    <p class="mb-2"><i class="bi bi-person-badge me-2 text-secondary"></i><?= ucfirst(htmlspecialchars($user['role'] ?? 'resident')) ?></p>
                    <p class="mb-0"><i class="bi bi-calendar-event me-2 text-secondary"></i>Joined <?= !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A' ?></p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Request History</h6>
                    <span class="badge bg-primary"><?= count($requests) ?> total</span>
                </div>
                <div class="card-body">
                    <?php if (empty($requests)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-1 text-secondary mb-2"></i><br>No requests found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Document</th>
                                        <th>Purpose</th>
                                        <th>Status</th>
                                        <th>Date Submitted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($request['document_type'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($request['details'] ?? 'N/A') ?></td>
                                            <td>
                                                <span class="badge bg-<?=
                                                    ($request['status'] ?? '') === 'pending' ? 'warning' :
                                                    (($request['status'] ?? '') === 'approved' ? 'success' :
                                                    (($request['status'] ?? '') === 'rejected' ? 'danger' : 'secondary'))
                                                ?>">
                                                    <?= ucfirst(htmlspecialchars($request['status'] ?? 'unknown')) ?>
                                                </span>
                                            </td>
                                            <td><?= !empty($request['created_at']) ? date('M d, Y', strtotime($request['created_at'])) : 'N/A' ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

