# PIDS API Troubleshooting Guide

## Issue: Personnel List Not Loading

### Understanding the Data Structure

The PIDS API uses a **three-tier relationship**:

1. **Personnel** has a `division_id` field
2. **Division** has a `department_id` field
3. **Department** has the actual department name

To get a personnel's department, the system must:

```
Personnel (division_id) → Division (department_id) → Department (name)
```

### API Endpoints Used

1. `GET /api/personnel` - Returns all personnel with their division_id
2. `GET /api/division` - Returns all divisions with their department_id
3. `GET /api/department` - Returns all departments with their name

### Debugging Steps:

#### 1. Test API Connection Directly

**Option A: Browser Test**
Open in browser: `http://localhost/earsv2/backend/web/test-api.html`

This will test the API connection directly from the browser and show you:

- If the API is accessible
- What data structure is returned
- How many records exist
- What fields are available

**Option B: Yii Debug Endpoint**
Open in browser: `http://localhost/earsv2/backend/web/index.php?r=debug/test-api`

This will test through the Yii component and show:

- If the component is configured
- If cURL is working
- The actual API response

#### 2. Check Application Logs

Location: `backend/runtime/logs/app.log`

Look for entries containing:

- `PidsApiComponent::getAllPersonnel`
- `UserManagementController::actionGetPersonnel`
- Any cURL errors

#### 3. Verify Configuration

File: `backend/config/main.php`

Ensure this exists in the components section:

```php
'pidsApi' => [
    'class' => 'backend\components\PidsApiComponent',
    'apiBaseUrl' => 'https://dts.pids.gov.ph/api',
    'apiToken' => 'db7af4b8bf265411f6e9ee3b6b78eabaa2ed3e9cb6d1b1239dbf2e86984d127d',
],
```

#### 4. Check Browser Console

When on `/user-management/index`:

1. Open browser DevTools (F12)
2. Go to Console tab
3. Look for:
   - "Loading personnel from API..."
   - "API Response:" - check what data is returned
   - Any error messages

#### 5. Check Network Tab

In browser DevTools:

1. Go to Network tab
2. Refresh the page
3. Look for request to `/user-management/get-personnel`
4. Check:
   - Status code (should be 200)
   - Response data
   - Any errors

### Common Issues & Solutions:

#### Issue 1: cURL Not Installed

**Symptom:** "Call to undefined function curl_init"
**Solution:**

- Enable cURL in php.ini
- Uncomment: `extension=curl`
- Restart Apache/PHP-FPM

#### Issue 2: SSL Certificate Error

**Symptom:** "SSL certificate problem"
**Solution:**

- The component disables SSL verification for development
- For production, download cacert.pem and configure in php.ini

#### Issue 3: Firewall/Network Block

**Symptom:** "Connection timeout" or "Could not resolve host"
**Solution:**

- Check firewall settings
- Verify internet connection
- Try accessing API URL directly in browser

#### Issue 4: API Token Invalid

**Symptom:** Empty response or error from API
**Solution:**

- Verify token is correct
- Check if token has expired
- Contact PIDS to get new token

#### Issue 5: Wrong Data Structure

**Symptom:** "API returned non-array data"
**Solution:**

- Check API response format
- Look at sample record in test results
- Update field mappings in UserManagementController

### Improved Error Messages

The system now shows:

- **Page Level:** Warning banner if API fails
- **Console:** Detailed logging of API requests/responses
- **Logs:** File logging for debugging

### Manual API Test (cURL Command)

Run this in terminal to test API directly:

```bash
curl "https://dts.pids.gov.ph/api/personnel?token=db7af4b8bf265411f6e9ee3b6b78eabaa2ed3e9cb6d1b1239dbf2e86984d127d"
```

### Next Steps After Testing:

1. **If API returns data in browser but not in Yii:**

   - Check PHP cURL configuration
   - Review runtime logs
   - Verify component is loaded

2. **If API returns different field names:**

   - Update field mappings in `actionGetPersonnel()`
   - The code now handles multiple field name variations

3. **If no data at all:**
   - Verify API token with PIDS
   - Check if your IP is whitelisted
   - Confirm API endpoint is correct

### Field Mapping Support

The system now automatically handles these field name variations:

- ID: `id`, `pids_id`, `personnel_id`
- Username: `username`, `user`
- Name: `full_name`, `name`, `fullname`
- Photo: `profile_picture`, `photo`, `avatar`
- Department: `department`, `dept`
- Position: `position`, `title`

### Contact for Help

If still not working after these steps, provide:

1. Output from `/debug/test-api`
2. Browser console output
3. Network tab screenshot
4. Last 20 lines from `backend/runtime/logs/app.log`
