<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * UserManagementController handles EARS access management
 */
class UserManagementController extends Controller
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
                        'roles' => ['@'], // Only authenticated users
                        // TODO: Add role-based access control for admin only
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'grant-access' => ['post'],
                    'revoke-access' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Display user management page
     * 
     * @return string
     */
    public function actionIndex()
    {
        // Get all personnel from PIDS API
        $pidsApi = Yii::$app->pidsApi;
        $allPersonnel = $pidsApi->getAllPersonnel();

        // Debug logging
        Yii::info('PIDS API Response: ' . print_r($allPersonnel, true), __METHOD__);

        // Get all users with EARS accounts
        $earsUsers = User::find()
            ->where(['not', ['pids_id' => null]])
            ->indexBy('pids_id')
            ->all();

        // Create a map of PIDS IDs to access status
        $accessMap = [];
        foreach ($earsUsers as $user) {
            $accessMap[$user->pids_id] = $user;
        }

        return $this->render('index', [
            'allPersonnel' => $allPersonnel,
            'accessMap' => $accessMap,
            'apiError' => $allPersonnel === null ? 'Failed to fetch personnel from PIDS API' : null,
        ]);
    }

    /**
     * Get personnel from PIDS API (AJAX)
     * 
     * @return Response
     */
    public function actionGetPersonnel()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pidsApi = Yii::$app->pidsApi;
        $allPersonnel = $pidsApi->getAllPersonnel();

        // Log the response for debugging
        Yii::info('Get Personnel AJAX - Fetched: ' . count($allPersonnel ?: []) . ' personnel', __METHOD__);

        if ($allPersonnel && is_array($allPersonnel)) {
            // Get current users with EARS accounts
            $earsUsers = User::find()
                ->where(['not', ['pids_id' => null]])
                ->andWhere(['status' => User::STATUS_ACTIVE])
                ->indexBy('pids_id')
                ->all();

            $result = [];
            foreach ($allPersonnel as $person) {
                // Get ID from personnel
                $pidsId = $person['id'] ?? null;
                
                if ($pidsId === null) {
                    Yii::warning('Personnel without ID', __METHOD__);
                    continue;
                }
                
                $hasAccess = isset($earsUsers[$pidsId]);

                // Extract data from nested structure and accounts array
                $username = '';
                $email = '';
                $profilePicture = null;
                
                if (isset($person['accounts']) && is_array($person['accounts']) && count($person['accounts']) > 0) {
                    $account = $person['accounts'][0];
                    $username = $account['username'] ?? '';
                    $email = $account['email'] ?? '';
                    $profilePicture = $account['profile_picture_url'] ?? $account['profile_picture'] ?? null;
                }

                $result[] = [
                    'id' => $pidsId,
                    'username' => $username,
                    'email' => $email,
                    'full_name' => $person['full_name'] ?? '',
                    'profile_picture' => $profilePicture,
                    'department' => $pidsApi->getDepartmentName($person),
                    'division' => $pidsApi->getDivisionName($person),
                    'position' => $person['position'] ?? '',
                    'has_access' => $hasAccess,
                ];
            }

            Yii::info('Processed ' . count($result) . ' personnel records with nested data', __METHOD__);

            return [
                'success' => true,
                'data' => $result,
                'count' => count($result),
            ];
        }

        Yii::error('Failed to fetch personnel or invalid response format', __METHOD__);
        
        return [
            'success' => false,
            'message' => 'Failed to fetch personnel from PIDS API',
            'debug' => YII_DEBUG ? ['response' => $allPersonnel] : null,
        ];
    }

    /**
     * Grant access to a user
     * 
     * @return Response
     */
    public function actionGrantAccess()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pidsId = Yii::$app->request->post('pids_id');

        if (!$pidsId) {
            Yii::error('Grant Access: PIDS ID is missing', __METHOD__);
            return [
                'success' => false,
                'message' => 'PIDS ID is required',
            ];
        }

        // Check if user is logged in
        if (Yii::$app->user->isGuest) {
            Yii::error('Grant Access: User not authenticated', __METHOD__);
            return [
                'success' => false,
                'message' => 'You must be logged in to grant access',
            ];
        }

        $adminId = Yii::$app->user->id;
        if (!$adminId) {
            Yii::error('Grant Access: Admin ID is null', __METHOD__);
            return [
                'success' => false,
                'message' => 'Unable to identify admin user',
            ];
        }

        // Get personnel data from API
        $pidsApi = Yii::$app->pidsApi;
        $allPersonnel = $pidsApi->getAllPersonnel();

        if (!$allPersonnel) {
            Yii::error('Grant Access: Failed to fetch personnel from API', __METHOD__);
            return [
                'success' => false,
                'message' => 'Failed to connect to PIDS API',
            ];
        }

        $personnelData = null;
        foreach ($allPersonnel as $person) {
            if (isset($person['id']) && $person['id'] == $pidsId) {
                $personnelData = $person;
                break;
            }
        }

        if (!$personnelData) {
            Yii::error('Grant Access: Personnel not found for PIDS ID: ' . $pidsId, __METHOD__);
            return [
                'success' => false,
                'message' => 'Personnel not found in PIDS API',
            ];
        }

        Yii::info('Granting access for PIDS ID: ' . $pidsId . ' by admin ID: ' . $adminId, __METHOD__);
        Yii::info('Personnel data: ' . print_r($personnelData, true), __METHOD__);

        // Grant access by creating/updating User account
        $result = User::grantAccess($pidsId, $personnelData);
        
        if ($result) {
            Yii::info('Access granted successfully for PIDS ID: ' . $pidsId, __METHOD__);
            return [
                'success' => true,
                'message' => 'User account created successfully',
            ];
        }

        Yii::error('Failed to grant access for PIDS ID: ' . $pidsId, __METHOD__);
        return [
            'success' => false,
            'message' => 'Failed to create user account. Please check the logs for details.',
        ];
    }

    /**
     * Revoke access from a user
     * 
     * @return Response
     */
    public function actionRevokeAccess()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $pidsId = Yii::$app->request->post('pids_id');

        if (!$pidsId) {
            return [
                'success' => false,
                'message' => 'PIDS ID is required',
            ];
        }

        if (User::revokeAccess($pidsId)) {
            return [
                'success' => true,
                'message' => 'Access revoked successfully',
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to revoke access',
        ];
    }

    /**
     * Get access statistics
     * 
     * @return Response
     */
    public function actionStats()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $totalWithAccess = User::find()->where(['not', ['pids_id' => null]])->andWhere(['status' => User::STATUS_ACTIVE])->count();
        $totalRevoked = User::find()->where(['not', ['pids_id' => null]])->andWhere(['status' => User::STATUS_INACTIVE])->count();

        return [
            'success' => true,
            'data' => [
                'total_with_access' => $totalWithAccess,
                'total_revoked' => $totalRevoked,
                'total_managed' => $totalWithAccess + $totalRevoked,
            ],
        ];
    }
}
