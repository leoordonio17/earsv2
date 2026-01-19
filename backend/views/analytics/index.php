<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $isAdmin bool */
/* @var $totalWorkplans int */
/* @var $workplansByStage array */
/* @var $workplansByTaskType array */
/* @var $workplanMonthlyTrend array */
/* @var $totalAccomplishments int */
/* @var $finalTasksCount int */
/* @var $withinTargetCount int */
/* @var $accomplishmentsByStatus array */
/* @var $modeOfDelivery array */
/* @var $accomplishmentMonthlyTrend array */
/* @var $totalProgressReports int */
/* @var $completedReports int */
/* @var $ongoingReports int */
/* @var $delayedReports int */
/* @var $reportsWithExtension int */
/* @var $progressReportStatus array */
/* @var $totalProjects int */
/* @var $topProjects array */
/* @var $teamStats array */
/* @var $completionRate float */
/* @var $workplansWithAccomplishments int */

$this->title = 'Analytics & Insights';
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<style>
    .analytics-container {
        padding: 30px;
        background: linear-gradient(135deg, #f8f5f1 0%, #fff 100%);
        min-height: 100vh;
    }

    .analytics-header {
        margin-bottom: 30px;
    }

    .analytics-header h1 {
        font-size: 32px;
        font-weight: 700;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .analytics-header p {
        color: #666;
        font-size: 16px;
        margin: 0;
    }

    .overview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .overview-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 5px solid;
        position: relative;
        overflow: hidden;
    }

    .overview-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, rgba(150, 114, 89, 0.1) 0%, transparent 100%);
        border-radius: 0 15px 0 100%;
    }

    .overview-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(150, 114, 89, 0.2);
    }

    .overview-card.primary {
        border-left-color: #967259;
    }

    .overview-card.success {
        border-left-color: #4caf50;
    }

    .overview-card.info {
        border-left-color: #2196f3;
    }

    .overview-card.warning {
        border-left-color: #ff9800;
    }

    .overview-card.danger {
        border-left-color: #f44336;
    }

    .overview-icon {
        font-size: 32px;
        margin-bottom: 12px;
    }

    .overview-value {
        font-size: 36px;
        font-weight: 700;
        color: #2d1f13;
        margin-bottom: 8px;
    }

    .overview-label {
        font-size: 14px;
        color: #666;
        font-weight: 500;
    }

    .overview-subtitle {
        font-size: 12px;
        color: #999;
        margin-top: 8px;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    .chart-card.full-width {
        grid-column: 1 / -1;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        color: #2d1f13;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .team-table {
        width: 100%;
        border-collapse: collapse;
    }

    .team-table th {
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
    }

    .team-table td {
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
        font-size: 14px;
    }

    .team-table tr:hover {
        background: #f8f5f1;
    }

    .stat-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        background: #e8f5e9;
        color: #2e7d32;
    }

    .progress-bar-container {
        width: 100%;
        height: 8px;
        background: #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    @media (max-width: 1200px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .overview-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="analytics-container">
    <div class="analytics-header">
        <h1>📊 Analytics & Insights</h1>
        <p><?= $isAdmin ? 'Organization-wide analytics and performance metrics' : 'Your personal performance analytics and statistics' ?></p>
    </div>

    <!-- Overview Cards -->
    <div class="overview-grid">
        <div class="overview-card primary">
            <div class="overview-icon">📋</div>
            <div class="overview-value"><?= $totalWorkplans ?></div>
            <div class="overview-label">Total Workplans</div>
            <div class="overview-subtitle"><?= $workplansWithAccomplishments ?> with accomplishments</div>
        </div>

        <div class="overview-card success">
            <div class="overview-icon">✅</div>
            <div class="overview-value"><?= $totalAccomplishments ?></div>
            <div class="overview-label">Total Accomplishments</div>
            <div class="overview-subtitle"><?= $finalTasksCount ?> final tasks</div>
        </div>

        <div class="overview-card info">
            <div class="overview-icon">📈</div>
            <div class="overview-value"><?= $totalProgressReports ?></div>
            <div class="overview-label">Progress Reports</div>
            <div class="overview-subtitle"><?= $completedReports ?> completed</div>
        </div>

        <div class="overview-card warning">
            <div class="overview-icon">🎯</div>
            <div class="overview-value"><?= $totalProjects ?></div>
            <div class="overview-label">Active Projects</div>
            <div class="overview-subtitle">Assigned projects</div>
        </div>

        <div class="overview-card success">
            <div class="overview-icon">📊</div>
            <div class="overview-value"><?= $completionRate ?>%</div>
            <div class="overview-label">Completion Rate</div>
            <div class="overview-subtitle">Workplans with accomplishments</div>
        </div>

        <div class="overview-card <?= $withinTargetCount == $finalTasksCount ? 'success' : 'warning' ?>">
            <div class="overview-icon">⏱️</div>
            <div class="overview-value"><?= $finalTasksCount > 0 ? round(($withinTargetCount / $finalTasksCount) * 100) : 0 ?>%</div>
            <div class="overview-label">On-Time Delivery</div>
            <div class="overview-subtitle"><?= $withinTargetCount ?>/<?= $finalTasksCount ?> within target</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Monthly Trends Comparison -->
        <div class="chart-card full-width">
            <h3 class="chart-title">📈 Monthly Activity Trends (Last 12 Months)</h3>
            <div style="height: 300px; position: relative;">
                <canvas id="monthlyTrendsChart"></canvas>
            </div>
        </div>

        <!-- Workplans by Stage -->
        <?php if (!empty($workplansByStage)): ?>
        <div class="chart-card">
            <h3 class="chart-title">🎯 Workplans by Project Stage</h3>
            <canvas id="stageChart"></canvas>
        </div>
        <?php endif; ?>

        <!-- Workplans by Task Type -->
        <?php if (!empty($workplansByTaskType)): ?>
        <div class="chart-card">
            <h3 class="chart-title">📝 Workplans by Task Type</h3>
            <canvas id="taskTypeChart"></canvas>
        </div>
        <?php endif; ?>

        <!-- Accomplishments by Status -->
        <?php if (!empty($accomplishmentsByStatus)): ?>
        <div class="chart-card">
            <h3 class="chart-title">✅ Accomplishments by Status</h3>
            <canvas id="accomplishmentStatusChart"></canvas>
        </div>
        <?php endif; ?>

        <!-- Mode of Delivery -->
        <?php if (!empty($modeOfDelivery)): ?>
        <div class="chart-card">
            <h3 class="chart-title">🏢 Mode of Delivery Distribution</h3>
            <canvas id="modeOfDeliveryChart"></canvas>
        </div>
        <?php endif; ?>

        <!-- Progress Report Status -->
        <div class="chart-card">
            <h3 class="chart-title">📊 Progress Report Status</h3>
            <canvas id="progressReportChart"></canvas>
        </div>

        <!-- Top Projects -->
        <?php if (!empty($topProjects)): ?>
        <div class="chart-card">
            <h3 class="chart-title">🏆 Top 5 Projects by Workplans</h3>
            <canvas id="topProjectsChart"></canvas>
        </div>
        <?php endif; ?>
    </div>

    <!-- Team Performance (Admin Only) -->
    <?php if ($isAdmin && !empty($teamStats)): ?>
    <div class="chart-card" style="margin-bottom: 30px;">
        <h3 class="chart-title">👥 Team Performance Overview</h3>
        <table class="team-table">
            <thead>
                <tr>
                    <th>Team Member</th>
                    <th style="text-align: center;">Workplans</th>
                    <th style="text-align: center;">Accomplishments</th>
                    <th style="text-align: center;">Progress Reports</th>
                    <th style="text-align: center;">Activity</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teamStats as $stat): ?>
                <tr>
                    <td><strong><?= Html::encode($stat['name']) ?></strong></td>
                    <td style="text-align: center;">
                        <span class="stat-badge" style="background: #e3f2fd; color: #1976d2;">
                            <?= $stat['workplans'] ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="stat-badge" style="background: #e8f5e9; color: #2e7d32;">
                            <?= $stat['accomplishments'] ?>
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="stat-badge" style="background: #fff3e0; color: #e65100;">
                            <?= $stat['reports'] ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $totalActivity = $stat['workplans'] + $stat['accomplishments'] + $stat['reports'];
                        $maxActivity = max(array_column($teamStats, 'workplans')) + 
                                      max(array_column($teamStats, 'accomplishments')) + 
                                      max(array_column($teamStats, 'reports'));
                        $percentage = $maxActivity > 0 ? ($totalActivity / $maxActivity) * 100 : 0;
                        ?>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php
// Prepare data for charts
$workplanMonths = json_encode(array_column($workplanMonthlyTrend, 'month'));
$workplanCounts = json_encode(array_column($workplanMonthlyTrend, 'count'));

$accomplishmentMonths = json_encode(array_column($accomplishmentMonthlyTrend, 'month'));
$accomplishmentCounts = json_encode(array_column($accomplishmentMonthlyTrend, 'count'));

$stageLabels = json_encode(array_column($workplansByStage, 'stage'));
$stageCounts = json_encode(array_column($workplansByStage, 'count'));

$taskTypeLabels = json_encode(array_column($workplansByTaskType, 'type'));
$taskTypeCounts = json_encode(array_column($workplansByTaskType, 'count'));

$accomplishmentStatusLabels = json_encode(array_column($accomplishmentsByStatus, 'status'));
$accomplishmentStatusCounts = json_encode(array_column($accomplishmentsByStatus, 'count'));
$accomplishmentStatusColors = json_encode(array_column($accomplishmentsByStatus, 'color'));

$modeLabels = json_encode(array_column($modeOfDelivery, 'mode'));
$modeCounts = json_encode(array_column($modeOfDelivery, 'count'));

$progressStatusLabels = json_encode(array_column($progressReportStatus, 'status'));
$progressStatusCounts = json_encode(array_column($progressReportStatus, 'count'));
$progressStatusColors = json_encode(array_column($progressReportStatus, 'color'));

$topProjectLabels = json_encode(array_column($topProjects, 'project'));
$topProjectCounts = json_encode(array_column($topProjects, 'count'));
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const brownGradient = ['#967259', '#B8926A', '#D4A574', '#C19A6B', '#8B6F47'];
    const statusColors = ['#4caf50', '#2196f3', '#ff9800', '#f44336', '#9c27b0'];

    // Monthly Trends Chart
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyTrendsCtx) {
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: <?= $workplanMonths ?>,
                datasets: [{
                    label: 'Workplans',
                    data: <?= $workplanCounts ?>,
                    borderColor: '#967259',
                    backgroundColor: 'rgba(150, 114, 89, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }, {
                    label: 'Accomplishments',
                    data: <?= $accomplishmentCounts ?>,
                    borderColor: '#4caf50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 3,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Workplans by Stage Chart
    const stageCtx = document.getElementById('stageChart');
    if (stageCtx) {
        new Chart(stageCtx, {
            type: 'doughnut',
            data: {
                labels: <?= $stageLabels ?>,
                datasets: [{
                    data: <?= $stageCounts ?>,
                    backgroundColor: brownGradient,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Workplans by Task Type Chart
    const taskTypeCtx = document.getElementById('taskTypeChart');
    if (taskTypeCtx) {
        new Chart(taskTypeCtx, {
            type: 'doughnut',
            data: {
                labels: <?= $taskTypeLabels ?>,
                datasets: [{
                    data: <?= $taskTypeCounts ?>,
                    backgroundColor: statusColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Accomplishments by Status Chart
    const accomplishmentStatusCtx = document.getElementById('accomplishmentStatusChart');
    if (accomplishmentStatusCtx) {
        new Chart(accomplishmentStatusCtx, {
            type: 'bar',
            data: {
                labels: <?= $accomplishmentStatusLabels ?>,
                datasets: [{
                    label: 'Count',
                    data: <?= $accomplishmentStatusCounts ?>,
                    backgroundColor: <?= $accomplishmentStatusColors ?>,
                    borderWidth: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Mode of Delivery Chart
    const modeCtx = document.getElementById('modeOfDeliveryChart');
    if (modeCtx) {
        new Chart(modeCtx, {
            type: 'pie',
            data: {
                labels: <?= $modeLabels ?>,
                datasets: [{
                    data: <?= $modeCounts ?>,
                    backgroundColor: ['#2196f3', '#4caf50', '#ff9800'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Progress Report Chart
    const progressReportCtx = document.getElementById('progressReportChart');
    if (progressReportCtx) {
        new Chart(progressReportCtx, {
            type: 'doughnut',
            data: {
                labels: <?= $progressStatusLabels ?>,
                datasets: [{
                    data: <?= $progressStatusCounts ?>,
                    backgroundColor: <?= $progressStatusColors ?>,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Top Projects Chart
    const topProjectsCtx = document.getElementById('topProjectsChart');
    if (topProjectsCtx) {
        new Chart(topProjectsCtx, {
            type: 'bar',
            data: {
                labels: <?= $topProjectLabels ?>,
                datasets: [{
                    label: 'Workplans',
                    data: <?= $topProjectCounts ?>,
                    backgroundColor: 'rgba(150, 114, 89, 0.8)',
                    borderWidth: 0,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>
