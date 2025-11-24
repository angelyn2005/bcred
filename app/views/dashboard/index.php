<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$requests = $requests ?? [];
$currentUser = $currentUser ?? ['name' => 'Admin'];
$page = $page ?? 'dashboard';

$totalRequests = $totalRequests ?? count($requests);
$pendingRequests = $pendingRequests ?? 0;
$approvedRequests = $approvedRequests ?? 0;
$releasedRequests = $releasedRequests ?? 0;
$rejectedRequests = $rejectedRequests ?? 0;
$totalUsers = $totalUsers ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - Barangay E-Credentials</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; min-height:100vh; font-family: "Poppins", sans-serif; }
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
.main-content { margin-left:280px; padding:40px; }
.topbar { background:#fff; border-bottom:1px solid #ddd; padding:15px 25px; display:flex; justify-content:space-between; align-items:center; border-radius:10px; }
.card { border-radius:10px; }
.card h6 { color:#6c757d; }
.card h3 { font-weight:700; }
.badge-status { font-size:0.85rem; font-weight:500; padding:0.5em 0.75em; border-radius:0.375rem; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="bi bi-shield-fill-check"></i> Admin Portal</h4>
    </div>
    <div class="sidebar-nav">
        <a href="?page=dashboard" class="<?= $page==='dashboard'?'active':'' ?>"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/requests"><i class="bi bi-file-earmark-text-fill"></i> Request Management</a>
        <a href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people-fill"></i> User Management</a>
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
    <div class="topbar shadow-sm">
        <h5 class="mb-0 fw-bold text-primary">Admin Dashboard</h5>
        <div class="username"><i class="bi bi-person-circle me-1 text-secondary"></i><?= $currentUser['name'] ?></div>
    </div>

    <div class="container-fluid mt-4">
        <?php if($page==='dashboard'): ?>
            <!-- Monitoring Cards -->
            <div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-6">
                <div class="col"><div class="card shadow-sm p-3 bg-white"><h6>Total Requests</h6><h3 class="text-primary"><?= $totalRequests ?></h3></div></div>
                <div class="col"><div class="card shadow-sm p-3 bg-white"><h6>Pending</h6><h3 class="text-warning"><?= $pendingRequests ?></h3></div></div>
                <div class="col"><div class="card shadow-sm p-3 bg-white"><h6>Approved</h6><h3 class="text-info"><?= $approvedRequests ?></h3></div></div>
                <div class="col"><div class="card shadow-sm p-3 bg-white"><h6>Released</h6><h3 class="text-success"><?= $releasedRequests ?></h3></div></div>
                <div class="col"><div class="card shadow-sm p-3 bg-white"><h6>Rejected</h6><h3 class="text-danger"><?= $rejectedRequests ?></h3></div></div>
                <div class="col"><div class="card shadow-sm p-3 bg-white"><h6>Total Users</h6><h3 class="text-success"><?= $totalUsers ?></h3></div></div>
            </div>

            <!-- Recent Requests -->
            <div class="row g-4 mt-3">
                <div class="col-12">
                    <div class="card shadow-sm p-3 bg-white">
                        <h6 class="fw-bold text-secondary">Recent Requests</h6>
                        <div class="list-group mt-2">
                            <?php if(empty($requests)): ?>
                                <p class="text-muted text-center py-3">No requests yet</p>
                            <?php else: ?>
                                <?php foreach(array_slice(array_reverse($requests),0,5) as $req): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= htmlspecialchars($req['user_id']) ?></strong> - <?= htmlspecialchars($req['document_type']) ?>
                                            <br><small class="text-muted"><?= date('M d, Y', strtotime($req['created_at'])) ?></small>
                                        </div>
                                        <span class="badge bg-<?=
                                            $req['status']=='pending'?'warning':
                                            ($req['status']=='approved'?'primary':
                                            ($req['status']=='released'?'success':'danger'))
                                        ?>"><?= ucfirst($req['status']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
