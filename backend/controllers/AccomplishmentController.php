<?php

namespace backend\controllers;

use Yii;
use common\models\Accomplishment;
use common\models\Workplan;
use common\models\Status;
use common\models\Milestone;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * AccomplishmentController implements the CRUD actions for Accomplishment model.
 */
class AccomplishmentController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Workplans with accomplishments for the current user.
     * @return string
     */
    public function actionIndex($search = null, $group_id = null)
    {
        $userId = Yii::$app->user->id;

        // Get all workplan groups for filter dropdown (exclude template groups)
        $workplanGroups = \common\models\WorkplanGroup::find()
            ->where(['user_id' => $userId])
            ->andWhere(['or', ['is_template' => 0], ['is_template' => null]])
            ->orderBy(['id' => SORT_DESC])
            ->all();

        // If this is the initial page load (no query parameters at all)
        // default to the most recently created workplan group
        if (!isset($_GET['group_id']) && !isset($_GET['search']) && !empty($workplanGroups)) {
            $group_id = $workplanGroups[0]->id;
        }

        // Get workplans that belong to the user and have accomplishments (exclude templates)
        $query = Workplan::find()
            ->where(['user_id' => $userId])
            ->andWhere(['or', ['is_template' => 0], ['is_template' => null]])
            ->with(['workplanGroup', 'accomplishments']);

        // Apply workplan group filter if provided
        if ($group_id) {
            $query->andWhere(['workplan_group_id' => $group_id]);
        }

        // Apply search filter if provided
        if ($search) {
            $query->andWhere([
                'or',
                ['like', 'project_name', $search],
                ['like', 'workplan', $search],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC, // Sort by most recently created
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchTerm' => $search,
            'workplanGroups' => $workplanGroups,
            'selectedGroupId' => $group_id,
        ]);
    }

    /**
     * Displays accomplishments for a specific workplan.
     * @param int $id Workplan ID
     * @return string
     * @throws NotFoundHttpException if the workplan cannot be found
     */
    public function actionViewWorkplan($id)
    {
        $workplan = Workplan::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        
        if ($workplan === null) {
            throw new NotFoundHttpException('The requested workplan does not exist.');
        }

        $query = Accomplishment::find()
            ->where(['workplan_id' => $id])
            ->with(['status', 'milestone']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'defaultOrder' => [
                    'start_date' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('view-workplan', [
            'workplan' => $workplan,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Accomplishment model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        // Ensure user can only view their own accomplishments
        if ($model->workplan->user_id != Yii::$app->user->id) {
            throw new NotFoundHttpException('You are not authorized to view this accomplishment.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Accomplishment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($workplan_id = null)
    {
        $model = new Accomplishment();
        $model->is_final_task = false;
        $model->within_target = true;

        // Pre-populate workplan_id if provided
        if ($workplan_id) {
            $workplan = Workplan::findOne(['id' => $workplan_id, 'user_id' => Yii::$app->user->id]);
            if ($workplan) {
                $model->workplan_id = $workplan_id;
            }
        }

        if ($model->load(Yii::$app->request->post())) {
            // Validate that the workplan belongs to the current user
            $workplan = Workplan::findOne($model->workplan_id);
            if (!$workplan || $workplan->user_id != Yii::$app->user->id) {
                Yii::$app->session->setFlash('error', 'Invalid workplan selected.');
                return $this->render('create', ['model' => $model]);
            }

            // Handle conditional fields
            if (!$model->is_final_task) {
                $model->milestone_id = null;
                $model->target_deadline = null;
                $model->actual_submission_date = null;
                $model->within_target = null;
                $model->reason_for_delay = null;
            } else {
                if ($model->within_target) {
                    $model->reason_for_delay = null;
                }
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Accomplishment created successfully.');
                return $this->redirect(['view-workplan', 'id' => $model->workplan_id]);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to create accomplishment. Please check the form for errors.');
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Accomplishment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Ensure user can only update their own accomplishments
        if ($model->workplan->user_id != Yii::$app->user->id) {
            throw new NotFoundHttpException('You are not authorized to update this accomplishment.');
        }

        if ($model->load(Yii::$app->request->post())) {
            // Validate that the workplan belongs to the current user
            $workplan = Workplan::findOne($model->workplan_id);
            if (!$workplan || $workplan->user_id != Yii::$app->user->id) {
                Yii::$app->session->setFlash('error', 'Invalid workplan selected.');
                return $this->render('update', ['model' => $model]);
            }

            // Handle conditional fields
            if (!$model->is_final_task) {
                $model->milestone_id = null;
                $model->target_deadline = null;
                $model->actual_submission_date = null;
                $model->within_target = null;
                $model->reason_for_delay = null;
            } else {
                if ($model->within_target) {
                    $model->reason_for_delay = null;
                }
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Accomplishment updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::$app->session->setFlash('error', 'Failed to update accomplishment. Please check the form for errors.');
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Accomplishment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Ensure user can only delete their own accomplishments
        if ($model->workplan->user_id != Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'You are not authorized to delete this accomplishment.');
            return $this->redirect(['index']);
        }

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Accomplishment deleted successfully.');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', 'Failed to delete accomplishment: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Finds the Accomplishment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Accomplishment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Accomplishment::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
