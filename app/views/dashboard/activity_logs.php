<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$logs = $data['logs'] ?? [];
$timelineLogs = $data['timeline'] ?? $logs;
$filters = array_merge([
    'search' => '',
    'action' => 'all',
], $data['filters'] ?? []);
$actionOptions = $data['actionOptions'] ?? [];

function action_badge_class($action) {
    $a = strtolower($action);
    if (strpos($a, 'approved') !== false) return 'badge bg-success text-white';
    if (strpos($a, 'rejected') !== false) return 'badge bg-danger text-white';
    if (strpos($a, 'released') !== false) return 'badge bg-primary text-white';
    if (strpos($a, 'login') !== false || strpos($a, 'created') !== false) return 'badge bg-info text-white';
    return 'badge bg-secondary text-white';
}

function action_icon_html($action) {
    $a = strtolower($action);
    if (strpos($a, 'approved') !== false) return '<i class="bi bi-check-circle-fill text-success"></i>';
    if (strpos($a, 'rejected') !== false) return '<i class="bi bi-x-circle-fill text-danger"></i>';
    if (strpos($a, 'released') !== false) return '<i class="bi bi-file-earmark-check-fill text-primary"></i>';
    if (strpos($a, 'login') !== false) return '<i class="bi bi-box-arrow-in-right text-info"></i>';
    return '<i class="bi bi-file-text"></i>';
}

function format_timestamp_text($timestamp) {
    if (empty($timestamp)) {
        return '—';
    }
    $time = strtotime($timestamp);
    return $time ? date('M d, Y H:i', $time) : $timestamp;
}

// ensure BASE_URL exists
if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Activity Logs - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: #f8f9fa; font-family: Poppins, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
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
.main-content { margin-left:280px; padding:30px; }
.card { border-radius: 10px; }
.badge { font-weight:600; }
.timeline-item { display:flex; gap:12px; }
.timeline-icon { width:44px; height:44px; border-radius:999px; display:flex; align-items:center; justify-content:center; }
.timeline-line { width:2px; background:#e9ecef; margin:4px 0 0 21px; }
@media (max-width: 768px) { .sidebar { position:relative; width:100%; } .main-content { margin-left:0; } }
</style>
</head>
<body>

<!-- Sidebar (reuse look from other admin pages) -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="bi bi-shield-fill-check"></i> Admin Portal</h4>
    </div>
    <div class="sidebar-nav">
        <a href="<?= BASE_URL ?>/dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/requests"><i class="bi bi-file-earmark-text-fill"></i> Request Management</a>
        <a href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people-fill"></i> User Management</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/admin/analytics"><i class="bi bi-bar-chart-line-fill"></i> Analytics & Reports</a>
        <a href="<?= BASE_URL ?>/admin/activity-logs" class="active"><i class="bi bi-clock-history"></i> Activity Logs</a>
        <a href="<?= BASE_URL ?>/admin/settings"><i class="bi bi-gear-fill"></i> Settings</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/logout" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Activity Logs</h3>
            <small class="text-muted">Track all admin actions and system activities</small>
        </div>
        <div class="text-end">
            <div class="small text-muted">Total records: <strong><?= count($logs) ?></strong></div>
            <div class="small text-muted">Timeline preview: <strong><?= count($timelineLogs) ?></strong></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form class="row g-2" method="get" action="">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" class="form-control" placeholder="Search by action, admin, details or request ID...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="action" class="form-select">
                        <option value="all" <?= $filters['action'] === 'all' ? 'selected' : '' ?>>All Actions</option>
                        <?php foreach ($actionOptions as $actionName): ?>
                            <option value="<?= htmlspecialchars($actionName) ?>" <?= strtolower($filters['action']) === strtolower($actionName) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($actionName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover align-middle">
                    <thead class="table-light" style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                        <tr>
                            <th style="width:160px">Timestamp</th>
                            <th style="width:220px">Action</th>
                            <th>Admin</th>
                            <th>Details</th>
                            <th style="width:110px">Request ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No activity logs found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-nowrap"><?= htmlspecialchars(format_timestamp_text($log['timestamp'] ?? null)) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="me-1"><?= action_icon_html($log['action']) ?></div>
                                            <span class="<?= action_badge_class($log['action']) ?> px-2"><?= htmlspecialchars($log['action']) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($log['admin_name'] ?? 'System') ?></td>
                                    <td style="max-width:400px;">
                                        <div class="text-truncate" style="max-width:400px;"><?= htmlspecialchars($log['details'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($log['request_id'])): ?>
                                            <code class="small bg-light px-2 py-1 rounded"><?= htmlspecialchars($log['request_id']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (count($logs) >= 10): ?>
                <div class="text-center mt-3">
                    <small class="text-muted"><i class="bi bi-info-circle"></i> Showing latest 10 activity logs. Use filters to search older records or scroll to view all.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Timeline -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">Activity Timeline</h6>
            <?php if (empty($timelineLogs)): ?>
                <div class="text-muted">No timeline entries.</div>
            <?php else: ?>
                <div style="max-height: 600px; overflow-y: auto;">
                    <div class="d-flex flex-column">
                        <?php foreach ($timelineLogs as $index => $log): ?>
                            <div class="timeline-item mb-4">
                                <div class="timeline-icon" style="background: #f1f5f9;">
                                    <?= action_icon_html($log['action']) ?>
                                </div>
                                <div style="flex:1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?= htmlspecialchars($log['action']) ?></strong>
                                            <div class="small text-muted">by <?= htmlspecialchars($log['admin_name'] ?? 'System') ?></div>
                                        </div>
                                        <div class="small text-muted text-nowrap"><?= htmlspecialchars(format_timestamp_text($log['timestamp'] ?? null)) ?></div>
                                    </div>
                                    <div class="mt-2">
                                        <div class="text-muted small"><?= htmlspecialchars($log['details'] ?? '') ?></div>
                                        <?php if (!empty($log['request_id'])): ?>
                                            <div class="mt-2"><code class="small bg-light px-2 py-1 rounded"><?= htmlspecialchars($log['request_id']) ?></code></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($index < count($timelineLogs) - 1): ?>
                                <div class="timeline-line"></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if (count($timelineLogs) >= 10): ?>
                    <div class="text-center mt-3">
                        <small class="text-muted"><i class="bi bi-info-circle"></i> Showing latest 10 timeline entries. Scroll to view all.</small>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
