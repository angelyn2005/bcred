<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000');
}

$currentUser = [
    'id' => 1,
    'name' => 'Admin Juan',
];

// Sample $requests for demo
$requests = $data['requests'] ?? [
    ['id'=>1,'user_id'=>2,'document_type'=>'Barangay Clearance','details'=>'For job','status'=>'pending','created_at'=>'2025-11-10'],
    ['id'=>2,'user_id'=>3,'document_type'=>'Certificate of Residency','details'=>'School','status'=>'approved','created_at'=>'2025-11-09'],
    ['id'=>3,'user_id'=>4,'document_type'=>'Certificate of Indigency','details'=>'Scholarship','status'=>'pending','created_at'=>'2025-11-08'],
];

// Helper to get badge class
function getBadgeClass($status) {
    return $status === 'pending' ? 'warning' :
           ($status === 'approved' ? 'success' :
           ($status === 'rejected' ? 'danger' : 'secondary'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request Management - Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; font-family: "Poppins", sans-serif; }
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
.topbar { background: #fff; border-bottom: 1px solid #ddd; border-radius: 10px; padding: 15px 25px; display: flex; justify-content: space-between; }
.table th { background-color: #f8f9fa; font-weight: 600; }
.badge-status { font-size: 0.85rem; }
.d-inline { display: inline-block; }

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
    max-width: 700px;
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
}
.modal-body-custom {
    padding: 25px;
}
.modal-footer-custom {
    padding: 15px 25px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: white;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.2s;
}
.modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}
.detail-row {
    display: flex;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}
.detail-row:last-child {
    border-bottom: none;
}
.detail-label {
    font-weight: 600;
    color: #555;
    min-width: 140px;
}
.detail-value {
    color: #333;
    flex: 1;
}
.btn-action {
    min-width: 95px;
    padding: 8px 16px;
    font-weight: 500;
}

/* Custom Alert/Confirm Styles */
.alert-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    z-index: 1060;
    max-width: 450px;
    width: 90%;
    display: none;
}
.alert-modal.show {
    display: block;
    animation: alertSlide 0.3s ease-out;
}
@keyframes alertSlide {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}
.alert-header {
    padding: 20px 25px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 12px;
}
.alert-header.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; }
.alert-header.error { background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); color: white; }
.alert-header.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
.alert-header.info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
.alert-body {
    padding: 25px;
    font-size: 15px;
    color: #333;
    line-height: 1.6;
}
.alert-footer {
    padding: 15px 25px;
    border-top: 1px solid #f0f0f0;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
.alert-icon {
    font-size: 24px;
}

@media (max-width: 768px) { .sidebar { width: 100%; position: relative; min-height: auto; } .main-content { margin-left: 0; } .modal-custom { width: 95%; } }
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
        <a href="<?= BASE_URL ?>/admin/requests" class="active"><i class="bi bi-file-earmark-text-fill"></i> Request Management</a>
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
    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-file-earmark-text me-2"></i>Request Management</h5>
    <div><i class="bi bi-person-circle me-1 text-secondary"></i><?= htmlspecialchars($currentUser['name'] ?? '') ?></div>
    </div>

    <div class="container-fluid mt-4">

        <!-- Search & Filter -->
        <div class="d-flex justify-content-start align-items-center mb-3 gap-2 flex-wrap">
            <input type="text" id="searchInput" class="form-control" placeholder="Search user or document..." style="max-width: 300px;">
            <select id="statusFilter" class="form-select" style="max-width: 180px;">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="released">Released</option>
            </select>
        </div>

        <!-- Requests Table -->
        <div class="card shadow-sm p-4 bg-white">
            <div class="card-body">
                <table class="table table-hover align-middle" id="requestsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User ID</th>
                            <th>Document</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($requests)): ?>
                            <?php foreach($requests as $i => $r): ?>
                            <tr data-id="<?= htmlspecialchars($r['id'] ?? '') ?>" data-status="<?= htmlspecialchars($r['status'] ?? '') ?>">
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($r['user_id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['document_type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['purpose'] ?? $r['details'] ?? '') ?></td>
                                <td>
                                    <span class="badge badge-status bg-<?= getBadgeClass($r['status'] ?? '') ?>">
                                        <?= ucfirst((string)($r['status'] ?? '')) ?>
                                    </span>
                                </td>
                                <td><?= !empty($r['created_at']) ? date('M d, Y', strtotime($r['created_at'])) : '' ?></td>
                                <td>
                                    <?php if($r['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-success action-btn" data-status="approved" title="Approve">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger action-btn" data-status="rejected" title="Reject">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="viewRequestModal(<?= htmlspecialchars(json_encode($r)) ?>)" class="btn btn-sm btn-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 text-secondary"></i><br>No requests found.
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Remarks Modal -->
<div class="modal-backdrop-custom" id="remarksModalBackdrop"></div>
<div class="modal-custom" id="remarksModal" style="max-width: 500px;">
    <div class="modal-header-custom">
        <h5 class="mb-0 fw-bold" id="remarksModalTitle"><i class="bi bi-chat-left-text me-2"></i>Add Remarks</h5>
        <button class="modal-close" onclick="closeRemarksModal()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="modal-body-custom">
        <label class="form-label fw-semibold">Remarks (Optional)</label>
        <textarea class="form-control" id="remarksInput" rows="4" placeholder="Enter your remarks here..."></textarea>
        <small class="text-muted">Add any additional notes or comments for this action.</small>
    </div>
    <div class="modal-footer-custom">
        <button class="btn btn-secondary btn-action" onclick="closeRemarksModal()">Cancel</button>
        <button class="btn btn-primary btn-action" id="remarksSubmitBtn" onclick="submitRemarks()">
            <i class="bi bi-check-circle me-1"></i>Submit
        </button>
    </div>
</div>

<!-- Custom Alert Modal -->
<div class="modal-backdrop-custom" id="customAlertBackdrop"></div>
<div class="alert-modal" id="customAlertModal">
    <div class="alert-header" id="customAlertHeader">
        <i class="bi alert-icon" id="customAlertIcon"></i>
        <h6 class="mb-0 fw-bold" id="customAlertTitle">Alert</h6>
    </div>
    <div class="alert-body" id="customAlertBody">
        Alert message here
    </div>
    <div class="alert-footer">
        <button class="btn btn-primary btn-action" onclick="closeCustomAlert()">
            <i class="bi bi-check-lg me-1"></i>OK
        </button>
    </div>
</div>

<!-- Custom Confirm Modal -->
<div class="modal-backdrop-custom" id="customConfirmBackdrop"></div>
<div class="alert-modal" id="customConfirmModal">
    <div class="alert-header warning" id="customConfirmHeader">
        <i class="bi bi-question-circle-fill alert-icon"></i>
        <h6 class="mb-0 fw-bold" id="customConfirmTitle">Confirm Action</h6>
    </div>
    <div class="alert-body" id="customConfirmBody">
        Are you sure?
    </div>
    <div class="alert-footer">
        <button class="btn btn-secondary btn-action" onclick="closeCustomConfirm(false)">
            <i class="bi bi-x-lg me-1"></i>Cancel
        </button>
        <button class="btn btn-primary btn-action" onclick="closeCustomConfirm(true)">
            <i class="bi bi-check-lg me-1"></i>Confirm
        </button>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal-backdrop-custom" id="requestModalBackdrop" onclick="closeRequestModal()"></div>
<div class="modal-custom" id="requestModal">
    <div class="modal-header-custom">
        <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Request Details</h5>
        <button class="modal-close" onclick="closeRequestModal()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="modal-body-custom" id="requestModalContent">
        <!-- Content will be populated by JavaScript -->
    </div>
    <div class="modal-footer-custom" id="requestModalFooter">
        <!-- Footer buttons will be populated by JavaScript -->
    </div>
</div>

<!-- JS: Search/Filter + AJAX Approve/Reject -->
<script>
// Custom Alert and Confirm Functions
let confirmCallback = null;

window.customAlert = function(message, type = 'info') {
    const modal = document.getElementById('customAlertModal');
    const backdrop = document.getElementById('customAlertBackdrop');
    const header = document.getElementById('customAlertHeader');
    const icon = document.getElementById('customAlertIcon');
    const title = document.getElementById('customAlertTitle');
    const body = document.getElementById('customAlertBody');
    
    const types = {
        success: { class: 'success', icon: 'bi-check-circle-fill', title: 'Success' },
        error: { class: 'error', icon: 'bi-x-circle-fill', title: 'Error' },
        warning: { class: 'warning', icon: 'bi-exclamation-triangle-fill', title: 'Warning' },
        info: { class: 'info', icon: 'bi-info-circle-fill', title: 'Information' }
    };
    
    const config = types[type] || types.info;
    
    header.className = 'alert-header ' + config.class;
    icon.className = 'bi alert-icon ' + config.icon;
    title.textContent = config.title;
    body.textContent = message;
    
    modal.classList.add('show');
    backdrop.classList.add('show');
};

window.closeCustomAlert = function() {
    document.getElementById('customAlertModal').classList.remove('show');
    document.getElementById('customAlertBackdrop').classList.remove('show');
};

window.customConfirm = function(message, callback) {
    const modal = document.getElementById('customConfirmModal');
    const backdrop = document.getElementById('customConfirmBackdrop');
    const body = document.getElementById('customConfirmBody');
    
    body.textContent = message;
    confirmCallback = callback;
    
    modal.classList.add('show');
    backdrop.classList.add('show');
};

window.closeCustomConfirm = function(confirmed) {
    document.getElementById('customConfirmModal').classList.remove('show');
    document.getElementById('customConfirmBackdrop').classList.remove('show');
    
    if (confirmCallback) {
        confirmCallback(confirmed);
        confirmCallback = null;
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.querySelector('#requestsTable tbody');

    // -----------------------------
    // Search & Filter
    // -----------------------------
    function filterTable() {
        const searchValue = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;

        tableBody.querySelectorAll('tr').forEach(row => {
            const userId = row.children[1].textContent.toLowerCase();
            const doc = row.children[2].textContent.toLowerCase();
            const status = row.children[4].textContent.toLowerCase();

            const matchesSearch = userId.includes(searchValue) || doc.includes(searchValue);
            const matchesStatus = (statusValue === 'all' || status === statusValue);

            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filterTable);
    statusFilter.addEventListener('change', filterTable);

    // -----------------------------
    // AJAX Approve/Reject (Event Delegation)
    // -----------------------------
    let pendingActionData = null;
    
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.action-btn');
        if (!btn) return; // click is not on a button

        const row = btn.closest('tr');
        const requestId = row.dataset.id;
        const newStatus = btn.dataset.status;
        const actionWord = newStatus === 'approved' ? 'approve' : 'reject';

        const confirmMessage = `Are you sure you want to ${actionWord} this request?`;
        
        customConfirm(confirmMessage, function(confirmed) {
            if (!confirmed) return;
            
            // Store action data and show remarks modal
            pendingActionData = { row, requestId, newStatus, actionWord };
            showRemarksModal(`${actionWord.charAt(0).toUpperCase() + actionWord.slice(1)} Request`);
        });
    });
    
    window.submitRemarks = function() {
        const remarks = document.getElementById('remarksInput').value.trim();
        
        if (pendingActionData) {
            // Handle approve/reject action
            const { row, requestId, newStatus } = pendingActionData;
            
            // Disable buttons while processing
            row.querySelectorAll('.action-btn').forEach(b => b.disabled = true);
            closeRemarksModal();

            fetch("<?= rtrim(base_url(), '/') ?>/admin/request/update/" + requestId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: `status=${encodeURIComponent(newStatus)}&remarks=${encodeURIComponent(remarks)}`
            })
            .then(res => {
                // If non-2xx, try to read text for debugging
                const contentType = (res.headers.get('content-type') || '').toLowerCase();
                if (!res.ok) {
                    return res.text().then(text => {
                        throw new Error('HTTP ' + res.status + ': ' + (text || res.statusText));
                    });
                }

                // If response is JSON, parse it. Otherwise dump text for debugging.
                if (contentType.includes('application/json')) {
                    return res.json();
                }

                return res.text().then(text => {
                    throw new Error('Expected JSON response but received HTML/text:\n' + text);
                });
            })
            .then(data => {
                if (data && data.success) {
                    const updatedStatus = data.status || newStatus;
                    const properLabel = updatedStatus.charAt(0).toUpperCase() + updatedStatus.slice(1);

                    row.dataset.status = updatedStatus;

                    const badge = row.querySelector('.badge-status');
                    badge.textContent = properLabel;
                    badge.className = 'badge badge-status bg-' +
                        (updatedStatus === 'approved' ? 'success' : 'danger');

                    // Remove action buttons once status is no longer pending
                    row.querySelectorAll('.action-btn').forEach(b => b.remove());

                    customAlert(data.message || ("Request updated to " + properLabel + "."), 'success');
                    pendingActionData = null;
                    return;
                }

                customAlert((data && data.error) || 'Failed to update.', 'error');
                row.querySelectorAll('.action-btn').forEach(b => b.disabled = false);
                pendingActionData = null;
            })
            .catch(err => {
                console.error('Approve/Reject error:', err);
                customAlert("Fetch error: " + err.message, 'error');
                row.querySelectorAll('.action-btn').forEach(b => b.disabled = false);
                pendingActionData = null;
            });
        } else if (pendingReleaseId) {
            // Handle mark as released action
            closeRemarksModal();
            
            fetch('<?= BASE_URL ?>/admin/request/release/' + pendingReleaseId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'remarks=' + encodeURIComponent(remarks)
            })
            .then(res => res.text())
            .then(data => {
                customAlert('Request marked as released!', 'success');
                setTimeout(() => {
                    closeRequestModal();
                    location.reload();
                }, 1500);
                pendingReleaseId = null;
            })
            .catch(err => {
                console.error('Release error:', err);
                customAlert('Error marking as released. Please try again.', 'error');
                pendingReleaseId = null;
            });
        }
    };
    
    window.showRemarksModal = function(title) {
        document.getElementById('remarksModalTitle').innerHTML = '<i class="bi bi-chat-left-text me-2"></i>' + title;
        document.getElementById('remarksInput').value = '';
        document.getElementById('remarksModal').classList.add('show');
        document.getElementById('remarksModalBackdrop').classList.add('show');
        document.getElementById('remarksInput').focus();
    };
    
    window.closeRemarksModal = function() {
        document.getElementById('remarksModal').classList.remove('show');
        document.getElementById('remarksModalBackdrop').classList.remove('show');
        pendingActionData = null;
        pendingReleaseId = null;
    };
    
    // Modal Functions
    window.viewRequestModal = function(request) {
        const modal = document.getElementById('requestModal');
        const backdrop = document.getElementById('requestModalBackdrop');
        const content = document.getElementById('requestModalContent');
        const footer = document.getElementById('requestModalFooter');
        
        const statusClass = request.status === 'approved' ? 'success' : 
                          request.status === 'pending' ? 'warning' : 
                          request.status === 'rejected' ? 'danger' : 
                          request.status === 'released' ? 'info' : 'secondary';
        
        content.innerHTML = `
            <div class="mb-3">
                <span class="badge bg-${statusClass} fs-6">${request.status ? request.status.toUpperCase() : 'N/A'}</span>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-hash me-2"></i>Request ID</div>
                <div class="detail-value">#${request.id || 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-person me-2"></i>User ID</div>
                <div class="detail-value">${request.user_id || 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-file-earmark me-2"></i>Document Type</div>
                <div class="detail-value">${request.document_type || 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-chat-left-text me-2"></i>Purpose</div>
                <div class="detail-value">${request.purpose || request.details || 'N/A'}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-calendar me-2"></i>Date Submitted</div>
                <div class="detail-value">${request.created_at || 'N/A'}</div>
            </div>
            ${request.remarks ? `
            <div class="detail-row">
                <div class="detail-label"><i class="bi bi-sticky me-2"></i>Remarks</div>
                <div class="detail-value">${request.remarks}</div>
            </div>
            ` : ''}
        `;
        
        // Dynamic footer based on status
        let footerHtml = '<button class="btn btn-secondary btn-action" onclick="closeRequestModal()">Close</button>';
        
        if (request.status === 'approved') {
            footerHtml = `
                <button class="btn btn-secondary btn-action" onclick="closeRequestModal()">Close</button>
                <button class="btn btn-success btn-action" onclick="markAsReleased(${request.id})">
                    <i class="bi bi-check-circle me-1"></i>Mark as Released
                </button>
            `;
        }
        
        footer.innerHTML = footerHtml;
        
        modal.classList.add('show');
        backdrop.classList.add('show');
    };
    
    window.closeRequestModal = function() {
        document.getElementById('requestModal').classList.remove('show');
        document.getElementById('requestModalBackdrop').classList.remove('show');
    };
    
    let pendingReleaseId = null;
    
    window.markAsReleased = function(requestId) {
        customConfirm('Mark this request as released? The user will receive an email with the PDF document.', function(confirmed) {
            if (!confirmed) return;
            
            // Proceed with release
            fetch('<?= BASE_URL ?>/admin/request/release/' + requestId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    customAlert(data.message || 'Request marked as released! Email with PDF sent to user.', 'success');
                    
                    // Update the table row if visible
                    const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
                    if (row) {
                        row.dataset.status = 'released';
                        const badge = row.querySelector('.badge-status');
                        if (badge) {
                            badge.textContent = 'RELEASED';
                            badge.className = 'badge badge-status bg-info';
                        }
                        // Remove action buttons
                        row.querySelectorAll('.action-btn').forEach(b => b.remove());
                    }
                    
                    setTimeout(() => {
                        closeRequestModal();
                        location.reload();
                    }, 1500);
                } else {
                    customAlert(data.error || 'Failed to mark as released', 'error');
                }
            })
            .catch(err => {
                console.error('Release error:', err);
                customAlert('Error marking as released. Please try again.', 'error');
            });
        });
    };
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRequestModal();
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
