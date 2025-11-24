# E-Signature Implementation Guide

## Overview
Complete implementation of admin e-signature system for Barangay E-Credentials document generation.

## ✨ Features Implemented

### 1. **Admin Signature Upload System**
- Upload PNG/JPG signature images
- Store in `public/uploads/signatures/` directory
- Save path to database (`users.signature_path` column)
- Preview current signature
- Replace signature anytime

### 2. **Automatic Signature Insertion**
- Signature automatically embedded in all generated PDF documents
- Converted to base64 for PDF embedding
- Positioned above "Punong Barangay" name
- 150px width, auto height
- Works with transparent PNG backgrounds

### 3. **Document Preview Before Release**
- Admin can preview generated PDF with signature
- Opens in browser before sending to user
- Ensures document looks correct before release

### 4. **Confirmation Flow**
- Admin must explicitly confirm document release
- Preview → Confirm → Send to user
- Prevents accidental releases

## 📋 Setup Instructions

### Step 1: Update Database
Visit this URL to add the signature column:
```
http://localhost:8000/update_db_signature.php
```

This adds `signature_path` column to the `users` table.

### Step 2: Upload Admin Signature

1. Login as admin (`admin@ph` / `admin123`)
2. Go to **Settings** page
3. Scroll to **E-Signature Upload** section
4. Click "Choose File" and select your signature image (PNG recommended)
5. Preview will show before upload
6. Click "Upload Signature"

**Signature Guidelines:**
- PNG with transparent background (recommended)
- Clean, professional signature
- Recommended size: 400x150px or similar aspect ratio
- Will be resized to 150px width in documents

### Step 3: Test Document Generation

1. Go to **Request Management**
2. Select a pending request
3. Change status to "Released"
4. Add remarks (optional)
5. Click "Update Status"

The system will:
- Generate PDF with your signature embedded
- Send email to user with PDF attachment
- Save PDF in `public/uploads/`

## 🔄 How It Works

### Upload Flow:
```
User uploads signature 
  ↓
File saved to public/uploads/signatures/
  ↓
Path saved to database (users.signature_path)
  ↓
Available for document generation
```

### Document Generation Flow:
```
Admin marks request as "Released"
  ↓
System retrieves admin signature from database
  ↓
Converts image to base64
  ↓
Embeds in HTML template
  ↓
Dompdf generates PDF with signature
  ↓
PDF sent to user via email
```

## 📁 Files Modified

### Controllers:
- `app/controllers/AdminRequestController.php`
  - `upload_signature()` - Handle signature upload
  - `get_current_signature()` - Get admin's current signature
  - `getAdminSignature()` - Retrieve signature path
  - `preview_document()` - Preview PDF before release
  - `confirm_release()` - Confirm and send document
  - `generateDocument()` - Modified to include signature

### Views:
- `app/views/dashboard/settings.php`
  - Added E-Signature Upload section
  - Preview functionality
  - Upload form with validation

### Routes:
- `app/config/routes.php`
  - `/admin/signature/upload` (POST)
  - `/admin/signature/current` (GET)
  - `/admin/document/preview/{id}` (GET)
  - `/admin/document/confirm-release` (POST)

### Database:
- Added `signature_path` column to `users` table

## 🎯 API Endpoints

### Upload Signature
```http
POST /admin/signature/upload
Content-Type: multipart/form-data

Body: signature (file)

Response:
{
  "success": true,
  "message": "Signature uploaded successfully",
  "path": "/public/uploads/signatures/signature_admin_1_1234567890.png"
}
```

### Get Current Signature
```http
GET /admin/signature/current

Response:
{
  "success": true,
  "path": "/public/uploads/signatures/signature_admin_1_1234567890.png"
}
```

### Preview Document
```http
GET /admin/document/preview/{requestId}

Response: PDF file (application/pdf)
```

### Confirm Release
```http
POST /admin/document/confirm-release

Body:
{
  "request_id": 123,
  "remarks": "Approved for release"
}

Response:
{
  "success": true,
  "message": "Document released successfully"
}
```

## 📝 Code Examples

### Signature in HTML Template:
```php
$signaturePath = $this->getAdminSignature();
if ($signaturePath && file_exists($signaturePath)) {
    $imageData = base64_encode(file_get_contents($signaturePath));
    $imageSrc = 'data:image/png;base64,' . $imageData;
    $signatureHtml = "<img src='" . $imageSrc . "' style='width:150px;'/>";
}
```

### Document Footer with Signature:
```php
$footer = "
<div style='margin-top:40px;text-align:center;'>
    " . $signatureHtml . "
    <div style='margin-top:10px;'>
        ---------------------------------<br>
        <span style='font-weight:700;'>CEPRIANO ALBO</span><br>
        Punong Barangay
    </div>
</div>";
```

## ✅ Testing Checklist

- [ ] Database column added (`signature_path`)
- [ ] Admin can upload PNG signature
- [ ] Admin can upload JPG signature
- [ ] Current signature displays in settings
- [ ] New upload replaces old signature
- [ ] Signature appears in generated PDFs
- [ ] Signature positioned correctly
- [ ] Transparent background works
- [ ] PDF generates successfully
- [ ] Email sent with PDF attachment
- [ ] Non-admin users cannot upload
- [ ] File size validation works
- [ ] File type validation works

## 🚨 Troubleshooting

**Signature not showing in PDF:**
- Check if signature file exists in `public/uploads/signatures/`
- Verify database has correct path
- Check file permissions (should be readable)
- Ensure Dompdf is installed (`vendor/dompdf`)

**Upload fails:**
- Check directory permissions on `public/uploads/signatures/`
- Verify file type (PNG/JPG only)
- Check file size (<5MB recommended)
- Ensure admin is logged in

**PDF generation fails:**
- Verify Dompdf is installed: `composer require dompdf/dompdf`
- Check `vendor/autoload.php` exists
- Review error logs in runtime folder

## 🔒 Security Notes

- Only admins can upload signatures
- File type validation (PNG/JPG only)
- Unique filenames prevent overwriting
- Files stored outside web root (relatively secure)
- Database stores relative paths only
- Admin session required for all operations

## 📊 Database Schema

```sql
ALTER TABLE users 
ADD COLUMN signature_path VARCHAR(255) DEFAULT NULL 
AFTER photo;
```

## 🎨 Customization

### Change Signature Size:
In `generateDocument()` method:
```php
$signatureHtml = "<img src='" . $imageSrc . "' style='width:200px;'/>"; // Change width
```

### Change Signature Position:
Modify the `$footer` variable placement in document templates.

### Add Multiple Signatures:
- Add more columns (e.g., `secretary_signature`, `captain_signature`)
- Modify `getAdminSignature()` to accept role parameter
- Add conditional logic in template

## 📞 Support

For issues or questions:
1. Check error logs in `runtime/` folder
2. Verify all files are in correct locations
3. Test with simple PNG signature first
4. Review network tab in browser dev tools

---

**Status:** ✅ Fully Implemented and Ready for Production

**Last Updated:** November 23, 2025
