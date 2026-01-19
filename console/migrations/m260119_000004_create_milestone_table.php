<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%milestone}}`.
 */
class m260119_000004_create_milestone_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%milestone}}', [
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
            'idx-milestone-is_active',
            '{{%milestone}}',
            'is_active'
        );

        // Create index for sort order
        $this->createIndex(
            'idx-milestone-sort_order',
            '{{%milestone}}',
            'sort_order'
        );

        // Insert some default milestones
        $this->batchInsert('{{%milestone}}', ['name', 'description', 'color', 'sort_order', 'created_at', 'updated_at'], [
            ['Kickoff', 'Project kickoff and initiation', '#3498DB', 1, time(), time()],
            ['Requirements Completed', 'All requirements gathered and approved', '#9B59B6', 2, time(), time()],
            ['Design Approved', 'Design phase completed and approved', '#E67E22', 3, time(), time()],
            ['Development Complete', 'Development phase finished', '#F39C12', 4, time(), time()],
            ['Testing Complete', 'All testing activities completed', '#1ABC9C', 5, time(), time()],
            ['Go Live', 'Project deployed to production', '#27AE60', 6, time(), time()],
            ['Project Closure', 'Project formally closed', '#95A5A6', 7, time(), time()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%milestone}}');
    }
}
