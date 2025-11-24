<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost:8000');
}

$barangay = $barangay ?? [
    'name' => 'Barangay Name',
    'email' => 'barangay@email.com',
    'address' => 'Barangay Hall, City',
    'contact' => '+63 900 000 0000',
];

$documents = $documents ?? [];
$notifications = $notifications ?? ['email' => false, 'sms' => false];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background:#f8f9fa; font-family:"Poppins",sans-serif; }
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
        .topbar { background:#fff; border-radius:10px; padding:15px 25px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        .card-section { border:0; border-radius:14px; box-shadow:0 2px 10px rgba(13,110,253,0.08); }
        .form-switch .form-check-input { width:3rem; height:1.5rem; }
        @media(max-width:768px){ .sidebar{width:100%; position:relative; min-height:auto;} .main-content{margin-left:0; padding:20px;} }
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
        <a href="<?= BASE_URL ?>/admin/users"><i class="bi bi-people-fill"></i> User Management</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/admin/analytics"><i class="bi bi-bar-chart-line-fill"></i> Analytics & Reports</a>
        <a href="<?= BASE_URL ?>/admin/activity-logs"><i class="bi bi-clock-history"></i> Activity Logs</a>
        <a href="<?= BASE_URL ?>/admin/settings" class="active"><i class="bi bi-gear-fill"></i> Settings</a>
        <div class="sidebar-divider"></div>
        <a href="<?= BASE_URL ?>/logout" class="logout-btn"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar mb-4">
        <div>
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-gear me-2"></i>Settings</h5>
            <small class="text-muted">Manage barangay information and digital signature</small>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card card-section">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-building me-2 text-primary"></i>Barangay Information</h5>
                    <form id="barangayForm">
                        <div class="mb-3">
                            <label class="form-label">Barangay Name</label>
                            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($barangay['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($barangay['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($barangay['address']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($barangay['contact']) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save Barangay Info</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-section">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-pen me-2 text-primary"></i>E-Signature Upload</h5>
                    <p class="text-muted small mb-3">Upload your digital signature (PNG with transparent background recommended). This will be automatically inserted into all generated documents.</p>
                    
                    <div id="currentSignature" class="mb-3" style="display: none;">
                        <label class="form-label fw-semibold">Current Signature:</label>
                        <div class="border rounded p-3 bg-light text-center">
                            <img id="signaturePreview" src="" alt="Current Signature" style="max-width: 200px; max-height: 100px;">
                        </div>
                    </div>

                    <form id="signatureForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Upload New Signature</label>
                            <input type="file" class="form-control" id="signatureFile" name="signature" accept="image/png,image/jpeg,image/jpg" required>
                            <div class="form-text">Accepted formats: PNG, JPG (PNG with transparent background recommended)</div>
                        </div>
                        
                        <div id="signatureNewPreview" class="mb-3" style="display: none;">
                            <label class="form-label fw-semibold">Preview:</label>
                            <div class="border rounded p-3 bg-light text-center">
                                <img id="newSignatureImg" src="" alt="Signature Preview" style="max-width: 200px; max-height: 100px;">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cloud-upload me-2"></i>Upload Signature
                        </button>
                    </form>
                    
                    <div id="signatureMessage" class="mt-3"></div>
                </div>
            </div>
        </div>


    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Signature upload functionality
(function() {
    const signatureForm = document.getElementById('signatureForm');
    const signatureFile = document.getElementById('signatureFile');
    const signatureMessage = document.getElementById('signatureMessage');
    const signatureNewPreview = document.getElementById('signatureNewPreview');
    const newSignatureImg = document.getElementById('newSignatureImg');
    const currentSignature = document.getElementById('currentSignature');
    const signaturePreview = document.getElementById('signaturePreview');

    // Load current signature if exists
    fetch('/admin/signature/current')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.path) {
                currentSignature.style.display = 'block';
                signaturePreview.src = data.path;
            }
        })
        .catch(e => console.log('No current signature'));

    // Preview new signature before upload
    signatureFile.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                newSignatureImg.src = event.target.result;
                signatureNewPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle signature upload
    signatureForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const file = signatureFile.files[0];
        if (!file) {
            signatureMessage.innerHTML = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Please select a file first.</div>';
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            signatureMessage.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>File size exceeds 5MB limit.</div>';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            signatureMessage.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Only PNG/JPG images are allowed. Your file type: ' + file.type + '</div>';
            return;
        }
        
        const formData = new FormData(signatureForm);
        signatureMessage.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Uploading signature...</div>';

        fetch('/admin/signature/upload', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                signatureMessage.innerHTML = '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data.message + '</div>';
                currentSignature.style.display = 'block';
                signaturePreview.src = data.path + '?t=' + Date.now(); // Cache bust
                signatureNewPreview.style.display = 'none';
                signatureForm.reset();
                
                setTimeout(() => {
                    signatureMessage.innerHTML = '';
                }, 5000);
            } else {
                signatureMessage.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>' + (data.message || 'Upload failed') + '</div>';
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            signatureMessage.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Upload failed. Please check console for details.</div>';
        });
    });
})();

// Barangay Information Form
const barangayForm = document.getElementById('barangayForm');
if (barangayForm) {
    barangayForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(barangayForm);
        const data = Object.fromEntries(formData);
        const submitBtn = barangayForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        
        fetch('<?= BASE_URL ?>/admin/settings/save-barangay', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                alertDiv.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>' + (result.message || 'Barangay information saved successfully!') + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                barangayForm.insertAdjacentElement('afterend', alertDiv);
                
                setTimeout(() => {
                    alertDiv.remove();
                }, 3000);
            } else {
                alert('Error: ' + (result.message || 'Failed to save barangay information'));
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            alert('Error saving barangay information. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
}
</script>
</body>
</html>

