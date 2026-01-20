<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'User Guide & Tutorial';
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
    .help-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .help-header {
        background: linear-gradient(135deg, #2D1F13 0%, #3E2F1F 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .help-header h1 {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 600;
    }
    
    .help-header p {
        margin: 0;
        font-size: 16px;
        opacity: 0.9;
    }
    
    .export-btn {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        margin-top: 20px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
    }
    
    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(150, 114, 89, 0.4);
        color: white;
    }
    
    .toc {
        background: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .toc h3 {
        color: #2D1F13;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 20px;
    }
    
    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .toc-list li {
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .toc-list li:last-child {
        border-bottom: none;
    }
    
    .toc-list a {
        color: #3E2F1F;
        text-decoration: none;
        font-size: 15px;
        transition: color 0.3s ease;
        display: flex;
        align-items: center;
    }
    
    .toc-list a:hover {
        color: #967259;
    }
    
    .toc-icon {
        margin-right: 10px;
        font-size: 18px;
    }
    
    .help-section {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .help-section h2 {
        color: #2D1F13;
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 3px solid #967259;
        font-size: 24px;
    }
    
    .help-section h3 {
        color: #3E2F1F;
        margin-top: 25px;
        margin-bottom: 15px;
        font-size: 20px;
    }
    
    .help-section h4 {
        color: #5A4A3A;
        margin-top: 20px;
        margin-bottom: 10px;
        font-size: 16px;
    }
    
    .help-section p {
        line-height: 1.8;
        color: #333;
        margin-bottom: 15px;
    }
    
    .help-section ul, .help-section ol {
        line-height: 1.8;
        color: #333;
        margin-bottom: 15px;
    }
    
    .help-section li {
        margin-bottom: 8px;
    }
    
    .feature-box {
        background: #f8f9fa;
        border-left: 4px solid #967259;
        padding: 15px 20px;
        margin: 15px 0;
        border-radius: 5px;
    }
    
    .feature-box strong {
        color: #2D1F13;
        display: block;
        margin-bottom: 5px;
    }
    
    .note-box {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px 20px;
        margin: 15px 0;
        border-radius: 5px;
    }
    
    .note-box strong {
        color: #856404;
        display: block;
        margin-bottom: 5px;
    }
    
    .tip-box {
        background: #d1ecf1;
        border-left: 4px solid #0c5460;
        padding: 15px 20px;
        margin: 15px 0;
        border-radius: 5px;
    }
    
    .tip-box strong {
        color: #0c5460;
        display: block;
        margin-bottom: 5px;
    }
    
    .screenshot-placeholder {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 40px;
        text-align: center;
        margin: 20px 0;
        color: #6c757d;
    }
    
    .step-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        border-radius: 50%;
        text-align: center;
        line-height: 30px;
        font-weight: bold;
        margin-right: 10px;
    }
    
    .code-block {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        font-size: 14px;
        overflow-x: auto;
        margin: 15px 0;
    }
    
    .back-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .back-to-top:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.3);
        color: white;
    }
</style>

<div class="help-container">
    <!-- Header -->
    <div class="help-header">
        <h1>📚 EARS User Guide & Tutorial</h1>
        <p>Comprehensive guide to using the Electronic Accomplishment Reporting System</p>
        <?= Html::a('📄 Export to PDF', ['help/export-pdf'], ['class' => 'export-btn', 'data-method' => 'post']) ?>
    </div>
    
    <!-- Table of Contents -->
    <div class="toc">
        <h3>📑 Table of Contents</h3>
        <ul class="toc-list">
            <li><a href="#getting-started"><span class="toc-icon">🚀</span> Getting Started</a></li>
            <li><a href="#dashboard"><span class="toc-icon">🏠</span> Dashboard Overview</a></li>
            <li><a href="#workplan"><span class="toc-icon">📋</span> Managing Workplans</a></li>
            <li><a href="#accomplishment"><span class="toc-icon">✅</span> Recording Accomplishments</a></li>
            <li><a href="#progress-report"><span class="toc-icon">📈</span> Progress Reports</a></li>
            <li><a href="#extension-requests"><span class="toc-icon">⏰</span> Extension Requests (Admin)</a></li>
            <li><a href="#analytics"><span class="toc-icon">📊</span> Analytics & Insights</a></li>
            <li><a href="#profile"><span class="toc-icon">👤</span> Profile Management</a></li>
            <li><a href="#faq"><span class="toc-icon">❓</span> FAQ & Troubleshooting</a></li>
        </ul>
    </div>
    
    <!-- Getting Started -->
    <div id="getting-started" class="help-section">
        <h2>🚀 Getting Started</h2>
        
        <h3>Welcome to EARS</h3>
        <p>The Electronic Accomplishment Reporting System (EARS) is designed to help you manage your work activities, track progress, and generate comprehensive reports. This guide will walk you through all the features available to you.</p>
        
        <h3>Logging In</h3>
        <ol>
            <li>Navigate to the EARS login page</li>
            <li>Enter your username and password</li>
            <li>Click the "Login" button</li>
        </ol>
        
        <div class="note-box">
            <strong>⚠️ Note:</strong>
            If you forget your password, contact your system administrator for assistance.
        </div>
        
        <h3>Understanding Your Role</h3>
        <p>EARS has two primary user roles:</p>
        <div class="feature-box">
            <strong>👤 Personnel:</strong>
            Can create and manage their own workplans, accomplishments, and progress reports. View personal analytics.
        </div>
        <div class="feature-box">
            <strong>👨‍💼 Administrator:</strong>
            Has all personnel capabilities plus the ability to approve/reject extension requests, view all users' data, and access system-wide analytics.
        </div>
    </div>
    
    <!-- Dashboard -->
    <div id="dashboard" class="help-section">
        <h2>🏠 Dashboard Overview</h2>
        
        <h3>Understanding the Dashboard</h3>
        <p>The dashboard provides a quick overview of your work status with the following sections:</p>
        
        <h4>Statistics Cards</h4>
        <ul>
            <li><strong>Active Projects:</strong> Number of currently active projects</li>
            <li><strong>Completed Reports:</strong> Total progress reports marked as completed</li>
            <li><strong>On-going Reports:</strong> Progress reports currently in progress</li>
            <li><strong>Delayed Reports:</strong> Reports that are past their deadline</li>
            <li><strong>Extension Requests:</strong> Number of pending extension requests</li>
            <li><strong>Incoming Deliverables:</strong> Tasks due within the next 30 days</li>
        </ul>
        
        <h4>Charts & Visualizations</h4>
        <ul>
            <li><strong>Monthly Activity Trends:</strong> Line chart showing your activity over the past 6 months</li>
            <li><strong>Status Distribution:</strong> Doughnut chart showing the breakdown of report statuses</li>
        </ul>
        
        <h4>Alerts & Notifications</h4>
        <div class="feature-box">
            <strong>⚠️ Workplans Without Accomplishments:</strong>
            This section highlights workplans that don't have any recorded accomplishments yet. Click "Add Accomplishment" to quickly create one.
        </div>
        
        <div class="tip-box">
            <strong>💡 Tip:</strong>
            Check your dashboard daily to stay on top of upcoming deadlines and pending tasks.
        </div>
    </div>
    
    <!-- Workplan -->
    <div id="workplan" class="help-section">
        <h2>📋 Managing Workplans</h2>
        
        <h3>What is a Workplan?</h3>
        <p>A workplan defines your planned activities, tasks, and deliverables. It serves as a blueprint for what you intend to accomplish.</p>
        
        <h3>Creating a Workplan</h3>
        <ol>
            <li><span class="step-number">1</span> Navigate to <strong>Tasks → Workplan</strong> from the sidebar</li>
            <li><span class="step-number">2</span> Click the <strong>"Create Workplan"</strong> button</li>
            <li><span class="step-number">3</span> Fill in the required fields:
                <ul>
                    <li><strong>Project:</strong> Select the project this workplan belongs to</li>
                    <li><strong>Milestone:</strong> Choose the relevant milestone</li>
                    <li><strong>Task Type:</strong> Select the type of task (Research, Development, etc.)</li>
                    <li><strong>Title:</strong> Provide a clear, descriptive title</li>
                    <li><strong>Description:</strong> Detailed description of the work to be done</li>
                    <li><strong>Start Date & End Date:</strong> Define the timeline</li>
                    <li><strong>Expected Output:</strong> What will be delivered</li>
                </ul>
            </li>
            <li><span class="step-number">4</span> Click <strong>"Save"</strong> to create the workplan</li>
        </ol>
        
        <h3>Viewing Workplans</h3>
        <p>The workplan list shows all your workplans with their status, dates, and project information. You can:</p>
        <ul>
            <li>Filter by project, status, or date range</li>
            <li>Search for specific workplans</li>
            <li>Sort by any column</li>
            <li>Click on a workplan to view detailed information</li>
        </ul>
        
        <h3>Editing & Deleting Workplans</h3>
        <p>From the workplan detail page, you can:</p>
        <ul>
            <li>Click <strong>"Update"</strong> to modify the workplan details</li>
            <li>Click <strong>"Delete"</strong> to remove the workplan (use with caution)</li>
        </ul>
        
        <div class="note-box">
            <strong>⚠️ Important:</strong>
            Deleting a workplan will also delete all associated accomplishments. This action cannot be undone.
        </div>
        
        <div class="tip-box">
            <strong>💡 Best Practice:</strong>
            Break down large projects into multiple smaller workplans for better tracking and management.
        </div>
    </div>
    
    <!-- Accomplishment -->
    <div id="accomplishment" class="help-section">
        <h2>✅ Recording Accomplishments</h2>
        
        <h3>What is an Accomplishment?</h3>
        <p>An accomplishment represents the actual work completed against a workplan. It tracks what you've done, when, and the outcomes achieved.</p>
        
        <h3>Creating an Accomplishment</h3>
        <ol>
            <li><span class="step-number">1</span> Navigate to <strong>Tasks → Accomplishment</strong> from the sidebar</li>
            <li><span class="step-number">2</span> Click the <strong>"Create Accomplishment"</strong> button</li>
            <li><span class="step-number">3</span> Fill in the required fields:
                <ul>
                    <li><strong>Workplan:</strong> Select the related workplan</li>
                    <li><strong>Activity Title:</strong> Brief title of what was accomplished</li>
                    <li><strong>Description:</strong> Detailed description of the work done</li>
                    <li><strong>Start Date & End Date:</strong> When the work was performed</li>
                    <li><strong>Status:</strong> Current status (Completed, In Progress, Pending)</li>
                    <li><strong>Percentage Completion:</strong> How much of the work is done (0-100%)</li>
                    <li><strong>Mode of Delivery:</strong> Onsite, Work From Home, or Hybrid</li>
                    <li><strong>Actual Output:</strong> What was actually delivered</li>
                </ul>
            </li>
            <li><span class="step-number">4</span> Click <strong>"Save"</strong> to record the accomplishment</li>
        </ol>
        
        <h3>Quick Add from Dashboard</h3>
        <p>You can quickly add accomplishments for workplans without any from the dashboard:</p>
        <ol>
            <li>Find the "Workplans Without Accomplishments" section</li>
            <li>Click the <strong>"Add Accomplishment"</strong> button next to any workplan</li>
            <li>The workplan will be pre-selected for you</li>
        </ol>
        
        <h3>Updating Progress</h3>
        <p>Regularly update your accomplishments to reflect current progress:</p>
        <ul>
            <li>Adjust the percentage completion as work progresses</li>
            <li>Update the status when tasks are completed</li>
            <li>Add notes or observations in the description</li>
        </ul>
        
        <div class="tip-box">
            <strong>💡 Tip:</strong>
            Record accomplishments regularly (daily or weekly) for accurate tracking and easier report generation.
        </div>
    </div>
    
    <!-- Progress Report -->
    <div id="progress-report" class="help-section">
        <h2>📈 Progress Reports</h2>
        
        <h3>About Progress Reports</h3>
        <p>Progress reports provide a formal summary of your accomplishments over a specific period. They are linked to project milestones and can include extension requests if needed.</p>
        
        <h3>Creating a Progress Report</h3>
        <ol>
            <li><span class="step-number">1</span> Navigate to <strong>Progress Report</strong> from the sidebar</li>
            <li><span class="step-number">2</span> Click the <strong>"Create Progress Report"</strong> button</li>
            <li><span class="step-number">3</span> Select the <strong>Project</strong> and <strong>Milestone</strong></li>
            <li><span class="step-number">4</span> Fill in the report details:
                <ul>
                    <li><strong>Report Period:</strong> Start and end dates for this report</li>
                    <li><strong>Status:</strong> Completed, On-going, or Delayed</li>
                    <li><strong>Summary:</strong> Overview of activities during the period</li>
                    <li><strong>Accomplishments:</strong> Detailed list of what was achieved</li>
                    <li><strong>Challenges:</strong> Any issues or obstacles encountered</li>
                    <li><strong>Next Steps:</strong> Plans for the upcoming period</li>
                </ul>
            </li>
            <li><span class="step-number">5</span> <strong>Upload Supporting Documents</strong> (optional but recommended)</li>
            <li><span class="step-number">6</span> Click <strong>"Submit"</strong> to create the report</li>
        </ol>
        
        <h3>Requesting a Time Extension</h3>
        <p>If you need more time to complete a milestone, you can request an extension:</p>
        <ol>
            <li>When creating or updating a progress report, check the <strong>"Request Extension"</strong> option</li>
            <li>Enter the <strong>Proposed Extension Date</strong></li>
            <li>Provide a detailed <strong>Justification</strong> for the extension</li>
            <li>Submit the report</li>
        </ol>
        
        <div class="feature-box">
            <strong>Extension Request Status:</strong>
            <ul style="margin-top: 10px; margin-bottom: 0;">
                <li><span style="color: #ffc107;">●</span> <strong>Pending:</strong> Awaiting administrator review</li>
                <li><span style="color: #28a745;">●</span> <strong>Approved:</strong> Extension has been granted</li>
                <li><span style="color: #dc3545;">●</span> <strong>Rejected:</strong> Extension was not approved</li>
            </ul>
        </div>
        
        <h3>Viewing Reports</h3>
        <p>From the progress report list, you can:</p>
        <ul>
            <li>View all your submitted reports</li>
            <li>Filter by project, milestone, or status</li>
            <li>Click on a report to see full details</li>
            <li>Download attached documents</li>
        </ul>
        
        <div class="note-box">
            <strong>⚠️ Note:</strong>
            Once a progress report is submitted, you may need administrator permission to make changes depending on your organization's policies.
        </div>
    </div>
    
    <!-- Extension Requests (Admin) -->
    <div id="extension-requests" class="help-section">
        <h2>⏰ Extension Requests (Administrator Only)</h2>
        
        <h3>About Extension Request Management</h3>
        <p>As an administrator, you can review and process time extension requests submitted by personnel through their progress reports.</p>
        
        <h3>Accessing Extension Requests</h3>
        <ol>
            <li>Navigate to <strong>Extension Requests</strong> from the sidebar (only visible to administrators)</li>
            <li>You'll see all pending extension requests in a card-based layout</li>
        </ol>
        
        <h3>Reviewing a Request</h3>
        <p>Each extension request card displays:</p>
        <ul>
            <li><strong>Project Name and Milestone</strong></li>
            <li><strong>Report ID:</strong> Link to view the full progress report</li>
            <li><strong>Requested By:</strong> Personnel who submitted the request</li>
            <li><strong>Proposed Extension Date:</strong> New deadline requested</li>
            <li><strong>Request Date:</strong> When the extension was requested</li>
            <li><strong>Justification:</strong> Reason for the extension request</li>
        </ul>
        
        <h3>Approving an Extension Request</h3>
        <ol>
            <li><span class="step-number">1</span> Click the <strong>"Approve"</strong> button on the request card</li>
            <li><span class="step-number">2</span> A modal will appear with a date picker</li>
            <li><span class="step-number">3</span> Select the <strong>Approved Extension Date</strong>
                <ul>
                    <li>You can accept the proposed date or set a different one</li>
                </ul>
            </li>
            <li><span class="step-number">4</span> Click <strong>"Approve Extension"</strong> to confirm</li>
        </ol>
        
        <div class="tip-box">
            <strong>💡 Tip:</strong>
            Review the justification carefully and consider the project timeline before approving extensions.
        </div>
        
        <h3>Rejecting an Extension Request</h3>
        <ol>
            <li><span class="step-number">1</span> Click the <strong>"Reject"</strong> button on the request card</li>
            <li><span class="step-number">2</span> A modal will appear with a text area</li>
            <li><span class="step-number">3</span> Enter a detailed <strong>Rejection Reason</strong>
                <ul>
                    <li>Be clear and constructive in your feedback</li>
                    <li>Suggest alternatives if possible</li>
                </ul>
            </li>
            <li><span class="step-number">4</span> Click <strong>"Reject Extension"</strong> to confirm</li>
        </ol>
        
        <div class="note-box">
            <strong>⚠️ Important:</strong>
            Personnel will be notified of your decision. Always provide clear feedback, especially when rejecting requests.
        </div>
        
        <h3>Viewing Processed Requests</h3>
        <p>Processed requests (approved or rejected) show additional information:</p>
        <ul>
            <li><strong>Processed By:</strong> Administrator who handled the request</li>
            <li><strong>Processed Date:</strong> When the decision was made</li>
            <li><strong>Approved Date:</strong> (for approved requests) The granted extension date</li>
            <li><strong>Rejection Reason:</strong> (for rejected requests) Why it was declined</li>
        </ul>
    </div>
    
    <!-- Analytics -->
    <div id="analytics" class="help-section">
        <h2>📊 Analytics & Insights</h2>
        
        <h3>Accessing Analytics</h3>
        <p>Navigate to <strong>Analytics</strong> from the sidebar to view comprehensive statistics and visualizations of your work.</p>
        
        <h3>Overview Cards</h3>
        <p>At the top of the analytics page, you'll find key metrics:</p>
        <ul>
            <li><strong>Total Workplans:</strong> Number of workplans created</li>
            <li><strong>Total Accomplishments:</strong> Number of accomplishments recorded</li>
            <li><strong>Progress Reports:</strong> Total reports submitted</li>
            <li><strong>Active Projects:</strong> Currently active projects</li>
            <li><strong>Completion Rate:</strong> Percentage of completed tasks</li>
            <li><strong>On-Time Delivery:</strong> Percentage of work completed on schedule</li>
        </ul>
        
        <h3>Charts & Visualizations</h3>
        
        <h4>Monthly Activity Trends</h4>
        <p>Line chart showing workplans and accomplishments over the past 12 months. Helps identify patterns in your work activity.</p>
        
        <h4>Workplans by Project Stage</h4>
        <p>Doughnut chart breaking down your workplans by their current stage (Planning, Implementation, Review, etc.).</p>
        
        <h4>Workplans by Task Type</h4>
        <p>Doughnut chart showing the distribution of task types in your workplans.</p>
        
        <h4>Accomplishments by Status</h4>
        <p>Bar chart displaying accomplishments grouped by their status (Completed, In Progress, Pending).</p>
        
        <h4>Mode of Delivery Distribution</h4>
        <p>Pie chart showing the breakdown of work done Onsite, Work From Home, or Hybrid.</p>
        
        <h4>Progress Report Status</h4>
        <p>Doughnut chart with percentages for Completed, On-going, and Delayed reports.</p>
        
        <h4>Top 5 Projects by Workplans</h4>
        <p>Horizontal bar chart highlighting your most active projects.</p>
        
        <h3>Administrator Analytics</h3>
        <p>Administrators have access to additional analytics:</p>
        <ul>
            <li><strong>System-wide Data:</strong> View analytics for all personnel combined</li>
            <li><strong>Team Performance Table:</strong> Compare activity across team members</li>
            <li><strong>Department Insights:</strong> Aggregated statistics by department or unit</li>
        </ul>
        
        <div class="tip-box">
            <strong>💡 Tip:</strong>
            Use analytics to identify trends, optimize your workflow, and prepare for performance reviews.
        </div>
    </div>
    
    <!-- Profile -->
    <div id="profile" class="help-section">
        <h2>👤 Profile Management</h2>
        
        <h3>Accessing Your Profile</h3>
        <p>Click on your profile picture or name in the top-right corner, then select <strong>"My Profile"</strong> from the dropdown menu.</p>
        
        <h3>Updating Profile Information</h3>
        <p>To update your credentials, email address, or password, please visit the <strong>PIDS Document Tracking System (DTS)</strong>.</p>
        
        <p>All changes to your account information must be made through DTS and will automatically sync with EARS.</p>
        
        <div class="note-box">
            <strong>⚠️ Important Note:</strong>
            EARS retrieves user information from the PIDS Document Tracking System. Any updates to your profile, credentials, or password must be made in DTS. These changes will be reflected in EARS automatically during your next login.
        </div>
    </div>
    
    <!-- FAQ -->
    <div id="faq" class="help-section">
        <h2>❓ Frequently Asked Questions & Troubleshooting</h2>
        
        <h3>General Questions</h3>
        
        <h4>Q: How often should I update my accomplishments?</h4>
        <p><strong>A:</strong> It's recommended to update accomplishments at least weekly, or daily for very active projects. Regular updates ensure accurate reporting and make it easier to generate progress reports.</p>
        
        <h4>Q: Can I edit a workplan after creating accomplishments for it?</h4>
        <p><strong>A:</strong> Yes, you can edit workplans at any time. However, be aware that changes to dates or scope may affect related accomplishments and reports.</p>
        
        <h4>Q: What happens if I miss a deadline?</h4>
        <p><strong>A:</strong> If you anticipate missing a deadline, submit an extension request through your progress report. Explain the reason and propose a new date. Your administrator will review and approve or reject the request.</p>
        
        <h4>Q: Can I delete a progress report after submitting it?</h4>
        <p><strong>A:</strong> Deletion policies vary by organization. Contact your administrator if you need to remove or significantly modify a submitted report.</p>
        
        <h3>Troubleshooting</h3>
        
        <h4>Issue: I can't see the Extension Requests menu</h4>
        <p><strong>Solution:</strong> This menu is only visible to users with Administrator role. If you believe you should have access, contact your system administrator.</p>
        
        <h4>Issue: Charts are not displaying correctly</h4>
        <p><strong>Solution:</strong> Try the following steps:
        <ul>
            <li>Refresh the page (F5)</li>
            <li>Clear your browser cache</li>
            <li>Try a different browser (Chrome, Firefox, or Edge recommended)</li>
            <li>Ensure JavaScript is enabled in your browser</li>
        </ul>
        </p>
        
        <h4>Issue: File upload fails</h4>
        <p><strong>Solution:</strong> Check that:
        <ul>
            <li>File size is under the maximum limit (usually 10MB)</li>
            <li>File type is supported (PDF, DOC, DOCX, JPG, PNG)</li>
            <li>You have a stable internet connection</li>
        </ul>
        </p>
        
        <h4>Issue: Data doesn't appear in analytics</h4>
        <p><strong>Solution:</strong> Analytics are based on completed records. Ensure you have:
        <ul>
            <li>Created workplans with proper dates</li>
            <li>Recorded accomplishments with status updates</li>
            <li>Submitted progress reports</li>
            <li>Waited a few minutes for data to refresh</li>
        </ul>
        </p>
        
        <h3>Getting Help</h3>
        <p>If you continue to experience issues or have questions not covered in this guide:</p>
        <ul>
            <li>Contact your system administrator</li>
            <li>Submit a support ticket through the helpdesk</li>
            <li>Refer to your organization's IT support resources</li>
        </ul>
        
        <div class="tip-box">
            <strong>💡 Pro Tip:</strong>
            Bookmark this help page for quick reference. You can also export it as a PDF for offline access!
        </div>
    </div>
</div>

<!-- Back to Top Button -->
<a href="#" class="back-to-top" title="Back to Top">▲</a>

<script>
// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Show/hide back to top button
window.addEventListener('scroll', function() {
    const backToTop = document.querySelector('.back-to-top');
    if (window.pageYOffset > 300) {
        backToTop.style.display = 'flex';
    } else {
        backToTop.style.display = 'none';
    }
});

// Initially hide back to top button
document.querySelector('.back-to-top').style.display = 'none';
</script>
