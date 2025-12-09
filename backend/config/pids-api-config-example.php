<?php
/**
 * PIDS API Integration Configuration
 * 
 * Add this to your backend/config/main.php or main-local.php
 * in the 'components' section:
 */

return [
    'components' => [
        // ... other components ...
        
        'pidsApi' => [
            'class' => 'backend\components\PidsApiComponent',
            'apiBaseUrl' => 'https://dts.pids.gov.ph/api',
            'apiToken' => 'db7af4b8bf265411f6e9ee3b6b78eabaa2ed3e9cb6d1b1239dbf2e86984d127d',
        ],
        
        // ... other components ...
    ],
];

/**
 * USAGE EXAMPLES:
 * 
 * 1. In your LoginForm model (common/models/LoginForm.php):
 * 
 * public function login()
 * {
 *     if ($this->validate()) {
 *         // Try to authenticate with PIDS API
 *         $pidsApi = Yii::$app->pidsApi;
 *         $personnel = $pidsApi->authenticateUser($this->username);
 *         
 *         if ($personnel && $pidsApi->hasEarsAccess($personnel)) {
 *             // Find or create local user
 *             $user = User::findByUsername($this->username);
 *             
 *             if (!$user) {
 *                 // Create new user from PIDS data
 *                 $user = new User();
 *                 $user->username = $personnel['username'] ?? $this->username;
 *                 $user->email = $personnel['email'] ?? '';
 *                 // Set other fields from $personnel array
 *                 $user->save();
 *             }
 *             
 *             return Yii::$app->user->login($user, $this->rememberMe ? 3600*24*30 : 0);
 *         }
 *     }
 *     return false;
 * }
 * 
 * 
 * 2. In your layout to show profile picture:
 * 
 * // In main.php layout
 * $pidsApi = Yii::$app->pidsApi;
 * $personnel = $pidsApi->getPersonnelByIdentifier(Yii::$app->user->identity->username);
 * $profilePicUrl = $pidsApi->getProfilePictureUrl($personnel);
 * 
 * if ($profilePicUrl) {
 *     echo Html::img($profilePicUrl, ['class' => 'profile-avatar', 'alt' => 'Profile']);
 * } else {
 *     // Show initial
 *     echo '<div class="profile-avatar">' . strtoupper(substr(Yii::$app->user->identity->username, 0, 1)) . '</div>';
 * }
 * 
 * 
 * 3. To get all personnel (for admin purposes):
 * 
 * $pidsApi = Yii::$app->pidsApi;
 * $allPersonnel = $pidsApi->getAllPersonnel();
 * 
 * foreach ($allPersonnel as $person) {
 *     echo $person['name'] . ' - ' . $person['email'];
 *     // Access other fields as available in the API response
 * }
 */
