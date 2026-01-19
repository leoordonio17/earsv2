<?php

namespace backend\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 */
class SiteController extends Controller
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role === \common\models\User::ROLE_ADMINISTRATOR;

        // Get progress reports data
        $progressReportQuery = \common\models\ProgressReport::find();
        if (!$isAdmin) {
            $progressReportQuery->where(['user_id' => $user->id]);
        }

        $totalReports = $progressReportQuery->count();
        $completedReports = (clone $progressReportQuery)->andWhere(['status' => \common\models\ProgressReport::STATUS_COMPLETED])->count();
        $ongoingReports = (clone $progressReportQuery)->andWhere(['status' => \common\models\ProgressReport::STATUS_ONGOING])->count();
        $delayedReports = (clone $progressReportQuery)->andWhere(['status' => \common\models\ProgressReport::STATUS_DELAYED])->count();
        $reportsWithExtension = (clone $progressReportQuery)->andWhere(['has_extension' => 1])->count();

        // Get recent reports
        $recentReports = (clone $progressReportQuery)
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        // Get monthly report counts for chart (last 6 months)
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $count = (clone $progressReportQuery)
                ->andWhere(['like', 'report_date', $month])
                ->count();
            $monthlyData[] = [
                'month' => date('M Y', strtotime($month . '-01')),
                'count' => $count,
            ];
        }

        // Status distribution
        $statusData = [
            ['status' => 'Completed', 'count' => $completedReports, 'color' => '#4caf50'],
            ['status' => 'On-going', 'count' => $ongoingReports, 'color' => '#2196f3'],
            ['status' => 'Delayed', 'count' => $delayedReports, 'color' => '#f44336'],
        ];

        // Get project assignments count
        $projectQuery = \common\models\ProjectAssignment::find();
        if (!$isAdmin) {
            $projectQuery->where(['user_id' => $user->id]);
        }
        $totalProjects = $projectQuery->count();

        // Get incoming deliverables within next 30 days (on-going status)
        $today = date('Y-m-d');
        $thirtyDaysLater = date('Y-m-d', strtotime('+30 days'));
        $incomingDeliverables = (clone $progressReportQuery)
            ->andWhere(['status' => \common\models\ProgressReport::STATUS_ONGOING])
            ->andWhere(['>=', 'report_date', $today])
            ->andWhere(['<=', 'report_date', $thirtyDaysLater])
            ->count();

        // Get workplans without accomplishments
        $workplansWithoutAccomplishments = \common\models\Workplan::find()
            ->where(['user_id' => $user->id])
            ->andWhere(['is_template' => false])
            ->andWhere(['NOT IN', 'id', 
                \common\models\Accomplishment::find()
                    ->select('workplan_id')
                    ->distinct()
            ])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(5)
            ->all();

        return $this->render('index', [
            'totalReports' => $totalReports,
            'completedReports' => $completedReports,
            'ongoingReports' => $ongoingReports,
            'delayedReports' => $delayedReports,
            'reportsWithExtension' => $reportsWithExtension,
            'totalProjects' => $totalProjects,
            'incomingDeliverables' => $incomingDeliverables,
            'recentReports' => $recentReports,
            'monthlyData' => $monthlyData,
            'statusData' => $statusData,
            'isAdmin' => $isAdmin,
            'workplansWithoutAccomplishments' => $workplansWithoutAccomplishments,
        ]);
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
