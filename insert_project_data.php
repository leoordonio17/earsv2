<?php
// Temporary script to insert sample project assignments
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/vendor/autoload.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php',
    require __DIR__ . '/console/config/main.php',
    require __DIR__ . '/console/config/main-local.php'
);

$application = new yii\console\Application($config);

// Get the first user
$user = \common\models\User::find()->orderBy(['id' => SORT_ASC])->one();

if ($user) {
    // Check if records already exist
    $existing = \common\models\ProjectAssignment::find()
        ->where(['project_id' => ['PROJ001', 'PROJ002']])
        ->count();
    
    if ($existing == 0) {
        // Insert sample data
        $assignment1 = new \common\models\ProjectAssignment();
        $assignment1->project_id = 'PROJ001';
        $assignment1->project_name = 'title';
        $assignment1->user_id = $user->id;
        $assignment1->is_active = 1;
        $assignment1->sort_order = 1;
        $assignment1->save();
        
        $assignment2 = new \common\models\ProjectAssignment();
        $assignment2->project_id = 'PROJ002';
        $assignment2->project_name = 'title2';
        $assignment2->user_id = $user->id;
        $assignment2->is_active = 1;
        $assignment2->sort_order = 2;
        $assignment2->save();
        
        echo "✓ Successfully inserted 2 project assignments for user: " . $user->username . "\n";
    } else {
        echo "✓ Project assignments already exist.\n";
    }
} else {
    echo "✗ No user found in database.\n";
}
