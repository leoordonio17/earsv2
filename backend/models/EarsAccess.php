<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "ears_access".
 *
 * @property int $id
 * @property int $pids_id PIDS personnel ID
 * @property string $username
 * @property string $email
 * @property string $full_name
 * @property string|null $profile_picture
 * @property string|null $department
 * @property string|null $position
 * @property int $has_access 1 = granted, 0 = revoked
 * @property int $created_by Admin user ID who granted access
 * @property string $created_at
 * @property string $updated_at
 */
class EarsAccess extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ears_access';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pids_id', 'username', 'email', 'full_name'], 'required'],
            [['pids_id', 'has_access', 'created_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['username', 'email'], 'string', 'max' => 255],
            [['full_name', 'profile_picture', 'department', 'position'], 'string', 'max' => 255],
            [['pids_id'], 'unique'],
            [['has_access'], 'default', 'value' => 0],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pids_id' => 'PIDS ID',
            'username' => 'Username',
            'email' => 'Email',
            'full_name' => 'Full Name',
            'profile_picture' => 'Profile Picture',
            'department' => 'Department',
            'position' => 'Position',
            'has_access' => 'Access Status',
            'created_by' => 'Granted By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Check if user has access to EARS
     * 
     * @param int $pidsId PIDS personnel ID
     * @return bool
     */
    public static function hasAccess($pidsId)
    {
        $access = self::findOne(['pids_id' => $pidsId]);
        return $access && $access->has_access == 1;
    }

    /**
     * Grant access to a user
     * 
     * @param int $pidsId
     * @param array $personnelData
     * @param int $adminId
     * @return bool
     */
    public static function grantAccess($pidsId, $personnelData, $adminId)
    {
        $access = self::findOne(['pids_id' => $pidsId]);
        
        if (!$access) {
            $access = new self();
            $access->pids_id = $pidsId;
            $access->created_by = $adminId;
        }
        
        // Extract username and email from accounts array (nested structure)
        $username = '';
        $email = '';
        $profilePicture = null;
        if (isset($personnelData['accounts']) && is_array($personnelData['accounts']) && count($personnelData['accounts']) > 0) {
            $account = $personnelData['accounts'][0];
            $username = $account['username'] ?? '';
            $email = $account['email'] ?? '';
            $profilePicture = $account['profile_picture_url'] ?? $account['profile_picture'] ?? null;
        }
        
        // Extract department name from nested structure: division.department.name
        $departmentName = null;
        if (isset($personnelData['division']['department']['name'])) {
            $departmentName = $personnelData['division']['department']['name'];
        } elseif (isset($personnelData['division']['department']['department_name'])) {
            $departmentName = $personnelData['division']['department']['department_name'];
        }
        
        $access->username = $username;
        $access->email = $email;
        $access->full_name = $personnelData['full_name'] ?? '';
        $access->profile_picture = $profilePicture;
        $access->department = $departmentName;
        $access->position = $personnelData['position'] ?? '';
        $access->has_access = 1;
        
        if (!$access->save()) {
            Yii::error('Failed to save EarsAccess: ' . print_r($access->errors, true), __METHOD__);
            return false;
        }
        
        return true;
    }

    /**
     * Revoke access from a user
     * 
     * @param int $pidsId
     * @return bool
     */
    public static function revokeAccess($pidsId)
    {
        $access = self::findOne(['pids_id' => $pidsId]);
        
        if ($access) {
            $access->has_access = 0;
            return $access->save();
        }
        
        return false;
    }

    /**
     * Get created by user
     */
    public function getCreatedBy()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'created_by']);
    }
}
