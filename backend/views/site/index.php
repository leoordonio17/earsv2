<?php

/** @var yii\web\View $this */

$this->title = 'Dashboard';
?>

<div class="dashboard-overview">
    <div class="row g-4">
        <!-- Statistics Cards -->
        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #7B6043, #967259);">
                    📊
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">248</h3>
                    <p class="stat-label">Total Reports</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #967259, #B8926A);">
                    ✅
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">192</h3>
                    <p class="stat-label">Completed</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #B8926A, #D4A574);">
                    ⏳
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">42</h3>
                    <p class="stat-label">In Progress</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="dashboard-card stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #D4A574, #C19A6B);">
                    👥
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">38</h3>
                    <p class="stat-label">Active Users</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="dashboard-card">
                <h2 class="card-title">Recent Reports</h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>#001234</td>
                                <td>Q4 Financial Summary</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>Dec 8, 2025</td>
                            </tr>
                            <tr>
                                <td>#001233</td>
                                <td>Monthly Project Review</td>
                                <td><span class="badge bg-warning">In Progress</span></td>
                                <td>Dec 7, 2025</td>
                            </tr>
                            <tr>
                                <td>#001232</td>
                                <td>Annual Audit Report</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>Dec 6, 2025</td>
                            </tr>
                            <tr>
                                <td>#001231</td>
                                <td>Team Performance Analysis</td>
                                <td><span class="badge bg-success">Completed</span></td>
                                <td>Dec 5, 2025</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="dashboard-card">
                <h2 class="card-title">Quick Actions</h2>
                <div class="quick-actions">
                    <button class="btn-brown w-100 mb-3">
                        <span style="margin-right: 8px;">➕</span> Create New Report
                    </button>
                    <button class="btn-brown w-100 mb-3">
                        <span style="margin-right: 8px;">📥</span> Import Data
                    </button>
                    <button class="btn-brown w-100">
                        <span style="margin-right: 8px;">📤</span> Export Reports
                    </button>
                </div>
            </div>

            <div class="dashboard-card mt-4">
                <h2 class="card-title">System Status</h2>
                <div class="status-list">
                    <div class="status-item">
                        <span class="status-label">Database</span>
                        <span class="status-indicator status-online">Online</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">API Server</span>
                        <span class="status-indicator status-online">Online</span>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Backup Service</span>
                        <span class="status-indicator status-online">Online</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #5D4E37;
    margin: 0;
}

.stat-label {
    font-size: 14px;
    color: #967259;
    margin: 4px 0 0 0;
}

.quick-actions {
    margin-top: 16px;
}

.status-list {
    margin-top: 16px;
}

.status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #F5E6D3;
}

.status-item:last-child {
    border-bottom: none;
}

.status-label {
    font-weight: 500;
    color: #3E2F1F;
}

.status-indicator {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-online {
    background: #d4edda;
    color: #155724;
}
</style>

    </div>
</div>
