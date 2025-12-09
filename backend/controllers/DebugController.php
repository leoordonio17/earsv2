<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

/**
 * Debug controller to test PIDS API connection
 */
class DebugController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?', '@'], // Allow both guest and authenticated users
                    ],
                ],
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        // Disable CSRF validation for this controller
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
    
    public function actionTestApi()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        // Test 1: Check if component exists
        if (!isset(Yii::$app->pidsApi)) {
            return [
                'error' => 'PIDS API component not configured',
                'message' => 'Check backend/config/main.php for pidsApi component'
            ];
        }
        
        $pidsApi = Yii::$app->pidsApi;
        
        // Test 2: Fetch personnel (includes nested division/department)
        $personnel = $pidsApi->getAllPersonnel();
        $personnelResult = [
            'success' => $personnel !== null,
            'count' => is_array($personnel) ? count($personnel) : 0,
            'sample' => is_array($personnel) && count($personnel) > 0 ? $personnel[0] : null,
        ];
        
        // Test 3: Extract nested data from sample
        $nestedTest = null;
        if ($personnel && count($personnel) > 0) {
            $samplePerson = $personnel[0];
            $nestedTest = [
                'personnel_id' => $samplePerson['id'] ?? null,
                'full_name' => $samplePerson['full_name'] ?? null,
                'division_name' => $pidsApi->getDivisionName($samplePerson),
                'department_name' => $pidsApi->getDepartmentName($samplePerson),
                'username' => isset($samplePerson['accounts'][0]) ? $samplePerson['accounts'][0]['username'] : null,
                'email' => isset($samplePerson['accounts'][0]) ? $samplePerson['accounts'][0]['email'] : null,
                'raw_division' => $samplePerson['division'] ?? null,
                'raw_accounts' => $samplePerson['accounts'] ?? null,
            ];
        }
        
        return [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'personnel' => $personnelResult,
            'nested_structure_test' => $nestedTest,
            'explanation' => 'PIDS API returns nested structure: personnel.division.department.name',
        ];
    }
}
