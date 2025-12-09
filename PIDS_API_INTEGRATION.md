# PIDS API Integration for EARS

## Overview

This integration allows EARS to authenticate users against the PIDS DTS API, implementing a Single Sign-On (SSO) approach where PIDS personnel can access EARS using their existing PIDS credentials.

## Installation Steps

### 1. Install HTTP Client (if not already installed)

Run this command in your project root:

```bash
composer require yiisoft/yii2-httpclient
```

### 2. Configure the PIDS API Component

Add to `backend/config/main-local.php` in the `components` section:

```php
'components' => [
    'pidsApi' => [
        'class' => 'backend\components\PidsApiComponent',
        'apiBaseUrl' => 'https://dts.pids.gov.ph/api',
        'apiToken' => 'db7af4b8bf265411f6e9ee3b6b78eabaa2ed3e9cb6d1b1239dbf2e86984d127d',
    ],
],
```

### 3. Update User Model

Add these fields to your User table/model if not present:

- `profile_picture` (string) - URL or path to profile picture
- `full_name` (string) - Full name from PIDS
- `email` (string) - Email from PIDS
- `pids_id` (integer) - PIDS personnel ID

### 4. Modify LoginForm to Use PIDS API

In `common/models/LoginForm.php`, update the `login()` method:

```php
public function login()
{
    if ($this->validate()) {
        // Authenticate with PIDS API
        $pidsApi = Yii::$app->pidsApi;
        $personnel = $pidsApi->authenticateUser($this->username);

        if ($personnel && $pidsApi->hasEarsAccess($personnel)) {
            // Find or create local user
            $user = User::findByUsername($this->username);

            if (!$user) {
                $user = new User();
                $user->username = $personnel['username'] ?? $this->username;
            }

            // Update user data from PIDS
            $user->email = $personnel['email'] ?? '';
            $user->full_name = $personnel['name'] ?? $personnel['full_name'] ?? '';
            $user->profile_picture = $pidsApi->getProfilePictureUrl($personnel);
            $user->pids_id = $personnel['id'] ?? null;
            $user->save(false);

            return Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0);
        } else {
            $this->addError('username', 'User not found in PIDS system or access denied.');
            return false;
        }
    }
    return false;
}
```

### 5. Update Layout for Profile Picture

The layout has been prepared to show profile pictures. When the API integration is active, it will automatically display the user's profile picture from PIDS.

## API Response Structure

The PIDS API returns personnel data in this format (example):

```json
[
  {
    "id": 123,
    "username": "jdoe",
    "email": "jdoe@pids.gov.ph",
    "name": "John Doe",
    "full_name": "John Doe",
    "profile_picture": "/uploads/profiles/jdoe.jpg",
    "department": "Research",
    "position": "Senior Researcher"
  }
]
```

## Features Implemented

1. **Single Sign-On**: Users authenticated via PIDS API
2. **Profile Pictures**: Automatically fetched from PIDS
3. **User Data Sync**: Personnel information synced to local database
4. **Access Control**: Configurable access rules via `hasEarsAccess()` method
5. **Fallback Support**: Works with existing User model structure

## Security Considerations

1. **API Token**: Store in environment variables or secure config
2. **HTTPS**: Always use HTTPS for API calls
3. **Token Rotation**: Implement token rotation if PIDS API supports it
4. **Access Logs**: Log all authentication attempts

## Testing

Test the API connection:

```php
$pidsApi = Yii::$app->pidsApi;
$personnel = $pidsApi->getAllPersonnel();
var_dump($personnel);
```

## Troubleshooting

1. **Connection Issues**: Check firewall and network access to dts.pids.gov.ph
2. **Invalid Token**: Verify token is correct and not expired
3. **Missing Data**: Check API response structure matches expectations
4. **Profile Pictures Not Loading**: Verify CORS and image URLs

## Customization

### Restrict Access by Department

Edit `hasEarsAccess()` in `PidsApiComponent.php`:

```php
public function hasEarsAccess($personnel)
{
    $allowedDepartments = ['IT', 'Research', 'Admin'];
    return isset($personnel['department']) &&
           in_array($personnel['department'], $allowedDepartments);
}
```

### Add Custom Fields

Extend the component to fetch additional personnel data as needed.
