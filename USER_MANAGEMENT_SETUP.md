# User Management System Setup Guide

## Overview

The User Management system allows administrators to control which PIDS personnel can access the EARS system. Personnel are fetched from the PIDS API, and administrators can grant or revoke access through an intuitive interface.

## Installation Steps

### 1. Run Database Migration

Run the migration to create the `ears_access` table:

```bash
php yii migrate
```

This will create the table with the following structure:

- `id` - Primary key
- `pids_id` - PIDS personnel ID (unique)
- `username` - Username from PIDS
- `email` - Email from PIDS
- `full_name` - Full name from PIDS
- `profile_picture` - Profile picture URL
- `department` - Department
- `position` - Position/Title
- `has_access` - Access status (1=granted, 0=revoked)
- `created_by` - Admin who granted access
- `created_at` - Timestamp
- `updated_at` - Timestamp

### 2. Configure PIDS API Component

Ensure your `backend/config/main-local.php` has the PIDS API component configured:

```php
'components' => [
    'pidsApi' => [
        'class' => 'backend\components\PidsApiComponent',
        'apiBaseUrl' => 'https://dts.pids.gov.ph/api',
        'apiToken' => 'db7af4b8bf265411f6e9ee3b6b78eabaa2ed3e9cb6d1b1239dbf2e86984d127d',
    ],
],
```

### 3. Install HTTP Client (if needed)

```bash
composer require yiisoft/yii2-httpclient
```

## Features

### User Management Page (`/user-management/index`)

**Location in Sidebar:** Settings → User Management

**Features:**

1. **View All Personnel** - Displays all personnel from PIDS API
2. **Search** - Search by name, email, or department
3. **Filter** - Filter by access status (All, Granted, Revoked, No Access)
4. **Grant Access** - Click "Grant Access" button to allow user into EARS
5. **Revoke Access** - Click "Revoke" button to remove user access
6. **Statistics** - View total personnel and active users
7. **Profile Pictures** - Shows profile pictures from PIDS API

### Access Control Flow

1. User tries to login with PIDS credentials
2. System checks if user exists in PIDS API
3. System checks `ears_access` table for access permission
4. If `has_access = 1`, login succeeds
5. If `has_access = 0` or record doesn't exist, login fails

### Administrator Actions

**Grant Access:**

- Fetches user data from PIDS API
- Creates/updates record in `ears_access` table
- Sets `has_access = 1`
- Records which admin granted access

**Revoke Access:**

- Updates record in `ears_access` table
- Sets `has_access = 0`
- User can no longer login (but record is preserved)

## API Endpoints

### GET `/user-management/get-personnel`

Fetches all personnel from PIDS API with their current access status

**Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "username": "jdoe",
      "email": "jdoe@pids.gov.ph",
      "full_name": "John Doe",
      "profile_picture": "https://...",
      "department": "Research",
      "position": "Senior Researcher",
      "has_access": true
    }
  ]
}
```

### POST `/user-management/grant-access`

Grant EARS access to a user

**Parameters:**

- `pids_id` - PIDS personnel ID

**Response:**

```json
{
  "success": true,
  "message": "Access granted successfully"
}
```

### POST `/user-management/revoke-access`

Revoke EARS access from a user

**Parameters:**

- `pids_id` - PIDS personnel ID

**Response:**

```json
{
  "success": true,
  "message": "Access revoked successfully"
}
```

## Models

### EarsAccess Model

**Location:** `backend/models/EarsAccess.php`

**Key Methods:**

- `hasAccess($pidsId)` - Check if user has access
- `grantAccess($pidsId, $personnelData, $adminId)` - Grant access
- `revokeAccess($pidsId)` - Revoke access

**Usage Example:**

```php
// Check access
if (EarsAccess::hasAccess($pidsId)) {
    // User has access
}

// Grant access
EarsAccess::grantAccess($pidsId, $personnelData, Yii::$app->user->id);

// Revoke access
EarsAccess::revokeAccess($pidsId);
```

## Integration with Login

Update your `LoginForm` to check access:

```php
public function login()
{
    if ($this->validate()) {
        $pidsApi = Yii::$app->pidsApi;
        $personnel = $pidsApi->authenticateUser($this->username);

        if ($personnel) {
            // Check if user has access to EARS
            if (!$pidsApi->hasEarsAccess($personnel)) {
                $this->addError('username', 'Access denied. Please contact administrator.');
                return false;
            }

            // Continue with login...
        }
    }
    return false;
}
```

## Security Considerations

1. **Admin Only Access** - Add role-based access control to UserManagementController
2. **Audit Trail** - All access grants/revocations are logged with timestamps
3. **PIDS API Security** - API token should be stored securely
4. **CSRF Protection** - All POST requests include CSRF token

## Customization

### Restrict to Admin Users Only

Edit `UserManagementController::behaviors()`:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['admin'], // Only admin role
                ],
            ],
        ],
    ];
}
```

### Add Department-Based Access

Edit `hasEarsAccess()` in `PidsApiComponent.php`:

```php
public function hasEarsAccess($personnel)
{
    // Check ears_access table
    $pidsId = $personnel['id'] ?? null;
    if ($pidsId && EarsAccess::hasAccess($pidsId)) {
        return true;
    }

    // Auto-grant to specific departments
    $autoDepartments = ['IT', 'Executive'];
    if (isset($personnel['department']) &&
        in_array($personnel['department'], $autoDepartments)) {
        return true;
    }

    return false;
}
```

## Troubleshooting

**Issue:** Personnel list not loading

- Check PIDS API connection
- Verify API token is correct
- Check network/firewall settings

**Issue:** Can't grant access

- Check database connection
- Verify migration was run successfully
- Check user has admin permissions

**Issue:** Profile pictures not showing

- Verify PIDS API returns profile_picture field
- Check CORS settings
- Verify image URLs are accessible

## Testing

1. Navigate to Settings → User Management
2. Verify personnel list loads from PIDS API
3. Grant access to a test user
4. Try logging in with that user
5. Revoke access
6. Verify user can no longer login

## Next Steps

1. Add role-based access control (admin, viewer, editor)
2. Implement email notifications when access is granted
3. Add bulk access management
4. Create access request workflow
5. Add access expiration dates
