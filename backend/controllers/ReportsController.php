<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\Workplan;
use common\models\Accomplishment;
use common\models\ProgressReport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * ReportsController handles report generation
 */
class ReportsController extends Controller
{
    /**
     * Convert signature image to base64 data URI for TCPDF
     * @param string $signaturePath
     * @return string|null
     */
    private function getSignatureDataUri($signaturePath)
    {
        if (!file_exists($signaturePath)) {
            return null;
        }
        
        // Get image info
        $imageInfo = @getimagesize($signaturePath);
        if (!$imageInfo) {
            return null;
        }
        
        // Convert PNG to JPEG to avoid TCPDF alpha channel issues (only if GD is available)
        if ($imageInfo[2] == IMAGETYPE_PNG && extension_loaded('gd')) {
            try {
                $sourceImage = @\imagecreatefrompng($signaturePath);
                if (!$sourceImage) {
                    // Fallback to original file if GD fails
                    $imageData = @file_get_contents($signaturePath);
                    if (!$imageData) {
                        return null;
                    }
                    $mimeType = $imageInfo['mime'];
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                }
                
                // Create a new true color image
                $width = \imagesx($sourceImage);
                $height = \imagesy($sourceImage);
                $newImage = \imagecreatetruecolor($width, $height);
                
                // Fill with white background
                $white = \imagecolorallocate($newImage, 255, 255, 255);
                \imagefill($newImage, 0, 0, $white);
                
                // Copy and merge
                \imagecopy($newImage, $sourceImage, 0, 0, 0, 0, $width, $height);
                
                // Get JPEG as base64
                ob_start();
                \imagejpeg($newImage, null, 90);
                $imageData = ob_get_clean();
                
                \imagedestroy($sourceImage);
                \imagedestroy($newImage);
                
                return 'data:image/jpeg;base64,' . base64_encode($imageData);
            } catch (\Exception $e) {
                // Fallback to original file if conversion fails
                $imageData = @file_get_contents($signaturePath);
                if (!$imageData) {
                    return null;
                }
                $mimeType = $imageInfo['mime'];
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
        }
        
        // For JPEG or other formats, or if GD is not available, just encode as is
        $imageData = @file_get_contents($signaturePath);
        if (!$imageData) {
            return null;
        }
        
        $mimeType = $imageInfo['mime'];
        return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    }
    
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
                    'export-excel' => ['post'],
                    'export-pdf' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Display reports page
     * @return string
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role === User::ROLE_ADMINISTRATOR;
        
        // Get list of personnel for admin
        $personnel = [];
        if ($isAdmin) {
            $users = User::find()
                ->select(['id', 'full_name'])
                ->where(['role' => User::ROLE_PERSONNEL])
                ->all();
            
            foreach ($users as $user) {
                $personnel[$user->id] = $user->full_name;
            }
        }
        
        // Get list of projects
        $projects = [];
        if ($isAdmin) {
            // Admin can see all projects
            $projectList = \common\models\ProjectAssignment::find()
                ->select(['project_id', 'project_name'])
                ->distinct()
                ->orderBy(['project_name' => SORT_ASC])
                ->all();
            
            foreach ($projectList as $project) {
                $projects[$project->project_id] = $project->project_name;
            }
        } else {
            // Personnel can only see their assigned projects
            $projectList = \common\models\ProjectAssignment::find()
                ->select(['project_id', 'project_name'])
                ->where(['user_id' => $user->id])
                ->distinct()
                ->orderBy(['project_name' => SORT_ASC])
                ->all();
            
            foreach ($projectList as $project) {
                $projects[$project->project_id] = $project->project_name;
            }
        }
        
        return $this->render('index', [
            'isAdmin' => $isAdmin,
            'personnel' => $personnel,
            'projects' => $projects,
        ]);
    }

    /**
     * Export report to Excel
     * @return mixed
     */
    public function actionExportExcel()
    {
        $type = Yii::$app->request->post('type');
        $userId = Yii::$app->request->post('user_id');
        $projectId = Yii::$app->request->post('project_id');
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role === User::ROLE_ADMINISTRATOR;
        
        // If not admin, force current user's ID
        if (!$isAdmin) {
            $userId = $user->id;
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        switch ($type) {
            case 'workplan':
                $this->generateWorkplanExcel($sheet, $userId, $startDate, $endDate);
                $filename = 'Workplan_Report_' . date('Y-m-d') . '.xlsx';
                break;
            case 'accomplishment':
                $this->generateAccomplishmentExcel($sheet, $userId, $startDate, $endDate);
                $filename = 'Accomplishment_Report_' . date('Y-m-d') . '.xlsx';
                break;
            case 'progress-report':
                $this->generateProgressReportExcel($sheet, $userId, $startDate, $endDate);
                $filename = 'Progress_Report_' . date('Y-m-d') . '.xlsx';
                break;
            case 'progress-report-combined':
                // Only allow admin to generate combined report
                if (!$isAdmin) {
                    throw new \yii\web\ForbiddenHttpException('You are not allowed to generate this report.');
                }
                $this->generateProgressReportCombinedExcel($sheet, $startDate, $endDate);
                $filename = 'Progress_Report_All_Projects_' . date('Y-m-d') . '.xlsx';
                break;
            case 'progress-report-by-project':
                if (empty($projectId)) {
                    throw new \yii\web\BadRequestHttpException('Project ID is required.');
                }
                $this->generateProgressReportByProjectExcel($sheet, $projectId, $startDate, $endDate, $isAdmin);
                $filename = 'Progress_Report_Project_' . date('Y-m-d') . '.xlsx';
                break;
            default:
                $filename = 'Report_' . date('Y-m-d') . '.xlsx';
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Generate Workplan Excel Report
     */
    private function generateWorkplanExcel($sheet, $userId, $startDate, $endDate)
    {
        // Get user info
        $user = $userId ? User::findOne($userId) : null;
        
        // Set header
        $sheet->setCellValue('A1', 'WORKPLAN REPORT');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        if ($user) {
            $sheet->setCellValue('A2', 'Employee Name: ' . $user->full_name);
            $sheet->setCellValue('D2', 'Position: ' . ($user->position ?? 'N/A'));
            $sheet->setCellValue('A3', 'Department: ' . ($user->department ?? 'N/A'));
            $sheet->setCellValue('D3', 'Period Covered: ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All'));
        }
        
        // Column headers
        $row = 5;
        $headers = ['Activities/Tasks', 'Task Type', 'Task Category', 'Start Date', 'End Date'];
        $columns = ['A', 'B', 'C', 'D', 'E'];
        
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columns[$index] . $row, $header);
        }
        
        // Style headers
        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '967259']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);
        
        // Get workplans
        $query = Workplan::find()->with(['taskType', 'taskCategory']);
        
        if ($userId) {
            $query->andWhere(['user_id' => $userId]);
        }
        
        if ($startDate) {
            $query->andWhere(['>=', 'start_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'end_date', $endDate]);
        }
        
        $workplans = $query->orderBy(['project_name' => SORT_ASC, 'start_date' => SORT_ASC])->all();
        
        // Group workplans by project
        $groupedWorkplans = [];
        foreach ($workplans as $workplan) {
            $projectName = $workplan->project_name ?: 'Not Project Related';
            if (!isset($groupedWorkplans[$projectName])) {
                $groupedWorkplans[$projectName] = [];
            }
            $groupedWorkplans[$projectName][] = $workplan;
        }
        
        $row++;
        $projectNumber = 1;
        foreach ($groupedWorkplans as $projectName => $projectWorkplans) {
            // Add project header row
            $projectLabel = ($projectName === 'Not Project Related') ? $projectName : 'Project #' . $projectNumber . ': ' . $projectName;
            $sheet->setCellValue('A' . $row, $projectLabel);
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]);
            $row++;
            
            // Add workplans for this project
            foreach ($projectWorkplans as $workplan) {
                $sheet->setCellValue('A' . $row, $workplan->workplan);
                $sheet->setCellValue('B' . $row, $workplan->taskType ? $workplan->taskType->name : '');
                $sheet->setCellValue('C' . $row, $workplan->taskCategory ? $workplan->taskCategory->name : '');
                $sheet->setCellValue('D' . $row, date('F j, Y', strtotime($workplan->start_date)));
                $sheet->setCellValue('E' . $row, date('F j, Y', strtotime($workplan->end_date)));
                
                // Apply borders
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ]
                ]);
                
                $row++;
            }
            
            if ($projectName !== 'Not Project Related') {
                $projectNumber++;
            }
        }
        
        // Add signature section
        $row += 2;
        if ($user) {
            // Prepared by
            $sheet->setCellValue('A' . $row, 'Prepared by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            if ($user->digital_signature) {
                $sheet->setCellValue('A' . $row, 'Signed');
                $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
                $row++;
            }
            $sheet->setCellValue('A' . $row, $user->full_name);
            $row++;
            $sheet->setCellValue('A' . $row, $user->position ?? '');
            
            // Reviewed by
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Reviewed by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $userReviewers = $user->userReviewers;
            if (!empty($userReviewers)) {
                foreach ($userReviewers as $index => $userReviewer) {
                    if ($index > 0) {
                        $row++;
                    }
                    $reviewer = $userReviewer->reviewer;
                    $sheet->setCellValue('A' . $row, $reviewer ? $reviewer->full_name : '');
                    $row++;
                    $sheet->setCellValue('A' . $row, $userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : ''));
                    if ($index < count($userReviewers) - 1) {
                        $row++;
                    }
                }
            } else {
                $sheet->setCellValue('A' . $row, '_________________________');
                $row++;
                $sheet->setCellValue('A' . $row, 'Name and Designation');
            }
            
            // Approved by
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Approved by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            if ($user->approver) {
                $sheet->setCellValue('A' . $row, $user->approver->full_name);
                $row++;
                $sheet->setCellValue('A' . $row, $user->approver_designation ?? $user->approver->position ?? '');
            } else {
                $sheet->setCellValue('A' . $row, '_________________________');
                $row++;
                $sheet->setCellValue('A' . $row, 'Name and Designation');
            }
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
    }

    /**
     * Generate Accomplishment Excel Report
     */
    private function generateAccomplishmentExcel($sheet, $userId, $startDate, $endDate)
    {
        // Get user info
        $user = $userId ? User::findOne($userId) : null;
        
        // Set header
        $sheet->setCellValue('A1', 'ACCOMPLISHMENT REPORT');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        if ($user) {
            $sheet->setCellValue('A2', 'Employee Name: ' . $user->full_name);
            $sheet->setCellValue('E2', 'Position: ' . ($user->position ?? 'N/A'));
            $sheet->setCellValue('A3', 'Department: ' . ($user->department ?? 'N/A'));
            $sheet->setCellValue('E3', 'Period Covered: ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All'));
        }
        
        // Column headers
        $row = 5;
        $headers = ['Activities/Tasks', 'Task Type', 'Task Category', 'Mode of Delivery', 'Accomplishments', 'Status', 'Start Date', 'End Date', 'Remarks'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columns[$index] . $row, $header);
        }
        
        // Style headers
        $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '967259']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);
        
        // Get all workplans with their accomplishments
        $query = Workplan::find()->with(['taskType', 'taskCategory', 'accomplishments.status']);
        
        if ($userId) {
            $query->andWhere(['user_id' => $userId]);
        }
        
        if ($startDate) {
            $query->andWhere(['>=', 'start_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'end_date', $endDate]);
        }
        
        $workplans = $query->orderBy(['project_name' => SORT_ASC, 'start_date' => SORT_ASC])->all();
        
        // Group workplans by project
        $groupedWorkplans = [];
        foreach ($workplans as $workplan) {
            $projectName = $workplan->project_name ?: 'Not Project Related';
            if (!isset($groupedWorkplans[$projectName])) {
                $groupedWorkplans[$projectName] = [];
            }
            $groupedWorkplans[$projectName][] = $workplan;
        }
        
        $row++;
        $projectNumber = 1;
        foreach ($groupedWorkplans as $projectName => $projectWorkplans) {
            // Add project header row
            $projectLabel = ($projectName === 'Not Project Related') ? $projectName : 'Project #' . $projectNumber . ': ' . $projectName;
            $sheet->setCellValue('A' . $row, $projectLabel);
            $sheet->mergeCells('A' . $row . ':I' . $row);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]);
            $row++;
            
            // Add workplans and their accomplishments
            foreach ($projectWorkplans as $workplan) {
                $accomplishments = $workplan->accomplishments;
                
                if (empty($accomplishments)) {
                    // Show workplan without accomplishment
                    $sheet->setCellValue('A' . $row, $workplan->workplan);
                    $sheet->setCellValue('B' . $row, $workplan->taskType ? $workplan->taskType->name : '');
                    $sheet->setCellValue('C' . $row, $workplan->taskCategory ? $workplan->taskCategory->name : '');
                    $sheet->setCellValue('D' . $row, '');
                    $sheet->setCellValue('E' . $row, '');
                    $sheet->setCellValue('F' . $row, '');
                    $sheet->setCellValue('G' . $row, date('F j, Y', strtotime($workplan->start_date)));
                    $sheet->setCellValue('H' . $row, date('F j, Y', strtotime($workplan->end_date)));
                    $sheet->setCellValue('I' . $row, '');
                    
                    $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ]
                        ]
                    ]);
                    $row++;
                } else {
                    // Show workplan with each accomplishment
                    foreach ($accomplishments as $accomplishment) {
                        $sheet->setCellValue('A' . $row, $workplan->workplan);
                        $sheet->setCellValue('B' . $row, $workplan->taskType ? $workplan->taskType->name : '');
                        $sheet->setCellValue('C' . $row, $workplan->taskCategory ? $workplan->taskCategory->name : '');
                        $sheet->setCellValue('D' . $row, $accomplishment->mode_of_delivery ? ucfirst($accomplishment->mode_of_delivery) : '');
                        $sheet->setCellValue('E' . $row, $accomplishment->accomplished_task);
                        $sheet->setCellValue('F' . $row, $accomplishment->status ? $accomplishment->status->name : '');
                        $sheet->setCellValue('G' . $row, date('F j, Y', strtotime($accomplishment->start_date)));
                        $sheet->setCellValue('H' . $row, date('F j, Y', strtotime($accomplishment->end_date)));
                        
                        // Apply borders
                        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                ]
                            ]
                        ]);
                        
                        $row++;
                    }
                }
            }
        
            if ($projectName !== 'Not Project Related') {
                $projectNumber++;
            }
        }
        
        // Add signature section
        $row += 2;
        if ($user) {
            // Prepared by
            $sheet->setCellValue('A' . $row, 'Prepared by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            if ($user->digital_signature) {
                $sheet->setCellValue('A' . $row, 'Signed');
                $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
                $row++;
            }
            $sheet->setCellValue('A' . $row, $user->full_name);
            $row++;
            $sheet->setCellValue('A' . $row, $user->position ?? '');
            
            // Reviewed by
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Reviewed by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $userReviewers = $user->userReviewers;
            if (!empty($userReviewers)) {
                foreach ($userReviewers as $index => $userReviewer) {
                    if ($index > 0) {
                        $row++;
                    }
                    $reviewer = $userReviewer->reviewer;
                    $sheet->setCellValue('A' . $row, $reviewer ? $reviewer->full_name : '');
                    $row++;
                    $sheet->setCellValue('A' . $row, $userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : ''));
                    if ($index < count($userReviewers) - 1) {
                        $row++;
                    }
                }
            } else {
                $sheet->setCellValue('A' . $row, '_________________________');
                $row++;
                $sheet->setCellValue('A' . $row, 'Name and Designation');
            }
            
            // Approved by
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Approved by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            if ($user->approver) {
                $sheet->setCellValue('A' . $row, $user->approver->full_name);
                $row++;
                $sheet->setCellValue('A' . $row, $user->approver_designation ?? $user->approver->position ?? '');
            } else {
                $sheet->setCellValue('A' . $row, '_________________________');
                $row++;
                $sheet->setCellValue('A' . $row, 'Name and Designation');
            }
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(25);
    }

    /**
     * Generate Progress Report Excel
     */
    private function generateProgressReportExcel($sheet, $userId, $startDate, $endDate)
    {
        // Get user info
        $user = $userId ? User::findOne($userId) : null;
        
        // Set header
        $sheet->setCellValue('A1', 'PROJECT PROGRESS REPORT FORM');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'PIDS Research Group');
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getFont()->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        if ($user) {
            $sheet->setCellValue('A4', 'Employee Name: ' . $user->full_name);
            $sheet->setCellValue('E4', 'Position: ' . ($user->position ?? ''));
            $sheet->setCellValue('A5', 'Department: ' . ($user->department ?? ''));
            $sheet->setCellValue('E5', 'Period Covered: ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All'));
        }
        
        // Get progress reports grouped by project
        $query = ProgressReport::find();
        
        if ($userId) {
            $query->andWhere(['user_id' => $userId]);
        }
        
        if ($startDate) {
            $query->andWhere(['>=', 'report_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'report_date', $endDate]);
        }
        
        $reports = $query->orderBy(['project_name' => SORT_ASC, 'report_date' => SORT_ASC])->all();
        
        // Group reports by project
        $groupedReports = [];
        foreach ($reports as $report) {
            $projectKey = $report->project_name . '|||' . $report->project_id;
            if (!isset($groupedReports[$projectKey])) {
                $groupedReports[$projectKey] = [
                    'project_name' => $report->project_name,
                    'project_id' => $report->project_id,
                    'reports' => []
                ];
            }
            $groupedReports[$projectKey]['reports'][] = $report;
        }
        
        $row = 7;
        foreach ($groupedReports as $projectData) {
            // Project Title
            $sheet->setCellValue('A' . $row, 'Project Title: ' . $projectData['project_name']);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            
            // Column headers
            $headers = ['Month', 'Milestone', 'Approved Date', 'Status', 'Remarks (if Delayed)', 'Requesting for Extension?', 'If yes, Proposed Date Extension', 'If yes, Justification for Extension'];
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            
            foreach ($headers as $index => $header) {
                $sheet->setCellValue($columns[$index] . $row, $header);
            }
            
            // Style headers
            $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '967259']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]);
            
            $row++;
            
            // Data rows
            foreach ($projectData['reports'] as $report) {
                $sheet->setCellValue('A' . $row, date('F Y', strtotime($report->report_date)));
                $sheet->setCellValue('B' . $row, $report->milestone_name ?? '');
                $sheet->setCellValue('C' . $row, $report->report_date ? date('F j, Y', strtotime($report->report_date)) : '');
                $sheet->setCellValue('D' . $row, ucfirst($report->status ?? ''));
                $sheet->setCellValue('E' . $row, $report->remarks ?? '');
                $sheet->setCellValue('F' . $row, $report->has_extension ? 'Yes' : 'No');
                $sheet->setCellValue('G' . $row, $report->extension_date ? date('F j, Y', strtotime($report->extension_date)) : '');
                $sheet->setCellValue('H' . $row, $report->extension_justification ?? '');
                
                // Apply borders
                $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ]
                ]);
                
                $row++;
            }
            
            $sheet->setCellValue('A' . $row, '');
            $row += 3;
        }
        
        // Add note section (after all projects)
        $sheet->setCellValue('A' . $row, 'Note: A Plan of Action and other pertinent documents need to be attached to this document to support the request for extension.');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $row += 2;
        
        // Add signature section
        if ($user) {
            $sheet->setCellValue('A' . $row, 'Prepared by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row += 2;
            
            // Add "Signed" if user has digital signature
            if ($user->digital_signature) {
                $sheet->setCellValue('A' . $row, 'Signed');
                $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
                $row++;
            }
            
            $sheet->setCellValue('A' . $row, $user->full_name);
            $row++;
            $sheet->setCellValue('A' . $row, $user->position ?? '');
            
            // Reviewed by
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Reviewed by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $userReviewers = $user->userReviewers;
            if (!empty($userReviewers)) {
                foreach ($userReviewers as $index => $userReviewer) {
                    if ($index > 0) {
                        $row++;
                    }
                    $reviewer = $userReviewer->reviewer;
                    $sheet->setCellValue('A' . $row, $reviewer ? $reviewer->full_name : '');
                    $row++;
                    $sheet->setCellValue('A' . $row, $userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : ''));
                    if ($index < count($userReviewers) - 1) {
                        $row++;
                    }
                }
            } else {
                $sheet->setCellValue('A' . $row, '_________________________');
                $row++;
                $sheet->setCellValue('A' . $row, 'Name and Designation');
            }
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(30);
    }

    /**
     * Export report to PDF
     * @return mixed
     */
    public function actionExportPdf()
    {
        $type = Yii::$app->request->post('type');
        $userId = Yii::$app->request->post('user_id');
        $projectId = Yii::$app->request->post('project_id');
        $startDate = Yii::$app->request->post('start_date');
        $endDate = Yii::$app->request->post('end_date');
        
        $user = Yii::$app->user->identity;
        $isAdmin = $user->role === User::ROLE_ADMINISTRATOR;
        
        // If not admin, force current user's ID
        if (!$isAdmin) {
            $userId = $user->id;
        }
        
        // Create PDF
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('EARS');
        $pdf->SetAuthor('EARS System');
        $pdf->SetTitle(ucfirst($type) . ' Report');
        
        // Special handling for progress-report-by-project to remove default header/footer
        if ($type === 'progress-report-by-project') {
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
        } else {
            // Set header data for other reports
            $pdf->SetHeaderData('', 0, ucfirst($type) . ' Report', 'Electronic Accomplishment Reporting System');
            
            // Set fonts
            $pdf->setHeaderFont(['helvetica', '', 10]);
            $pdf->setFooterFont(['helvetica', '', 8]);
            
            // Set margins
            $pdf->SetMargins(15, 27, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(true, 25);
        }
        
        // Add page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 9);
        
        // Generate content based on type
        switch ($type) {
            case 'workplan':
                $html = $this->generateWorkplanPdf($userId, $startDate, $endDate);
                $filename = 'Workplan_Report_' . date('Y-m-d') . '.pdf';
                break;
            case 'accomplishment':
                $html = $this->generateAccomplishmentPdf($userId, $startDate, $endDate);
                $filename = 'Accomplishment_Report_' . date('Y-m-d') . '.pdf';
                break;
            case 'progress-report':
                $html = $this->generateProgressReportPdf($userId, $startDate, $endDate);
                $filename = 'Progress_Report_' . date('Y-m-d') . '.pdf';
                break;
            case 'progress-report-combined':
                // Only allow admin to generate combined report
                if (!$isAdmin) {
                    throw new \yii\web\ForbiddenHttpException('You are not allowed to generate this report.');
                }
                $html = $this->generateProgressReportCombinedPdf($startDate, $endDate);
                $filename = 'Progress_Report_All_Projects_' . date('Y-m-d') . '.pdf';
                break;
            case 'progress-report-by-project':
                if (empty($projectId)) {
                    throw new \yii\web\BadRequestHttpException('Project ID is required.');
                }
                $html = $this->generateProgressReportByProjectPdf($projectId, $startDate, $endDate, $isAdmin);
                $filename = 'Progress_Report_Project_' . date('Y-m-d') . '.pdf';
                break;
            default:
                $html = '<p>Invalid report type</p>';
                $filename = 'Report_' . date('Y-m-d') . '.pdf';
        }
        
        // Output HTML
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Output PDF
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Generate Workplan PDF HTML
     */
    private function generateWorkplanPdf($userId, $startDate, $endDate)
    {
        $user = $userId ? User::findOne($userId) : null;
        
         $html = '<style>
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #967259; color: white; padding: 8px; text-align: center; font-weight: bold; border: 1px solid #000; }
            td { padding: 6px; border: 1px solid #000; font-size: 9px; }
            .header-info { margin-bottom: 10px; }
            .project-header { background-color: #E8E8E8; font-weight: bold; padding: 8px; border: 1px solid #000; }
            .signature-section { margin-top: 30px; }
        </style>';
        
        if ($user) {
            $html .= '<div class="header-info">
                <strong>Employee Name:</strong> ' . $user->full_name . ' &nbsp;&nbsp;&nbsp; 
                <strong>Position:</strong> ' . ($user->position ?? 'N/A') . '<br>
                <strong>Department:</strong> ' . ($user->department ?? 'N/A') . ' &nbsp;&nbsp;&nbsp; 
                <strong>Period Covered:</strong> ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All') . '
            </div>';
        }
        
        $html .= '
        <table>
            <thead>
                <tr>
                    <th>Activities/Tasks</th>
                    <th>Task Type</th>
                    <th>Task Category</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>';
        
        // Get workplans
        $query = Workplan::find()->with(['taskType', 'taskCategory']);
        
        if ($userId) {
            $query->andWhere(['user_id' => $userId]);
        }
        
        if ($startDate) {
            $query->andWhere(['>=', 'start_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'end_date', $endDate]);
        }
        
        $workplans = $query->orderBy(['project_name' => SORT_ASC, 'start_date' => SORT_ASC])->all();
        
        // Group workplans by project
        $groupedWorkplans = [];
        foreach ($workplans as $workplan) {
            $projectName = $workplan->project_name ?: 'Not Project Related';
            if (!isset($groupedWorkplans[$projectName])) {
                $groupedWorkplans[$projectName] = [];
            }
            $groupedWorkplans[$projectName][] = $workplan;
        }
        
        $projectNumber = 1;
        foreach ($groupedWorkplans as $projectName => $projectWorkplans) {
            // Add project header row
            $projectLabel = ($projectName === 'Not Project Related') ? $projectName : 'Project #' . $projectNumber . ': ' . $projectName;
            $html .= '<tr>
                <td colspan="5" class="project-header">' . htmlspecialchars($projectLabel) . '</td>
            </tr>';
            
            // Add workplans for this project
            foreach ($projectWorkplans as $workplan) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($workplan->workplan) . '</td>
                    <td>' . htmlspecialchars($workplan->taskType ? $workplan->taskType->name : '') . '</td>
                    <td>' . htmlspecialchars($workplan->taskCategory ? $workplan->taskCategory->name : '') . '</td>
                    <td>' . htmlspecialchars(date('F j, Y', strtotime($workplan->start_date))) . '</td>
                    <td>' . htmlspecialchars(date('F j, Y', strtotime($workplan->end_date))) . '</td>
                </tr>';
            }
            
            if ($projectName !== 'Not Project Related') {
                $projectNumber++;
            }
        }
        
        $html .= '</tbody></table>';
        
        // Add signature section
        if ($user) {
            $html .= '<div class="signature-section" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 20px;">';
            
            // Prepared by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Prepared by:</div>';
            
            // Add digital signature image if available
            if ($user->digital_signature) {
                $signaturePath = Yii::getAlias('@backend/web') . $user->digital_signature;
                $signatureDataUri = $this->getSignatureDataUri($signaturePath);
                if ($signatureDataUri) {
                    $html .= '<div style="width: 70px; height: 25px; overflow: hidden; margin: 0; padding: 0; line-height: 0;"><img src="' . $signatureDataUri . '" style="width: 70px; height: 25px; display: block; margin: 0; padding: 0;"></div>';
                }
            }
            
            $html .= '<div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($user->full_name) . '</div>
                    <div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($user->position ?? '') . '</div>
                </div>';
            
            // Reviewed by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Reviewed by:</div>';
            
            $userReviewers = $user->userReviewers;
            if (!empty($userReviewers)) {
                foreach ($userReviewers as $index => $userReviewer) {
                    $reviewer = $userReviewer->reviewer;
                    if ($index > 0) {
                        $html .= '<div style="margin-top: 10px;"></div>';
                    }
                    $html .= '<div>' . htmlspecialchars($reviewer ? $reviewer->full_name : '') . '</div>
                            <div>' . htmlspecialchars($userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : '')) . '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div>_________________________</div><div>Name and Designation</div></div>';
            }
            
            // Approved by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Approved by:</div>';
            
            if ($user->approver) {
                $html .= '<div>' . htmlspecialchars($user->approver->full_name) . '</div>
                        <div>' . htmlspecialchars($user->approver_designation ?? $user->approver->position ?? '') . '</div>
                    </div>';
            } else {
                $html .= '<div>_________________________</div><div>Name and Designation</div></div>';
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }

    /**
     * Generate Accomplishment PDF HTML
     */
    private function generateAccomplishmentPdf($userId, $startDate, $endDate)
    {
        $user = $userId ? User::findOne($userId) : null;
        
        $html = '<style>
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #967259; color: white; padding: 8px; text-align: center; font-weight: bold; border: 1px solid #000; }
            td { padding: 6px; border: 1px solid #000; font-size: 9px; }
            .header-info { margin-bottom: 10px; }
            .project-header { background-color: #E8E8E8; font-weight: bold; padding: 8px; border: 1px solid #000; }
            .signature-section { margin-top: 30px; }
        </style>';
        
        if ($user) {
            $html .= '<div class="header-info">
                <strong>Employee Name:</strong> ' . $user->full_name . ' &nbsp;&nbsp;&nbsp; 
                <strong>Position:</strong> ' . ($user->position ?? 'N/A') . '<br>
                <strong>Department:</strong> ' . ($user->department ?? 'N/A') . ' &nbsp;&nbsp;&nbsp; 
                <strong>Period Covered:</strong> ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All') . '
            </div>';
        }
        
        $html .= '<table>
            <thead>
                <tr>
                    <th>Activities/Tasks</th>
                    <th>Task Type</th>
                    <th>Task Category</th>
                    <th>Mode of Delivery</th>
                    <th>Accomplishments</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>';
        
        // Get all workplans with their accomplishments
        $query = Workplan::find()->with(['taskType', 'taskCategory', 'accomplishments.status']);
        
        if ($userId) {
            $query->andWhere(['user_id' => $userId]);
        }
        
        if ($startDate) {
            $query->andWhere(['>=', 'start_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'end_date', $endDate]);
        }
        
        $workplans = $query->orderBy(['project_name' => SORT_ASC, 'start_date' => SORT_ASC])->all();
        
        // Group workplans by project
        $groupedWorkplans = [];
        foreach ($workplans as $workplan) {
            $projectName = $workplan->project_name ?: 'Not Project Related';
            if (!isset($groupedWorkplans[$projectName])) {
                $groupedWorkplans[$projectName] = [];
            }
            $groupedWorkplans[$projectName][] = $workplan;
        }
        
        $projectNumber = 1;
        foreach ($groupedWorkplans as $projectName => $projectWorkplans) {
            // Add project header row
            $projectLabel = ($projectName === 'Not Project Related') ? $projectName : 'Project #' . $projectNumber . ': ' . $projectName;
            $html .= '<tr>
                <td colspan="9" class="project-header">' . htmlspecialchars($projectLabel) . '</td>
            </tr>';;
            
            // Add workplans and their accomplishments
            foreach ($projectWorkplans as $workplan) {
                $accomplishments = $workplan->accomplishments;
                
                if (empty($accomplishments)) {
                    // Show workplan without accomplishment
                    $html .= '<tr>
                        <td>' . htmlspecialchars($workplan->workplan) . '</td>
                        <td>' . htmlspecialchars($workplan->taskType ? $workplan->taskType->name : '') . '</td>
                        <td>' . htmlspecialchars($workplan->taskCategory ? $workplan->taskCategory->name : '') . '</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>' . htmlspecialchars(date('F j, Y', strtotime($workplan->start_date))) . '</td>
                        <td>' . htmlspecialchars(date('F j, Y', strtotime($workplan->end_date))) . '</td>
                        <td></td>
                    </tr>';
                } else {
                    // Show workplan with each accomplishment
                    foreach ($accomplishments as $accomplishment) {
                        $html .= '<tr>
                            <td>' . htmlspecialchars($workplan->workplan) . '</td>
                            <td>' . htmlspecialchars($workplan->taskType ? $workplan->taskType->name : '') . '</td>
                            <td>' . htmlspecialchars($workplan->taskCategory ? $workplan->taskCategory->name : '') . '</td>
                            <td>' . htmlspecialchars($accomplishment->mode_of_delivery ? ucfirst($accomplishment->mode_of_delivery) : '') . '</td>
                            <td>' . htmlspecialchars($accomplishment->accomplished_task) . '</td>
                            <td>' . htmlspecialchars($accomplishment->status ? $accomplishment->status->name : '') . '</td>
                            <td>' . htmlspecialchars(date('F j, Y', strtotime($accomplishment->start_date))) . '</td>
                            <td>' . htmlspecialchars(date('F j, Y', strtotime($accomplishment->end_date))) . '</td>
                            <td></td>
                        </tr>';
                    }
                }
            }
            
            if ($projectName !== 'Not Project Related') {
                $projectNumber++;
            }
        }
        
        $html .= '</tbody></table>';
        
        // Add signature section
        if ($user) {
            $html .= '<div class="signature-section" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 20px;">';
            
            // Prepared by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Prepared by:</div>';
            
            // Add digital signature image if available
            if ($user->digital_signature) {
                $signaturePath = Yii::getAlias('@backend/web') . $user->digital_signature;
                $signatureDataUri = $this->getSignatureDataUri($signaturePath);
                if ($signatureDataUri) {
                    $html .= '<div style="width: 70px; height: 25px; overflow: hidden; margin: 0; padding: 0; line-height: 0;"><img src="' . $signatureDataUri . '" style="width: 70px; height: 25px; display: block; margin: 0; padding: 0;"></div>';
                }
            }
            
            $html .= '<div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($user->full_name) . '</div>
                    <div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($user->position ?? '') . '</div>
                </div>';
            
            // Reviewed by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Reviewed by:</div>';
            
            $userReviewers = $user->userReviewers;
            if (!empty($userReviewers)) {
                foreach ($userReviewers as $index => $userReviewer) {
                    $reviewer = $userReviewer->reviewer;
                    if ($index > 0) {
                        $html .= '<div style="margin-top: 10px;"></div>';
                    }
                    $html .= '<div>' . htmlspecialchars($reviewer ? $reviewer->full_name : '') . '</div>
                            <div>' . htmlspecialchars($userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : '')) . '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div>_________________________</div><div>Name and Designation</div></div>';
            }
            
            // Approved by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Approved by:</div>';
            
            if ($user->approver) {
                $html .= '<div>' . htmlspecialchars($user->approver->full_name) . '</div>
                        <div>' . htmlspecialchars($user->reviewer_designation ?? $user->approver->position ?? '') . '</div>
                    </div>';
            } else {
                $html .= '<div>_________________________</div><div>Name and Designation</div></div>';
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }

    /**
     * Generate Progress Report PDF HTML
     */
    private function generateProgressReportPdf($userId, $startDate, $endDate)
    {
        $user = $userId ? User::findOne($userId) : null;
        
        $html = '<style>
            table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
            th { background-color: #967259; color: white; padding: 8px; text-align: center; font-weight: bold; border: 1px solid #000; }
            td { padding: 6px; border: 1px solid #000; font-size: 9px; }
            .header-info { margin-bottom: 10px; }
            .project-title { font-weight: bold; margin-top: 20px; margin-bottom: 5px; }
            .note { font-style: italic; margin: 10px 0; }
            .signature-section { margin-top: 30px; }
        </style>';
        
        $html .= '<h2 style="text-align: center;">PROJECT PROGRESS REPORT FORM</h2>';
        $html .= '<h3 style="text-align: center;">PIDS Research Group</h3>';
        
        if ($user) {
            $html .= '<div class="header-info">
                <strong>Employee Name:</strong> ' . $user->full_name . ' &nbsp;&nbsp;&nbsp; 
                <strong>Position:</strong> ' . ($user->position ?? '') . '<br>
                <strong>Department:</strong> ' . ($user->department ?? '') . ' &nbsp;&nbsp;&nbsp; 
                <strong>Period Covered:</strong> ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All') . '
            </div>';
        }
        
        // Get progress reports grouped by project
        $query = ProgressReport::find();
        
        if ($userId) {
            $query->andWhere(['user_id' => $userId]);
        }
        
        if ($startDate) {
            $query->andWhere(['>=', 'report_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'report_date', $endDate]);
        }
        
        $reports = $query->orderBy(['project_name' => SORT_ASC, 'report_date' => SORT_ASC])->all();
        
        // Group reports by project
        $groupedReports = [];
        foreach ($reports as $report) {
            $projectKey = $report->project_name . '|||' . $report->project_id;
            if (!isset($groupedReports[$projectKey])) {
                $groupedReports[$projectKey] = [
                    'project_name' => $report->project_name,
                    'project_id' => $report->project_id,
                    'reports' => []
                ];
            }
            $groupedReports[$projectKey]['reports'][] = $report;
        }
        
        foreach ($groupedReports as $projectData) {
            // Project Title
            $html .= '<div class="project-title">Project Title: ' . htmlspecialchars($projectData['project_name']) . '</div>';
            
            $html .= '<table>
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Milestone</th>
                        <th>Approved Date</th>
                        <th>Status</th>
                        <th>Remarks (if Delayed)</th>
                        <th>Requesting for Extension?</th>
                        <th>If yes, Proposed Date Extension</th>
                        <th>If yes, Justification for Extension</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($projectData['reports'] as $report) {
                $html .= '<tr>
                    <td>' . htmlspecialchars(date('F Y', strtotime($report->report_date))) . '</td>
                    <td>' . htmlspecialchars($report->milestone_name ?? '') . '</td>
                    <td>' . htmlspecialchars($report->report_date ? date('F j, Y', strtotime($report->report_date)) : '') . '</td>
                    <td>' . htmlspecialchars(ucfirst($report->status ?? '')) . '</td>
                    <td>' . htmlspecialchars($report->remarks ?? '') . '</td>
                    <td>' . htmlspecialchars($report->has_extension ? 'Yes' : 'No') . '</td>
                    <td>' . htmlspecialchars($report->extension_date ? date('F j, Y', strtotime($report->extension_date)) : '') . '</td>
                    <td>' . htmlspecialchars($report->extension_justification ?? '') . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        // Note section (after all projects)
        $html .= '<div class="note" style="margin-top: 20px;">Note: A Plan of Action and other pertinent documents need to be attached to this document to support the request for extension.</div>';
        
        // Signature section
        $html .= '<div class="signature-section" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 20px;">';
        
        if ($user) {
            // Prepared by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Prepared by:</div>';
            
            // Add digital signature image if available
            if ($user->digital_signature) {
                $signaturePath = Yii::getAlias('@backend/web') . $user->digital_signature;
                $signatureDataUri = $this->getSignatureDataUri($signaturePath);
                if ($signatureDataUri) {
                    $html .= '<div style="width: 70px; height: 25px; overflow: hidden; margin: 0; padding: 0; line-height: 0;"><img src="' . $signatureDataUri . '" style="width: 70px; height: 25px; display: block; margin: 0; padding: 0;"></div>';
                }
            }
            
            $html .= '<div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($user->full_name) . '</div>
                <div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($user->position ?? '') . '</div></div>';
            
            // Reviewed by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Reviewed by:</div>';
            
            $userReviewers = $user->userReviewers;
            if (!empty($userReviewers)) {
                foreach ($userReviewers as $index => $userReviewer) {
                    $reviewer = $userReviewer->reviewer;
                    if ($index > 0) {
                        $html .= '<div style="margin-top: 10px;"></div>';
                    }
                    $html .= '<div>' . htmlspecialchars($reviewer ? $reviewer->full_name : '') . '</div>
                        <div>' . htmlspecialchars($userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : '')) . '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div>______________________________</div>
                    <div>Name and Designation</div></div>';
            }
        } else {
            $html .= '<div style="flex: 1; text-align: left;"><div style="font-weight: bold; margin-bottom: 5px;">Prepared by:</div>
                <div>______________________________</div>
                <div>Name and Position</div></div>';
            
            $html .= '<div style="flex: 1; text-align: left;"><div style="font-weight: bold; margin-bottom: 5px;">Reviewed by:</div>
                <div>______________________________</div>
                <div>Designation</div></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate Combined Progress Report Excel (All Projects from All Personnel)
     */
    private function generateProgressReportCombinedExcel($sheet, $startDate, $endDate)
    {
        // Set header
        $sheet->setCellValue('A1', 'PROJECT PROGRESS REPORT - ALL PROJECTS COMBINED');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A2', 'PIDS Research Group');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'Period Covered: ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All'));
        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $row = 5;
        
        // Get all progress reports from all users
        $query = ProgressReport::find()->with(['user']);
        
        if ($startDate) {
            $query->andWhere(['>=', 'report_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'report_date', $endDate]);
        }
        
        $reports = $query->orderBy(['project_name' => SORT_ASC, 'user_id' => SORT_ASC, 'report_date' => SORT_ASC])->all();
        
        // Group reports by project
        $groupedReports = [];
        foreach ($reports as $report) {
            $projectKey = $report->project_name . '|||' . $report->project_id;
            if (!isset($groupedReports[$projectKey])) {
                $groupedReports[$projectKey] = [
                    'project_name' => $report->project_name,
                    'project_id' => $report->project_id,
                    'reports' => []
                ];
            }
            $groupedReports[$projectKey]['reports'][] = $report;
        }
        
        foreach ($groupedReports as $projectData) {
            // Project Title
            $sheet->setCellValue('A' . $row, 'Project Title: ' . $projectData['project_name']);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            
            // Column headers
            $headers = ['Personnel', 'Month', 'Milestone', 'Approved Date', 'Status', 'Remarks (if Delayed)', 'Requesting for Extension?', 'If yes, Proposed Date Extension', 'If yes, Justification for Extension'];
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
            
            foreach ($headers as $index => $header) {
                $sheet->setCellValue($columns[$index] . $row, $header);
            }
            
            // Style headers
            $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '967259']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]);
            
            $row++;
            
            // Data rows
            foreach ($projectData['reports'] as $report) {
                $sheet->setCellValue('A' . $row, $report->user ? $report->user->full_name : 'N/A');
                $sheet->setCellValue('B' . $row, date('F Y', strtotime($report->report_date)));
                $sheet->setCellValue('C' . $row, $report->milestone_name ?? '');
                $sheet->setCellValue('D' . $row, $report->report_date ? date('F j, Y', strtotime($report->report_date)) : '');
                $sheet->setCellValue('E' . $row, ucfirst($report->status ?? ''));
                $sheet->setCellValue('F' . $row, $report->remarks ?? '');
                $sheet->setCellValue('G' . $row, $report->has_extension ? 'Yes' : 'No');
                $sheet->setCellValue('H' . $row, $report->extension_date ? date('F j, Y', strtotime($report->extension_date)) : '');
                $sheet->setCellValue('I' . $row, $report->extension_justification ?? '');
                
                // Apply borders
                $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ]
                ]);
                
                $row++;
            }
            
            $sheet->setCellValue('A' . $row, '');
            $row += 3;
        }
        
        // Note section (after all projects)
        $row++;
        $sheet->setCellValue('A' . $row, 'Note: A Plan of Action and other pertinent documents need to be attached to this document to support the request for extension.');
        $sheet->mergeCells('A' . $row . ':I' . $row);
        $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        $row += 2;
        
        // Signature section
        $sheet->setCellValue('A' . $row, 'Prepared by:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row += 2;
        
        // Get current user (admin)
        $currentUser = Yii::$app->user->identity;
        if ($currentUser && $currentUser->digital_signature) {
            $sheet->setCellValue('A' . $row, 'Signed');
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
            $row++;
        }
        
        if ($currentUser) {
            $sheet->setCellValue('A' . $row, $currentUser->full_name);
            $row++;
            $sheet->setCellValue('A' . $row, $currentUser->position ?? '');
        } else {
            $sheet->setCellValue('A' . $row, '______________________________');
            $row++;
            $sheet->setCellValue('A' . $row, 'Administrator Name and Position');
        }
        
        // Reviewed by
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Reviewed by:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $userReviewers = $currentUser ? $currentUser->userReviewers : [];
        if (!empty($userReviewers)) {
            foreach ($userReviewers as $index => $userReviewer) {
                if ($index > 0) {
                    $row++;
                }
                $reviewer = $userReviewer->reviewer;
                $sheet->setCellValue('A' . $row, $reviewer ? $reviewer->full_name : '');
                $row++;
                $sheet->setCellValue('A' . $row, $userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : ''));
                if ($index < count($userReviewers) - 1) {
                    $row++;
                }
            }
        } else {
            $sheet->setCellValue('A' . $row, '_________________________');
            $row++;
            $sheet->setCellValue('A' . $row, 'Name and Designation');
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(25);
    }

    /**
     * Generate Combined Progress Report PDF (All Projects from All Personnel)
     */
    private function generateProgressReportCombinedPdf($startDate, $endDate)
    {
        $html = '<style>
            table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
            th { background-color: #967259; color: white; padding: 8px; text-align: center; font-weight: bold; border: 1px solid #000; }
            td { padding: 6px; border: 1px solid #000; font-size: 9px; }
            .header-info { margin-bottom: 10px; }
            .project-title { font-weight: bold; margin-top: 20px; margin-bottom: 5px; }
            .note { font-style: italic; margin: 10px 0; }
            .signature-section { margin-top: 30px; }
        </style>';
        
        $html .= '<h2 style="text-align: center;">PROJECT PROGRESS REPORT - ALL PROJECTS COMBINED</h2>';
        $html .= '<h3 style="text-align: center;">PIDS Research Group</h3>';
        
        $html .= '<div class="header-info">
            <strong>Period Covered:</strong> ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All') . '
        </div>';
        
        // Get all progress reports from all users
        $query = ProgressReport::find()->with(['user']);
        
        if ($startDate) {
            $query->andWhere(['>=', 'report_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'report_date', $endDate]);
        }
        
        $reports = $query->orderBy(['project_name' => SORT_ASC, 'user_id' => SORT_ASC, 'report_date' => SORT_ASC])->all();
        
        // Group reports by project
        $groupedReports = [];
        foreach ($reports as $report) {
            $projectKey = $report->project_name . '|||' . $report->project_id;
            if (!isset($groupedReports[$projectKey])) {
                $groupedReports[$projectKey] = [
                    'project_name' => $report->project_name,
                    'project_id' => $report->project_id,
                    'reports' => []
                ];
            }
            $groupedReports[$projectKey]['reports'][] = $report;
        }
        
        foreach ($groupedReports as $projectData) {
            // Project Title
            $html .= '<div class="project-title">Project Title: ' . htmlspecialchars($projectData['project_name']) . '</div>';
            
            $html .= '<table>
                <thead>
                    <tr>
                        <th>Personnel</th>
                        <th>Month</th>
                        <th>Milestone</th>
                        <th>Approved Date</th>
                        <th>Status</th>
                        <th>Remarks (if Delayed)</th>
                        <th>Requesting for Extension?</th>
                        <th>If yes, Proposed Date Extension</th>
                        <th>If yes, Justification for Extension</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($projectData['reports'] as $report) {
                $html .= '<tr>
                    <td>' . htmlspecialchars($report->user ? $report->user->full_name : 'N/A') . '</td>
                    <td>' . htmlspecialchars(date('F Y', strtotime($report->report_date))) . '</td>
                    <td>' . htmlspecialchars($report->milestone_name ?? '') . '</td>
                    <td>' . htmlspecialchars($report->report_date ? date('F j, Y', strtotime($report->report_date)) : '') . '</td>
                    <td>' . htmlspecialchars(ucfirst($report->status ?? '')) . '</td>
                    <td>' . htmlspecialchars($report->remarks ?? '') . '</td>
                    <td>' . htmlspecialchars($report->has_extension ? 'Yes' : 'No') . '</td>
                    <td>' . htmlspecialchars($report->extension_date ? date('F j, Y', strtotime($report->extension_date)) : '') . '</td>
                    <td>' . htmlspecialchars($report->extension_justification ?? '') . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        // Note section (after all projects)
        $html .= '<div class="note" style="margin-top: 20px;">Note: A Plan of Action and other pertinent documents need to be attached to this document to support the request for extension.</div>';
        
        // Signature section
        $html .= '<div class="signature-section" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 20px;">';
        
        // Get current user (admin)
        $currentUser = Yii::$app->user->identity;
        
        // Prepared by
        $html .= '<div style="width: 33%; text-align: left;">
            <div style="font-weight: bold; margin-bottom: 5px;">Prepared by:</div>';
        
        // Add digital signature image if available
        if ($currentUser && $currentUser->digital_signature) {
            $signaturePath = Yii::getAlias('@backend/web') . $currentUser->digital_signature;
            $signatureDataUri = $this->getSignatureDataUri($signaturePath);
            if ($signatureDataUri) {
                $html .= '<div style="width: 70px; height: 25px; overflow: hidden; margin: 0; padding: 0; line-height: 0;"><img src="' . $signatureDataUri . '" style="width: 70px; height: 25px; display: block; margin: 0; padding: 0;"></div>';
            }
        }
        
        if ($currentUser) {
            $html .= '<div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($currentUser->full_name) . '</div>
                <div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($currentUser->position ?? '') . '</div></div>';
            
            // Reviewed by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Reviewed by:</div>';
            
            $userReviewers = $currentUser->userReviewers;
            if (!empty($userReviewers)) {
                foreach ($userReviewers as $index => $userReviewer) {
                    $reviewer = $userReviewer->reviewer;
                    if ($index > 0) {
                        $html .= '<div style="margin-top: 10px;"></div>';
                    }
                    $html .= '<div>' . htmlspecialchars($reviewer ? $reviewer->full_name : '') . '</div>
                        <div>' . htmlspecialchars($userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : '')) . '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div>______________________________</div>
                    <div>Name and Designation</div></div>';
            }
        } else {
            $html .= '______________________________<br>
                Administrator Name and Position</div>';
            
            $html .= '<div style="text-align: center;"><strong>Reviewed by:</strong><br>
                ______________________________<br>
                Designation</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate Progress Report per Project Excel
     */
    private function generateProgressReportByProjectExcel($sheet, $projectId, $startDate, $endDate, $isAdmin = true)
    {
        // Get project info from first report
        $firstReport = ProgressReport::find()
            ->where(['project_id' => $projectId])
            ->one();
        
        $projectName = 'Unknown Project';
        $projectCode = ''; // Blank by default
        
        if ($firstReport) {
            $projectName = $firstReport->project_name;
        }
        
        // Try to fetch project_code from PMIS database
        try {
            $project = Yii::$app->dbPmis->createCommand(
                'SELECT project_code FROM projects WHERE id = :id',
                [':id' => $projectId]
            )->queryOne();
            
            if ($project && !empty($project['project_code'])) {
                $projectCode = $project['project_code'];
            }
        } catch (\Exception $e) {
            // If PMIS query fails, try from project_data JSON
            if ($firstReport && $firstReport->project_data) {
                $projectData = json_decode($firstReport->project_data, true);
                $projectCode = $projectData['project_code'] ?? '';
            }
        }
        
        // Determine column range based on role
        $lastCol = $isAdmin ? 'I' : 'H';
        
        // Add reference code in its own row at top right
        $sheet->setCellValue($lastCol . '1', 'RES-QF-02 Rev.02/01-31-2023');
        $sheet->getStyle($lastCol . '1')->getFont()->setItalic(true)->setSize(10);
        $sheet->getStyle($lastCol . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        
        // Set header
        $sheet->setCellValue('A2', 'PROJECT PROGRESS REPORT FORM');
        $sheet->mergeCells('A2:' . $lastCol . '2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'PIDS Research Group');
        $sheet->mergeCells('A3:' . $lastCol . '3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A5', 'Project Title: ' . $projectName);
        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->mergeCells('A5:' . $lastCol . '5');
        
        $sheet->setCellValue('A6', 'Project ID: ' . $projectCode);
        $sheet->getStyle('A6')->getFont()->setBold(true);
        $sheet->mergeCells('A6:' . $lastCol . '6');
        
        $sheet->setCellValue('A7', 'Period Covered: ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All'));
        $sheet->mergeCells('A7:' . $lastCol . '7');
        
        $row = 9;
        
        // Get progress reports for this project
        $query = ProgressReport::find()
            ->where(['project_id' => $projectId])
            ->with(['user']);
        
        if ($startDate) {
            $query->andWhere(['>=', 'report_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'report_date', $endDate]);
        }
        
        $reports = $query->orderBy(['user_id' => SORT_ASC, 'report_date' => SORT_ASC])->all();
        
        // Column headers - exclude Personnel column for non-admin
        if ($isAdmin) {
            $headers = ['Personnel', 'Month', 'Milestone', 'Approved Date', 'Status', 'Remarks (if Delayed)', 'Requesting for Extension?', 'If yes, Proposed Date Extension', 'If yes, Justification for Extension'];
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        } else {
            $headers = ['Month', 'Milestone', 'Approved Date', 'Status', 'Remarks (if Delayed)', 'Requesting for Extension?', 'If yes, Proposed Date Extension', 'If yes, Justification for Extension'];
            $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        }
        
        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columns[$index] . $row, $header);
        }
        
        // Style headers
        $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '967259']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);
        
        $row++;
        
        // Data rows
        foreach ($reports as $report) {
            if ($isAdmin) {
                $sheet->setCellValue('A' . $row, $report->user ? $report->user->full_name : 'N/A');
                $sheet->setCellValue('B' . $row, date('F Y', strtotime($report->report_date)));
                $sheet->setCellValue('C' . $row, $report->milestone_name ?? '');
                $sheet->setCellValue('D' . $row, $report->report_date ? date('F j, Y', strtotime($report->report_date)) : '');
                $sheet->setCellValue('E' . $row, ucfirst($report->status ?? ''));
                $sheet->setCellValue('F' . $row, $report->remarks ?? '');
                $sheet->setCellValue('G' . $row, $report->has_extension ? 'Yes' : 'No');
                $sheet->setCellValue('H' . $row, $report->extension_date ? date('F j, Y', strtotime($report->extension_date)) : '');
                $sheet->setCellValue('I' . $row, $report->extension_justification ?? '');
            } else {
                $sheet->setCellValue('A' . $row, date('F Y', strtotime($report->report_date)));
                $sheet->setCellValue('B' . $row, $report->milestone_name ?? '');
                $sheet->setCellValue('C' . $row, $report->report_date ? date('F j, Y', strtotime($report->report_date)) : '');
                $sheet->setCellValue('D' . $row, ucfirst($report->status ?? ''));
                $sheet->setCellValue('E' . $row, $report->remarks ?? '');
                $sheet->setCellValue('F' . $row, $report->has_extension ? 'Yes' : 'No');
                $sheet->setCellValue('G' . $row, $report->extension_date ? date('F j, Y', strtotime($report->extension_date)) : '');
                $sheet->setCellValue('H' . $row, $report->extension_justification ?? '');
            }
            
            // Apply borders
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]);
            
            $row++;
        }
        
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Note: A Plan of Action and other pertinent documents need to be attached to this document to support the request for extension.');
        $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
        
        // Signature section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Prepared by:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row += 2;
        
        // Get current user (admin or assigned personnel)
        $currentUser = Yii::$app->user->identity;
        if ($currentUser && $currentUser->digital_signature) {
            $sheet->setCellValue('A' . $row, 'Signed');
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
            $row++;
        }
        
        if ($currentUser) {
            $sheet->setCellValue('A' . $row, $currentUser->full_name);
            $row++;
            $sheet->setCellValue('A' . $row, $currentUser->position ?? '');
        } else {
            $sheet->setCellValue('A' . $row, '_________________________');
            $row++;
            $sheet->setCellValue('A' . $row, 'Personnel Name');
            $row++;
            $sheet->setCellValue('A' . $row, 'Position');
        }
        
        // Reviewed by
        $row += 2;
        $sheet->setCellValue('A' . $row, 'Reviewed by:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $userReviewers = $currentUser ? $currentUser->userReviewers : [];
        if (!empty($userReviewers)) {
            foreach ($userReviewers as $index => $userReviewer) {
                if ($index > 0) {
                    $row++;
                }
                $reviewer = $userReviewer->reviewer;
                $sheet->setCellValue('A' . $row, $reviewer ? $reviewer->full_name : '');
                $row++;
                $sheet->setCellValue('A' . $row, $userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : ''));
                if ($index < count($userReviewers) - 1) {
                    $row++;
                }
            }
        } else {
            $sheet->setCellValue('A' . $row, '_________________________');
            $row++;
            $sheet->setCellValue('A' . $row, 'Name and Designation');
        }
        
        // Set column widths
        if ($isAdmin) {
            $sheet->getColumnDimension('A')->setWidth(20);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(12);
            $sheet->getColumnDimension('F')->setWidth(25);
            $sheet->getColumnDimension('G')->setWidth(12);
            $sheet->getColumnDimension('H')->setWidth(15);
            $sheet->getColumnDimension('I')->setWidth(25);
        } else {
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(25);
            $sheet->getColumnDimension('F')->setWidth(12);
            $sheet->getColumnDimension('G')->setWidth(15);
            $sheet->getColumnDimension('H')->setWidth(25);
        }
    }

    /**
     * Generate Progress Report per Project PDF
     */
    private function generateProgressReportByProjectPdf($projectId, $startDate, $endDate, $isAdmin = true)
    {
        // Get project info from first report
        $firstReport = ProgressReport::find()
            ->where(['project_id' => $projectId])
            ->one();
        
        $projectName = 'Unknown Project';
        $projectCode = ''; // Blank by default
        
        if ($firstReport) {
            $projectName = $firstReport->project_name;
        }
        
        // Try to fetch project_code from PMIS database
        try {
            $project = Yii::$app->dbPmis->createCommand(
                'SELECT project_code FROM projects WHERE id = :id',
                [':id' => $projectId]
            )->queryOne();
            
            if ($project && !empty($project['project_code'])) {
                $projectCode = $project['project_code'];
            }
        } catch (\Exception $e) {
            // If PMIS query fails, try from project_data JSON
            if ($firstReport && $firstReport->project_data) {
                $projectData = json_decode($firstReport->project_data, true);
                $projectCode = $projectData['project_code'] ?? '';
            }
        }
        
        $html = '<style>
            table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
            th { background-color: #967259; color: white; padding: 8px; text-align: center; font-weight: bold; border: 1px solid #000; }
            td { padding: 6px; border: 1px solid #000; font-size: 9px; }
            .project-title { font-weight: bold; margin: 15px 0; font-size: 12px; }
            .note { margin-top: 20px; font-size: 10px; font-style: italic; }
            .signature-section { margin-top: 30px; font-size: 10px; }
            .ref-code { text-align: right; font-style: italic; font-size: 10px; margin-bottom: 10px; }
        </style>';
        
        $html .= '<div class="ref-code">RES-QF-02 Rev.02/01-31-2023</div>';
        $html .= '<h2 style="text-align: center; margin-top: 0;">PROJECT PROGRESS REPORT FORM</h2>';
        $html .= '<h3 style="text-align: center;">PIDS Research Group</h3>';
        
        $html .= '<div class="project-title">Project Title: ' . htmlspecialchars($projectName) . '</div>';
        $html .= '<div style="font-weight: bold; margin-bottom: 15px; font-size: 12px;"><strong>Project ID:</strong> ' . htmlspecialchars($projectCode) . '</div>';
        $html .= '<div style="margin-bottom: 15px;"><strong>Period Covered:</strong> ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All') . '</div>';
        
        // Get progress reports for this project
        $query = ProgressReport::find()
            ->where(['project_id' => $projectId])
            ->with(['user']);
        
        if ($startDate) {
            $query->andWhere(['>=', 'report_date', $startDate]);
        }
        
        if ($endDate) {
            $query->andWhere(['<=', 'report_date', $endDate]);
        }
        
        $reports = $query->orderBy(['user_id' => SORT_ASC, 'report_date' => SORT_ASC])->all();
        
        // Build table headers - exclude Personnel column for non-admin
        $html .= '<table>
            <thead>
                <tr>';
        
        if ($isAdmin) {
            $html .= '<th>Personnel</th>';
        }
        
        $html .= '<th>Month</th>
                    <th>Milestone</th>
                    <th>Approved Date</th>
                    <th>Status</th>
                    <th>Remarks (if Delayed)</th>
                    <th>Requesting for Extension?</th>
                    <th>If yes, Proposed Date Extension</th>
                    <th>If yes, Justification for Extension</th>
                </tr>
            </thead>
            <tbody>';
        
        // Build data rows - exclude Personnel column for non-admin
        foreach ($reports as $report) {
            $html .= '<tr>';
            
            if ($isAdmin) {
                $html .= '<td>' . htmlspecialchars($report->user ? $report->user->full_name : 'N/A') . '</td>';
            }
            
            $html .= '<td>' . htmlspecialchars(date('F Y', strtotime($report->report_date))) . '</td>
                <td>' . htmlspecialchars($report->milestone_name ?? '') . '</td>
                <td>' . htmlspecialchars($report->report_date ? date('F j, Y', strtotime($report->report_date)) : '') . '</td>
                <td>' . htmlspecialchars(ucfirst($report->status ?? '')) . '</td>
                <td>' . htmlspecialchars($report->remarks ?? '') . '</td>
                <td>' . htmlspecialchars($report->has_extension ? 'Yes' : 'No') . '</td>
                <td>' . htmlspecialchars($report->extension_date ? date('F j, Y', strtotime($report->extension_date)) : '') . '</td>
                <td>' . htmlspecialchars($report->extension_justification ?? '') . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table>';
        
        // Note section
        $html .= '<div class="note">Note: A Plan of Action and other pertinent documents need to be attached to this document to support the request for extension.</div>';
        
        // Signature section
        $html .= '<div class="signature-section" style="display: flex; justify-content: space-between; align-items: flex-start; margin-top: 20px;">';
        
        // Get current user
        $currentUser = Yii::$app->user->identity;
        
        // Prepared by
        $html .= '<div style="width: 33%; text-align: left;">
            <div style="font-weight: bold; margin-bottom: 5px;">Prepared by:</div>';
        
        // Add digital signature image if available
        if ($currentUser && $currentUser->digital_signature) {
            $signaturePath = Yii::getAlias('@backend/web') . $currentUser->digital_signature;
            $signatureDataUri = $this->getSignatureDataUri($signaturePath);
            if ($signatureDataUri) {
                $html .= '<div style="width: 70px; height: 25px; overflow: hidden; margin: 0; padding: 0; line-height: 0;"><img src="' . $signatureDataUri . '" style="width: 70px; height: 25px; display: block; margin: 0; padding: 0;"></div>';
            }
        }
        
        if ($currentUser) {
            $html .= '<div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($currentUser->full_name) . '</div>
                <div style="margin: 0; padding: 0; line-height: 1.1;">' . htmlspecialchars($currentUser->position ?? '') . '</div></div>';
            
            // Reviewed by
            $html .= '<div style="width: 33%; text-align: left;">
                <div style="font-weight: bold; margin-bottom: 5px;">Reviewed by:</div>';
            
            $userReviewers = $currentUser->userReviewers;
            if (!empty($userReviewers)) {
                foreach ($userReviewers as $index => $userReviewer) {
                    $reviewer = $userReviewer->reviewer;
                    if ($index > 0) {
                        $html .= '<div style="margin-top: 10px;"></div>';
                    }
                    $html .= '<div>' . htmlspecialchars($reviewer ? $reviewer->full_name : '') . '</div>
                        <div>' . htmlspecialchars($userReviewer->reviewer_designation ?? ($reviewer ? $reviewer->position : '')) . '</div>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div>______________________________</div>
                    <div>Name and Designation</div></div>';
            }
        } else {
            $html .= '______________________________<br>
                Personnel Name and Position</div>';
            
            $html .= '<div style="text-align: center;"><strong>Reviewed by:</strong><br>
                ______________________________<br>
                Designation</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
