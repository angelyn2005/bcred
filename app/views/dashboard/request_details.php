<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000');
}

$status = strtolower($request['status'] ?? '');
$canRelease = $status === 'approved';
$releasedMessage = isset($_GET['released']);
$errorCode = $_GET['error'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: "Poppins", sans-serif; }
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
        .main-content { margin-left:260px; padding:40px; }
        .topbar { background:#fff; border-radius:10px; padding:15px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        .detail-label { font-weight:600; color:#6c757d; }
        .detail-value { font-weight:600; color:#212529; }
        .badge-status { font-size:0.85rem; }
        @media (max-width: 768px) { .sidebar { width:100%; position:relative; min-height:auto; } .main-content { margin-left:0; padding:20px; } }
    </style>
</head>
<body>

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

<div class="main-content">
    <div class="topbar mb-4">
        <div>
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-eye me-2"></i>Request Details</h5>
            <small class="text-muted">Review request information and actions</small>
        </div>
        <div class="text-end">
            <div class="detail-label mb-1">Status</div>
            <?php
                $badgeClass = $status === 'pending' ? 'warning' :
                              ($status === 'approved' ? 'success' :
                              ($status === 'released' ? 'primary' : 'secondary'));
            ?>
            <span class="badge badge-status bg-<?= $badgeClass ?>"><?= ucfirst($status) ?></span>
        </div>
    </div>

    <?php if ($releasedMessage): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> Request successfully marked as released.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($errorCode === 'not_approved'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> Only approved requests can be marked as released.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($errorCode === 'release_failed'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-x-circle me-1"></i> Failed to mark the request as released. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="detail-label">Request ID</div>
                    <div class="detail-value">#<?= $request['id'] ?></div>
                </div>
                <div class="col-md-6">
                    <div class="detail-label">Document Type</div>
                    <div class="detail-value"><?= htmlspecialchars($request['document_type'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="detail-label">Applicant Name</div>
                    <div class="detail-value"><?= htmlspecialchars($request['fullname'] ?? $request['full_name'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?= htmlspecialchars($request['email'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="detail-label">Purpose</div>
                    <div class="detail-value"><?= htmlspecialchars($request['purpose'] ?? $request['details'] ?? '') ?></div>
                </div>
                <div class="col-md-6">
                    <div class="detail-label">Date Submitted</div>
                    <div class="detail-value"><?= !empty($request['created_at']) ? date('M d, Y H:i', strtotime($request['created_at'])) : '' ?></div>
                </div>
                <div class="col-md-6">
                    <div class="detail-label">Last Update</div>
                    <div class="detail-value"><?= $request['updated_at'] ? date('M d, Y H:i', strtotime($request['updated_at'])) : '—' ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="bi bi-paperclip me-2"></i>Attachments</h5>
                    <?php if (!empty($attachments)): ?>
                <ul class="list-group">
                    <?php foreach ($attachments as $a): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><?= htmlspecialchars($a['filename'] ?? '') ?></span>
                            <a href="<?= BASE_URL ?>/uploads/<?= htmlspecialchars($a['filename'] ?? '') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted mb-0">No attachments uploaded.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="bi bi-card-text me-2"></i>Submitted Fields</h5>
            <?php
                // Keys that are internal/system and shouldn't be shown in the submitted-fields list
                $skip = [
                    'id','user_id','status','admin_note','created_at','updated_at','document_type','fullname','full_name','email'
                ];

                // If there's a meta JSON column, try to parse it and merge its data into the display map
                $meta = [];
                if (!empty($request['meta'])) {
                    if (is_string($request['meta'])) {
                        $decoded = json_decode($request['meta'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $meta = $decoded;
                        }
                    } elseif (is_array($request['meta'])) {
                        $meta = $request['meta'];
                    }
                }

                // Merge request fields and meta (meta has lower priority)
                $all = array_merge($request, $meta);

                // Helper to prettify keys
                $label = function($k) {
                    return ucwords(str_replace(['_','-'], [' ', ' '], $k));
                };

                // Collect displayable fields
                $displayed = 0;
                foreach ($all as $k => $v) {
                    if (in_array($k, $skip, true)) continue;
                    if ($k === 'attachments' || $k === 'files') continue;
                    if ($v === null || $v === '') continue;

                    // Skip if key looks like internal timestamp already handled
                    if (preg_match('/^(created|updated|deleted)_at$/i', $k)) continue;

                    // Prepare value for display
                    $out = '';
                    if (is_array($v)) {
                        $out = htmlspecialchars(json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    } else {
                        // Try to format dates
                        if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T].*)?$/', $v)) {
                            $out = date('M d, Y', strtotime($v));
                        } else {
                            $out = htmlspecialchars((string)$v);
                        }
                    }

                    $displayed++;
                    echo '<div class="mb-3">';
                    echo '<div class="detail-label">' . $label($k) . '</div>';
                    echo '<div class="detail-value">' . $out . '</div>';
                    echo '</div>';
                }

                if ($displayed === 0) {
                    echo '<p class="text-muted mb-0">No additional fields were submitted.</p>';
                }
            ?>
        </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/requests" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Requests
        </a>
        <?php if ($canRelease): ?>
            <button type="button" class="btn btn-primary" id="releaseBtn">
                <i class="bi bi-box-arrow-up me-2"></i>Mark as Released
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if ($canRelease): ?>
<form id="releaseForm" action="<?= BASE_URL ?>/admin/request/release/<?= $request['id'] ?>" method="POST" class="d-none">
    <input type="hidden" name="remarks" id="releaseRemarks">
</form>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($canRelease): ?>
<script>
document.getElementById('releaseBtn').addEventListener('click', function() {
    const remarks = prompt('Enter remarks for release (optional):', '') || '';
    if (!confirm('Mark this request as released?')) {
        return;
    }
    document.getElementById('releaseRemarks').value = remarks.trim();
    document.getElementById('releaseForm').submit();
});
</script>
<?php endif; ?>
</body>
</html>

