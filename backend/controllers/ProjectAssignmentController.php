<?php

namespace backend\controllers;

use Yii;
use common\models\ProjectAssignment;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * ProjectAssignmentController implements the CRUD actions for ProjectAssignment model.
 */
class ProjectAssignmentController extends Controller
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
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->identity->role === \common\models\User::ROLE_ADMINISTRATOR;
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'delete-project' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all ProjectAssignment models.
     * @return string
     */
    public function actionIndex($search = null)
    {
        $matchingProjectIds = [];
        
        // Apply search filter if provided
        if ($search) {
            // Find projects matching the search term
            $projectQuery = ProjectAssignment::find()
                ->select('project_id')
                ->distinct()
                ->where([
                    'or',
                    ['like', 'project_name', $search],
                    ['like', 'project_id', $search],
                ]);
            
            // Also search in researcher names and get their project IDs
            $userIds = \common\models\User::find()
                ->select('id')
                ->where(['like', 'full_name', $search])
                ->column();
            
            if (!empty($userIds)) {
                $projectQuery->orWhere(['user_id' => $userIds]);
            }
            
            $matchingProjectIds = $projectQuery->column();
        }
        
        // Get all assignments
        $query = ProjectAssignment::find()->with('user');
        
        // Filter by matching projects if search was performed
        if ($search && !empty($matchingProjectIds)) {
            $query->where(['project_id' => $matchingProjectIds]);
        } elseif ($search && empty($matchingProjectIds)) {
            // No matches found, return empty result
            $query->where(['id' => -1]); // Force empty result
        }
        
        $assignments = $query->orderBy(['project_name' => SORT_ASC])->all();
        
        // Group assignments by project
        $groupedData = [];
        foreach ($assignments as $assignment) {
            $projectKey = $assignment->project_id;
            if (!isset($groupedData[$projectKey])) {
                $groupedData[$projectKey] = [
                    'id' => $assignment->id, // Use first assignment ID for actions
                    'project_id' => $assignment->project_id,
                    'project_name' => $assignment->project_name,
                    'researchers' => [],
                    'assignment_ids' => [],
                ];
            }
            $groupedData[$projectKey]['researchers'][] = $assignment->user->full_name;
            $groupedData[$projectKey]['assignment_ids'][] = $assignment->id;
        }

        return $this->render('index', [
            'groupedData' => $groupedData,
            'searchTerm' => $search,
        ]);
    }

    /**
     * Displays a single ProjectAssignment model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new ProjectAssignment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new ProjectAssignment();

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            
            // Handle multiple researchers
            if (isset($postData['researcher_ids']) && is_array($postData['researcher_ids'])) {
                $researcherIds = $postData['researcher_ids'];
                $projectId = $postData['ProjectAssignment']['project_id'] ?? null;
                $projectName = $postData['ProjectAssignment']['project_name'] ?? null;
                
                if ($projectId && $projectName && !empty($researcherIds)) {
                    $successCount = 0;
                    $errors = [];
                    
                    foreach ($researcherIds as $userId) {
                        $assignment = new ProjectAssignment();
                        $assignment->project_id = $projectId;
                        $assignment->project_name = $projectName;
                        $assignment->user_id = $userId;
                        
                        if ($assignment->save()) {
                            $successCount++;
                        } else {
                            $errors[] = $assignment->errors;
                        }
                    }
                    
                    if ($successCount > 0) {
                        Yii::$app->session->setFlash('success', "Successfully assigned {$successCount} researcher(s) to the project.");
                        return $this->redirect(['index']);
                    } else {
                        Yii::$app->session->setFlash('error', 'Failed to create project assignments.');
                    }
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ProjectAssignment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            
            // Handle multiple researchers
            if (isset($postData['researcher_ids']) && is_array($postData['researcher_ids'])) {
                $researcherIds = $postData['researcher_ids'];
                $projectId = $postData['ProjectAssignment']['project_id'] ?? null;
                $projectName = $postData['ProjectAssignment']['project_name'] ?? null;
                
                if ($projectId && $projectName && !empty($researcherIds)) {
                    // Delete ALL existing assignments for this project
                    ProjectAssignment::deleteAll(['project_id' => $projectId]);
                    
                    $successCount = 0;
                    $errors = [];
                    
                    foreach ($researcherIds as $userId) {
                        $assignment = new ProjectAssignment();
                        $assignment->project_id = $projectId;
                        $assignment->project_name = $projectName;
                        $assignment->user_id = $userId;
                        
                        if ($assignment->save()) {
                            $successCount++;
                        } else {
                            $errors[] = $assignment->errors;
                        }
                    }
                    
                    if ($successCount > 0) {
                        Yii::$app->session->setFlash('success', "Successfully updated. {$successCount} researcher(s) assigned to the project.");
                        return $this->redirect(['index']);
                    } else {
                        Yii::$app->session->setFlash('error', 'Failed to update project assignments.');
                    }
                }
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ProjectAssignment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Project Assignment deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Deletes all assignments for a specific project.
     * @param string $project_id Project ID
     * @return \yii\web\Response
     */
    public function actionDeleteProject($project_id)
    {
        $count = ProjectAssignment::deleteAll(['project_id' => $project_id]);
        Yii::$app->session->setFlash('success', "Successfully removed {$count} researcher(s) from the project.");

        return $this->redirect(['index']);
    }

    /**
     * Finds the ProjectAssignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return ProjectAssignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ProjectAssignment::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested project assignment does not exist.');
    }
}
