<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%task_type}}`.
 */
class m260119_000001_create_task_type_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%task_type}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'description' => $this->text(),
            'color' => $this->string(7)->defaultValue('#967259'),
            'is_active' => $this->tinyInteger(1)->defaultValue(1),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create index for active status
        $this->createIndex(
            'idx-task_type-is_active',
            '{{%task_type}}',
            'is_active'
        );

        // Create index for sort order
        $this->createIndex(
            'idx-task_type-sort_order',
            '{{%task_type}}',
            'sort_order'
        );

        // Insert some default task types
        $this->batchInsert('{{%task_type}}', ['name', 'description', 'color', 'sort_order', 'created_at', 'updated_at'], [
            ['Administrative', 'Administrative tasks', '#3498DB', 1, time(), time()],
            ['Research', 'Research and analysis tasks', '#9B59B6', 2, time(), time()],
            ['Development', 'Development and implementation', '#E67E22', 3, time(), time()],
            ['Review', 'Review and approval tasks', '#27AE60', 4, time(), time()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%task_type}}');
    }
}
