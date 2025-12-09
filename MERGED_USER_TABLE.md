# User Table Merge - ears_access → user

## Overview

Successfully consolidated the `ears_access` and `user` tables. Granting access now creates/activates user accounts directly in the `user` table instead of maintaining a separate access tracking table.

## Changes Made

### 1. Database Migration

- **Migration File**: `console/migrations/m241209_120000_migrate_ears_access_to_user.php`
- **Status**: ✅ Applied Successfully
- **Results**:
  - Migrated 3 existing records from `ears_access` to `user` table
  - Updated 1 existing user (ID 1, PIDS ID: 2)
  - Created 2 new users (PIDS IDs: 6, 4)
  - All profile data (full_name, position, department, profile_picture) transferred
  - Access status converted: `has_access=1` → `status=10` (ACTIVE), `has_access=0` → `status=9` (INACTIVE)

### 2. User Model Updates

**File**: `common/models/User.php`

Added three new static methods for access management:

#### `grantAccess($pidsId, $personnelData)`

- Creates new user account or updates existing one
- Sets `status = STATUS_ACTIVE` (10)
- Extracts data from PIDS API nested structure:
  - Username/email from `accounts[0]`
  - Department from `division.department.name`
  - Profile picture from `accounts[0].profile_picture_url`
- Generates random auth_key and password_hash (SSO only, not used for login)
- Returns `true` on success, `false` on failure

#### `revokeAccess($pidsId)`

- Finds user by PIDS ID
- Sets `status = STATUS_INACTIVE` (9)
- Does NOT delete the user record
- Returns `true` on success, `false` if user not found

#### `hasEarsAccess($pidsId)`

- Checks if user exists with `pids_id` and `status = STATUS_ACTIVE`
- Returns boolean

### 3. Controller Updates

**File**: `backend/controllers/UserManagementController.php`

Changed from `use backend\models\EarsAccess` to `use common\models\User`

#### actionIndex()

- Changed from `EarsAccess::find()->all()` to `User::find()->where(['not', ['pids_id' => null]])->all()`
- Creates accessMap using User records

#### actionGetPersonnel()

- Updated query to check `User::STATUS_ACTIVE` instead of `ears_access.has_access`
- Uses `User` model instead of `EarsAccess`

#### actionGrantAccess()

- Now calls `User::grantAccess()` instead of `EarsAccess::grantAccess()`
- Success message changed to "User account created successfully"

#### actionRevokeAccess()

- Now calls `User::revokeAccess()` instead of `EarsAccess::revokeAccess()`
- Sets user status to INACTIVE instead of setting has_access flag

#### actionStats()

- Updated to count active/inactive users from `user` table
- Queries: `User::find()->where(['not', ['pids_id' => null]])->andWhere(['status' => STATUS_ACTIVE/INACTIVE])`

## User Status Values

| Status   | Constant                | Value | Meaning         |
| -------- | ----------------------- | ----- | --------------- |
| ACTIVE   | `User::STATUS_ACTIVE`   | 10    | Has EARS access |
| INACTIVE | `User::STATUS_INACTIVE` | 9     | Access revoked  |
| DELETED  | `User::STATUS_DELETED`  | 0     | Soft deleted    |

## Authentication Flow

1. **Login** → User enters PIDS credentials
2. **PIDS API Verification** → Validates against DTS API
3. **Local User Check** → Finds user by `pids_id` with `status = ACTIVE`
4. **Grant Session** → If exists and active, log in user

## Next Steps (Optional)

### Deprecate ears_access Table

Once confirmed working in production:

```sql
-- Backup first!
CREATE TABLE ears_access_backup AS SELECT * FROM ears_access;

-- Then drop
DROP TABLE ears_access;
```

### Remove EarsAccess Model

Delete file: `backend/models/EarsAccess.php` (no longer needed)

## Testing Checklist

- [x] Migration applied successfully
- [ ] Grant access creates new user account
- [ ] Grant access updates existing user account
- [ ] Revoke access sets user to INACTIVE
- [ ] Personnel list shows correct access status
- [ ] Profile page displays user information
- [ ] Login works for users with ACTIVE status
- [ ] Login denied for users with INACTIVE status

## Benefits

✅ **Single Source of Truth**: All user data in one table  
✅ **Simplified Authentication**: Direct `User` model queries  
✅ **Better Data Integrity**: No duplicate records across tables  
✅ **Standard Yii Pattern**: Uses built-in User identity interface  
✅ **SSO Ready**: Profile data from PIDS API, status controls access

## Database Schema

### user Table (Consolidated)

```sql
- id (PK)
- pids_id (UNIQUE, from PIDS API)
- username
- full_name
- email
- position
- department
- profile_picture
- auth_key
- password_hash (random, not used for SSO)
- status (10=ACTIVE, 9=INACTIVE, 0=DELETED)
- created_at
- updated_at
```

### ears_access Table (Deprecated)

Can be removed after confirming system stability.
