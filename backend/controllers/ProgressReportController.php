<?php

namespace backend\controllers;

use Yii;
use common\models\ProgressReport;
use common\models\ProjectAssignment;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

/**
 * ProgressReportController implements the CRUD actions for ProgressReport model.
 */
class ProgressReportController extends Controller
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
     * Lists all ProgressReport models.
     *
     * @return string
     */
    public function actionIndex($search = null, $month = null, $year = null)
    {
        $query = ProgressReport::find();

        // Check user role - administrators see all, personnel see reports from team members on the same projects
        if (Yii::$app->user->identity->role !== \common\models\User::ROLE_ADMINISTRATOR) {
            // Get user's assigned project IDs
            $userProjectIds = ProjectAssignment::find()
                ->select('project_id')
                ->where(['user_id' => Yii::$app->user->id])
                ->column();
            
            if (!empty($userProjectIds)) {
                // Get all users assigned to the same projects
                $teamMemberIds = ProjectAssignment::find()
                    ->select('user_id')
                    ->where(['project_id' => $userProjectIds])
                    ->distinct()
                    ->column();
                
                // Show reports from all team members on the same projects
                $query->where(['user_id' => $teamMemberIds]);
            } else {
                // If user has no project assignments, only show their own reports
                $query->where(['user_id' => Yii::$app->user->id]);
            }
        }

        $query->orderBy(['report_date' => SORT_DESC, 'created_at' => SORT_DESC]);

        if ($search) {
            $query->andWhere(['or',
                ['like', 'project_name', $search],
                ['like', 'milestone_name', $search],
                ['like', 'status', $search],
            ]);
        }

        if ($month && $year) {
            $query->andWhere(['>=', 'report_date', $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01'])
                  ->andWhere(['<', 'report_date', date('Y-m-01', strtotime("+1 month", strtotime($year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01')))]);
        } elseif ($year) {
            $query->andWhere(['>=', 'report_date', $year . '-01-01'])
                  ->andWhere(['<', 'report_date', ($year + 1) . '-01-01']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 12,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchQuery' => $search,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Displays a single ProgressReport model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Creates a new ProgressReport model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new ProgressReport();
        $model->user_id = Yii::$app->user->id;

        // Get assigned projects from database
        $assignedProjects = ArrayHelper::map(
            ProjectAssignment::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->all(),
            'project_id',
            'project_name'
        );

        if ($model->load(Yii::$app->request->post())) {
            // Handle file uploads
            $model->uploadedFiles = UploadedFile::getInstances($model, 'uploadedFiles');
            
            if ($model->uploadedFiles) {
                $uploadPath = Yii::getAlias('@backend/web/uploads/progress-reports/');
                FileHelper::createDirectory($uploadPath);
                
                $uploadedPaths = [];
                foreach ($model->uploadedFiles as $file) {
                    $fileName = time() . '_' . uniqid() . '.' . $file->extension;
                    $filePath = $uploadPath . $fileName;
                    
                    if ($file->saveAs($filePath)) {
                        $uploadedPaths[] = '/uploads/progress-reports/' . $fileName;
                    }
                }
                
                if (!empty($uploadedPaths)) {
                    $existingDocs = $model->getDocumentsArray();
                    $model->setDocumentsArray(array_merge($existingDocs, $uploadedPaths));
                }
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Progress report created successfully!');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'assignedProjects' => $assignedProjects,
        ]);
    }

    /**
     * Updates an existing ProgressReport model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        // Get assigned projects from database
        $assignedProjects = ArrayHelper::map(
            ProjectAssignment::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->all(),
            'project_id',
            'project_name'
        );

        if ($model->load(Yii::$app->request->post())) {
            // Handle file uploads
            $model->uploadedFiles = UploadedFile::getInstances($model, 'uploadedFiles');
            
            if ($model->uploadedFiles) {
                $uploadPath = Yii::getAlias('@backend/web/uploads/progress-reports/');
                FileHelper::createDirectory($uploadPath);
                
                $uploadedPaths = [];
                foreach ($model->uploadedFiles as $file) {
                    $fileName = time() . '_' . uniqid() . '.' . $file->extension;
                    $filePath = $uploadPath . $fileName;
                    
                    if ($file->saveAs($filePath)) {
                        $uploadedPaths[] = '/uploads/progress-reports/' . $fileName;
                    }
                }
                
                if (!empty($uploadedPaths)) {
                    $existingDocs = $model->getDocumentsArray();
                    $model->setDocumentsArray(array_merge($existingDocs, $uploadedPaths));
                }
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Progress report updated successfully!');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'assignedProjects' => $assignedProjects,
        ]);
    }

    /**
     * Deletes an existing ProgressReport model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Delete associated files
        $documents = $model->getDocumentsArray();
        if (!empty($documents)) {
            foreach ($documents as $doc) {
                $filePath = Yii::getAlias('@backend/web') . $doc;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
        
        $model->delete();
        Yii::$app->session->setFlash('success', 'Progress report deleted successfully!');

        return $this->redirect(['index']);
    }

    /**
     * Fetch projects from PMIS database and filter by assigned project IDs
     * @param array $assignedProjectIds
     * @return array
     */
    protected function fetchProjectsFromApi($assignedProjectIds)
    {
        try {
            if (empty($assignedProjectIds)) {
                return [];
            }
            
            // Query directly from pmis database
            $placeholders = implode(',', array_fill(0, count($assignedProjectIds), '?'));
            $query = Yii::$app->dbPmis->createCommand(
                "SELECT id, project_title FROM projects WHERE id IN ($placeholders) AND status = 10 ORDER BY project_title ASC",
                $assignedProjectIds
            );
            
            $results = $query->queryAll();
            
            $projects = [];
            foreach ($results as $project) {
                if (isset($project['id']) && isset($project['project_title'])) {
                    $projects[$project['id']] = $project['project_title'];
                }
            }
            
            return $projects;
        } catch (\Exception $e) {
            Yii::error('Error fetching projects from database: ' . $e->getMessage());
        }
        
        // Fallback to project assignments if database query fails
        return ArrayHelper::map(
            ProjectAssignment::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->all(),
            'project_id',
            'project_name'
        );
    }

    /**
     * Fetch deliverables for a project from PMIS database
     * @param string $projectId
     * @return array JSON response
     */
    public function actionGetDeliverables($projectId)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            // Query project information from pmis database
            $project = Yii::$app->dbPmis->createCommand(
                'SELECT id, project_code, project_title, project_description, project_start_date, project_end_date, 
                        project_price_amount, contract_number
                 FROM projects 
                 WHERE id = :project_id',
                [':project_id' => $projectId]
            )->queryOne();
            
            if (!$project) {
                return [
                    'success' => false,
                    'message' => 'Project not found for ID: ' . $projectId,
                ];
            }
            
            // Query investigators from proponents table
            $investigators = Yii::$app->dbPmis->createCommand(
                'SELECT p.id, p.name, p.email, p.type, pi.is_director
                 FROM proponents p
                 INNER JOIN project_investigator pi ON p.id = pi.proponent_id
                 WHERE pi.project_id = :project_id
                 ORDER BY pi.is_director DESC, pi.sort_order ASC',
                [':project_id' => $projectId]
            )->queryAll();
            
            // Query deliverables directly from pmis database
            $deliverables = Yii::$app->dbPmis->createCommand(
                'SELECT id, schedule_name, details, budget_allocation, target_date, submission_date, status, remarks 
                 FROM project_deliverable 
                 WHERE project_id = :project_id 
                 ORDER BY target_date ASC',
                [':project_id' => $projectId]
            )->queryAll();
            
            // Format deliverables for Select2 dropdown
            $formattedDeliverables = [];
            foreach ($deliverables as $deliverable) {
                $formattedDeliverables[] = [
                    'id' => $deliverable['id'] ?? uniqid(),
                    'text' => $deliverable['schedule_name'] ?? 'Unnamed Deliverable',
                    'details' => $deliverable['details'] ?? '',
                    'budget_allocation' => $deliverable['budget_allocation'] ?? '',
                    'target_date' => $deliverable['target_date'] ?? '',
                    'submission_date' => $deliverable['submission_date'] ?? '',
                    'status' => $deliverable['status'] ?? '',
                    'remarks' => $deliverable['remarks'] ?? '',
                ];
            }
            
            // Format investigators
            $formattedInvestigators = [];
            foreach ($investigators as $investigator) {
                $formattedInvestigators[] = [
                    'id' => $investigator['id'],
                    'name' => $investigator['name'],
                    'email' => $investigator['email'],
                    'type' => $investigator['type'],
                    'is_director' => $investigator['is_director'],
                ];
            }
            
            // Format project data
            $projectData = [
                'project_name' => $project['project_title'] ?? '',
                'project_description' => $project['project_description'] ?? '',
                'project_start_date' => $project['project_start_date'] ?? '',
                'project_end_date' => $project['project_end_date'] ?? '',
                'budget' => $project['project_price_amount'] ?? '',
                'contract_no' => $project['contract_number'] ?? '',
                'investigators' => $formattedInvestigators,
            ];

            return [
                'success' => true,
                'deliverables' => $formattedDeliverables,
                'projectData' => $projectData,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching deliverables: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a specific document from progress report
     * @param int $id Progress Report ID
     * @param string $doc Document path
     * @return \yii\web\Response
     */
    public function actionDeleteDocument($id, $doc)
    {
        $model = $this->findModel($id);
        
        $documents = $model->getDocumentsArray();
        $key = array_search($doc, $documents);
        
        if ($key !== false) {
            // Delete the file
            $filePath = Yii::getAlias('@backend/web') . $doc;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Remove from array and save
            unset($documents[$key]);
            $model->setDocumentsArray(array_values($documents));
            $model->save(false);
            
            Yii::$app->session->setFlash('success', 'Document deleted successfully!');
        }
        
        return $this->redirect(['update', 'id' => $id]);
    }

    /**
     * Lists all extension requests (Admin only)
     *
     * @return string
     */
    public function actionExtensionRequests($status = null, $month = null, $year = null)
    {
        // Check if user is administrator
        if (Yii::$app->user->identity->role !== \common\models\User::ROLE_ADMINISTRATOR) {
            throw new \yii\web\ForbiddenHttpException('You do not have permission to access this page.');
        }

        $query = ProgressReport::find()
            ->where(['has_extension' => 1])
            ->orderBy(['extension_status' => SORT_ASC, 'created_at' => SORT_DESC])
            ->with(['user']);

        // Apply status filter if provided
        if ($status !== null && in_array($status, [ProgressReport::EXTENSION_PENDING, ProgressReport::EXTENSION_APPROVED, ProgressReport::EXTENSION_REJECTED])) {
            $query->andWhere(['extension_status' => $status]);
        }

        // Apply month and year filter if provided
        if ($month !== null && $year !== null) {
            $startOfMonth = mktime(0, 0, 0, $month, 1, $year);
            $endOfMonth = mktime(23, 59, 59, $month, date('t', $startOfMonth), $year);
            $query->andWhere(['between', 'created_at', $startOfMonth, $endOfMonth]);
        } elseif ($year !== null) {
            $startOfYear = mktime(0, 0, 0, 1, 1, $year);
            $endOfYear = mktime(23, 59, 59, 12, 31, $year);
            $query->andWhere(['between', 'created_at', $startOfYear, $endOfYear]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('extension-requests', [
            'dataProvider' => $dataProvider,
            'statusFilter' => $status,
            'monthFilter' => $month,
            'yearFilter' => $year,
        ]);
    }

    /**
     * Process extension request (approve or reject) - Admin only
     *
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionProcessExtension($id)
    {
        // Check if user is administrator
        if (Yii::$app->user->identity->role !== \common\models\User::ROLE_ADMINISTRATOR) {
            throw new \yii\web\ForbiddenHttpException('You do not have permission to perform this action.');
        }

        $model = ProgressReport::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested report does not exist.');
        }

        if (!$model->has_extension) {
            Yii::$app->session->setFlash('error', 'This report does not have an extension request.');
            return $this->redirect(['view', 'id' => $id]);
        }

        if (Yii::$app->request->isPost) {
            $action = Yii::$app->request->post('action');
            
            if ($action === 'approve') {
                $approvedDate = Yii::$app->request->post('approved_date');
                if (empty($approvedDate)) {
                    Yii::$app->session->setFlash('error', 'Please provide an approved extension date.');
                    return $this->redirect(['extension-requests']);
                }
                
                $model->extension_status = ProgressReport::EXTENSION_APPROVED;
                $model->extension_approved_date = $approvedDate;
                $model->extension_rejection_reason = null;
                $model->extension_processed_by = Yii::$app->user->id;
                $model->extension_processed_at = time();
                
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Extension request approved successfully.');
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to approve extension request.');
                }
            } elseif ($action === 'reject') {
                $rejectionReason = Yii::$app->request->post('rejection_reason');
                if (empty($rejectionReason)) {
                    Yii::$app->session->setFlash('error', 'Please provide a rejection reason.');
                    return $this->redirect(['extension-requests']);
                }
                
                $model->extension_status = ProgressReport::EXTENSION_REJECTED;
                $model->extension_approved_date = null;
                $model->extension_rejection_reason = $rejectionReason;
                $model->extension_processed_by = Yii::$app->user->id;
                $model->extension_processed_at = time();
                
                if ($model->save(false)) {
                    Yii::$app->session->setFlash('success', 'Extension request rejected.');
                } else {
                    Yii::$app->session->setFlash('error', 'Failed to reject extension request.');
                }
            }
        }

        return $this->redirect(['extension-requests']);
    }

    /**
     * Finds the ProgressReport model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return ProgressReport the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        // Administrators can view all reports
        if (Yii::$app->user->identity->role === \common\models\User::ROLE_ADMINISTRATOR) {
            $model = ProgressReport::findOne(['id' => $id]);
        } else {
            // Personnel can view reports from team members on the same projects
            $model = ProgressReport::findOne(['id' => $id]);
            
            if ($model !== null) {
                // Check if the current user is on the same project as the report creator
                $userProjectIds = ProjectAssignment::find()
                    ->select('project_id')
                    ->where(['user_id' => Yii::$app->user->id])
                    ->column();
                
                $reportUserProjectIds = ProjectAssignment::find()
                    ->select('project_id')
                    ->where(['user_id' => $model->user_id])
                    ->column();
                
                // Check if there's any overlap in projects
                $sharedProjects = array_intersect($userProjectIds, $reportUserProjectIds);
                
                if (empty($sharedProjects) && $model->user_id !== Yii::$app->user->id) {
                    // User is not on the same team and not the owner
                    throw new NotFoundHttpException('The requested page does not exist.');
                }
            }
        }

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
