<?php

use yii\db\Migration;

/**
 * Handles adding custom_initials column to table `{{%user}}`.
 * Adds a field for users to customize their initials used in report filenames.
 */
class m260213_000000_add_custom_initials_to_user_table extends Migration
{
    /**
     * Extract initials from full name
     * @param string $fullName
     * @return string Initials in uppercase (e.g., "Juan Dela Cruz" -> "JDC")
     */
    private function getInitials($fullName)
    {
        if (empty($fullName)) {
            return '';
        }
        
        // Split name by spaces and get first letter of each part
        $parts = preg_split('/\s+/', trim($fullName));
        $initials = '';
        
        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }
        
        return $initials;
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add custom_initials column
        $this->addColumn('{{%user}}', 'custom_initials', $this->string(10)->after('full_name'));

        // Populate custom_initials with generated initials from current full_name for existing users
        $users = $this->db->createCommand('SELECT id, full_name FROM {{%user}}')->queryAll();
        
        foreach ($users as $user) {
            if (!empty($user['full_name'])) {
                $initials = $this->getInitials($user['full_name']);
                $this->update('{{%user}}', ['custom_initials' => $initials], ['id' => $user['id']]);
            }
        }

        echo "Custom initials populated for " . count($users) . " users.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'custom_initials');
    }
}
