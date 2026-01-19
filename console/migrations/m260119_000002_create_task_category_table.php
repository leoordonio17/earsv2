<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_category}}`.
 */
class m260119_000002_create_task_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_category}}', [
            'id' => $this->primaryKey(),
            'task_type_id' => $this->integer()->notNull(),
            'name' => $this->string(100)->notNull(),
            'description' => $this->text(),
            'color' => $this->string(7)->defaultValue('#B8926A'),
            'is_active' => $this->tinyInteger(1)->defaultValue(1),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create index for task_type_id
        $this->createIndex(
            'idx-task_category-task_type_id',
            '{{%task_category}}',
            'task_type_id'
        );

        // Create index for active status
        $this->createIndex(
            'idx-task_category-is_active',
            '{{%task_category}}',
            'is_active'
        );

        // Create index for sort order
        $this->createIndex(
            'idx-task_category-sort_order',
            '{{%task_category}}',
            'sort_order'
        );

        // Add foreign key
        $this->addForeignKey(
            'fk-task_category-task_type_id',
            '{{%task_category}}',
            'task_type_id',
            '{{%task_type}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Insert some default task categories
        $this->batchInsert('{{%task_category}}', ['task_type_id', 'name', 'description', 'color', 'sort_order', 'created_at', 'updated_at'], [
            [1, 'Documentation', 'Document preparation and filing', '#3498DB', 1, time(), time()],
            [1, 'Meeting', 'Meeting coordination and minutes', '#3498DB', 2, time(), time()],
            [2, 'Data Collection', 'Gathering data and information', '#9B59B6', 1, time(), time()],
            [2, 'Analysis', 'Data analysis and reporting', '#9B59B6', 2, time(), time()],
            [3, 'Coding', 'Software development', '#E67E22', 1, time(), time()],
            [3, 'Testing', 'Quality assurance testing', '#E67E22', 2, time(), time()],
            [4, 'Document Review', 'Review documents and reports', '#27AE60', 1, time(), time()],
            [4, 'Approval', 'Final approval process', '#27AE60', 2, time(), time()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-task_category-task_type_id', '{{%task_category}}');
        $this->dropTable('{{%task_category}}');
    }
}
