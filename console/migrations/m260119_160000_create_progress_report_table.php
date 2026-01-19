<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%progress_report}}`.
 */
class m260119_160000_create_progress_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%progress_report}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'report_date' => $this->date()->notNull()->comment('Month and year only'),
            'project_id' => $this->string(50)->notNull()->comment('Project ID from API'),
            'project_name' => $this->string(255)->notNull()->comment('Project name from API'),
            'project_data' => $this->text()->comment('JSON data of project info from API'),
            'milestone_id' => $this->string(50)->comment('Milestone ID from API'),
            'milestone_name' => $this->string(255)->comment('Milestone name from API'),
            'deliverables_data' => $this->text()->comment('JSON data of deliverables from API'),
            'status' => $this->string(20)->notNull()->comment('Completed, On-going, Delayed'),
            'has_extension' => $this->boolean()->notNull()->defaultValue(0)->comment('Yes=1, No=0'),
            'extension_date' => $this->date()->comment('Date proposed for extension'),
            'extension_justification' => $this->text()->comment('Justification of extension'),
            'documents' => $this->text()->comment('JSON array of uploaded document paths'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create index for faster querying
        $this->createIndex(
            'idx-progress_report-user_id',
            '{{%progress_report}}',
            'user_id'
        );

        $this->createIndex(
            'idx-progress_report-report_date',
            '{{%progress_report}}',
            'report_date'
        );

        $this->createIndex(
            'idx-progress_report-status',
            '{{%progress_report}}',
            'status'
        );

        // Add foreign key for user_id
        $this->addForeignKey(
            'fk-progress_report-user_id',
            '{{%progress_report}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-progress_report-user_id', '{{%progress_report}}');
        $this->dropIndex('idx-progress_report-status', '{{%progress_report}}');
        $this->dropIndex('idx-progress_report-report_date', '{{%progress_report}}');
        $this->dropIndex('idx-progress_report-user_id', '{{%progress_report}}');
        $this->dropTable('{{%progress_report}}');
    }
}
