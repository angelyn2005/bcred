<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class RequestController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->call->model('RequestModel');
        $this->call->model('AttachmentModel');
        $this->call->model('UserModel');
        $this->call->model('NotificationModel');
        $this->call->model('ActivityLogModel');
        $this->call->helper('mail');
    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/user_dashboard?page=new_request');
            return;
        }

        if (empty($_SESSION['user_id'])) {
            redirect('/login');
            return;
        }

        $userId = $_SESSION['user_id'];
        $documentType = trim($_POST['doc_type'] ?? '');
        $details = trim($_POST['details'] ?? '');

        // Collect any dynamic fields submitted and append them to details
        $extra = $_POST;
        unset($extra['doc_type']);
        unset($extra['details']);
        // Remove file inputs (they come via $_FILES)
        if (isset($extra['_token'])) unset($extra['_token']);

        $extraText = '';
        foreach ($extra as $k => $v) {
            if (is_array($v)) continue; // skip arrays (files handled separately)
            $val = trim($v);
            if ($val === '') continue;
            $extraText .= ucfirst(str_replace('_', ' ', $k)) . ': ' . $val . "\n";
        }

        if ($documentType === '' || ($details === '' && $extraText === '')) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Please complete all required fields before submitting your request.',
            ];
            redirect('/user_dashboard?page=new_request');
            return;
        }

        if ($details === '' && $extraText !== '') {
            // keep details empty if we will store structured fields
            $details = null;
        } elseif ($details !== '' && $extraText !== '') {
            $details = $details . "\n\nAdditional details:\n" . $extraText;
        }

        // Map known form fields to specific columns so data is not all saved into `details`.
        $requestData = [
            'user_id' => $userId,
            'document_type' => $documentType,
            'details' => $details,
            'purpose' => trim($_POST['purpose'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'date_of_birth' => !empty($_POST['dob']) ? trim($_POST['dob']) : (trim($_POST['date_of_birth'] ?? '') ?: null),
            'gender' => trim($_POST['gender'] ?? ''),
            'civil_status' => trim($_POST['civil_status'] ?? ''),
            'nationality' => trim($_POST['nationality'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'contact_number' => trim($_POST['contact_number'] ?? ''),
            'date_requested' => !empty($_POST['date_requested']) ? trim($_POST['date_requested']) : null,
            'occupation' => trim($_POST['occupation'] ?? ''),
            'residency_period' => trim($_POST['residency_period'] ?? ''),
            // Business fields
            'business_name' => trim($_POST['business_name'] ?? ''),
            'business_owner_name' => trim($_POST['owner_name'] ?? ''),
            'business_owner_dob' => !empty($_POST['owner_dob']) ? trim($_POST['owner_dob']) : null,
            'business_owner_gender' => trim($_POST['owner_gender'] ?? ''),
            'business_owner_civil_status' => trim($_POST['owner_civil_status'] ?? ''),
            'business_owner_nationality' => trim($_POST['owner_nationality'] ?? ''),
            'business_owner_address' => trim($_POST['owner_address'] ?? ''),
            'business_address' => trim($_POST['business_address'] ?? ''),
            'business_nature' => trim($_POST['business_nature'] ?? ''),
            'business_start_date' => !empty($_POST['business_start_date']) ? trim($_POST['business_start_date']) : null,
            'permit_type' => trim($_POST['permit_type'] ?? ''),
        ];

        // Ensure Business Permit stores both fields: if document is Business Permit and permit_type provided,
        // set purpose to permit_type so both columns contain the permit type (e.g., "New", "Renewal").
        if (strtolower($documentType) === strtolower('Business Permit') && !empty($requestData['permit_type'])) {
            $requestData['purpose'] = $requestData['permit_type'];
        } else {
            // Fallback: if purpose is empty but permit_type exists for non-business types, use it
            if (empty($requestData['purpose']) && !empty($requestData['permit_type'])) {
                $requestData['purpose'] = $requestData['permit_type'];
            }
        }

        // Remove empty values to avoid DB errors if columns don't exist in older schemas
        $requestData = array_filter($requestData, function($v){ return $v !== null && $v !== ''; });

        $requestId = $this->RequestModel->addRequest($requestData);

        if (!$requestId) {
            $_SESSION['flash'] = [
                'type' => 'danger',
                'message' => 'Error submitting your request. Please try again.',
            ];
            redirect('/user_dashboard?page=new_request');
            return;
        }

        $this->saveAttachments($requestId, $userId);

        // Get user info first
        $user = $this->UserModel->getUserById($userId);
        $recipientName = $user['fullname'] ?? ($_SESSION['user'] ?? 'Resident');

        // Record activity: submitted request
        try {
            $this->ActivityLogModel->record([
                'action' => 'Submitted Request',
                'details' => sprintf('%s (user_id=%s) submitted %s', $recipientName, $userId, $documentType),
                'request_id' => $requestId,
                'admin_id' => null,
            ]);
        } catch (Throwable $e) {
            error_log('Failed to record activity for submitted request: ' . $e->getMessage());
        }
        $recipientEmail = trim($user['email'] ?? '');
        if ($recipientEmail === '' && !empty($_SESSION['email'] ?? '')) {
            $recipientEmail = trim($_SESSION['email']);
        }
        if ($recipientEmail !== '') {
            $_SESSION['email'] = $recipientEmail;
        }

        $emailStatus = null;
        if ($recipientEmail !== '') {
            $subject = 'Request Submitted - ' . $documentType;
            $message = sprintf(
                "Hi %s,\n\nYour request for %s has been received on %s.\n\nPurpose: %s\nReference ID: %s\nStatus: Pending review by the barangay staff.\n\nWe will notify you once the status changes.\n\nThank you,\nBarangay E-Credentials",
                $recipientName,
                $documentType,
                date('F d, Y h:i A'),
                $details,
                $requestId
            );

            $emailStatus = mail_helper(
                $recipientName,
                $recipientEmail,
                $subject,
                $message
            );
        }

        $this->NotificationModel->addNotification([
            'user_id' => $userId,
            'request_id' => $requestId,
            'title' => 'Request submitted',
            'message' => sprintf(
                'We received your %s request (Reference #%d). We will let you know once the status changes.',
                $documentType,
                $requestId
            ),
            'channel' => 'in-app',
        ]);

        $flashMessage = 'Request submitted successfully. Please check your email for the confirmation message.';
        if ($emailStatus !== null && $emailStatus !== true) {
            $flashMessage .= ' However, we could not send the confirmation email right now.';
        } elseif ($recipientEmail === '') {
            $flashMessage .= ' Update your profile with a valid email to receive notifications.';
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => $flashMessage,
        ];

        redirect('/user_dashboard?page=my_requests');
    }

    private function saveAttachments($requestId, $userId)
    {
        if (empty($_FILES['attachments']['name'][0])) {
            return [];
        }

        $uploadRoot = ROOT_DIR . 'public/uploads/';
        if (!is_dir($uploadRoot)) {
            mkdir($uploadRoot, 0777, true);
        }

        $saved = [];
        foreach ($_FILES['attachments']['tmp_name'] as $key => $tmpName) {
            if (($tmpName ?? '') === '' || ($_FILES['attachments']['error'][$key] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }

            $originalName = basename($_FILES['attachments']['name'][$key]);
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $uniqueName = uniqid('req' . $requestId . '_', true) . '_' . $safeName;
            $absolutePath = $uploadRoot . $uniqueName;

            if (move_uploaded_file($tmpName, $absolutePath)) {
                $relativePath = 'public/uploads/' . $uniqueName;
                
                // Get MIME type and ensure it fits in the database column (VARCHAR 50)
                $mimeType = $_FILES['attachments']['type'][$key] ?? mime_content_type($absolutePath) ?? 'application/octet-stream';
                
                // Truncate long MIME types or use simplified version
                if (strlen($mimeType) > 50) {
                    // Get file extension and use simplified MIME type
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $mimeMap = [
                        'pdf' => 'application/pdf',
                        'doc' => 'application/msword',
                        'docx' => 'application/vnd.ms-word',
                        'xls' => 'application/vnd.ms-excel',
                        'xlsx' => 'application/vnd.ms-excel',
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'txt' => 'text/plain',
                        'zip' => 'application/zip',
                        'rar' => 'application/x-rar',
                    ];
                    $mimeType = $mimeMap[$ext] ?? substr($mimeType, 0, 50);
                }
                
                $this->AttachmentModel->addAttachment([
                    'request_id' => $requestId,
                    'filename' => $originalName,
                    'filepath' => $relativePath,
                    'filetype' => $mimeType,
                    'uploaded_by' => $userId,
                ]);
                $saved[] = $relativePath;

                // Record activity: attachment uploaded
                try {
                    if (isset($this->ActivityLogModel)) {
                        $this->ActivityLogModel->record([
                            'action' => 'Uploaded Attachment',
                            'details' => sprintf('Uploaded %s for request #%s', $originalName, $requestId),
                            'request_id' => $requestId,
                            'admin_id' => null,
                        ]);
                    }
                } catch (Throwable $e) {
                    error_log('Failed to record attachment activity: ' . $e->getMessage());
                }
            }
        }

        return $saved;
    }
}
