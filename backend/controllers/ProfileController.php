<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Profile controller
 */
class ProfileController extends Controller
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
                    ],
                ],
            ],
        ];
    }

    /**
     * Display user profile
     * 
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Sync profile data from PIDS API
     * 
     * @return \yii\web\Response
     */
    public function actionSync()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        try {
            $user = Yii::$app->user->identity;
            if (!$user || !$user->pids_id) {
                return [
                    'success' => false,
                    'message' => 'User not found or PIDS ID missing',
                ];
            }

            // Get fresh data from PIDS API
            $pidsApi = Yii::$app->pidsApi;
            $allPersonnel = $pidsApi->getAllPersonnel();

            if (!$allPersonnel) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to PIDS API',
                ];
            }

            // Find current user's data
            $personnelData = null;
            foreach ($allPersonnel as $person) {
                if (isset($person['id']) && $person['id'] == $user->pids_id) {
                    $personnelData = $person;
                    break;
                }
            }

            if (!$personnelData) {
                return [
                    'success' => false,
                    'message' => 'Personnel data not found in PIDS API',
                ];
            }

            // Extract username, email, password_hash, and profile picture from accounts array
            $username = '';
            $email = '';
            $profilePicture = null;
            $passwordHash = null;
            if (isset($personnelData['accounts']) && is_array($personnelData['accounts']) && count($personnelData['accounts']) > 0) {
                $account = $personnelData['accounts'][0];
                $username = $account['username'] ?? '';
                $email = $account['email'] ?? '';
                $profilePicture = $account['profile_picture_url'] ?? $account['profile_picture'] ?? null;
                $passwordHash = $account['password'] ?? $account['password_hash'] ?? null;
            }

            // Extract department and division names from nested structure
            $departmentName = null;
            $divisionName = null;
            if (isset($personnelData['division']['department']['name'])) {
                $departmentName = $personnelData['division']['department']['name'];
            } elseif (isset($personnelData['division']['department']['department_name'])) {
                $departmentName = $personnelData['division']['department']['department_name'];
            }
            
            if (isset($personnelData['division']['name'])) {
                $divisionName = $personnelData['division']['name'];
            } elseif (isset($personnelData['division']['division_name'])) {
                $divisionName = $personnelData['division']['division_name'];
            }

            // Update user data
            $user->username = $username;
            $user->email = $email;
            $user->full_name = $personnelData['full_name'] ?? $user->full_name;
            $user->position = $personnelData['position'] ?? $user->position;
            $user->department = $departmentName;
            $user->division = $divisionName;
            $user->profile_picture = $profilePicture;
            // Update password_hash if provided from API
            if ($passwordHash) {
                $user->password_hash = $passwordHash;
                Yii::info('Password hash synced from DTS API', __METHOD__);
            }

            if ($user->save()) {
                return [
                    'success' => true,
                    'message' => 'Profile synced successfully from PIDS DTS',
                    'data' => [
                        'full_name' => $user->full_name,
                        'position' => $user->position,
                        'department' => $user->department,
                        'division' => $user->division,
                        'email' => $user->email,
                        'username' => $user->username,
                        'profile_picture' => $user->profile_picture,
                    ],
                ];
            } else {
                Yii::error('Failed to save user: ' . print_r($user->errors, true), __METHOD__);
                return [
                    'success' => false,
                    'message' => 'Failed to update profile',
                    'errors' => $user->errors,
                ];
            }
        } catch (\Exception $e) {
            Yii::error('Profile sync error: ' . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'An error occurred while syncing profile',
            ];
        }
    }
}
