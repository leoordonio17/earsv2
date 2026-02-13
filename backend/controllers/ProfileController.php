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

            // Auto-update custom_initials if not set or if full_name changed
            if (empty($user->custom_initials) || $user->isAttributeChanged('full_name')) {
                $user->custom_initials = \common\models\User::generateInitials($user->full_name);
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

    /**
     * Upload digital signature
     * 
     * @return \yii\web\Response
     */
    public function actionUploadSignature()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;
        $file = \yii\web\UploadedFile::getInstanceByName('signature');

        if (!$file) {
            return [
                'success' => false,
                'message' => 'No file uploaded',
            ];
        }

        // Validate file type
        $allowedExtensions = ['png', 'jpg', 'jpeg'];
        $extension = strtolower($file->extension);
        
        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'message' => 'Only PNG and JPG files are allowed',
            ];
        }

        // Validate file size (max 2MB)
        if ($file->size > 2 * 1024 * 1024) {
            return [
                'success' => false,
                'message' => 'File size must not exceed 2MB',
            ];
        }

        // Create upload directory if it doesn't exist
        $uploadDir = Yii::getAlias('@backend/web/uploads/signatures/');
        if (!\yii\helpers\FileHelper::createDirectory($uploadDir)) {
            return [
                'success' => false,
                'message' => 'Failed to create upload directory',
            ];
        }

        // Delete old signature if exists
        if ($user->digital_signature) {
            $oldFile = Yii::getAlias('@backend/web') . $user->digital_signature;
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }
        }

        // Generate unique filename
        $filename = 'signature_' . $user->id . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $filename;

        // Save file
        if ($file->saveAs($filePath)) {
            $user->digital_signature = '/uploads/signatures/' . $filename;
            
            if ($user->save(false)) {
                return [
                    'success' => true,
                    'message' => 'Signature uploaded successfully',
                    'signature_url' => $user->digital_signature,
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Failed to save signature',
        ];
    }

    /**
     * Update reviewer and approver
     * 
     * @return \yii\web\Response
     */
    public function actionUpdateSignatureSettings()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;
        $post = Yii::$app->request->post();

        $reviewerIds = $post['reviewer_ids'] ?? [];
        $approverId = $post['approver_id'] ?? null;

        // Handle multiple reviewers
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Delete existing reviewer assignments
            \common\models\UserReviewer::deleteAll(['user_id' => $user->id]);

            // Create new reviewer assignments
            if (!empty($reviewerIds) && is_array($reviewerIds)) {
                foreach ($reviewerIds as $reviewerId) {
                    $reviewer = \common\models\User::findOne($reviewerId);
                    if ($reviewer) {
                        $userReviewer = new \common\models\UserReviewer();
                        $userReviewer->user_id = $user->id;
                        $userReviewer->reviewer_id = $reviewerId;
                        $userReviewer->reviewer_designation = $reviewer->position;
                        
                        if (!$userReviewer->save()) {
                            throw new \Exception('Failed to save reviewer assignment');
                        }
                    }
                }
            }

            // Handle approver (still single)
            if ($approverId) {
                $approver = \common\models\User::findOne($approverId);
                $user->approver_id = $approverId;
                $user->approver_designation = $approver ? $approver->position : null;
            } else {
                $user->approver_id = null;
                $user->approver_designation = null;
            }

            if ($user->save(false)) {
                $transaction->commit();
                return [
                    'success' => true,
                    'message' => 'Signature settings updated successfully',
                ];
            }

            throw new \Exception('Failed to update user');

        } catch (\Exception $e) {
            $transaction->rollBack();
            return [
                'success' => false,
                'message' => 'Failed to update signature settings: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete digital signature
     * 
     * @return \yii\web\Response
     */
    public function actionDeleteSignature()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;

        if ($user->digital_signature) {
            $oldFile = Yii::getAlias('@backend/web') . $user->digital_signature;
            if (file_exists($oldFile)) {
                @unlink($oldFile);
            }

            $user->digital_signature = null;
            
            if ($user->save(false)) {
                return [
                    'success' => true,
                    'message' => 'Signature deleted successfully',
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'No signature to delete or failed to delete',
        ];
    }

    /**
     * Update custom initials
     * 
     * @return \yii\web\Response
     */
    public function actionUpdateInitials()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;
        $customInitials = Yii::$app->request->post('custom_initials');

        if (empty($customInitials)) {
            return [
                'success' => false,
                'message' => 'Custom initials cannot be empty',
            ];
        }

        // Validate format (uppercase letters and numbers only)
        if (!preg_match('/^[A-Z0-9]+$/', $customInitials)) {
            return [
                'success' => false,
                'message' => 'Custom initials must contain only uppercase letters and numbers',
            ];
        }

        if (strlen($customInitials) > 10) {
            return [
                'success' => false,
                'message' => 'Custom initials must not exceed 10 characters',
            ];
        }

        $user->custom_initials = strtoupper($customInitials);
        
        if ($user->save(false)) {
            return [
                'success' => true,
                'message' => 'Custom initials updated successfully',
                'initials' => $user->custom_initials,
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to update custom initials',
        ];
    }
}
