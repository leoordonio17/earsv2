<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            
            // First attempt: validate with current password hash
            if (!$user || !$user->validatePassword($this->password)) {
                // Password validation failed - try syncing from DTS API
                if ($user && $user->pids_id) {
                    Yii::info('Password validation failed for user: ' . $user->username . '. Attempting to sync from DTS API...', __METHOD__);
                    
                    // Sync password from PIDS API
                    $syncSuccess = $this->syncPasswordFromAPI($user);
                    
                    if ($syncSuccess) {
                        // Refresh user object to get updated password_hash
                        $user->refresh();
                        
                        // Second attempt: validate with newly synced password hash
                        if ($user->validatePassword($this->password)) {
                            Yii::info('Password validated successfully after DTS sync for user: ' . $user->username, __METHOD__);
                            // Clear the cached user to ensure we use the updated one
                            $this->_user = $user;
                            return; // Password is valid after sync
                        }
                    }
                }
                
                // If we get here, password is still invalid
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Sync password from PIDS API
     * 
     * @param User $user
     * @return bool True if password was synced successfully
     */
    private function syncPasswordFromAPI($user)
    {
        try {
            // Get PIDS API component
            $pidsApi = Yii::$app->pidsApi;
            if (!$pidsApi) {
                Yii::warning('PIDS API component not available', __METHOD__);
                return false;
            }

            // Get all personnel from API
            $allPersonnel = $pidsApi->getAllPersonnel();
            if (!$allPersonnel) {
                Yii::warning('Failed to fetch personnel from PIDS API', __METHOD__);
                return false;
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
                Yii::warning('Personnel data not found in PIDS API for user: ' . $user->username, __METHOD__);
                return false;
            }

            // Extract password hash from accounts array
            if (isset($personnelData['accounts']) && is_array($personnelData['accounts']) && count($personnelData['accounts']) > 0) {
                $account = $personnelData['accounts'][0];
                $passwordHash = $account['password'] ?? $account['password_hash'] ?? null;
                
                if ($passwordHash && $passwordHash !== $user->password_hash) {
                    // Update password hash
                    $user->password_hash = $passwordHash;
                    if ($user->save(false)) {
                        Yii::info('Password hash synced from DTS API for user: ' . $user->username, __METHOD__);
                        return true;
                    } else {
                        Yii::error('Failed to save synced password for user: ' . $user->username, __METHOD__);
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            Yii::error('Error syncing password from API: ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $loginSuccess = Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            
            // Automatically sync profile from PIDS API after successful login
            if ($loginSuccess && $user && $user->pids_id) {
                $this->syncUserProfile($user);
            }
            
            return $loginSuccess;
        }
        
        return false;
    }

    /**
     * Sync user profile from PIDS API
     * 
     * @param User $user
     * @return void
     */
    private function syncUserProfile($user)
    {
        try {
            // Get PIDS API component
            $pidsApi = Yii::$app->pidsApi;
            if (!$pidsApi) {
                return;
            }

            // Get all personnel from API
            $allPersonnel = $pidsApi->getAllPersonnel();
            if (!$allPersonnel) {
                return;
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
                return;
            }

            // Extract data from accounts array
            $updated = false;
            if (isset($personnelData['accounts']) && is_array($personnelData['accounts']) && count($personnelData['accounts']) > 0) {
                $account = $personnelData['accounts'][0];
                
                // Update profile picture
                $profilePicture = $account['profile_picture_url'] ?? $account['profile_picture'] ?? null;
                if ($profilePicture && $profilePicture !== $user->profile_picture) {
                    $user->profile_picture = $profilePicture;
                    $updated = true;
                }
                
                // Update password hash
                $passwordHash = $account['password'] ?? $account['password_hash'] ?? null;
                if ($passwordHash && $passwordHash !== $user->password_hash) {
                    $user->password_hash = $passwordHash;
                    $updated = true;
                    Yii::info('Password hash auto-synced from DTS on login for user: ' . $user->username, __METHOD__);
                }
                
                // Update email
                $email = $account['email'] ?? null;
                if ($email && $email !== $user->email) {
                    $user->email = $email;
                    $updated = true;
                }
            }

            // Extract division and department
            $departmentName = null;
            $divisionName = null;
            if (isset($personnelData['division']['department']['name'])) {
                $departmentName = $personnelData['division']['department']['name'];
            }
            if (isset($personnelData['division']['name'])) {
                $divisionName = $personnelData['division']['name'];
            }
            
            if ($departmentName && $departmentName !== $user->department) {
                $user->department = $departmentName;
                $updated = true;
            }
            if ($divisionName && $divisionName !== $user->division) {
                $user->division = $divisionName;
                $updated = true;
            }
            
            // Update other fields
            if (isset($personnelData['full_name']) && $personnelData['full_name'] !== $user->full_name) {
                $user->full_name = $personnelData['full_name'];
                $updated = true;
            }
            if (isset($personnelData['position']) && $personnelData['position'] !== $user->position) {
                $user->position = $personnelData['position'];
                $updated = true;
            }

            // Save if any changes detected
            if ($updated) {
                $user->save(false); // Skip validation for auto-sync
                Yii::info('Auto-synced profile data from DTS API for user: ' . $user->username, __METHOD__);
            }
        } catch (\Exception $e) {
            // Log error but don't interrupt login process
            Yii::error('Error during auto-sync profile: ' . $e->getMessage(), __METHOD__);
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
