<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%workplan}}`.
 */
class m260119_120000_create_workplan_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%workplan}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'project_id' => $this->string(50)->notNull(),
            'project_name' => $this->string(255)->notNull(),
            'task_type_id' => $this->integer()->notNull(),
            'task_category_id' => $this->integer()->notNull(),
            'project_stage_id' => $this->integer()->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'workplan' => $this->text()->notNull(),
            'is_template' => $this->boolean()->defaultValue(false),
            'template_name' => $this->string(255)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-workplan-user_id',
            '{{%workplan}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-workplan-task_type_id',
            '{{%workplan}}',
            'task_type_id',
            '{{%task_type}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-workplan-task_category_id',
            '{{%workplan}}',
            'task_category_id',
            '{{%task_category}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-workplan-project_stage_id',
            '{{%workplan}}',
            'project_stage_id',
            '{{%project_stage}}',
            'id',
            'CASCADE'
        );

        // Add indexes
        $this->createIndex('idx-workplan-user_id', '{{%workplan}}', 'user_id');
        $this->createIndex('idx-workplan-project_id', '{{%workplan}}', 'project_id');
        $this->createIndex('idx-workplan-is_template', '{{%workplan}}', 'is_template');
        $this->createIndex('idx-workplan-start_date', '{{%workplan}}', 'start_date');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-workplan-project_stage_id', '{{%workplan}}');
        $this->dropForeignKey('fk-workplan-task_category_id', '{{%workplan}}');
        $this->dropForeignKey('fk-workplan-task_type_id', '{{%workplan}}');
        $this->dropForeignKey('fk-workplan-user_id', '{{%workplan}}');
        $this->dropTable('{{%workplan}}');
    }
}
