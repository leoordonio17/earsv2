<?php

namespace backend\controllers;

use Yii;
use common\models\Workplan;
use common\models\WorkplanGroup;
use common\models\TaskType;
use common\models\TaskCategory;
use common\models\ProjectStage;
use common\models\ProjectAssignment;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * WorkplanController implements the CRUD actions for Workplan model.
 */
class WorkplanController extends Controller
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
     * Lists all Workplan Groups.
     * @return string
     */
    public function actionIndex($search = null, $view = 'groups')
    {
        $userId = Yii::$app->user->id;
        
        if ($view === 'templates') {
            // Show template groups
            $query = WorkplanGroup::find()
                ->where(['user_id' => $userId, 'is_template' => 1])
                ->with(['user', 'workplans']);
        } else {
            // Show regular workplan groups
            $query = WorkplanGroup::find()
                ->where(['user_id' => $userId])
                ->andWhere(['or', ['is_template' => 0], ['is_template' => null]])
                ->with(['user', 'workplans']);
        }

        // Apply search filter if provided
        if ($search) {
            $query->andWhere([
                'or',
                ['like', 'title', $search],
                ['like', 'description', $search],
                ['like', 'template_name', $search],
            ]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'start_date' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchTerm' => $search,
            'currentView' => $view,
        ]);
    }

    /**
     * Displays a single Workplan model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = Workplan::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        
        if ($model === null) {
            throw new NotFoundHttpException('The requested workplan does not exist.');
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Displays workplans within a group.
     * @param int $id WorkplanGroup ID
     * @return string
     * @throws NotFoundHttpException if the group cannot be found
     */
    public function actionViewGroup($id)
    {
        $group = \common\models\WorkplanGroup::findOne($id);
        
        if ($group === null || $group->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('The requested workplan group does not exist.');
        }

        $query = Workplan::find()
            ->where(['workplan_group_id' => $id])
            ->with(['taskType', 'taskCategory', 'projectStage']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
            'sort' => [
                'defaultOrder' => [
                    'start_date' => SORT_ASC,
                ]
            ],
        ]);

        return $this->render('view-group', [
            'group' => $group,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Workplan model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $userId = Yii::$app->user->id;
        $submittedData = [];

        if (Yii::$app->request->isPost) {
            $groupData = Yii::$app->request->post('WorkplanGroup', []);
            $workplans = Yii::$app->request->post('Workplan', []);
            
            // Preserve submitted data
            $submittedData = [
                'group' => $groupData,
                'workplans' => $workplans
            ];
            
            if (empty($groupData) || empty($workplans)) {
                Yii::$app->session->setFlash('error', 'Please fill in the workplan group details and add at least one workplan.');
                return $this->render('create', ['submittedData' => $submittedData]);
            }

            $savedCount = 0;
            $errors = [];
            $transaction = Yii::$app->db->beginTransaction();

            try {
                // Create workplan group first
                $group = new \common\models\WorkplanGroup();
                $group->attributes = $groupData;
                $group->user_id = $userId;

                if (!$group->save()) {
                    foreach ($group->getErrors() as $attribute => $messages) {
                        $errors[] = "Workplan Group - " . $attribute . ": " . implode(', ', $messages);
                    }
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Failed to create workplan group. Errors: ' . implode(' | ', $errors));
                    return $this->render('create', ['submittedData' => $submittedData]);
                }

                // Create individual workplans under the group
                foreach ($workplans as $index => $workplanData) {
                    $model = new Workplan();
                    $model->attributes = $workplanData;
                    $model->user_id = $userId;
                    $model->workplan_group_id = $group->id;
                    $model->is_template = 0; // Regular workplan, not a template

                    // Populate project_name from project_id if not set
                    if ($model->project_id && !$model->project_name) {
                        if ($model->project_id === 'NOT_PROJECT_RELATED') {
                            $model->project_name = 'Not Project Related';
                        } else {
                            $assignment = ProjectAssignment::findOne(['project_id' => $model->project_id, 'user_id' => $userId]);
                            if ($assignment) {
                                $model->project_name = $assignment->project_name;
                            }
                        }
                    }

                    if ($model->save()) {
                        $savedCount++;
                    } else {
                        foreach ($model->getErrors() as $attribute => $messages) {
                            $errors[] = "Workplan #" . $index . " - " . $attribute . ": " . implode(', ', $messages);
                        }
                    }
                }

                if (!empty($errors)) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Failed to create workplans. Errors: ' . implode(' | ', $errors));
                    return $this->render('create', ['submittedData' => $submittedData]);
                } else {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', "Successfully created workplan group '{$group->title}' with {$savedCount} workplan(s).");
                    return $this->redirect(['index']);
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
                return $this->render('create', ['submittedData' => $submittedData]);
            }
        }

        return $this->render('create', ['submittedData' => $submittedData]);
    }

    /**
     * Updates an existing Workplan model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = Workplan::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        
        if ($model === null) {
            throw new NotFoundHttpException('The requested workplan does not exist.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Workplan updated successfully!');
            return $this->redirect(['view-group', 'id' => $model->workplan_group_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Workplan model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = Workplan::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        
        if ($model === null) {
            throw new NotFoundHttpException('The requested workplan does not exist.');
        }

        $groupId = $model->workplan_group_id;
        $model->delete();

        Yii::$app->session->setFlash('success', 'Workplan deleted successfully!');
        return $this->redirect(['view-group', 'id' => $groupId]);
    }

    /**
     * Load template data via AJAX
     * @param int $id Template ID
     * @return array
     */
    public function actionLoadTemplate($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $template = Workplan::findOne(['id' => $id, 'is_template' => true]);

        if ($template) {
            return [
                'success' => true,
                'data' => [
                    'project_id' => $template->project_id,
                    'project_name' => $template->project_name,
                    'task_type_id' => $template->task_type_id,
                    'task_category_id' => $template->task_category_id,
                    'project_stage_id' => $template->project_stage_id,
                    'workplan' => $template->workplan,
                ],
            ];
        }

        return [
            'success' => false,
            'message' => 'Template not found.',
        ];
    }

    /**
     * Get task categories based on task type via AJAX
     * @param int $task_type_id Task Type ID
     * @return array
     */
    public function actionGetCategories($task_type_id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $categories = TaskCategory::find()
            ->select(['id', 'name'])
            ->where(['task_type_id' => $task_type_id, 'is_active' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->asArray()
            ->all();

        return [
            'success' => true,
            'categories' => $categories,
        ];
    }

    public function actionAddToGroup($groupId)
    {
        $group = WorkplanGroup::findOne(['id' => $groupId, 'user_id' => Yii::$app->user->id]);
        
        if ($group === null) {
            throw new NotFoundHttpException('Workplan group not found.');
        }

        $model = new Workplan();
        $model->user_id = Yii::$app->user->id;
        $model->workplan_group_id = $groupId;
        $model->is_template = 0; // Regular workplan, not a template

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Workplan added successfully!');
                return $this->redirect(['view-group', 'id' => $groupId]);
            }
        }

        return $this->render('add-to-group', [
            'model' => $model,
            'group' => $group,
        ]);
    }

    public function actionUpdateGroup($id)
    {
        $model = WorkplanGroup::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        
        if ($model === null) {
            throw new NotFoundHttpException('The requested workplan group does not exist.');
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Workplan group updated successfully!');
            return $this->redirect(['view-group', 'id' => $model->id]);
        }

        return $this->render('update-group', [
            'model' => $model,
        ]);
    }

    public function actionDeleteGroup($id)
    {
        $model = WorkplanGroup::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        
        if ($model === null) {
            throw new NotFoundHttpException('The requested workplan group does not exist.');
        }

        // Delete all workplans in this group first
        Workplan::deleteAll(['workplan_group_id' => $id]);
        
        // Delete the group
        $model->delete();

        Yii::$app->session->setFlash('success', 'Workplan group and all its workplans deleted successfully!');
        return $this->redirect(['index']);
    }

    /**
     * Save workplan group as template
     * @param int $id WorkplanGroup ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionSaveAsTemplate($id)
    {
        $group = WorkplanGroup::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        
        if ($group === null) {
            throw new NotFoundHttpException('The requested workplan group does not exist.');
        }

        if (Yii::$app->request->isPost) {
            $templateName = Yii::$app->request->post('template_name');
            
            if (empty($templateName)) {
                Yii::$app->session->setFlash('error', 'Template name is required.');
                return $this->redirect(['view-group', 'id' => $id]);
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Create a copy of the group as template
                $templateGroup = new WorkplanGroup();
                $templateGroup->attributes = $group->attributes;
                $templateGroup->id = null; // Reset ID for new record
                $templateGroup->is_template = 1;
                $templateGroup->template_name = $templateName;
                $templateGroup->title = $templateName;
                
                if (!$templateGroup->save()) {
                    throw new \Exception('Failed to save template group.');
                }

                // Copy all workplans in the group
                $workplans = Workplan::find()->where(['workplan_group_id' => $id])->all();
                foreach ($workplans as $workplan) {
                    $templateWorkplan = new Workplan();
                    $templateWorkplan->attributes = $workplan->attributes;
                    $templateWorkplan->id = null;
                    $templateWorkplan->workplan_group_id = $templateGroup->id;
                    $templateWorkplan->is_template = 1; // Mark as template
                    $templateWorkplan->template_name = null; // Templates don't need individual names
                    
                    if (!$templateWorkplan->save()) {
                        throw new \Exception('Failed to save template workplan.');
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Successfully saved '{$templateName}' as a template!");
                return $this->redirect(['index', 'view' => 'templates']);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error saving template: ' . $e->getMessage());
                return $this->redirect(['view-group', 'id' => $id]);
            }
        }

        return $this->redirect(['view-group', 'id' => $id]);
    }

    /**
     * Create workplan group from template
     * @param int $templateId Template WorkplanGroup ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionCreateFromTemplate($templateId)
    {
        $template = WorkplanGroup::findOne(['id' => $templateId, 'user_id' => Yii::$app->user->id, 'is_template' => 1]);
        
        if ($template === null) {
            throw new NotFoundHttpException('The requested template does not exist.');
        }

        $userId = Yii::$app->user->id;
        $newGroup = new WorkplanGroup();
        
        if (Yii::$app->request->isPost) {
            $groupData = Yii::$app->request->post('WorkplanGroup', []);
            
            if (empty($groupData['title']) || empty($groupData['start_date']) || empty($groupData['end_date'])) {
                Yii::$app->session->setFlash('error', 'Please fill in all required fields.');
                return $this->render('create-from-template', [
                    'template' => $template,
                    'newGroup' => $newGroup,
                ]);
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                // Create new group
                $newGroup->attributes = $groupData;
                $newGroup->user_id = $userId;
                $newGroup->is_template = 0;
                
                if (!$newGroup->save()) {
                    throw new \Exception('Failed to create workplan group.');
                }

                // Copy workplans from template
                $templateWorkplans = Workplan::find()->where(['workplan_group_id' => $templateId])->all();
                $savedCount = 0;
                
                foreach ($templateWorkplans as $templateWorkplan) {
                    $newWorkplan = new Workplan();
                    $newWorkplan->attributes = $templateWorkplan->attributes;
                    $newWorkplan->id = null;
                    $newWorkplan->workplan_group_id = $newGroup->id;
                    $newWorkplan->user_id = $userId;
                    $newWorkplan->is_template = 0; // Not a template, it's a regular workplan
                    $newWorkplan->template_name = null; // Clear template name
                    // Use the group's dates
                    $newWorkplan->start_date = $newGroup->start_date;
                    $newWorkplan->end_date = $newGroup->end_date;
                    
                    if ($newWorkplan->save()) {
                        $savedCount++;
                    } else {
                        throw new \Exception('Failed to create workplan.');
                    }
                }

                $transaction->commit();
                Yii::$app->session->setFlash('success', "Successfully created workplan group '{$newGroup->title}' with {$savedCount} workplan(s) from template!");
                return $this->redirect(['view-group', 'id' => $newGroup->id]);
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error creating from template: ' . $e->getMessage());
            }
        }

        return $this->render('create-from-template', [
            'template' => $template,
            'newGroup' => $newGroup,
        ]);
    }

    /**
     * Finds the Workplan model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Workplan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Workplan::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
