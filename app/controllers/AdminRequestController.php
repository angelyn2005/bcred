<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AdminRequestController extends Controller
{
   
    private function jsonResponse(array $payload, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        http_response_code($statusCode);
        echo json_encode($payload);
        exit;
    }

    public function __construct()
    {
        parent::__construct();
        $this->call->model('RequestModel');
        $this->call->model('AttachmentModel');
        $this->call->model('ActivityLogModel');
        $this->call->model('NotificationModel');
        $this->call->helper('mail');
    }

    // LIST ALL REQUESTS
    public function list()
    {
        $requests = $this->RequestModel->getAllRequests();
        $this->call->view('dashboard/request_management', ['requests' => $requests]);
    }

    // VIEW REQUEST DETAILS
    public function view($id)
{
    $request = $this->RequestModel->getRequestById($id);
    if (!$request) {
        show_404();
        return;
    }

    $attachments = $this->AttachmentModel->getAttachmentsByRequest($id);

    $this->call->view('dashboard/request_details', [
        'request' => $request,
        'attachments' => $attachments
    ]);
}


  
    public function updateRequestAjax($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return $this->jsonResponse(
                    ['success' => false, 'error' => 'Invalid method'],
                    405
                );
            }

            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                return $this->jsonResponse(
                    ['success' => false, 'error' => 'Unauthorized'],
                    401
                );
            }

            // POST STATUS
            $status = strtolower(trim($_POST['status'] ?? ''));
            $remarks = trim($_POST['remarks'] ?? '');

            if (!in_array($status, ['approved', 'rejected'])) {
                return $this->jsonResponse(
                    ['success' => false, 'error' => 'Invalid status'],
                    400
                );
            }

            // Get request
            $request = $this->RequestModel->getRequestById($id);
            if (!$request) {
                return $this->jsonResponse(
                    ['success' => false, 'error' => 'Request not found'],
                    404
                );
            }

            if (($request['status'] ?? null) === $status) {
                return $this->jsonResponse([
                    'success' => true,
                    'status' => $status,
                    'message' => 'Request already has this status.'
                ]);
            }

            // Update DB
            $adminName = $_SESSION['user'] ?? 'Admin';
            $note = sprintf(
                'Status set to %s by %s on %s',
                $status,
                $adminName,
                date('Y-m-d H:i:s')
            );
            if ($remarks !== '') {
                $note .= ' | Remarks: ' . $remarks;
            }

            $updated = $this->RequestModel->updateRequestStatus($id, $status, $note);

            if (!$updated) {
                return $this->jsonResponse(
                    ['success' => false, 'error' => 'Database update failed'],
                    500
                );
            }

            $this->recordActivity([
                'action' => $status === 'approved' ? 'Approved Request' : 'Rejected Request',
                'details' => sprintf(
                    '%s request #%d (%s) %s',
                    ucfirst($status),
                    $id,
                    $request['document_type'] ?? 'Document',
                    $remarks ? "(Remarks: {$remarks})" : ''
                ),
                'request_id' => $id,
            ]);

            $payload = [
                'success' => true,
                'status' => $status,
                'request_id' => (int) $id,
                'message' => 'Request updated successfully.'
            ];

            $this->notifyRequestStatusChange($request, $status, $remarks);

            return $this->jsonResponse($payload);
        } catch (Throwable $e) {
            error_log('Failed to update request status: ' . $e->getMessage());
            return $this->jsonResponse(
                ['success' => false, 'error' => 'Server error: ' . $e->getMessage()],
                500
            );
        }
    }

    private function recordActivity(array $payload): void
    {
        try {
            $this->ActivityLogModel->record([
                'action'     => $payload['action'] ?? 'Request Update',
                'details'    => $payload['details'] ?? '',
                'request_id' => $payload['request_id'] ?? null,
                'admin_id'   => $_SESSION['user_id'] ?? null,
            ]);
        } catch (Throwable $e) {
            error_log('Failed to record activity log: ' . $e->getMessage());
        }
    }

    public function markReleased($id)
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            return;
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        $request = $this->RequestModel->getRequestById($id);
        if (!$request) {
            echo json_encode(['success' => false, 'error' => 'Request not found']);
            return;
        }

        if (strtolower($request['status'] ?? '') !== 'approved') {
            echo json_encode(['success' => false, 'error' => 'Request must be approved first']);
            return;
        }

        $note = sprintf(
            'Marked as released by %s on %s',
            $_SESSION['user'] ?? 'Admin',
            date('Y-m-d H:i:s')
        );

        $updated = $this->RequestModel->updateRequestStatus($id, 'released', $note);

        if ($updated) {
            $this->recordActivity([
                'action' => 'Released Document',
                'details' => sprintf(
                    'Released request #%d (%s)',
                    $id,
                    $request['document_type'] ?? 'Document'
                ),
                'request_id' => $id,
            ]);

            $this->notifyRequestStatusChange($request, 'released', '');

            echo json_encode([
                'success' => true,
                'message' => 'Request marked as released. Email with PDF document sent to user.',
                'status' => 'released'
            ]);
            return;
        }

        echo json_encode(['success' => false, 'error' => 'Failed to update status']);
    }

    private function notifyRequestStatusChange(array $request, string $status, string $remarks = ''): void
    {
        try {
            $userId = $request['user_id'] ?? null;
            if (empty($userId)) {
                return;
            }

            $requestId = $request['id'] ?? null;
            $document = $request['document_type'] ?? 'document';
            $title = sprintf('Request %s', ucfirst($status));
            $message = sprintf(
                'Your %s request%s is now %s%s.',
                $document,
                $requestId ? " (#{$requestId})" : '',
                $status,
                $remarks !== '' ? " (Remarks: {$remarks})" : ''
            );

            $this->NotificationModel->addNotification([
                'user_id' => $userId,
                'request_id' => $requestId,
                'title' => $title,
                'message' => $message,
                'channel' => 'in-app',
            ]);

            // Send email notification to the user for key status changes
            $recipientEmail = trim($request['email'] ?? '');
            $recipientName = trim($request['fullname'] ?? $request['full_name'] ?? '');
            if ($recipientEmail !== '') {
                $statusLower = strtolower($status);
                $subject = '';
                $body = '';

                if ($statusLower === 'approved') {
                    $subject = sprintf('Your %s request has been approved', $document);
                    $body = sprintf(
                        "Hi %s,\n\nGood news — your %s request%s has been approved by the barangay staff on %s.\n\nReference ID: %s\n\nRemarks: %s\n\nYou may check your request status by logging into the portal.\n\nThank you,\nBarangay E-Credentials",
                        $recipientName !== '' ? $recipientName : 'Resident',
                        $document,
                        $requestId ? " (#{$requestId})" : '',
                        date('F d, Y h:i A'),
                        $requestId,
                        $remarks !== '' ? $remarks : 'No remarks.'
                    );
                } elseif ($statusLower === 'rejected') {
                    $subject = sprintf('Your %s request has been rejected', $document);
                    $body = sprintf(
                        "Hi %s,\n\nWe are sorry to inform you that your %s request%s was rejected on %s.\n\nReference ID: %s\n\nRemarks/Reason: %s\n\nIf you believe this is an error or need assistance, please contact the barangay office or reply to this email.\n\nThank you,\nBarangay E-Credentials",
                        $recipientName !== '' ? $recipientName : 'Resident',
                        $document,
                        $requestId ? " (#{$requestId})" : '',
                        date('F d, Y h:i A'),
                        $requestId,
                        $remarks !== '' ? $remarks : 'No remarks provided.'
                    );
                } elseif ($statusLower === 'released') {
                    $subject = sprintf('Your %s is ready for release', $document);
                    $body = sprintf(
                        "Hi %s,\n\nYour %s request%s has been marked as released on %s.\n\nReference ID: %s\n\nRemarks: %s\n\nYou may claim your document at the barangay office. Please bring a valid ID and this reference number.\n\nThank you,\nBarangay E-Credentials",
                        $recipientName !== '' ? $recipientName : 'Resident',
                        $document,
                        $requestId ? " (#{$requestId})" : '',
                        date('F d, Y h:i A'),
                        $requestId,
                        $remarks !== '' ? $remarks : 'No remarks.'
                    );
                }

                if ($subject !== '' && $body !== '') {
                    try {
                        $attachmentPath = null;
                        if ($statusLower === 'released') {
                            $attachmentPath = $this->generateDocument($request);
                            if (!empty($attachmentPath) && file_exists($attachmentPath)) {
                                $filename = basename($attachmentPath);
                                $relative = 'public/uploads/' . $filename;
                                try {
                                    $this->AttachmentModel->addAttachment([
                                        'request_id' => $requestId,
                                        'filename' => $filename,
                                        'filepath' => $relative,
                                        'filetype' => mime_content_type($attachmentPath) ?: 'application/octet-stream',
                                        'uploaded_by' => $_SESSION['user_id'] ?? null,
                                    ]);
                                } catch (Throwable $e) {
                                    error_log('Failed to save generated document attachment record: ' . $e->getMessage());
                                }
                            }
                        }

                        $sent = mail_helper($recipientName, $recipientEmail, $subject, $body, $attachmentPath);
                        if ($sent !== true) {
                            error_log('Failed to send status email for request ' . ($requestId ?? 'unknown') . ': ' . print_r($sent, true));
                        }
                    } catch (Throwable $e) {
                        error_log('Exception while sending status email: ' . $e->getMessage());
                    }
                }
            }
        } catch (Throwable $e) {
            error_log('Failed to create notification: ' . $e->getMessage());
        }
    }

    /**
     * @param array $request
     * @return string|null Absolute path to generated file or null on failure
     */
    private function generateDocument(array $request): ?string
    {
        try {
            $docType = trim($request['document_type'] ?? '');
            $id = $request['id'] ?? null;

            // Helper function to calculate age from date of birth
            $calculateAge = function($dateOfBirth) {
                if (!$dateOfBirth) return '';
                try {
                    $dob = new DateTime($dateOfBirth);
                    $today = new DateTime();
                    $age = $today->diff($dob)->y;
                    return $age;
                } catch (Exception $e) {
                    return '';
                }
            };

            // Normalize requester fields
            $name = htmlspecialchars($request['fullname'] ?? $request['full_name'] ?? '');
            $dob = htmlspecialchars($request['date_of_birth'] ?? $request['dob'] ?? '');
            
            // Calculate age from date of birth
            $age = $calculateAge($request['date_of_birth'] ?? $request['dob'] ?? '');
            if ($age === '' && isset($request['age'])) {
                $age = htmlspecialchars($request['age']);
            }
            
            $gender = htmlspecialchars($request['gender'] ?? '');
            $civil = htmlspecialchars($request['civil_status'] ?? '');
            $nationality = htmlspecialchars($request['nationality'] ?? '');
            $address = htmlspecialchars($request['address'] ?? '');
            $purpose = htmlspecialchars($request['purpose'] ?? $request['details'] ?? '');
            $contact = htmlspecialchars($request['contact_number'] ?? '');
            $occupation = htmlspecialchars($request['occupation'] ?? '');
            $residency = htmlspecialchars($request['residency_period'] ?? '');

            // Business fields
            $business_name = htmlspecialchars($request['business_name'] ?? '');
            $business_address = htmlspecialchars($request['business_address'] ?? '');
            $business_nature = htmlspecialchars($request['business_nature'] ?? '');
            $business_start = htmlspecialchars($request['business_start_date'] ?? '');
            $permit_type = htmlspecialchars($request['permit_type'] ?? '');
            
            // Business owner info (may be separate from main requester)
            $business_owner_name = htmlspecialchars($request['business_owner_name'] ?? $name);
            $business_owner_dob = htmlspecialchars($request['business_owner_dob'] ?? $dob);
            $business_owner_age = $calculateAge($request['business_owner_dob'] ?? $request['date_of_birth'] ?? $request['dob'] ?? '');
            if ($business_owner_age === '' && isset($request['age'])) {
                $business_owner_age = htmlspecialchars($request['age']);
            }
            $business_owner_gender = htmlspecialchars($request['business_owner_gender'] ?? $gender);
            $business_owner_civil = htmlspecialchars($request['business_owner_civil_status'] ?? $civil);
            $business_owner_nationality = htmlspecialchars($request['business_owner_nationality'] ?? $nationality);
            $business_owner_address = htmlspecialchars($request['business_owner_address'] ?? $address);

            $issuedDate = date('F d, Y');

            // Get admin signature
            $signaturePath = $this->getAdminSignature();
            error_log("Signature path retrieved: " . ($signaturePath ?? 'NULL'));
            
            $signatureHtml = '';
            if ($signaturePath && file_exists($signaturePath)) {
                error_log("Signature file exists, embedding in document");
                // Convert image to base64 for embedding in PDF
                $imageData = base64_encode(file_get_contents($signaturePath));
                $ext = strtolower(pathinfo($signaturePath, PATHINFO_EXTENSION));
                $mimeType = ($ext === 'png') ? 'image/png' : 'image/jpeg';
                $imageSrc = 'data:' . $mimeType . ';base64,' . $imageData;
                $signatureHtml = "<img src='" . $imageSrc . "' style='width:120px;height:auto;display:block;margin:0 auto 2px auto;' alt='Signature'/>";
            } else {
                error_log("No signature file found or file doesn't exist");
            }

            $header = "<div style='text-align:center;font-weight:700;'>Republic of the Philippines<br>Province of Oriental Mindoro<br>Municipality of Puerto Galera<br>Barangay Poblacion</div>";
            $footer = "<div style='margin-top:30px;text-align:center;'>" . $signatureHtml . "<div style='border-top:1px solid #000;width:200px;margin:0 auto;padding-top:2px;'><span style='font-weight:700;'>CEPRIANO ALBO</span><br>Punong Barangay</div></div>";

            // Main body wrapper and headings to match the sample layout
            $body = "<div style='font-family:Arial, sans-serif;color:#111;'>";
            $body .= "<div style='text-align:center;margin-top:5px;font-size:18px;font-weight:700;'>OFFICE OF THE PUNONG BARANGAY</div>";
            $body .= "<h1 style='text-align:center;margin:5px 0 10px 0;font-size:26px;letter-spacing:2px;'>CERTIFICATION</h1>";
            $body .= $header;
            $body .= "<p style='text-align:right;margin:8px 0;'><strong>Date Issued:</strong> " . $issuedDate . "</p>";

            if (stripos($docType, 'clearance') !== false) {
                $body .= "<p><strong>TO WHOM IT MAY CONCERN:</strong></p>";
                $body .= "<p>This is to certify that:</p>";
                $body .= "<p>Name: <u>" . $name . "</u><br>Age: <u>" . $age . "</u> &nbsp; Date of Birth: <u>" . $dob . "</u><br>Gender: <u>" . $gender . "</u><br>Civil Status: <u>" . $civil . "</u><br>Nationality: <u>" . $nationality . "</u><br>Address: <u>" . $address . "</u>";
                if ($contact) $body .= "<br>Contact Number: <u>" . $contact . "</u>";
                $body .= "</p>";
                $body .= "<p>is a bonafide resident of this Barangay and is known to be of good moral character and has no derogatory record on file.</p>";
                $body .= "<p>This Barangay Clearance is issued upon the request of the above-named person for the purpose of:</p>";
                $body .= "<p>Purpose: <u>" . $purpose . "</u></p>";
                $body .= $footer;
            } elseif (stripos($docType, 'indigency') !== false) {
                $body .= "<p><strong>TO WHOM IT MAY CONCERN:</strong></p>";
                $body .= "<p>This is to certify that:</p>";
                $body .= "<p>Name: <u>" . $name . "</u><br>Age: <u>" . $age . "</u> &nbsp; Date of Birth: <u>" . $dob . "</u><br>Gender: <u>" . $gender . "</u><br>Civil Status: <u>" . $civil . "</u><br>Nationality: <u>" . $nationality . "</u><br>Address: <u>" . $address . "</u>";
                if ($contact) $body .= "<br>Contact Number: <u>" . $contact . "</u>";
                $body .= "</p>";
                $body .= "<p>is a resident of this Barangay and is considered <strong>INDIGENT</strong> based on the assessment of Barangay Officials.</p>";
                if ($occupation) {
                    $body .= "<p>Occupation / Source of Income:<br><u>" . $occupation . "</u></p>";
                }
                $body .= "<p>This certificate is issued upon the request of the above-named person for the purpose of:</p>";
                $body .= "<p>Purpose: <u>" . $purpose . "</u></p>";
                $body .= $footer;
            } elseif (stripos($docType, 'resid') !== false) {
                $body .= "<p><strong>TO WHOM IT MAY CONCERN:</strong></p>";
                $body .= "<p>This is to certify that:</p>";
                $body .= "<p>Name: <u>" . $name . "</u><br>Age: <u>" . $age . "</u> &nbsp; Date of Birth: <u>" . $dob . "</u><br>Gender: <u>" . $gender . "</u><br>Civil Status: <u>" . $civil . "</u><br>Nationality: <u>" . $nationality . "</u><br>Address: <u>" . $address . "</u>";
                if ($contact) $body .= "<br>Contact Number: <u>" . $contact . "</u>";
                $body .= "</p>";
                if ($residency) {
                    $body .= "<p>has been a resident of this Barangay since:<br>Period of Residency: <u>" . $residency . "</u></p>";
                }
                $body .= "<p>The above-named individual is known to be a law-abiding resident.</p>";
                $body .= "<p>This Certificate of Residency is issued upon the request of the above-named person for the purpose of:</p>";
                $body .= "<p>Purpose: <u>" . $purpose . "</u></p>";
                $body .= $footer;
            } elseif (stripos($docType, 'business') !== false || stripos($docType, 'permit') !== false) {
                $body .= "<p><strong>BUSINESS INFORMATION</strong></p>";
                $body .= "<p>Business Name / Trade Name:<br><u>" . $business_name . "</u></p>";
                $body .= "<p>Business Address:<br><u>" . $business_address . "</u></p>";
                $body .= "<p>Nature / Type of Business:<br><u>" . $business_nature . "</u></p>";
                $body .= "<p>Business Start Date: <u>" . $business_start . "</u></p>";
                $body .= "<p><strong>OWNER INFORMATION</strong><br>Owner's Full Name: <u>" . $business_owner_name . "</u>";
                if ($business_owner_age) $body .= "<br>Age: <u>" . $business_owner_age . "</u>";
                if ($business_owner_dob) $body .= " &nbsp; Date of Birth: <u>" . $business_owner_dob . "</u>";
                if ($business_owner_gender) $body .= "<br>Gender: <u>" . $business_owner_gender . "</u>";
                if ($business_owner_civil) $body .= "<br>Civil Status: <u>" . $business_owner_civil . "</u>";
                if ($business_owner_nationality) $body .= "<br>Nationality: <u>" . $business_owner_nationality . "</u>";
                if ($business_owner_address) $body .= "<br>Owner's Address:<br><u>" . $business_owner_address . "</u>";
                if ($contact) $body .= "<br>Contact Number: <u>" . $contact . "</u>";
                $body .= "</p>";
                $body .= "<p>TYPE OF PERMIT REQUESTED: <u>" . $permit_type . "</u></p>";
                $body .= "<p>This permit is issued upon compliance with Barangay regulations and is valid for one calendar year unless revoked or suspended.</p>";
                $body .= $footer;
            } else {
                // Generic fallback template
                $body .= "<p><strong>TO WHOM IT MAY CONCERN:</strong></p>";
                $body .= "<p>This certifies that <u>" . $name . "</u> submitted the request for <u>" . htmlspecialchars($docType) . "</u> for the following purpose:</p>";
                $body .= "<p>Purpose: <u>" . $purpose . "</u></p>";
                $body .= $footer;
            }

            $body .= "</div>";

            // Wrap minimal HTML
            $html = "<!doctype html><html><head><meta charset='utf-8'><title>" . htmlspecialchars($docType) . "</title></head><body style='margin:20px;font-size:14px;line-height:1.4;'>" . $body . "</body></html>";

            $uploadRoot = ROOT_DIR . 'public/uploads/';
            if (!is_dir($uploadRoot)) {
                mkdir($uploadRoot, 0777, true);
            }

            // Attempt to generate PDF if Dompdf is available. Otherwise, save HTML fallback.
            $filenamePdf = 'request_' . ($id ?? 'unknown') . '_' . time() . '.pdf';
            $absolutePdf = $uploadRoot . $filenamePdf;

            // If composer autoload exists, include it so Dompdf classes are available
            $composerAutoload = ROOT_DIR . 'vendor/autoload.php';
            if (file_exists($composerAutoload)) {
                @require_once $composerAutoload;
            }

            if (class_exists('\Dompdf\\Dompdf')) {
                try {
                    $dompdf = new \Dompdf\Dompdf();
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    $output = $dompdf->output();
                    file_put_contents($absolutePdf, $output);
                    return file_exists($absolutePdf) ? $absolutePdf : null;
                } catch (Throwable $e) {
                    error_log('Dompdf generation failed: ' . $e->getMessage());
                    // fall back to HTML
                }
            }

            // HTML fallback
            $filename = 'request_' . ($id ?? 'unknown') . '_' . time() . '.html';
            $absolute = $uploadRoot . $filename;
            file_put_contents($absolute, $html);
            return file_exists($absolute) ? $absolute : null;
        } catch (Throwable $e) {
            error_log('Failed to generate document: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current admin signature
     */
    public function get_current_signature()
    {
        $signaturePath = $this->getAdminSignature();
        
        if ($signaturePath) {
            $relativePath = str_replace(ROOT_DIR, '/', $signaturePath);
            $this->jsonResponse([
                'success' => true,
                'path' => $relativePath
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'No signature uploaded'
            ]);
        }
    }

    /**
     * Upload admin e-signature
     */
    public function upload_signature()
    {
        error_log("=== SIGNATURE UPLOAD START ===");
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("FILES: " . print_r($_FILES, true));
        error_log("SESSION: " . print_r($_SESSION, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        // More detailed file upload error checking
        if (!isset($_FILES['signature'])) {
            $this->jsonResponse(['success' => false, 'message' => 'No signature file in request'], 400);
            return;
        }

        $file = $_FILES['signature'];
        error_log("Upload error code: " . $file['error']);
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
            ];
            $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error: ' . $file['error'];
            $this->jsonResponse(['success' => false, 'message' => $errorMsg], 400);
            return;
        }

        // Check file type by extension AND mime type
        $allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        $allowedExtensions = ['png', 'jpg', 'jpeg'];
        
        $fileType = $file['type'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        error_log("File type: $fileType, Extension: $ext");

        if (!in_array($fileType, $allowedMimeTypes) && !in_array($ext, $allowedExtensions)) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Only PNG/JPG images are allowed. Detected: ' . $fileType
            ], 400);
            return;
        }

        // Create signatures directory
        $signatureDir = ROOT_DIR . 'public/uploads/signatures/';
        if (!is_dir($signatureDir)) {
            mkdir($signatureDir, 0777, true);
        }

        $adminId = $_SESSION['user_id'] ?? null;
        error_log("Admin ID: " . ($adminId ?? 'NULL'));
        
        if (!$adminId) {
            $this->jsonResponse(['success' => false, 'message' => 'Admin not logged in'], 401);
            return;
        }

        // Generate unique filename - use the detected extension
        $filename = 'signature_admin_' . $adminId . '_' . time() . '.' . $ext;
        $filepath = $signatureDir . $filename;
        
        error_log("Attempting to save to: $filepath");

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            error_log("File saved successfully");
            
            // Update user's signature path
            $relativePath = 'public/uploads/signatures/' . $filename;
            $this->call->model('UserModel');
            
            // Check if signature_path column exists
            try {
                $this->UserModel->updateUser($adminId, ['signature_path' => $relativePath]);
                error_log("Database updated successfully");
            } catch (Exception $e) {
                error_log("Database update failed: " . $e->getMessage());
                $this->jsonResponse([
                    'success' => false, 
                    'message' => 'File uploaded but database update failed. Run database migration first.'
                ], 500);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Signature uploaded successfully',
                'path' => '/' . $relativePath
            ]);
        } else {
            error_log("move_uploaded_file failed");
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Failed to save file. Check directory permissions.'
            ], 500);
        }
    }

    /**
     * Get admin signature path
     */
    private function getAdminSignature(): ?string
    {
        $adminId = $_SESSION['user_id'] ?? null;
        error_log("Getting signature for admin ID: " . ($adminId ?? 'NULL'));
        
        if (!$adminId) {
            error_log("No admin ID in session");
            return null;
        }

        $this->call->model('UserModel');
        $admin = $this->UserModel->getUserById($adminId);
        
        error_log("Admin data: " . print_r($admin, true));
        
        if (!empty($admin['signature_path'])) {
            $fullPath = ROOT_DIR . $admin['signature_path'];
            error_log("Checking signature at: " . $fullPath);
            error_log("File exists: " . (file_exists($fullPath) ? 'YES' : 'NO'));
            
            if (file_exists($fullPath)) {
                return $fullPath;
            } else {
                error_log("Signature file path in DB but file doesn't exist!");
            }
        } else {
            error_log("No signature_path in admin record");
        }

        return null;
    }

    /**
     * Preview document before releasing
     */
    public function preview_document($requestId)
    {
        $this->call->model('RequestModel');
        $request = $this->RequestModel->getRequestById($requestId);

        if (!$request) {
            echo json_encode(['success' => false, 'message' => 'Request not found']);
            return;
        }

        // Generate document with signature
        $documentPath = $this->generateDocument($request);

        if ($documentPath && file_exists($documentPath)) {
            // Return PDF for preview
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="preview.pdf"');
            readfile($documentPath);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to generate document']);
        }
    }

    /**
     * Confirm and release document
     */
    public function confirm_release()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        $requestId = $_POST['request_id'] ?? null;
        $remarks = trim($_POST['remarks'] ?? '');

        if (!$requestId) {
            $this->jsonResponse(['success' => false, 'message' => 'Request ID required'], 400);
            return;
        }

        $this->call->model('RequestModel');
        $request = $this->RequestModel->getRequestById($requestId);

        if (!$request) {
            $this->jsonResponse(['success' => false, 'message' => 'Request not found'], 404);
            return;
        }

        // Update status to released
        $updateData = [
            'status' => 'released',
            'remarks' => $remarks,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $updated = $this->RequestModel->updateRequest($requestId, $updateData);

        if ($updated) {
            // Send notification and email with generated document
            $this->sendNotificationAndEmail($requestId, $request, 'released', $remarks);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Document released successfully'
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Failed to update request'], 500);
        }
    }
}

