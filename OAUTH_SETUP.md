# OAuth Setup Instructions

## Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth client ID"
5. Choose "Web application"
6. Add authorized JavaScript origins: `http://localhost:8000` (or your domain)
7. Add authorized redirect URIs: `http://localhost:8000` (or your domain)
8. Copy the Client ID (format: `123456789-abc.apps.googleusercontent.com`)
9. Add to `app/config/config.php`:
   ```php
   $config['GOOGLE_CLIENT_ID'] = '522902353300-4hlt8puimct4tumoaq35p5r526r47dkv.apps.googleusercontent.com';
   ```

## Facebook OAuth Setup

1. Go to [Facebook Developers](https://developers.facebook.com/)
2. Create a new app or select an existing one
3. Add "Facebook Login" product
4. Go to Settings → Basic
5. Add your site URL: `http://localhost:8000` (or your domain)
6. Copy the App ID
7. Add to `app/config/config.php`:
   ```php
   $config['FACEBOOK_APP_ID'] = 'YOUR_APP_ID_HERE';
   ```

## Configuration

Edit `app/config/config.php` and set the OAuth credentials:

```php
$config['GOOGLE_CLIENT_ID'] = 'your-google-client-id.apps.googleusercontent.com';
$config['FACEBOOK_APP_ID'] = 'your-facebook-app-id';
```

**Note:** If OAuth credentials are not set, the OAuth buttons will be automatically disabled.

## Admin Account Setup

Default admin accounts are hardcoded in `app/helpers/admin_seeder.php`:

**Primary Admin:**
- **Email:** `admin@ph`
- **Password:** `admin123`
- **Username:** `admin`

**Secondary Admin:**
- **Email:** `staff@barangay.gov.ph`
- **Password:** `staff123`
- **Username:** `barangay_staff`

To seed these accounts, run:
```
GET /setup/seed-admins
```

Or manually create them in the database with role='admin'.

## Login Instructions

**Admin Login:**
- Use email: `admin@ph`
- Use password: `admin123`
- You will be redirected to the admin dashboard

**Resident Login:**
- Register first via the registration page
- Verify email using the 6-digit code sent
- Login using your registered email and password

## Notes

- Registration is restricted to residents only
- Admin accounts must be created manually or via the seeder
- OAuth users are automatically registered as residents
- Same login page is used for both admin and user accounts

