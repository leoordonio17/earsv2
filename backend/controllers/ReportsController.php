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
        
        return $this->render('index', [
            'isAdmin' => $isAdmin,
            'personnel' => $personnel,
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
            'font' => ['bold' => true],
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
            $sheet->setCellValue('A' . $row, 'Prepared by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A' . $row, $user->full_name);
            $row++;
            $sheet->setCellValue('A' . $row, $user->position ?? '');
            
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Approved By:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A' . $row, '_________________________');
            $row++;
            $sheet->setCellValue('A' . $row, 'Fellow');
        }
        
        // Auto-size columns
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
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
            $sheet->setCellValue('A' . $row, 'Prepared by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue('A' . $row, $user->full_name);
            $row++;
            $sheet->setCellValue('A' . $row, $user->position ?? '');
            
            $row += 2;
            $sheet->setCellValue('A' . $row, 'Approved By:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue('A' . $row, '_________________________');
        $row++;
        $sheet->setCellValue('A' . $row, 'Fellow');
    }
        
        // Auto-size columns
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
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
            $sheet->setCellValue('A' . $row, $user->full_name);
            $row++;
            $sheet->setCellValue('A' . $row, $user->position ?? '');
            
            $row += 4; // Add more space between Prepared by and Reviewed by
            $sheet->setCellValue('A' . $row, 'Reviewed by:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row += 2;
            $sheet->setCellValue('A' . $row, '_________________________');
            $row++;
            $sheet->setCellValue('A' . $row, 'Designation');
        }
        
        // Auto-size columns
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($columns as $column) {
        }
        
        // Auto-size columns
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    /**
     * Export report to PDF
     * @return mixed
     */
    public function actionExportPdf()
    {
        $type = Yii::$app->request->post('type');
        $userId = Yii::$app->request->post('user_id');
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
        
        // Set header data
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
        </style>';
        
        if ($user) {
            $html .= '<div class="header-info">
                <strong>Employee Name:</strong> ' . $user->full_name . ' &nbsp;&nbsp;&nbsp; 
                <strong>Position:</strong> ' . ($user->position ?? 'N/A') . '<br>
                <strong>Department:</strong> ' . ($user->department ?? 'N/A') . ' &nbsp;&nbsp;&nbsp; 
                <strong>Period Covered:</strong> ' . ($startDate ?? 'All') . ' to ' . ($endDate ?? 'All') . '
            </div>';
        }
        
        $html .= '<style>
            .project-header { background-color: #E8E8E8; font-weight: bold; padding: 8px; border: 1px solid #000; }
            .signature-section { margin-top: 30px; }
            .signature-box { display: inline-block; width: 45%; vertical-align: top; }
        </style>
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
            $html .= '<div class="signature-section">
                <div>
                    <strong>Prepared by:</strong><br><br>
                    ' . htmlspecialchars($user->full_name) . '<br>
                    ' . htmlspecialchars($user->position ?? '') . '
                </div>
                <div style="margin-top: 20px;">
                    <strong>Approved By:</strong><br><br>
                    _________________________<br>
                    Fellow
                </div>
            </div>';
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
            $html .= '<div class="signature-section">
                <div>
                    <strong>Prepared by:</strong><br><br>
                    ' . htmlspecialchars($user->full_name) . '<br>
                    ' . htmlspecialchars($user->position ?? '') . '
                </div>
                <div style="margin-top: 20px;">
                    <strong>Approved By:</strong><br><br>
                    _________________________<br>
                    Fellow
                </div>
            </div>';
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
        $html .= '<div class="signature-section">';
        
        if ($user) {
            $html .= '<div><strong>Prepared by:</strong></div>
                <div style="margin-top: 5px;">' . htmlspecialchars($user->full_name) . '</div>
                <div>' . htmlspecialchars($user->position ?? '') . '</div>';
        } else {
            $html .= '<div><strong>Prepared by:</strong></div>
                <div style="margin-top: 30px;">______________________________</div>
                <div>Name and Position</div>';
        }
        
        $html .= '<div style="margin-top: 50px;"><strong>Reviewed by:</strong></div>
            <div style="margin-top: 30px;">______________________________</div>
            <div>Designation</div>
        </div>';
        
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
        $sheet->setCellValue('A' . $row, '______________________________');
        $row++;
        $sheet->setCellValue('A' . $row, 'Administrator Name and Position');
        
        $row += 4; // Add more space between Prepared by and Reviewed by
        $sheet->setCellValue('A' . $row, 'Reviewed by:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row += 2;
        $sheet->setCellValue('A' . $row, '_________________________');
        $row++;
        $sheet->setCellValue('A' . $row, 'Designation');
        
        // Auto-size columns
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
        foreach ($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
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
        $html .= '<div class="signature-section">';
        $html .= '<div><strong>Prepared by:</strong></div>
            <div style="margin-top: 30px;">______________________________</div>
            <div>Administrator Name and Position</div>';
        
        $html .= '<div style="margin-top: 50px;"><strong>Reviewed by:</strong></div>
            <div style="margin-top: 30px;">______________________________</div>
            <div>Designation</div>
        </div>';
        
        return $html;
    }
}
