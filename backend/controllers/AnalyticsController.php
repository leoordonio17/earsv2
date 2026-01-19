<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\User;
use common\models\Workplan;
use common\models\Accomplishment;
use common\models\ProgressReport;
use common\models\ProjectAssignment;

/**
 * AnalyticsController provides comprehensive analytics and statistics
 */
class AnalyticsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Displays comprehensive analytics dashboard
     * @return string
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role === User::ROLE_ADMINISTRATOR;

        // === WORKPLAN ANALYTICS ===
        $workplanQuery = Workplan::find()->where(['is_template' => false]);
        if (!$isAdmin) {
            $workplanQuery->where(['user_id' => $user->id]);
        }

        $totalWorkplans = $workplanQuery->count();
        
        // Workplans by project stage
        $workplansByStage = [];
        $stageQuery = clone $workplanQuery;
        $stageData = $stageQuery
            ->select(['project_stage_id', 'COUNT(*) as count'])
            ->joinWith('projectStage')
            ->groupBy(['project_stage_id'])
            ->asArray()
            ->all();
        
        foreach ($stageData as $data) {
            $stage = \common\models\ProjectStage::findOne($data['project_stage_id']);
            if ($stage) {
                $workplansByStage[] = [
                    'stage' => $stage->name,
                    'count' => $data['count'],
                ];
            }
        }

        // Workplans by task type
        $workplansByTaskType = [];
        $taskTypeQuery = clone $workplanQuery;
        $taskTypeData = $taskTypeQuery
            ->select(['task_type_id', 'COUNT(*) as count'])
            ->joinWith('taskType')
            ->groupBy(['task_type_id'])
            ->asArray()
            ->all();
        
        foreach ($taskTypeData as $data) {
            $taskType = \common\models\TaskType::findOne($data['task_type_id']);
            if ($taskType) {
                $workplansByTaskType[] = [
                    'type' => $taskType->name,
                    'count' => $data['count'],
                ];
            }
        }

        // Monthly workplan trends (last 12 months)
        $workplanMonthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $count = (clone $workplanQuery)
                ->andWhere(['>=', 'start_date', $month . '-01'])
                ->andWhere(['<', 'start_date', date('Y-m-d', strtotime($month . '-01 +1 month'))])
                ->count();
            $workplanMonthlyTrend[] = [
                'month' => date('M Y', strtotime($month . '-01')),
                'count' => $count,
            ];
        }

        // === ACCOMPLISHMENT ANALYTICS ===
        $accomplishmentQuery = Accomplishment::find();
        if (!$isAdmin) {
            $accomplishmentQuery->joinWith('workplan')
                ->andWhere(['workplan.user_id' => $user->id]);
        }

        $totalAccomplishments = $accomplishmentQuery->count();
        $finalTasksCount = (clone $accomplishmentQuery)->andWhere(['is_final_task' => true])->count();
        $withinTargetCount = (clone $accomplishmentQuery)
            ->andWhere(['is_final_task' => true])
            ->andWhere(['within_target' => true])
            ->count();

        // Accomplishments by status
        $accomplishmentsByStatus = [];
        $statusQuery = clone $accomplishmentQuery;
        $statusData = $statusQuery
            ->select(['status_id', 'COUNT(*) as count'])
            ->joinWith('status')
            ->groupBy(['status_id'])
            ->asArray()
            ->all();
        
        foreach ($statusData as $data) {
            $status = \common\models\Status::findOne($data['status_id']);
            if ($status) {
                $accomplishmentsByStatus[] = [
                    'status' => $status->name,
                    'count' => $data['count'],
                    'color' => $status->color ?? '#ccc',
                ];
            }
        }

        // Mode of delivery distribution
        $modeOfDelivery = [];
        $modes = ['Onsite', 'WFH', 'Hybrid'];
        foreach ($modes as $mode) {
            $count = (clone $accomplishmentQuery)->andWhere(['mode_of_delivery' => $mode])->count();
            if ($count > 0) {
                $modeOfDelivery[] = [
                    'mode' => $mode,
                    'count' => $count,
                ];
            }
        }

        // Monthly accomplishment trends (last 12 months)
        $accomplishmentMonthlyTrend = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $count = (clone $accomplishmentQuery)
                ->andWhere(['>=', 'accomplishment.start_date', $month . '-01'])
                ->andWhere(['<', 'accomplishment.start_date', date('Y-m-d', strtotime($month . '-01 +1 month'))])
                ->count();
            $accomplishmentMonthlyTrend[] = [
                'month' => date('M Y', strtotime($month . '-01')),
                'count' => $count,
            ];
        }

        // === PROGRESS REPORT ANALYTICS ===
        $progressReportQuery = ProgressReport::find();
        if (!$isAdmin) {
            $progressReportQuery->where(['user_id' => $user->id]);
        }

        $totalProgressReports = $progressReportQuery->count();
        $completedReports = (clone $progressReportQuery)->andWhere(['status' => ProgressReport::STATUS_COMPLETED])->count();
        $ongoingReports = (clone $progressReportQuery)->andWhere(['status' => ProgressReport::STATUS_ONGOING])->count();
        $delayedReports = (clone $progressReportQuery)->andWhere(['status' => ProgressReport::STATUS_DELAYED])->count();
        $reportsWithExtension = (clone $progressReportQuery)->andWhere(['has_extension' => 1])->count();

        // Progress report status distribution
        $progressReportStatus = [
            ['status' => 'Completed', 'count' => $completedReports, 'color' => '#4caf50'],
            ['status' => 'On-going', 'count' => $ongoingReports, 'color' => '#2196f3'],
            ['status' => 'Delayed', 'count' => $delayedReports, 'color' => '#f44336'],
        ];

        // === PROJECT ANALYTICS ===
        $projectQuery = ProjectAssignment::find();
        if (!$isAdmin) {
            $projectQuery->where(['user_id' => $user->id]);
        }
        $totalProjects = $projectQuery->count();

        // Top 5 projects by workplans
        $topProjects = [];
        if ($isAdmin) {
            $projectData = Workplan::find()
                ->select(['project_name', 'COUNT(*) as count'])
                ->where(['is_template' => false])
                ->groupBy(['project_name'])
                ->orderBy(['count' => SORT_DESC])
                ->limit(5)
                ->asArray()
                ->all();
        } else {
            $projectData = Workplan::find()
                ->select(['project_name', 'COUNT(*) as count'])
                ->where(['user_id' => $user->id, 'is_template' => false])
                ->groupBy(['project_name'])
                ->orderBy(['count' => SORT_DESC])
                ->limit(5)
                ->asArray()
                ->all();
        }
        
        foreach ($projectData as $data) {
            $topProjects[] = [
                'project' => $data['project_name'],
                'count' => $data['count'],
            ];
        }

        // === TEAM ANALYTICS (Admin only) ===
        $teamStats = [];
        if ($isAdmin) {
            $personnel = User::find()
                ->where(['role' => User::ROLE_PERSONNEL])
                ->limit(10)
                ->all();
            
            foreach ($personnel as $member) {
                $workplansCount = Workplan::find()
                    ->where(['user_id' => $member->id, 'is_template' => false])
                    ->count();
                $accomplishmentsCount = Accomplishment::find()
                    ->joinWith('workplan')
                    ->andWhere(['workplan.user_id' => $member->id])
                    ->count();
                $reportsCount = ProgressReport::find()
                    ->where(['user_id' => $member->id])
                    ->count();
                
                $teamStats[] = [
                    'name' => $member->username,
                    'workplans' => $workplansCount,
                    'accomplishments' => $accomplishmentsCount,
                    'reports' => $reportsCount,
                ];
            }
        }

        // === COMPLETION RATE ANALYTICS ===
        $workplansWithAccomplishments = 0;
        if ($totalWorkplans > 0) {
            if ($isAdmin) {
                $workplansWithAccomplishments = Workplan::find()
                    ->where(['is_template' => false])
                    ->andWhere(['IN', 'id', 
                        Accomplishment::find()
                            ->select('workplan_id')
                            ->distinct()
                    ])
                    ->count();
            } else {
                $workplansWithAccomplishments = Workplan::find()
                    ->where(['user_id' => $user->id, 'is_template' => false])
                    ->andWhere(['IN', 'id', 
                        Accomplishment::find()
                            ->select('workplan_id')
                            ->distinct()
                    ])
                    ->count();
            }
        }

        $completionRate = $totalWorkplans > 0 ? round(($workplansWithAccomplishments / $totalWorkplans) * 100, 2) : 0;

        return $this->render('index', [
            'isAdmin' => $isAdmin,
            // Workplan data
            'totalWorkplans' => $totalWorkplans,
            'workplansByStage' => $workplansByStage,
            'workplansByTaskType' => $workplansByTaskType,
            'workplanMonthlyTrend' => $workplanMonthlyTrend,
            // Accomplishment data
            'totalAccomplishments' => $totalAccomplishments,
            'finalTasksCount' => $finalTasksCount,
            'withinTargetCount' => $withinTargetCount,
            'accomplishmentsByStatus' => $accomplishmentsByStatus,
            'modeOfDelivery' => $modeOfDelivery,
            'accomplishmentMonthlyTrend' => $accomplishmentMonthlyTrend,
            // Progress report data
            'totalProgressReports' => $totalProgressReports,
            'completedReports' => $completedReports,
            'ongoingReports' => $ongoingReports,
            'delayedReports' => $delayedReports,
            'reportsWithExtension' => $reportsWithExtension,
            'progressReportStatus' => $progressReportStatus,
            // Project data
            'totalProjects' => $totalProjects,
            'topProjects' => $topProjects,
            // Team data
            'teamStats' => $teamStats,
            // Metrics
            'completionRate' => $completionRate,
            'workplansWithAccomplishments' => $workplansWithAccomplishments,
        ]);
    }
}
