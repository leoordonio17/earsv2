<?php

use yii\db\Migration;

class m260212_045049_drop_reviewer_columns_from_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Drop the foreign key constraint first
        $this->dropForeignKey('fk-user-reviewer_id', '{{%user}}');
        
        // Drop the legacy reviewer columns since we now use user_reviewers junction table
        $this->dropColumn('{{%user}}', 'reviewer_id');
        $this->dropColumn('{{%user}}', 'reviewer_designation');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Restore the columns if migration needs to be reverted
        $this->addColumn('{{%user}}', 'reviewer_id', $this->integer()->null());
        $this->addColumn('{{%user}}', 'reviewer_designation', $this->string(255)->null());
        
        // Optionally restore foreign key if needed
        $this->addForeignKey(
            'fk-user-reviewer_id',
            '{{%user}}',
            'reviewer_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260212_045049_drop_reviewer_columns_from_user cannot be reverted.\n";

        return false;
    }
    */
}
