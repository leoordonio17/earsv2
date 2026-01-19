<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $totalReports */
/** @var int $completedReports */
/** @var int $ongoingReports */
/** @var int $delayedReports */
/** @var int $reportsWithExtension */
/** @var int $totalProjects */
/** @var int $incomingDeliverables */
/** @var array $recentReports */
/** @var array $monthlyData */
/** @var array $statusData */
/** @var bool $isAdmin */
/** @var array $workplansWithoutAccomplishments */

$this->title = 'Dashboard';
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', ['position' => \yii\web\View::POS_HEAD]);
?>

<style>
    .dashboard-container {
        padding: 30px;
        background: linear-gradient(135deg, #967259 0%, #B8926A 100%);
        min-height: 100vh;
    }

    .dashboard-header {
        margin-bottom: 30px;
    }

    .dashboard-header h1 {
        color: white;
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 10px 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .dashboard-header p {
        color: rgba(255, 255, 255, 0.9);
        font-size: 16px;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        opacity: 0.1;
    }

    .stat-card.completed::before {
        background: #4caf50;
    }

    .stat-card.ongoing::before {
        background: #2196f3;
    }

    .stat-card.delayed::before {
        background: #f44336;
    }

    .stat-card.extension::before {
        background: #ff9800;
    }

    .stat-card.projects::before {
        background: #9c27b0;
    }

    .stat-card.incoming::before {
        background: #00bcd4;
    }

    .stat-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .stat-value {
        font-size: 36px;
        font-weight: 700;
        color: #2d3748;
        margin: 0 0 5px 0;
    }

    .stat-label {
        font-size: 14px;
        color: #718096;
        font-weight: 500;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .chart-section {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }

    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        color: #2d3748;
        margin: 0 0 20px 0;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }

    .recent-reports-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .report-item {
        padding: 15px;
        border-left: 4px solid #967259;
        background: #f7fafc;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .report-item:hover {
        background: #edf2f7;
        transform: translateX(5px);
    }

    .report-project {
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 5px;
    }

    .report-meta {
        font-size: 13px;
        color: #718096;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        color: white;
        margin-left: 8px;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #a0aec0;
    }

    @media (max-width: 1024px) {
        .chart-section {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>🏠 Dashboard</h1>
        <p><?= $isAdmin ? 'Overview of all progress reports across the organization' : 'Your personal progress report statistics' ?></p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card projects">
            <div class="stat-icon">🎯</div>
            <div class="stat-value"><?= $totalProjects ?></div>
            <div class="stat-label">Active Projects</div>
        </div>

        <div class="stat-card completed">
            <div class="stat-icon">✅</div>
            <div class="stat-value"><?= $completedReports ?></div>
            <div class="stat-label">Completed</div>
        </div>

        <div class="stat-card ongoing">
            <div class="stat-icon">⏳</div>
            <div class="stat-value"><?= $ongoingReports ?></div>
            <div class="stat-label">On-going</div>
        </div>

        <div class="stat-card delayed">
            <div class="stat-icon">⚠️</div>
            <div class="stat-value"><?= $delayedReports ?></div>
            <div class="stat-label">Delayed</div>
        </div>

        <div class="stat-card extension">
            <div class="stat-icon">📅</div>
            <div class="stat-value"><?= $reportsWithExtension ?></div>
            <div class="stat-label">Extensions</div>
        </div>

        <div class="stat-card incoming">
            <div class="stat-icon">📥</div>
            <div class="stat-value"><?= $incomingDeliverables ?></div>
            <div class="stat-label">Incoming (30d)</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="chart-section">
        <div class="chart-card">
            <h3 class="chart-title">📈 Monthly Progress Reports (Last 6 Months)</h3>
            <canvas id="monthlyChart" height="80"></canvas>
        </div>

        <div class="chart-card">
            <h3 class="chart-title">📊 Status Distribution</h3>
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    <!-- Workplans Without Accomplishments -->
    <?php if (!empty($workplansWithoutAccomplishments)): ?>
    <div class="recent-reports-card" style="margin-bottom: 30px;">
        <h3 class="chart-title">⚠️ Workplans Without Accomplishments</h3>
        <?php foreach ($workplansWithoutAccomplishments as $workplan): ?>
        <div style="padding: 15px; background: #fff9e6; border-left: 4px solid #ff9800; border-radius: 8px; margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: start; gap: 15px;">
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #2d1f13; margin-bottom: 8px;">
                        <?= Html::encode($workplan->project_name) ?>
                    </div>
                    <div style="font-size: 13px; color: #666; margin-bottom: 6px; max-height: 40px; overflow: hidden; text-overflow: ellipsis;">
                        <?= Html::encode($workplan->workplan) ?>
                    </div>
                    <div style="font-size: 12px; color: #888;">
                        📅 <?= Yii::$app->formatter->asDate($workplan->start_date, 'php:M d, Y') ?> - <?= Yii::$app->formatter->asDate($workplan->end_date, 'php:M d, Y') ?>
                    </div>
                </div>
                <div>
                    <?= Html::a('Add Accomplishment →', ['/accomplishment/create', 'workplan_id' => $workplan->id], [
                        'style' => 'background: #ff9800; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 13px; white-space: nowrap; display: inline-block;'
                    ]) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Recent Reports -->
    <div class="recent-reports-card">
        <h3 class="chart-title">📋 Recent Progress Reports</h3>
        <?php if (!empty($recentReports)): ?>
            <?php foreach ($recentReports as $report): ?>
                <div class="report-item">
                    <div class="report-project">
                        <?= Html::encode($report->project_name) ?>
                        <span class="status-badge" style="background: <?= $report->statusColor ?>;">
                            <?= Html::encode($report->status) ?>
                        </span>
                    </div>
                    <div class="report-meta">
                        <?= Html::encode($report->milestone_name ?? 'No milestone') ?> • 
                        <?= Yii::$app->formatter->asDate($report->report_date, 'php:F Y') ?>
                        <?php if ($isAdmin && $report->user): ?>
                            • by <?= Html::encode($report->user->username) ?>
                        <?php endif; ?>
                        <?php if ($report->has_extension): ?>
                            <span style="color: #f56565; font-weight: 600;">• Extension Requested</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <div style="text-align: center; margin-top: 20px;">
                <?= Html::a('View All Reports →', ['progress-report/index'], [
                    'class' => 'btn',
                    'style' => 'padding: 10px 24px; background: linear-gradient(135deg, #967259 0%, #B8926A 100%); color: white; border: none; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600;'
                ]) ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div style="font-size: 48px; margin-bottom: 15px;">📭</div>
                <p>No progress reports yet. Create your first report to get started!</p>
                <?= Html::a('➕ Create Report', ['progress-report/create'], [
                    'class' => 'btn',
                    'style' => 'padding: 10px 24px; background: linear-gradient(135deg, #967259 0%, #B8926A 100%); color: white; border: none; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; margin-top: 15px;'
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Prepare data for charts
$monthLabels = json_encode(array_column($monthlyData, 'month'));
$monthCounts = json_encode(array_column($monthlyData, 'count'));

$statusLabels = json_encode(array_column($statusData, 'status'));
$statusCounts = json_encode(array_column($statusData, 'count'));
$statusColors = json_encode(array_column($statusData, 'color'));

$this->registerJs("
    // Monthly Reports Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: $monthLabels,
            datasets: [{
                label: 'Number of Reports',
                data: $monthCounts,
                borderColor: '#967259',
                backgroundColor: 'rgba(150, 114, 89, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#967259',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#2d3748',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: '#e2e8f0'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: $statusLabels,
            datasets: [{
                data: $statusCounts,
                backgroundColor: $statusColors,
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 13,
                            weight: '600'
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: '#2d3748',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
");
?>
