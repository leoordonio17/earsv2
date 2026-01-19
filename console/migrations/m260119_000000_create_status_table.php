<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%status}}`.
 */
class m260119_000000_create_status_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%status}}', [
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
            'idx-status-is_active',
            '{{%status}}',
            'is_active'
        );

        // Create index for sort order
        $this->createIndex(
            'idx-status-sort_order',
            '{{%status}}',
            'sort_order'
        );

        // Insert some default statuses
        $this->batchInsert('{{%status}}', ['name', 'description', 'color', 'sort_order', 'created_at', 'updated_at'], [
            ['Pending', 'Waiting for action', '#FFA500', 1, time(), time()],
            ['In Progress', 'Currently being worked on', '#3498DB', 2, time(), time()],
            ['Completed', 'Finished successfully', '#27AE60', 3, time(), time()],
            ['Cancelled', 'No longer needed', '#E74C3C', 4, time(), time()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%status}}');
    }
}
