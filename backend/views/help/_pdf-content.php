<?php
// PDF content - simplified version of the help guide optimized for PDF export
?>

<style>
    h1 { color: #2D1F13; font-size: 24px; margin-bottom: 10px; }
    h2 { color: #3E2F1F; font-size: 20px; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid #967259; padding-bottom: 5px; }
    h3 { color: #5A4A3A; font-size: 16px; margin-top: 15px; margin-bottom: 8px; }
    h4 { color: #6A5A4A; font-size: 14px; margin-top: 12px; margin-bottom: 6px; }
    p, li { font-size: 11px; line-height: 1.6; color: #333; }
    ul, ol { margin-left: 20px; }
    .note-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0; }
    .tip-box { background-color: #d1ecf1; border-left: 4px solid #0c5460; padding: 10px; margin: 10px 0; }
    .feature-box { background-color: #f8f9fa; border-left: 4px solid #967259; padding: 10px; margin: 10px 0; }
</style>

<h1>📚 EARS User Guide & Tutorial</h1>
<p><strong>Electronic Accomplishment Reporting System</strong></p>
<p>Generated on: <?= date('F d, Y') ?></p>

<h2>Table of Contents</h2>
<ol>
    <li>Getting Started</li>
    <li>Dashboard Overview</li>
    <li>Managing Workplans</li>
    <li>Recording Accomplishments</li>
    <li>Progress Reports</li>
    <li>Extension Requests (Admin)</li>
    <li>Analytics & Insights</li>
    <li>Profile Management</li>
    <li>FAQ & Troubleshooting</li>
</ol>

<h2>1. Getting Started</h2>

<h3>Welcome to EARS</h3>
<p>The Electronic Accomplishment Reporting System (EARS) is designed to help you manage your work activities, track progress, and generate comprehensive reports.</p>

<h3>Logging In</h3>
<ol>
    <li>Navigate to the EARS login page</li>
    <li>Enter your username and password</li>
    <li>Click the "Login" button</li>
</ol>

<div class="note-box">
    <strong>Note:</strong> If you forget your password, contact your system administrator for assistance.
</div>

<h3>Understanding Your Role</h3>
<div class="feature-box">
    <strong>Personnel:</strong> Can create and manage their own workplans, accomplishments, and progress reports. View personal analytics.
</div>
<div class="feature-box">
    <strong>Administrator:</strong> Has all personnel capabilities plus the ability to approve/reject extension requests, view all users' data, and access system-wide analytics.
</div>

<h2>2. Dashboard Overview</h2>

<h3>Statistics Cards</h3>
<ul>
    <li><strong>Active Projects:</strong> Number of currently active projects</li>
    <li><strong>Completed Reports:</strong> Total progress reports marked as completed</li>
    <li><strong>On-going Reports:</strong> Progress reports currently in progress</li>
    <li><strong>Delayed Reports:</strong> Reports that are past their deadline</li>
    <li><strong>Extension Requests:</strong> Number of pending extension requests</li>
    <li><strong>Incoming Deliverables:</strong> Tasks due within the next 30 days</li>
</ul>

<h3>Charts & Visualizations</h3>
<ul>
    <li><strong>Monthly Activity Trends:</strong> Line chart showing your activity over the past 6 months</li>
    <li><strong>Status Distribution:</strong> Doughnut chart showing the breakdown of report statuses</li>
</ul>

<div class="tip-box">
    <strong>Tip:</strong> Check your dashboard daily to stay on top of upcoming deadlines and pending tasks.
</div>

<h2>3. Managing Workplans</h2>

<h3>What is a Workplan?</h3>
<p>A workplan defines your planned activities, tasks, and deliverables. It serves as a blueprint for what you intend to accomplish.</p>

<h3>Creating a Workplan</h3>
<ol>
    <li>Navigate to Tasks → Workplan from the sidebar</li>
    <li>Click the "Create Workplan" button</li>
    <li>Fill in the required fields:
        <ul>
            <li>Project: Select the project this workplan belongs to</li>
            <li>Milestone: Choose the relevant milestone</li>
            <li>Task Type: Select the type of task</li>
            <li>Title: Provide a clear, descriptive title</li>
            <li>Description: Detailed description of the work</li>
            <li>Start Date & End Date: Define the timeline</li>
            <li>Expected Output: What will be delivered</li>
        </ul>
    </li>
    <li>Click "Save" to create the workplan</li>
</ol>

<div class="tip-box">
    <strong>Best Practice:</strong> Break down large projects into multiple smaller workplans for better tracking.
</div>

<h2>4. Recording Accomplishments</h2>

<h3>What is an Accomplishment?</h3>
<p>An accomplishment represents the actual work completed against a workplan. It tracks what you've done, when, and the outcomes achieved.</p>

<h3>Creating an Accomplishment</h3>
<ol>
    <li>Navigate to Tasks → Accomplishment from the sidebar</li>
    <li>Click the "Create Accomplishment" button</li>
    <li>Fill in the required fields:
        <ul>
            <li>Workplan: Select the related workplan</li>
            <li>Activity Title: Brief title of what was accomplished</li>
            <li>Description: Detailed description of the work done</li>
            <li>Start Date & End Date: When the work was performed</li>
            <li>Status: Current status (Completed, In Progress, Pending)</li>
            <li>Percentage Completion: How much is done (0-100%)</li>
            <li>Mode of Delivery: Onsite, WFH, or Hybrid</li>
            <li>Actual Output: What was delivered</li>
        </ul>
    </li>
    <li>Click "Save" to record the accomplishment</li>
</ol>

<div class="tip-box">
    <strong>Tip:</strong> Record accomplishments regularly (daily or weekly) for accurate tracking.
</div>

<h2>5. Progress Reports</h2>

<h3>About Progress Reports</h3>
<p>Progress reports provide a formal summary of your accomplishments over a specific period. They are linked to project milestones and can include extension requests if needed.</p>

<h3>Creating a Progress Report</h3>
<ol>
    <li>Navigate to Progress Report from the sidebar</li>
    <li>Click the "Create Progress Report" button</li>
    <li>Select the Project and Milestone</li>
    <li>Fill in the report details:
        <ul>
            <li>Report Period: Start and end dates</li>
            <li>Status: Completed, On-going, or Delayed</li>
            <li>Summary: Overview of activities</li>
            <li>Accomplishments: What was achieved</li>
            <li>Challenges: Issues encountered</li>
            <li>Next Steps: Plans for upcoming period</li>
        </ul>
    </li>
    <li>Upload Supporting Documents (optional)</li>
    <li>Click "Submit" to create the report</li>
</ol>

<h3>Requesting a Time Extension</h3>
<ol>
    <li>Check the "Request Extension" option</li>
    <li>Enter the Proposed Extension Date</li>
    <li>Provide detailed Justification</li>
    <li>Submit the report</li>
</ol>

<div class="feature-box">
    <strong>Extension Status:</strong>
    <ul>
        <li>Pending: Awaiting administrator review</li>
        <li>Approved: Extension has been granted</li>
        <li>Rejected: Extension was not approved</li>
    </ul>
</div>

<h2>6. Extension Requests (Administrator Only)</h2>

<h3>About Extension Request Management</h3>
<p>Administrators can review and process time extension requests submitted by personnel through their progress reports.</p>

<h3>Approving an Extension</h3>
<ol>
    <li>Navigate to Extension Requests from the sidebar</li>
    <li>Click the "Approve" button on a request card</li>
    <li>Select the Approved Extension Date</li>
    <li>Click "Approve Extension" to confirm</li>
</ol>

<h3>Rejecting an Extension</h3>
<ol>
    <li>Click the "Reject" button on a request card</li>
    <li>Enter a detailed Rejection Reason</li>
    <li>Click "Reject Extension" to confirm</li>
</ol>

<div class="note-box">
    <strong>Important:</strong> Always provide clear feedback, especially when rejecting requests.
</div>

<h2>7. Analytics & Insights</h2>

<h3>Overview Metrics</h3>
<ul>
    <li>Total Workplans: Number of workplans created</li>
    <li>Total Accomplishments: Number of accomplishments recorded</li>
    <li>Progress Reports: Total reports submitted</li>
    <li>Active Projects: Currently active projects</li>
    <li>Completion Rate: Percentage of completed tasks</li>
    <li>On-Time Delivery: Percentage completed on schedule</li>
</ul>

<h3>Available Charts</h3>
<ul>
    <li>Monthly Activity Trends (12 months)</li>
    <li>Workplans by Project Stage</li>
    <li>Workplans by Task Type</li>
    <li>Accomplishments by Status</li>
    <li>Mode of Delivery Distribution</li>
    <li>Progress Report Status</li>
    <li>Top 5 Projects by Workplans</li>
    <li>Team Performance (Admin only)</li>
</ul>

<div class="tip-box">
    <strong>Tip:</strong> Use analytics to identify trends and optimize your workflow.
</div>

<h2>8. Profile Management</h2>

<h3>Accessing Your Profile</h3>
<p>Click on your profile picture or name in the top-right corner, then select "My Profile" from the dropdown menu.</p>

<h3>Updating Profile Information</h3>
<p>To update your credentials, email address, or password, please visit the <strong>PIDS Document Tracking System (DTS)</strong>.</p>

<p>All changes to your account information must be made through DTS and will automatically sync with EARS.</p>

<div class="note-box">
    <strong>Important Note:</strong> EARS retrieves user information from the PIDS Document Tracking System. Any updates to your profile, credentials, or password must be made in DTS. These changes will be reflected in EARS automatically during your next login.
</div>

<h2>9. FAQ & Troubleshooting</h2>

<h3>Common Questions</h3>

<h4>Q: How often should I update accomplishments?</h4>
<p>A: At least weekly, or daily for active projects. Regular updates ensure accurate reporting.</p>

<h4>Q: Can I edit a workplan after creating accomplishments?</h4>
<p>A: Yes, but changes to dates or scope may affect related accomplishments and reports.</p>

<h4>Q: What if I miss a deadline?</h4>
<p>A: Submit an extension request through your progress report explaining the reason and proposing a new date.</p>

<h3>Troubleshooting</h3>

<h4>Charts not displaying</h4>
<ul>
    <li>Refresh the page (F5)</li>
    <li>Clear browser cache</li>
    <li>Try a different browser (Chrome, Firefox, Edge)</li>
    <li>Ensure JavaScript is enabled</li>
</ul>

<h4>File upload fails</h4>
<ul>
    <li>Check file size (max 10MB)</li>
    <li>Verify file type is supported</li>
    <li>Ensure stable internet connection</li>
</ul>

<h4>Data missing in analytics</h4>
<ul>
    <li>Ensure records are completed properly</li>
    <li>Check date ranges are correct</li>
    <li>Wait a few minutes for data refresh</li>
</ul>

<h3>Getting Support</h3>
<p>If issues persist:</p>
<ul>
    <li>Contact your system administrator</li>
    <li>Submit a support ticket</li>
    <li>Refer to IT support resources</li>
</ul>

<hr>
<p style="text-align: center; font-size: 10px; color: #666;">
    <strong>EARS - Electronic Accomplishment Reporting System</strong><br>
    This document was generated on <?= date('F d, Y \a\t g:i A') ?><br>
    For the latest version, please visit the Help page in the system.
</p>
