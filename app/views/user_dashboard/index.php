<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$currentUser = $data['currentUser'] ?? ['id'=>0,'fullname'=>'Guest'];
$page = $data['page'] ?? 'dashboard';
$requests = $data['requests'] ?? [];
$notifications = $data['notifications'] ?? [];
$totalRequests = $data['totalRequests'] ?? 0;
$pendingRequests = $data['pendingRequests'] ?? 0;
$completedRequests = $data['completedRequests'] ?? 0;
$flash = $data['flash'] ?? null;
$fullName = trim($currentUser['fullname'] ?? 'Guest');
$userInitials = '';
$nameParts = array_filter(explode(' ', $fullName));
foreach ($nameParts as $idx => $part) {
    if ($idx > 1) {
        break;
    }
    $userInitials .= strtoupper(substr($part, 0, 1));
}
if ($userInitials === '') {
    $userInitials = strtoupper(substr($fullName, 0, 1));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Dashboard - Barangay E-Credentials</title>
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
    border-left-color: #10b981;
    padding-left: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.sidebar a:hover i {
    transform: scale(1.2) rotate(5deg);
}
.sidebar a.active {
    background: linear-gradient(90deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.1) 100%);
    color: #fff;
    border-left-color: #10b981;
    font-weight: 600;
    box-shadow: 0 4px 15px rgba(16,185,129,0.3);
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
.user-meta { display:flex; align-items:center; gap:20px; }
.user-avatar { width:48px; height:48px; border-radius:50%; object-fit:cover; border:2px solid rgba(13,110,253,.4); }
.user-avatar-placeholder { width:48px; height:48px; border-radius:50%; background:#e9ecef; color:#0d6efd; display:flex; align-items:center; justify-content:center; font-weight:600; }
.card { border-radius:10px; }
.card h6 { color:#6c757d; }
.card h3 { font-weight:700; }
.badge-status { font-size:0.85rem; font-weight:500; padding:0.5em 0.75em; border-radius:0.375rem; }
.notification-bell { position:relative; cursor:pointer; }
.notification-bell i { font-size:1.5rem; color:#0d6efd; transition:color 0.2s; }
.notification-bell:hover i { color:#084298; }
.notification-badge { position:absolute; top:-8px; right:-8px; background:#dc3545; color:#fff; border-radius:50%; width:20px; height:20px; display:flex; align-items:center; justify-content:center; font-size:0.7rem; font-weight:700; }
.notification-dropdown { position:absolute; top:50px; right:0; background:#fff; border:1px solid #ddd; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.15); width:350px; max-height:400px; overflow-y:auto; z-index:1000; display:none; }
.notification-dropdown.show { display:block; }
.notification-dropdown-header { padding:12px 16px; border-bottom:1px solid #e9ecef; font-weight:600; color:#0d6efd; }
.notification-item { padding:12px 16px; border-bottom:1px solid #f0f0f0; transition:background 0.2s; cursor:pointer; }
.notification-item:hover { background:#f8f9fa; }
.notification-item:last-child { border-bottom:none; }
.notification-item.unread { background:#e7f3ff; font-weight:600; }
.notification-item .notification-title { font-weight:600; color:#212529; margin-bottom:4px; }
.notification-item .notification-message { font-size:0.85rem; color:#6c757d; margin-bottom:4px; }
.notification-item .notification-time { font-size:0.75rem; color:#adb5bd; }
.notification-empty { padding:40px 20px; text-align:center; color:#adb5bd; }
.sidebar-profile { text-align:center; padding:30px 20px; border-bottom:2px solid rgba(255,255,255,0.15); margin-bottom:25px; background: rgba(0,0,0,0.15); position: relative; }
.sidebar-profile::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 100%; background: radial-gradient(circle at top, rgba(255,255,255,0.1) 0%, transparent 70%); }
.sidebar-profile-pic { width:90px; height:90px; border-radius:50%; object-fit:cover; margin:0 auto 15px; border:4px solid rgba(255,255,255,0.3); box-shadow: 0 8px 20px rgba(0,0,0,0.3); transition: all 0.3s ease; position: relative; }
.sidebar-profile-pic:hover { transform: scale(1.1); border-color: #10b981; box-shadow: 0 12px 30px rgba(16,185,129,0.4); }
.sidebar-profile-placeholder { width:90px; height:90px; border-radius:50%; background: linear-gradient(135deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 100%); color:#fff; display:flex; align-items:center; justify-content:center; font-size:2.2rem; font-weight:700; margin:0 auto 15px; border:4px solid rgba(255,255,255,0.3); box-shadow: 0 8px 20px rgba(0,0,0,0.3); transition: all 0.3s ease; position: relative; }
.sidebar-profile-placeholder:hover { transform: scale(1.1); border-color: #10b981; box-shadow: 0 12px 30px rgba(16,185,129,0.4); }
.sidebar-profile-name { color:#fff; font-weight:700; font-size:1.2rem; margin-bottom:6px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); position: relative; }
.sidebar-profile-email { color:rgba(255,255,255,0.8); font-size:0.85rem; word-break:break-word; position: relative; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <!-- Profile Section -->
    <div class="sidebar-profile">
        <?php if (!empty($currentUser['photo_url'])): ?>
            <img src="<?= htmlspecialchars($currentUser['photo_url']) ?>" alt="Profile" class="sidebar-profile-pic">
        <?php else: ?>
            <div class="sidebar-profile-placeholder"><?= htmlspecialchars($userInitials) ?></div>
        <?php endif; ?>
        <div class="sidebar-profile-name"><?= htmlspecialchars(explode(' ', $fullName)[0] ?? 'Guest') ?></div>
        <div class="sidebar-profile-email"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
    </div>
    
    <div class="sidebar-nav">
        <a href="?page=dashboard" class="<?= $page==='dashboard'?'active':'' ?>"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="?page=my_requests" class="<?= $page==='my_requests'?'active':'' ?>"><i class="bi bi-file-earmark-text-fill"></i> My Requests</a>
        <a href="?page=new_request" class="<?= $page==='new_request'?'active':'' ?>"><i class="bi bi-plus-circle-fill"></i> New Request</a>
        <a href="?page=profile" class="<?= $page==='profile'?'active':'' ?>"><i class="bi bi-person-circle"></i> Profile</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/logout" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>


<!-- Main Content -->
<div class="main-content">
    <div class="topbar shadow-sm">
        <h5 class="mb-0 fw-bold text-primary"><?= ucfirst($page) ?></h5>
        <div class="user-meta">
            <!-- Notification Bell -->
            <div class="notification-bell" id="notificationBell">
                <i class="bi bi-bell-fill"></i>
                <?php 
                    $unreadCount = count(array_filter($notifications, fn($n) => empty($n['is_read'])));
                ?>
                <?php if($unreadCount > 0): ?>
                    <span class="notification-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                <?php endif; ?>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-dropdown-header">
                        <i class="bi bi-bell me-2"></i>Notifications
                        <?php if($unreadCount > 0): ?>
                            <span class="badge bg-danger ms-2"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if(empty($notifications)): ?>
                        <div class="notification-empty">
                            <i class="bi bi-inbox" style="font-size:3rem; opacity:0.3;"></i>
                            <p class="mt-2 mb-0">No notifications yet</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($notifications as $n): ?>
                            <div class="notification-item <?= empty($n['is_read']) ? 'unread' : '' ?>">
                                <div class="notification-title"><?= htmlspecialchars($n['title'] ?? 'Notification') ?></div>
                                <?php if (!empty($n['message'])): ?>
                                    <div class="notification-message"><?= htmlspecialchars($n['message']) ?></div>
                                <?php endif; ?>
                                <div class="notification-time">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= !empty($n['created_at']) ? date('M d, Y H:i', strtotime($n['created_at'])) : '' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(count($notifications) >= 10): ?>
                            <div style="padding:10px; text-align:center; background:#f8f9fa; font-size:0.75rem; color:#6c757d;">
                                <i class="bi bi-info-circle me-1"></i>Showing latest 10 notifications
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($currentUser['photo_url'])): ?>
                <img src="<?= htmlspecialchars($currentUser['photo_url']) ?>" alt="Profile photo" class="user-avatar">
            <?php else: ?>
                <div class="user-avatar user-avatar-placeholder"><?= htmlspecialchars($userInitials) ?></div>
            <?php endif; ?>
            <div class="text-end">
                <div class="fw-bold text-primary mb-0"><?= htmlspecialchars($currentUser['fullname'] ?? 'Guest') ?></div>
                <small class="text-muted text-capitalize"><?= htmlspecialchars($currentUser['role'] ?? '') ?></small>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">

    <?php if (!empty($flash['message'] ?? '')): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if($page==='dashboard'): ?>
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="card shadow-sm p-3 bg-white"><h6>Pending Requests</h6><h3 class="text-warning"><?= $pendingRequests ?></h3></div></div>
            <div class="col-md-4"><div class="card shadow-sm p-3 bg-white"><h6>Total Requests</h6><h3 class="text-primary"><?= $totalRequests ?></h3></div></div>
            <div class="col-md-4"><div class="card shadow-sm p-3 bg-white"><h6>Completed Requests</h6><h3 class="text-success"><?= $completedRequests ?></h3></div></div>
        </div>

        <!-- Document Price List -->
        <div class="card shadow-sm p-4 mb-4 bg-white">
            <h5 class="fw-bold mb-3 text-primary"><i class="bi bi-file-earmark-text me-2"></i>Available Documents</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Document Type</th>
                            <th style="width: 150px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $documentPrices = [
                            ['name' => 'Barangay Clearance', 'price' => '₱50.00'],
                            ['name' => 'Indigency Certificate', 'price' => '₱30.00'],
                            ['name' => 'Residency Certificate', 'price' => '₱40.00'],
                            ['name' => 'Business Permit', 'price' => '₱200.00'],
                        ];
                        foreach ($documentPrices as $index => $doc):
                        ?>
                            <tr>
                                <td>
                                    <strong class="text-dark"><?= htmlspecialchars($doc['name']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <a href="?page=new_request&doc_type=<?= urlencode($doc['name']) ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle me-1"></i> Request
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <i class="bi bi-info-circle me-2"></i>
                <small><strong>Note:</strong> Click the "Request" button to start your document request.</small>
            </div>
        </div>

    <?php elseif($page==='my_requests'): ?>
        <h3>My Requests</h3>
        <div class="card shadow-sm p-4 bg-white">
            <table class="table table-hover mt-3">
                <thead><tr><th>#</th><th>Document</th><th>Purpose</th><th>Date Submitted</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach($requests as $i=>$r): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($r['document_type'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['purpose'] ?? $r['details'] ?? '') ?></td>
                            <td><?= !empty($r['created_at']) ? date('M d, Y', strtotime($r['created_at'])) : '' ?></td>
                            <td><?= ucfirst($r['status'] ?? 'pending') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No requests submitted yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif($page==='new_request'): ?>
        <h3>New Request</h3>
        <?php
            $selectedDocType = $_GET['doc_type'] ?? '';
        ?>
        <?php if (!empty($selectedDocType)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <strong><?= htmlspecialchars($selectedDocType) ?></strong> has been pre-selected. Please fill out the form below.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="card shadow-sm p-4 bg-white">
            <form method="post" action="<?= site_url('request/submit') ?>" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Document Type</label>
                    <?php
                        $documentOptions = [
                            'Barangay Clearance',
                            'Indigency Certificate',
                            'Residency Certificate',
                            'Business Permit',
                        ];
                    ?>
                    <select id="docTypeSelect" name="doc_type" class="form-select" required>
                        <option value="">Select document</option>
                        <?php foreach ($documentOptions as $option): ?>
                            <option value="<?= $option ?>" <?= $selectedDocType === $option ? 'selected' : '' ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="dynamicFields" class="mb-3">
                    <!-- Dynamic fields will be injected here depending on document type -->
                </div>

                <button type="submit" class="btn btn-primary">Submit Request</button>
            </form>
        </div>

    <?php elseif($page==='profile'): ?>
        <?php include 'profile_lavalust.php'; ?>
    <?php endif; ?>

    </div>
</div>

<script>
// Dynamic fields for request types
(function(){
    const templates = {
        'Barangay Clearance': `
            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input name="full_name" class="form-control" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="row g-2">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input name="dob" type="date" class="form-control" placeholder="e.g. 1990-05-12" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">Select</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Civil Status <span class="text-danger">*</span></label>
                <input name="civil_status" class="form-control" placeholder="Single, Married, Widow/Widower" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nationality <span class="text-danger">*</span></label>
                <input name="nationality" class="form-control" placeholder="Filipino" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Address <span class="text-danger">*</span></label>
                <input name="address" class="form-control" placeholder="House No., Street, Barangay, Municipality/City" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Purpose of Clearance <span class="text-danger">*</span></label>
                <input name="purpose" class="form-control" placeholder="Employment, School, Business" required>
            </div>
            <div class="row g-2">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Number</label>
                    <input name="contact_number" class="form-control" placeholder="0917xxxxxxx">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date Requested</label>
                    <input name="date_requested" type="date" class="form-control" value="`+ new Date().toISOString().slice(0,10) +`">
                </div>
            </div>
        `,

        'Indigency Certificate': `
            <div class="mb-3">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input name="full_name" class="form-control" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="row g-2">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input name="dob" type="date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required><option value="">Select</option><option>Male</option><option>Female</option></select>
                </div>
            </div>
            <div class="mb-3"><label class="form-label">Civil Status <span class="text-danger">*</span></label><input name="civil_status" class="form-control" placeholder="Single" required></div>
            <div class="mb-3"><label class="form-label">Nationality <span class="text-danger">*</span></label><input name="nationality" class="form-control" placeholder="Filipino" required></div>
            <div class="mb-3"><label class="form-label">Address <span class="text-danger">*</span></label><input name="address" class="form-control" placeholder="Complete address" required></div>
            <div class="mb-3"><label class="form-label">Occupation / Source of Income</label><input name="occupation" class="form-control" placeholder="Farmer, Vendor, etc."></div>
            <div class="mb-3"><label class="form-label">Purpose of Indigency Certificate <span class="text-danger">*</span></label><input name="purpose" class="form-control" placeholder="Scholarship support" required></div>
            <div class="row g-2"><div class="col-md-6 mb-3"><label class="form-label">Contact Number</label><input name="contact_number" class="form-control" placeholder="0917xxxxxxx"></div><div class="col-md-6 mb-3"><label class="form-label">Date Requested</label><input name="date_requested" type="date" class="form-control" value="`+ new Date().toISOString().slice(0,10) +`"></div></div>
        `,

        'Residency Certificate': `
            <div class="mb-3"><label class="form-label">Full Name <span class="text-danger">*</span></label><input name="full_name" class="form-control" placeholder="Juan Dela Cruz" required></div>
            <div class="row g-2"><div class="col-md-6 mb-3"><label class="form-label">Date of Birth <span class="text-danger">*</span></label><input name="dob" type="date" class="form-control" required></div><div class="col-md-6 mb-3"><label class="form-label">Gender <span class="text-danger">*</span></label><select name="gender" class="form-select" required><option value="">Select</option><option>Male</option><option>Female</option></select></div></div>
            <div class="mb-3"><label class="form-label">Civil Status <span class="text-danger">*</span></label><input name="civil_status" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Nationality <span class="text-danger">*</span></label><input name="nationality" class="form-control" placeholder="Filipino" required></div>
            <div class="mb-3"><label class="form-label">Complete Address <span class="text-danger">*</span></label><input name="address" class="form-control" placeholder="House No., Street, Barangay, Municipality/City" required></div>
            <div class="mb-3"><label class="form-label">Period of Residency</label><input name="residency_period" class="form-control" placeholder="Residing here since 2015"></div>
            <div class="mb-3"><label class="form-label">Purpose <span class="text-danger">*</span></label><input name="purpose" class="form-control" placeholder="School enrollment" required></div>
            <div class="row g-2"><div class="col-md-6 mb-3"><label class="form-label">Contact Number</label><input name="contact_number" class="form-control"></div><div class="col-md-6 mb-3"><label class="form-label">Date Requested</label><input name="date_requested" type="date" class="form-control" value="`+ new Date().toISOString().slice(0,10) +`"></div></div>
        `,

        'Business Permit': `
            <div class="mb-3"><label class="form-label">Business Name / Trade Name <span class="text-danger">*</span></label><input name="business_name" class="form-control" placeholder="ABC Store" required></div>
            <div class="mb-3"><label class="form-label">Business Owner's Full Name <span class="text-danger">*</span></label><input name="owner_name" class="form-control" placeholder="Juan Dela Cruz" required></div>
            <div class="row g-2"><div class="col-md-4 mb-3"><label class="form-label">Owner Date of Birth <span class="text-danger">*</span></label><input name="owner_dob" type="date" class="form-control" required></div><div class="col-md-4 mb-3"><label class="form-label">Gender <span class="text-danger">*</span></label><select name="owner_gender" class="form-select" required><option value="">Select</option><option>Male</option><option>Female</option></select></div><div class="col-md-4 mb-3"><label class="form-label">Civil Status <span class="text-danger">*</span></label><input name="owner_civil_status" class="form-control" required></div></div>
            <div class="mb-3"><label class="form-label">Nationality <span class="text-danger">*</span></label><input name="owner_nationality" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Owner Address <span class="text-danger">*</span></label><input name="owner_address" class="form-control" placeholder="123 Riverside St., Brgy. San Isidro, Puerto Galera, Oriental Mindoro" required></div>
            <div class="mb-3"><label class="form-label">Contact Number</label><input name="contact_number" class="form-control" placeholder="0917xxxxxxx"></div>
            <div class="mb-3"><label class="form-label">Business Address <span class="text-danger">*</span></label><input name="business_address" class="form-control" placeholder="Same as owner's address" required></div>
            <div class="mb-3"><label class="form-label">Nature of Business <span class="text-danger">*</span></label><input name="business_nature" class="form-control" placeholder="Retail, Service, etc." required></div>
            <div class="mb-3"><label class="form-label">Business Start Date</label><input name="business_start_date" type="date" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Purpose / Type of Permit</label><input name="permit_type" class="form-control" placeholder="New, Renewal"></div>
            <div class="mb-3"><label class="form-label">Date Requested</label><input name="date_requested" type="date" class="form-control" value="`+ new Date().toISOString().slice(0,10) +`"></div>
        `
    };

    const select = document.getElementById('docTypeSelect');
    const target = document.getElementById('dynamicFields');

    function renderFields(val) {
        if (!val || !templates[val]) {
            target.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Purpose / Additional details</label>
                    <textarea name="details" class="form-control" rows="3" placeholder="Briefly describe your purpose or additional details"></textarea>
                </div>
            `;
            return;
        }
        target.innerHTML = templates[val];
    }

    if (select) {
        // Auto-trigger on page load if document is pre-selected from URL parameter
        if (select.value) {
            renderFields(select.value);
        }
        
        select.addEventListener('change', function(e){
            renderFields(e.target.value);
        });
    }
})();

// Notification Bell Dropdown Toggle
(function(){
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');
    
    if(bell && dropdown) {
        bell.addEventListener('click', function(e){
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e){
            if(!bell.contains(e.target) && !dropdown.contains(e.target)){
                dropdown.classList.remove('show');
            }
        });
        
        // Prevent dropdown from closing when clicking inside it
        dropdown.addEventListener('click', function(e){
            e.stopPropagation();
        });
    }
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
