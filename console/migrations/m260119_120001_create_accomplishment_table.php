<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%accomplishment}}`.
 */
class m260119_120001_create_accomplishment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%accomplishment}}', [
            'id' => $this->primaryKey(),
            'workplan_id' => $this->integer()->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'accomplished_task' => $this->text()->notNull(),
            'status_id' => $this->integer()->notNull(),
            'is_final_task' => $this->boolean()->defaultValue(false),
            'milestone_id' => $this->integer()->null(),
            'target_deadline' => $this->date()->null(),
            'actual_submission_date' => $this->date()->null(),
            'within_target' => $this->boolean()->null(),
            'reason_for_delay' => $this->text()->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-accomplishment-workplan_id',
            '{{%accomplishment}}',
            'workplan_id',
            '{{%workplan}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-accomplishment-status_id',
            '{{%accomplishment}}',
            'status_id',
            '{{%status}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-accomplishment-milestone_id',
            '{{%accomplishment}}',
            'milestone_id',
            '{{%milestone}}',
            'id',
            'SET NULL'
        );

        // Add indexes
        $this->createIndex('idx-accomplishment-workplan_id', '{{%accomplishment}}', 'workplan_id');
        $this->createIndex('idx-accomplishment-status_id', '{{%accomplishment}}', 'status_id');
        $this->createIndex('idx-accomplishment-is_final_task', '{{%accomplishment}}', 'is_final_task');
        $this->createIndex('idx-accomplishment-start_date', '{{%accomplishment}}', 'start_date');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-accomplishment-milestone_id', '{{%accomplishment}}');
        $this->dropForeignKey('fk-accomplishment-status_id', '{{%accomplishment}}');
        $this->dropForeignKey('fk-accomplishment-workplan_id', '{{%accomplishment}}');
        $this->dropTable('{{%accomplishment}}');
    }
}
