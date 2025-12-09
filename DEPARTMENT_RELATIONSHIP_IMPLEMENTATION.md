# Department Relationship Implementation

## Changes Made

### Problem

The department field was being read directly from the personnel record, but the PIDS API uses a relational structure where:

- Personnel → has `division_id`
- Division → has `department_id`
- Department → has `name`

### Solution

Updated the system to properly resolve the relationship chain by fetching all three data sources and linking them together.

---

## Files Modified

### 1. `backend/components/PidsApiComponent.php`

**Added Methods:**

- `getAllDivisions()` - Fetches all divisions from API and indexes by division_id
- `getAllDepartments()` - Fetches all departments from API and indexes by department_id
- `getDepartmentName($person, $divisions, $departments)` - Resolves department name through the relationship chain

**How it works:**

```php
// 1. Get division_id from personnel
$divisionId = $person['division_id'];

// 2. Lookup division by ID
$division = $divisions[$divisionId];

// 3. Get department_id from division
$departmentId = $division['department_id'];

// 4. Lookup department by ID
$department = $departments[$departmentId];

// 5. Return department name
return $department['name'];
```

---

### 2. `backend/controllers/UserManagementController.php`

**Updated `actionGetPersonnel()`:**

- Now fetches personnel, divisions, and departments
- Uses `PidsApiComponent::getDepartmentName()` to resolve department for each person
- Logs the count of all three data sources

**Before:**

```php
$allPersonnel = $pidsApi->getAllPersonnel();
// Department directly from person
'department' => $person['department'] ?? null
```

**After:**

```php
$allPersonnel = $pidsApi->getAllPersonnel();
$divisions = $pidsApi->getAllDivisions();
$departments = $pidsApi->getAllDepartments();

// Department resolved through relationship
'department' => $pidsApi->getDepartmentName($person, $divisions, $departments)
```

---

### 3. `backend/controllers/DebugController.php`

**Updated `actionTestApi()`:**

- Tests all three endpoints (personnel, divisions, departments)
- Shows sample records from each endpoint
- Demonstrates the relationship resolution for a sample personnel
- Returns detailed debug information

**New Response Structure:**

```json
{
  "personnel": {
    "success": true,
    "count": 150,
    "sample": { ... }
  },
  "divisions": {
    "success": true,
    "count": 25,
    "sample": { ... }
  },
  "departments": {
    "success": true,
    "count": 10,
    "sample": { ... }
  },
  "relationship_test": {
    "sample_personnel": { ... },
    "resolved_department": "Finance Department"
  }
}
```

---

### 4. `backend/web/test-api.html`

**Enhanced Testing Page:**

- Added separate buttons to test each API endpoint
- Shows results for personnel, divisions, and departments
- Demonstrates the full relationship resolution visually
- Auto-runs the relationship test on page load

**Visual Flow:**

```
Personnel Record
  ↓ (has division_id: 5)
Division Record
  ↓ (has department_id: 2)
Department Record
  ↓ (has name: "Finance Department")
Final Result: "Finance Department"
```

---

### 5. `PIDS_API_TROUBLESHOOTING.md`

**Added Section:**

- Explanation of the three-tier relationship structure
- Clear diagram of the data flow
- Updated debugging steps to check all three endpoints

---

## Testing the Changes

### Option 1: Direct Browser Test

Open: `http://localhost/earsv2/backend/web/test-api.html`

This will:

1. Fetch all personnel, divisions, and departments
2. Show sample records from each endpoint
3. Demonstrate the relationship resolution
4. Display any errors clearly

### Option 2: Debug Endpoint

Open: `http://localhost/earsv2/backend/web/index.php?r=debug/test-api`

Returns JSON with:

- Count of records from each endpoint
- Sample records
- Relationship test results

### Option 3: User Management Page

1. Go to Settings → User Management
2. Open browser console (F12)
3. Check for logs showing:
   - "Fetched X personnel, Y divisions, Z departments"
   - Department names appearing in the personnel list

---

## Expected Behavior

### Before (Incorrect):

```
Personnel: John Doe
Department: null (or undefined)
```

### After (Correct):

```
Personnel: John Doe
  division_id: 5 → Division: "Research Division"
    department_id: 2 → Department: "Research and Development Department"

Final Display: "Research and Development Department"
```

---

## API Endpoints

All endpoints require the API token as a query parameter:

```
GET https://dts.pids.gov.ph/api/personnel?token={token}
GET https://dts.pids.gov.ph/api/division?token={token}
GET https://dts.pids.gov.ph/api/department?token={token}
```

---

## Logging

Check `backend/runtime/logs/app.log` for:

```
Fetching personnel from: https://dts.pids.gov.ph/api/personnel
Successfully fetched 150 personnel records

Fetching divisions from: https://dts.pids.gov.ph/api/division
Successfully fetched 25 division records

Fetching departments from: https://dts.pids.gov.ph/api/department
Successfully fetched 10 department records

Get Personnel AJAX - Fetched: 150 personnel, 25 divisions, 10 departments
Processed 150 personnel records with departments
```

---

## What to Check If It Still Doesn't Work

1. **All three API endpoints must be accessible**

   - Test each endpoint individually in browser/Postman
   - Verify API token is valid for all endpoints

2. **Field names may vary**

   - The code handles variations like `id` vs `division_id` vs `department_id`
   - Check actual field names in API responses

3. **Relationships must be valid**

   - Every personnel must have a valid division_id
   - Every division must have a valid department_id
   - IDs must match between tables

4. **Network/CORS issues**
   - Check if all three API calls complete successfully
   - Look for CORS errors in browser console
   - Verify cURL is working in PHP

---

## Performance Note

The system now makes **3 API calls** instead of 1:

1. Fetch all personnel (~150 records)
2. Fetch all divisions (~25 records)
3. Fetch all departments (~10 records)

Total response time may increase, but this ensures accurate department information. The data is cached in memory during the request, so the relationship resolution is fast (O(1) lookups).
