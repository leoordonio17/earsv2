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

        // Check user role - administrators see all, personnel see only their own
        if (Yii::$app->user->identity->role !== \common\models\User::ROLE_ADMINISTRATOR) {
            $query->where(['user_id' => Yii::$app->user->id]);
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
     * Fetch projects from API and filter by assigned project IDs
     * @param array $assignedProjectIds
     * @return array
     */
    protected function fetchProjectsFromApi($assignedProjectIds)
    {
        try {
            $apiUrl = Yii::$app->params['apiBaseUrl'] . '/projects?api_key=' . Yii::$app->params['apiKey'];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                
                $projects = [];
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $project) {
                        // Only include projects assigned to this user
                        if (isset($project['project_id']) && in_array($project['project_id'], $assignedProjectIds)) {
                            $projects[$project['project_id']] = $project['project_name'] ?? 'Unnamed Project';
                        }
                    }
                }
                
                return $projects;
            }
        } catch (\Exception $e) {
            Yii::error('Error fetching projects from API: ' . $e->getMessage());
        }
        
        // Fallback to database if API fails
        return ArrayHelper::map(
            ProjectAssignment::find()
                ->where(['user_id' => Yii::$app->user->id])
                ->all(),
            'project_id',
            'project_name'
        );
    }

    /**
     * Fetch deliverables for a project from API
     * @param string $projectId
     * @return array JSON response
     */
    public function actionGetDeliverables($projectId)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $apiUrl = Yii::$app->params['apiBaseUrl'] . '/projects?api_key=' . Yii::$app->params['apiKey'];
            
            // Make API call using curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                
                // Log the API response for debugging
                Yii::info('API Response: ' . print_r($data, true), __METHOD__);
                Yii::info('Looking for project_id: ' . $projectId, __METHOD__);
                
                // Find the specific project by ID
                $selectedProject = null;
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $project) {
                        $projId = $project['id'] ?? null;
                        Yii::info('Checking project ID: ' . $projId, __METHOD__);
                        
                        // Check if project_assignment's project_id (e.g., "PROJ001") matches API's id
                        // Extract numeric ID from project_id like "PROJ001" -> 1
                        if (preg_match('/\d+$/', $projectId, $matches)) {
                            $numericId = (int)$matches[0];
                            if ($projId == $numericId) {
                                $selectedProject = $project;
                                Yii::info('Found matching project by numeric ID!', __METHOD__);
                                break;
                            }
                        }
                        
                        // Also try direct match
                        if ($projId == $projectId) {
                            $selectedProject = $project;
                            Yii::info('Found matching project by direct match!', __METHOD__);
                            break;
                        }
                    }
                }

                if ($selectedProject) {
                    // Format deliverables for Select2 dropdown (API uses "deliverables" not "milestones")
                    $deliverables = [];
                    if (isset($selectedProject['deliverables']) && is_array($selectedProject['deliverables'])) {
                        Yii::info('Found ' . count($selectedProject['deliverables']) . ' deliverables', __METHOD__);
                        foreach ($selectedProject['deliverables'] as $deliverable) {
                            $deliverables[] = [
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
                    } else {
                        Yii::info('No deliverables found in project. Keys: ' . implode(', ', array_keys($selectedProject)), __METHOD__);
                    }

                    return [
                        'success' => true,
                        'deliverables' => $deliverables,
                        'projectData' => $selectedProject,
                    ];
                } else {
                    $availableIds = isset($data['data']) ? array_map(function($p) { return $p['id'] ?? 'N/A'; }, $data['data']) : [];
                    return [
                        'success' => false,
                        'message' => 'Project not found in API response. Looking for: ' . $projectId,
                        'debug' => 'Available IDs: ' . implode(', ', $availableIds),
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'API Error: HTTP ' . $httpCode . ($curlError ? ' - ' . $curlError : ''),
                ];
            }
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
    public function actionExtensionRequests()
    {
        // Check if user is administrator
        if (Yii::$app->user->identity->role !== \common\models\User::ROLE_ADMINISTRATOR) {
            throw new \yii\web\ForbiddenHttpException('You do not have permission to access this page.');
        }

        $query = ProgressReport::find()
            ->where(['has_extension' => 1])
            ->orderBy(['extension_status' => SORT_ASC, 'created_at' => SORT_DESC])
            ->with(['user']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('extension-requests', [
            'dataProvider' => $dataProvider,
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
        // Administrators can view all reports, personnel can only view their own
        if (Yii::$app->user->identity->role === \common\models\User::ROLE_ADMINISTRATOR) {
            $model = ProgressReport::findOne(['id' => $id]);
        } else {
            $model = ProgressReport::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);
        }

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
