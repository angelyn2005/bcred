<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

// These values MUST come from your controller
$total_requests    = $data['total_requests']    ?? 0;
$approved_requests = $data['approved_requests'] ?? 0;
$pending_requests  = $data['pending_requests']  ?? 0;

// For charts
$monthly_labels = $data['monthly_labels'] ?? [];
$monthly_values = $data['monthly_values'] ?? [];

$doc_labels = $data['doc_labels'] ?? [
    'Barangay Clearance',
    'Indigency Certificate',
    'Residency Certificate',
    'Business Permit',
    'Barangay ID',
];
$doc_values = $data['doc_values'] ?? array_fill(0, count($doc_labels), 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics & Reports - Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

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

        .main-content {
            margin-left: 280px;
            padding: 40px;
        }

        .topbar {
            background: white;
            border-radius: 10px;
            padding: 20px 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex; justify-content: space-between; align-items: center;
        }

        .stat-card {
            border-radius: 15px;
            border: none;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .stat-card .card-body {
            padding: 1.5rem;
            position: relative;
        }

        .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5rem;
            opacity: 0.15;
        }

        .chart-card {
            border-radius: 15px;
            border: none;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .chart-card .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #0d6efd;
            padding: 1rem 1.5rem;
            font-weight: 600;
            color: #212529;
        }

        canvas { max-height: 320px; }
    </style>
</head>
<body>

<!-- ================= SIDEBAR ================= -->
<div class="sidebar">
    <div class="sidebar-header">
        <h4><i class="bi bi-shield-fill-check"></i> Admin Portal</h4>
    </div>
    <div class="sidebar-nav">
        <a href="<?= BASE_URL ?>/dashboard"><i class="bi bi-grid-fill"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/requests"><i class="bi bi-file-earmark-text-fill"></i> Request Management</a>
        <a href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people-fill"></i> User Management</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/admin/analytics" class="active"><i class="bi bi-bar-chart-line-fill"></i> Analytics & Reports</a>
        <a href="<?= BASE_URL ?>/admin/activity-logs"><i class="bi bi-clock-history"></i> Activity Logs</a>
        <a href="<?= BASE_URL ?>/admin/settings"><i class="bi bi-gear-fill"></i> Settings</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/logout" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<!-- ================= MAIN CONTENT ================= -->
<div class="main-content">

    <div class="topbar">
        <h5 class="fw-bold text-primary"><i class="bi bi-bar-chart-line me-2"></i>Analytics & Reports</h5>
        <div><i class="bi bi-person-circle me-1"></i> Admin</div>
    </div>

    <!-- Stats Overview -->
    <div class="row g-4">

        <div class="col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-file-earmark-text stat-icon text-primary"></i>
                    <h6 class="fw-semibold text-secondary mb-2">Total Requests</h6>
                    <h2 class="fw-bold text-primary mb-1"><?= number_format($total_requests) ?></h2>
                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>Overall submissions</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-check-circle stat-icon text-success"></i>
                    <h6 class="fw-semibold text-secondary mb-2">Approved Requests</h6>
                    <h2 class="fw-bold text-success mb-1"><?= number_format($approved_requests) ?></h2>
                    <p class="text-muted small mb-0"><i class="bi bi-check-circle me-1"></i>Marked as approved</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <i class="bi bi-clock-history stat-icon text-warning"></i>
                    <h6 class="fw-semibold text-secondary mb-2">Pending Requests</h6>
                    <h2 class="fw-bold text-warning mb-1"><?= number_format($pending_requests) ?></h2>
                    <p class="text-muted small mb-0"><i class="bi bi-hourglass-split me-1"></i>Awaiting action</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts -->
    <div class="row mt-4 g-4">

        <div class="col-lg-8">
            <div class="card chart-card">
                <div class="card-header">
                    <i class="bi bi-graph-up me-2"></i>Monthly Request Overview
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card chart-card">
                <div class="card-header">
                    <i class="bi bi-pie-chart me-2"></i>Document Type Distribution
                </div>
                <div class="card-body">
                    <canvas id="documentsChart"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
const docColors = ['#0d6efd','#20c997','#ffc107','#dc3545','#6f42c1'];

new Chart(document.getElementById("monthlyChart"), {
    type: 'line',
    data: {
        labels: <?= json_encode($monthly_labels) ?>,
        datasets: [{
            label: 'Requests',
            data: <?= json_encode($monthly_values) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13,110,253,0.15)',
            borderWidth: 3,
            tension: 0.35,
            fill: true
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

new Chart(document.getElementById("documentsChart"), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($doc_labels) ?>,
        datasets: [{
            data: <?= json_encode($doc_values) ?>,
            backgroundColor: docColors.slice(0, <?= count($doc_labels) ?>)
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
