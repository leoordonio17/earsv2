<?php

use yii\db\Migration;

/**
 * Adds profile fields to user table for PIDS SSO integration
 */
class m241209_100000_add_profile_fields_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'pids_id', $this->integer()->null()->after('id'));
        $this->addColumn('{{%user}}', 'full_name', $this->string()->null()->after('username'));
        $this->addColumn('{{%user}}', 'position', $this->string()->null()->after('email'));
        $this->addColumn('{{%user}}', 'department', $this->string()->null()->after('position'));
        $this->addColumn('{{%user}}', 'profile_picture', $this->string()->null()->after('department'));
        
        $this->createIndex('idx-user-pids_id', '{{%user}}', 'pids_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-user-pids_id', '{{%user}}');
        
        $this->dropColumn('{{%user}}', 'profile_picture');
        $this->dropColumn('{{%user}}', 'department');
        $this->dropColumn('{{%user}}', 'position');
        $this->dropColumn('{{%user}}', 'full_name');
        $this->dropColumn('{{%user}}', 'pids_id');
    }
}
