<?php

use yii\db\Migration;

/**
 * Handles adding extension approval fields to table `{{%progress_report}}`.
 */
class m260120_120000_add_extension_approval_fields_to_progress_report extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%progress_report}}', 'extension_status', $this->string(20)->defaultValue('pending')->comment('pending, approved, rejected')->after('extension_justification'));
        $this->addColumn('{{%progress_report}}', 'extension_approved_date', $this->date()->null()->comment('Approved extension date by admin')->after('extension_status'));
        $this->addColumn('{{%progress_report}}', 'extension_rejection_reason', $this->text()->null()->comment('Reason for rejection')->after('extension_approved_date'));
        $this->addColumn('{{%progress_report}}', 'extension_processed_by', $this->integer()->null()->comment('Admin user ID who processed')->after('extension_rejection_reason'));
        $this->addColumn('{{%progress_report}}', 'extension_processed_at', $this->integer()->null()->comment('Timestamp when processed')->after('extension_processed_by'));

        // Add index for extension status
        $this->createIndex(
            'idx-progress_report-extension_status',
            '{{%progress_report}}',
            'extension_status'
        );

        // Add foreign key for extension_processed_by
        $this->addForeignKey(
            'fk-progress_report-extension_processed_by',
            '{{%progress_report}}',
            'extension_processed_by',
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
        $this->dropForeignKey('fk-progress_report-extension_processed_by', '{{%progress_report}}');
        $this->dropIndex('idx-progress_report-extension_status', '{{%progress_report}}');
        $this->dropColumn('{{%progress_report}}', 'extension_processed_at');
        $this->dropColumn('{{%progress_report}}', 'extension_processed_by');
        $this->dropColumn('{{%progress_report}}', 'extension_rejection_reason');
        $this->dropColumn('{{%progress_report}}', 'extension_approved_date');
        $this->dropColumn('{{%progress_report}}', 'extension_status');
    }
}
