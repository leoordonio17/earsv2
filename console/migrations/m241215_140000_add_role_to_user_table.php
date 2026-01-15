<?php

use yii\db\Migration;

/**
 * Add role column to user table
 */
class m241215_140000_add_role_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'role', $this->string(50)->notNull()->defaultValue('personnel')->after('status'));
        
        // Create index for role column
        $this->createIndex(
            'idx-user-role',
            '{{%user}}',
            'role'
        );
        
        echo "Added role column to user table.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-user-role', '{{%user}}');
        $this->dropColumn('{{%user}}', 'role');
    }
}
