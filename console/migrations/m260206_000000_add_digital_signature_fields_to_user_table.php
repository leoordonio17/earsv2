<?php

use yii\db\Migration;

/**
 * Adds digital signature and reviewer/approver fields to user table
 */
class m260206_000000_add_digital_signature_fields_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add digital signature field
        $this->addColumn('{{%user}}', 'digital_signature', $this->string()->null()->after('profile_picture')->comment('Path to digital signature image'));
        
        // Add reviewer fields
        $this->addColumn('{{%user}}', 'reviewer_id', $this->integer()->null()->after('digital_signature')->comment('User ID of reviewer'));
        $this->addColumn('{{%user}}', 'reviewer_designation', $this->string()->null()->after('reviewer_id')->comment('Reviewer designation/title'));
        
        // Add approver fields  
        $this->addColumn('{{%user}}', 'approver_id', $this->integer()->null()->after('reviewer_designation')->comment('User ID of approver'));
        $this->addColumn('{{%user}}', 'approver_designation', $this->string()->null()->after('approver_id')->comment('Approver designation/title'));
        
        // Add foreign keys
        $this->addForeignKey(
            'fk-user-reviewer_id',
            '{{%user}}',
            'reviewer_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk-user-approver_id',
            '{{%user}}',
            'approver_id',
            '{{%user}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-user-approver_id', '{{%user}}');
        $this->dropForeignKey('fk-user-reviewer_id', '{{%user}}');
        
        $this->dropColumn('{{%user}}', 'approver_designation');
        $this->dropColumn('{{%user}}', 'approver_id');
        $this->dropColumn('{{%user}}', 'reviewer_designation');
        $this->dropColumn('{{%user}}', 'reviewer_id');
        $this->dropColumn('{{%user}}', 'digital_signature');
    }
}
