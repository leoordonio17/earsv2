<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property integer $pids_id
 * @property string $username
 * @property string $full_name
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $position
 * @property string $department
 * @property string $profile_picture
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
            [['pids_id'], 'integer'],
            [['full_name', 'position', 'department', 'division', 'profile_picture'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Grant EARS access by creating/updating user account
     * 
     * @param int $pidsId PIDS personnel ID
     * @param array $personnelData Personnel data from PIDS API
     * @return bool
     */
    public static function grantAccess($pidsId, $personnelData)
    {
        // Find existing user by pids_id
        $user = self::find()->where(['pids_id' => $pidsId])->one();
        
        if (!$user) {
            $user = new self();
            $user->pids_id = $pidsId;
            $user->status = self::STATUS_ACTIVE;
            $user->auth_key = Yii::$app->security->generateRandomString();
            // Set a random password hash (user won't use it, SSO only)
            $user->password_hash = Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomString(32));
        }
        
        // Extract username and email from accounts array
        $username = '';
        $email = '';
        $profilePicture = null;
        if (isset($personnelData['accounts']) && is_array($personnelData['accounts']) && count($personnelData['accounts']) > 0) {
            $account = $personnelData['accounts'][0];
            $username = $account['username'] ?? '';
            $email = $account['email'] ?? '';
            $profilePicture = $account['profile_picture_url'] ?? $account['profile_picture'] ?? null;
        }
        
        // Extract department name from nested structure
        $departmentName = null;
        $divisionName = null;
        if (isset($personnelData['division']['department']['name'])) {
            $departmentName = $personnelData['division']['department']['name'];
        } elseif (isset($personnelData['division']['department']['department_name'])) {
            $departmentName = $personnelData['division']['department']['department_name'];
        }
        
        // Extract division name
        if (isset($personnelData['division']['name'])) {
            $divisionName = $personnelData['division']['name'];
        } elseif (isset($personnelData['division']['division_name'])) {
            $divisionName = $personnelData['division']['division_name'];
        }
        
        // Update user data
        $user->username = $username;
        $user->email = $email;
        $user->full_name = $personnelData['full_name'] ?? '';
        $user->position = $personnelData['position'] ?? '';
        $user->department = $departmentName;
        $user->division = $divisionName;
        $user->profile_picture = $profilePicture;
        $user->status = self::STATUS_ACTIVE;
        
        if (!$user->save()) {
            Yii::error('Failed to save User: ' . print_r($user->errors, true), __METHOD__);
            return false;
        }
        
        return true;
    }

    /**
     * Revoke EARS access by deactivating user
     * 
     * @param int $pidsId PIDS personnel ID
     * @return bool
     */
    public static function revokeAccess($pidsId)
    {
        $user = self::find()->where(['pids_id' => $pidsId])->one();
        
        if ($user) {
            $user->status = self::STATUS_INACTIVE;
            return $user->save();
        }
        
        return false;
    }

    /**
     * Check if user has EARS access
     * 
     * @param int $pidsId PIDS personnel ID  
     * @return bool
     */
    public static function hasEarsAccess($pidsId)
    {
        return self::find()
            ->where(['pids_id' => $pidsId, 'status' => self::STATUS_ACTIVE])
            ->exists();
    }
}
