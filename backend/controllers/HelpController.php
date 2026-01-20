<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * HelpController provides user guide and tutorial functionality
 */
class HelpController extends Controller
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
                    'export-pdf' => ['post', 'get'],
                ],
            ],
        ];
    }

    /**
     * Display the comprehensive user guide
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Export user guide to PDF
     * @return mixed
     */
    public function actionExportPdf()
    {
        // Create new PDF document
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('TCPDF');
        $pdf->SetAuthor('EARS System');
        $pdf->SetTitle('EARS User Guide & Tutorial');
        $pdf->SetSubject('Comprehensive User Guide');
        
        // Set default header data
        $pdf->SetHeaderData('', 0, 'EARS User Guide & Tutorial', 'Electronic Accomplishment Reporting System');
        
        // Set header and footer fonts
        $pdf->setHeaderFont(['helvetica', '', 10]);
        $pdf->setFooterFont(['helvetica', '', 8]);
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont('courier');
        
        // Set margins
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(true, 25);
        
        // Set image scale factor
        $pdf->setImageScale(1.25);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Get the HTML content
        $html = $this->renderPartial('_pdf-content');
        
        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $pdf->Output('EARS_User_Guide.pdf', 'D');
        
        return Yii::$app->response;
    }
}
