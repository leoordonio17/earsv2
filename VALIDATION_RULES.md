# Data Validation and Required Fields Documentation

This document provides a comprehensive overview of all validation rules and required fields across the EARS v2 system.

---

## Legend

- **Required** - Field must have a value
- **Conditional** - Field is required only under certain conditions
- **Optional** - Field is not required
- 🔴 Critical validation rule

---

## 1. Project Assignment Module

### Fields and Validation

| Field Name      | Type         | Required | Validation Rules                         | Notes                                                |
| --------------- | ------------ | -------- | ---------------------------------------- | ---------------------------------------------------- |
| **Project**     | Dropdown     | ✅ Yes   | Must select a valid project from API     | Only unassigned projects shown in create form        |
| **Researchers** | Multi-Select | ✅ Yes   | At least one researcher must be selected | Multiple researchers can be assigned to same project |

### Business Rules

- 🔴 Project cannot be changed in update form (must delete and recreate)
- 🔴 Only projects not currently assigned to any researcher are shown in create form
- Each project-researcher combination creates a separate database record
- Updating assignments deletes all existing assignments for that project and recreates them

---

## 2. Workplan Module

### Fields and Validation

| Field Name               | Type        | Required       | Validation Rules                                    | Notes                                          |
| ------------------------ | ----------- | -------------- | --------------------------------------------------- | ---------------------------------------------- |
| **Project**              | Dropdown    | ✅ Yes         | Must select from user's assigned projects           | Only shows projects assigned to logged-in user |
| **Task Type**            | Dropdown    | ✅ Yes         | Must select a valid active task type                | Changes available task categories              |
| **Task Category**        | Dropdown    | ✅ Yes         | Must select a valid category for selected task type | Dynamically loaded via AJAX based on task type |
| **Project Stage**        | Dropdown    | ✅ Yes         | Must select a valid active project stage            | -                                              |
| **Start Date**           | Date Picker | ✅ Yes         | Valid date in YYYY-MM-DD format                     | -                                              |
| **End Date**             | Date Picker | ✅ Yes         | Valid date; Must be >= Start Date                   | 🔴 Cannot be earlier than start date           |
| **Workplan Description** | Textarea    | ✅ Yes         | Must provide detailed description                   | -                                              |
| **Save as Template**     | Checkbox    | Optional       | -                                                   | If checked, template name becomes required     |
| **Template Name**        | Text Input  | ⚠️ Conditional | Required if "Save as Template" is checked           | Max 255 characters                             |

### Business Rules

- 🔴 **Date Validation**: End date must not be earlier than start date (server-side validation)
- **Template Name Conditional**: Only required when "Save as Template" checkbox is checked
- **Task Category Dependency**: Task categories are filtered based on selected task type
- **User Context**: Only projects assigned to the logged-in user are available
- **Template Loading**: Can load from saved templates to populate all fields except dates

### Validation Messages

- End date error: "End date must not be earlier than start date."
- Template name error: "Template name is required when saving as template."

---

## 3. Accomplishment Module

### Fields and Validation

| Field Name                                | Type          | Required              | Validation Rules                                              | Notes                                |
| ----------------------------------------- | ------------- | --------------------- | ------------------------------------------------------------- | ------------------------------------ |
| **Workplan**                              | Dropdown      | ✅ Yes                | Must select from user's workplans                             | Shows workplan with date range       |
| **Start Date**                            | Date Picker   | ✅ Yes                | Valid date in YYYY-MM-DD format                               | -                                    |
| **End Date**                              | Date Picker   | ✅ Yes                | Valid date; Must be >= Start Date                             | 🔴 Cannot be earlier than start date |
| **Accomplished Task/Activity**            | Textarea      | ✅ Yes                | Must provide detailed description                             | -                                    |
| **Status**                                | Dropdown      | ✅ Yes                | Must select a valid active status                             | -                                    |
| **Final Task for Deliverable/Milestone?** | Checkbox      | Optional              | -                                                             | Triggers conditional fields below    |
| **Milestone**                             | Dropdown      | ⚠️ Conditional        | Required if "Final Task" is checked                           | Must select valid milestone          |
| **Target Deadline for Milestone**         | Date Picker   | ⚠️ Conditional        | Required if "Final Task" is checked                           | Valid date in YYYY-MM-DD format      |
| **Actual Date of Submission**             | Date Picker   | ⚠️ Conditional        | Required if "Final Task" is checked                           | Valid date in YYYY-MM-DD format      |
| **Within Target?**                        | Radio Buttons | ⚠️ Conditional        | Required if "Final Task" is checked                           | Yes/No options                       |
| **Reason for Delay**                      | Textarea      | ⚠️ Double Conditional | Required if "Final Task" is checked AND "Within Target" is No | Explain delay reason                 |

### Business Rules

- 🔴 **Date Validation**: End date must not be earlier than start date (server-side validation)
- **Conditional Field Visibility**: When "Final Task for Deliverable/Milestone?" is unchecked, all milestone-related fields are hidden and not validated
- **Double Conditional Logic**: "Reason for Delay" field only appears and is required when:
  1. "Final Task for Deliverable/Milestone?" is checked, AND
  2. "Within Target?" is set to "No"

### Validation Messages

- End date error: "End date must not be earlier than start date."
- Milestone fields error: "(Field name) is required when this is a final task."
- Reason for delay error: "Reason for delay is required when submission is not within target deadline."

### JavaScript Behavior

- Real-time show/hide of conditional fields based on checkbox/radio selections
- Client-side validation matches server-side conditional logic
- Fields are disabled/enabled dynamically to prevent invalid submissions

---

## General Validation Principles

### 1. Required Field Indicators

- All required fields are marked with a red asterisk <span style="color: red;">\*</span> next to the label
- Help text below each field provides additional context
- Form instruction banner at the top of each form explains the asterisk convention

### 2. Error Display

- **Field-level errors**: Display directly below the problematic field in red text
- **Color coding**: Invalid fields show red error messages
- **Real-time feedback**: Validation occurs on form submission and on field blur events

### 3. Date Validation Pattern

```php
// All date comparison validations use this pattern:
[['end_date'], 'compare',
    'compareAttribute' => 'start_date',
    'operator' => '>=',
    'message' => 'End date must not be earlier than start date.'
]
```

### 4. Conditional Validation Pattern

```php
// Workplan template name
[['template_name'], 'required',
    'when' => function($model) {
        return $model->is_template == true;
    },
    'message' => 'Template name is required when saving as template.'
]

// Accomplishment final task fields
[['milestone_id', 'target_deadline', 'actual_submission_date', 'within_target'], 'required',
    'when' => function($model) {
        return $model->is_final_task == true;
    }
]

// Accomplishment reason for delay (double conditional)
[['reason_for_delay'], 'required',
    'when' => function($model) {
        return $model->is_final_task == true && $model->within_target == false;
    }
]
```

### 5. Data Type Validations

- **Strings**: Max length constraints specified in database schema
- **Integers**: Type validation for IDs and foreign keys
- **Dates**: Safe date validation + comparison rules
- **Booleans**: Checkbox/radio inputs with explicit true/false/null handling
- **Text**: Unlimited length for descriptions and detailed content

---

## API Integration Validation

### Project Assignment API

- **Endpoint**: `http://localhost/pmisv2/backend/web/api/projects` (dev) or `https://projects.pids.gov.ph/api/projects` (production)
- **API Key**: Validated on every request
- **Field Mapping**:
  - API `project_id` → Local `project_id`
  - API `project_title` → Local `project_name`
- **Validation**: Projects must exist in API before assignment
- **Error Handling**: If API is unreachable, dropdown shows "No projects available - Check API connection"

---

## Access Control

### Role-Based Validation

- **Administrator**: Full access to all modules
- **Personnel**: Access to Dashboard, Tasks (Workplan & Accomplishment), Reports, Analytics
  - Settings menu restricted to Administrator only

### Data Filtering by User

- **Workplan**: Automatically filtered to show only logged-in user's assigned projects
- **Accomplishment**: Automatically filtered to show only logged-in user's workplans
- **Project Assignment**: No user filtering (admins can assign any researcher to any project)

---

## Browser Validation

### Client-Side Validation

- HTML5 required attributes on form inputs
- JavaScript validation for conditional fields
- Real-time feedback on invalid selections
- Prevent form submission if validation fails

### Server-Side Validation

- All client-side validations are duplicated on server
- Additional business logic validation (date comparisons, conditional requirements)
- Database constraint validation (foreign keys, data types)
- Final safety net before data persistence

---

## Testing Validation Rules

### Manual Testing Checklist

#### Project Assignment

- [ ] Try to create assignment without selecting project
- [ ] Try to create assignment without selecting researcher
- [ ] Verify only unassigned projects appear in create form
- [ ] Verify project field is disabled in update form
- [ ] Verify multi-researcher selection works correctly

#### Workplan

- [ ] Try to submit with empty required fields
- [ ] Set end date before start date - should show error
- [ ] Check "Save as Template" without template name - should show error
- [ ] Verify task category dropdown updates when task type changes
- [ ] Load a template and verify all fields populate correctly

#### Accomplishment

- [ ] Try to submit with empty required fields
- [ ] Set end date before start date - should show error
- [ ] Check "Final Task" and verify conditional fields appear
- [ ] Try to submit final task without milestone - should show error
- [ ] Select "Within Target: No" and verify reason field appears and is required
- [ ] Uncheck "Final Task" and verify conditional fields are hidden and not validated

---

## Error Messages Reference

### Common Error Messages

| Field/Scenario                          | Error Message                                                                 |
| --------------------------------------- | ----------------------------------------------------------------------------- |
| Any required field empty                | "{Field name} cannot be blank."                                               |
| End date before start date              | "End date must not be earlier than start date."                               |
| Template name when saving as template   | "Template name is required when saving as template."                          |
| Milestone fields when final task        | "{Field name} is required when this is a final task."                         |
| Reason for delay when not within target | "Reason for delay is required when submission is not within target deadline." |
| Invalid foreign key                     | "{Field name} is invalid."                                                    |
| Project assignment validation           | "Please select at least one researcher."                                      |

---

## Maintenance Notes

### Adding New Validation Rules

1. Update model's `rules()` method in `common/models/`
2. Add field label to model's `attributeLabels()` method
3. Update view form to include field with proper required indicator (\*)
4. Add help text using `->hint()` method
5. Update this documentation file
6. Test both client-side and server-side validation

### Modifying Existing Rules

1. Check if rule affects existing data (may need migration)
2. Update model validation rules
3. Update form view if help text needs changes
4. Test thoroughly to ensure no data loss or validation bypass
5. Update this documentation

---

## Support and Questions

For questions about validation rules or to report validation bugs, contact the development team.

**Last Updated**: January 19, 2026  
**Version**: 2.0  
**Author**: EARS v2 Development Team
