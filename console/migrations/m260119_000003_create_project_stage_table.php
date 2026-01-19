<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_stage}}`.
 */
class m260119_000003_create_project_stage_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project_stage}}', [
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
            'idx-project_stage-is_active',
            '{{%project_stage}}',
            'is_active'
        );

        // Create index for sort order
        $this->createIndex(
            'idx-project_stage-sort_order',
            '{{%project_stage}}',
            'sort_order'
        );

        // Insert some default project stages
        $this->batchInsert('{{%project_stage}}', ['name', 'description', 'color', 'sort_order', 'created_at', 'updated_at'], [
            ['Planning', 'Project planning and design phase', '#3498DB', 1, time(), time()],
            ['Development', 'Active development phase', '#E67E22', 2, time(), time()],
            ['Testing', 'Quality assurance and testing', '#9B59B6', 3, time(), time()],
            ['Deployment', 'Deployment and release phase', '#27AE60', 4, time(), time()],
            ['Maintenance', 'Post-deployment maintenance', '#95A5A6', 5, time(), time()],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%project_stage}}');
    }
}
