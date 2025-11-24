<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

$fullName = $currentUser['fullname'] ?? ($currentUser['name'] ?? '');
$username = $currentUser['username'] ?? '';
$email = $currentUser['email'] ?? '';
$contact = $currentUser['contact'] ?? '';
$address = $currentUser['address'] ?? '';
$role = ucfirst($currentUser['role'] ?? 'resident');
$photoUrl = $currentUser['photo_url'] ?? '';
$initials = '';
$parts = array_filter(explode(' ', trim($fullName)));
foreach ($parts as $idx => $part) {
    if ($idx > 1) {
        break;
    }
    $initials .= strtoupper(substr($part, 0, 1));
}
if ($initials === '') {
    $initials = strtoupper(substr($fullName, 0, 1));
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h3 class="mb-3 text-primary"><i class="bi bi-person-circle me-2"></i>My Profile</h3>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- Profile Picture -->
        <div class="col-lg-4">
            <div class="card shadow-sm p-4">
                <h5 class="mb-3 fw-bold">Profile Picture</h5>
                <form id="photoForm" method="post" action="<?= site_url('user/profile/photo') ?>" enctype="multipart/form-data">
                    <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*">
                    <div class="text-center mb-3">
                        <div class="position-relative d-inline-block">
                            <img
                                src="<?= htmlspecialchars($photoUrl) ?>"
                                id="profilePhotoPreview"
                                class="rounded-circle <?= empty($photoUrl) ? 'd-none' : '' ?>"
                                width="150"
                                height="150"
                                style="object-fit:cover; border:4px solid #e9ecef;"
                                alt="Profile photo">
                            <div
                                id="profilePhotoPlaceholder"
                                class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content:center <?= empty($photoUrl) ? '' : 'd-none' ?>"
                                style="width:150px;height:150px;font-size:3rem;font-weight:700;margin:0 auto;justify-content:center;border:4px solid #e9ecef;">
                                <?= htmlspecialchars($initials ?: 'U') ?>
                            </div>
                            <button type="button" class="btn btn-primary rounded-circle position-absolute" style="bottom:10px;right:10px;width:40px;height:40px;padding:0;" title="Change Photo" id="changePhotoBtn">
                                <i class="bi bi-camera-fill"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-muted text-center mb-3" style="font-size:0.9rem;"><i class="bi bi-info-circle me-1"></i>Click camera icon to change profile picture</p>
                    <button type="submit" class="btn btn-success w-100 d-none" id="uploadPhotoBtn">
                        <i class="bi bi-cloud-upload me-1"></i> Upload Photo
                    </button>
                </form>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="col-lg-8">
            <div class="card shadow-sm p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-bold">Personal Information</h5>
                    <button class="btn btn-outline-primary btn-sm" id="editProfileBtn">
                        <i class="bi bi-pencil me-1"></i>Edit Profile
                    </button>
                </div>

                <form id="profileForm" method="post" action="<?= site_url('user/profile/update') ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" name="fullname" value="<?= htmlspecialchars($fullName) ?>" disabled required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Username</label>
                            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($username) ?>" disabled required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email) ?>" disabled required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Number</label>
                            <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($contact) ?>" disabled <?= strtolower($currentUser['role'] ?? '') === 'resident' ? 'required' : '' ?>>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($address) ?>" disabled <?= strtolower($currentUser['role'] ?? '') === 'resident' ? 'required' : '' ?>>
                        </div>
                    </div>

                    <div class="mt-3 d-none" id="profileActions">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Save Changes</button>
                        <button type="button" class="btn btn-outline-secondary" id="cancelEditBtn"><i class="bi bi-x-circle me-1"></i>Cancel</button>
                    </div>
                </form>

                <!-- Account Information -->
                <div class="mt-4 pt-4 border-top">
                    <h5 class="mb-3 fw-bold">Account Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">User ID</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($currentUser['id'] ?? 'N/A') ?>" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Member Since</label>
                            <input type="text" class="form-control" value="<?= !empty($currentUser['created_at']) ? date('d/m/Y', strtotime($currentUser['created_at'])) : date('d/m/Y') ?>" disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Enable editing when "Edit" is clicked
const editBtn = document.getElementById('editProfileBtn');
const cancelBtn = document.getElementById('cancelEditBtn');
const form = document.getElementById('profileForm');
const inputs = form.querySelectorAll('input');
const actions = document.getElementById('profileActions');
const photoForm = document.getElementById('photoForm');
const photoInput = document.getElementById('photoInput');
const changePhotoBtn = document.getElementById('changePhotoBtn');
const uploadPhotoBtn = document.getElementById('uploadPhotoBtn');
const photoPreview = document.getElementById('profilePhotoPreview');
const photoPlaceholder = document.getElementById('profilePhotoPlaceholder');

editBtn.addEventListener('click', () => {
    // Enable only form inputs with name attribute (editable fields)
    inputs.forEach(i => {
        if (i.hasAttribute('name')) {
            i.disabled = false;
        }
    });
    actions.classList.remove('d-none');
    editBtn.classList.add('d-none');
});

cancelBtn.addEventListener('click', () => {
    inputs.forEach(i => i.disabled = true);
    actions.classList.add('d-none');
    editBtn.classList.remove('d-none');

    // Reset values
    inputs.forEach(i => i.value = i.defaultValue);
});

form.addEventListener('submit', (e) => {
    // Don't hide edit button immediately - let the page refresh with flash message
});

changePhotoBtn.addEventListener('click', () => {
    photoInput.click();
});

photoInput.addEventListener('change', () => {
    if (!photoInput.files || !photoInput.files[0]) {
        uploadPhotoBtn.classList.add('d-none');
        return;
    }

    const file = photoInput.files[0];
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file.');
        photoInput.value = '';
        uploadPhotoBtn.classList.add('d-none');
        return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
        if (photoPreview) {
            photoPreview.src = event.target.result;
            photoPreview.classList.remove('d-none');
        }
        if (photoPlaceholder) {
            photoPlaceholder.classList.add('d-none');
        }
    };
    reader.readAsDataURL(file);

    uploadPhotoBtn.classList.remove('d-none');
});

photoForm.addEventListener('submit', () => {
    uploadPhotoBtn.disabled = true;
    uploadPhotoBtn.innerText = 'Uploading...';
});
</script>
