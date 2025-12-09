<?php

use yii\db\Migration;

/**
 * Migrates existing ears_access data to user table
 */
class m241209_120000_migrate_ears_access_to_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Check if ears_access table exists
        $tableSchema = $this->db->getTableSchema('ears_access');
        if (!$tableSchema) {
            echo "ears_access table does not exist. Skipping migration.\n";
            return true;
        }

        // Get all records from ears_access
        $earsAccessRecords = (new \yii\db\Query())
            ->select('*')
            ->from('ears_access')
            ->all();

        echo "Found " . count($earsAccessRecords) . " records in ears_access table.\n";

        $migrated = 0;
        $skipped = 0;
        
        foreach ($earsAccessRecords as $record) {
            // Check if user already exists with this pids_id OR username
            $existingUser = (new \yii\db\Query())
                ->select('id, pids_id')
                ->from('user')
                ->where(['or', 
                    ['pids_id' => $record['pids_id']],
                    ['username' => $record['username']]
                ])
                ->one();

            if ($existingUser) {
                // Update existing user - set pids_id and profile data
                $status = $record['has_access'] == 1 ? 10 : 9; // STATUS_ACTIVE : STATUS_INACTIVE
                
                $this->update('user', [
                    'pids_id' => $record['pids_id'], // Set pids_id if it wasn't set
                    'email' => $record['email'],
                    'full_name' => $record['full_name'],
                    'position' => $record['position'],
                    'department' => $record['department'],
                    'profile_picture' => $record['profile_picture'],
                    'status' => $status,
                    'updated_at' => time(),
                ], ['id' => $existingUser['id']]);
                
                echo "Updated existing user ID {$existingUser['id']} with PIDS ID: {$record['pids_id']}\n";
                $migrated++;
            } else {
                // Create new user
                $status = $record['has_access'] == 1 ? 10 : 9; // STATUS_ACTIVE : STATUS_INACTIVE
                
                $this->insert('user', [
                    'pids_id' => $record['pids_id'],
                    'username' => $record['username'],
                    'email' => $record['email'],
                    'full_name' => $record['full_name'],
                    'position' => $record['position'],
                    'department' => $record['department'],
                    'profile_picture' => $record['profile_picture'],
                    'auth_key' => Yii::$app->security->generateRandomString(),
                    'password_hash' => Yii::$app->security->generatePasswordHash(Yii::$app->security->generateRandomString(32)),
                    'status' => $status,
                    'created_at' => $record['created_at'] ?? time(),
                    'updated_at' => $record['updated_at'] ?? time(),
                ]);
                
                echo "Created new user with PIDS ID: {$record['pids_id']}\n";
                $migrated++;
            }
        }

        echo "\nMigration complete!\n";
        echo "Migrated/Updated: $migrated records\n";
        echo "Skipped: $skipped records\n";

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "This migration cannot be reverted automatically.\n";
        echo "If you need to rollback, you'll need to manually restore the data.\n";
        return true;
    }
}
