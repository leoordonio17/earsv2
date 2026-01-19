<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\ProjectAssignment;
use common\models\User;

class DataController extends Controller
{
    public function actionInsertProjects($userId = null, $projectId1 = 25, $projectName1 = 'title', $projectId2 = 29, $projectName2 = 'title2')
    {
        // If userId not provided, get the first user
        if ($userId === null) {
            $user = User::find()->orderBy(['id' => SORT_ASC])->one();
            if (!$user) {
                echo "✗ No user found in database.\n";
                return 1;
            }
            $userId = $user->id;
        } else {
            $user = User::findOne($userId);
            if (!$user) {
                echo "✗ User with ID {$userId} not found.\n";
                return 1;
            }
        }
        
        // Clear existing assignments for this user first
        ProjectAssignment::deleteAll(['user_id' => $userId]);
        
        // Insert sample data
        $assignment1 = new ProjectAssignment();
        $assignment1->project_id = 'PROJ' . str_pad($projectId1, 3, '0', STR_PAD_LEFT);
        $assignment1->project_name = $projectName1;
        $assignment1->user_id = $userId;
        
        if (!$assignment1->save()) {
            echo "✗ Error saving assignment1: " . print_r($assignment1->errors, true) . "\n";
            return 1;
        }
        
        $assignment2 = new ProjectAssignment();
        $assignment2->project_id = 'PROJ' . str_pad($projectId2, 3, '0', STR_PAD_LEFT);
        $assignment2->project_name = $projectName2;
        $assignment2->user_id = $userId;
        
        if (!$assignment2->save()) {
            echo "✗ Error saving assignment2: " . print_r($assignment2->errors, true) . "\n";
            return 1;
        }
        
        echo "✓ Successfully inserted 2 project assignments for user: " . $user->username . " (ID: {$userId})\n";
        echo "  - {$assignment1->project_id}: {$projectName1}\n";
        echo "  - {$assignment2->project_id}: {$projectName2}\n";
        
        return 0;
    }
}
