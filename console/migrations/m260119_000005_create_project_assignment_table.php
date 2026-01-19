<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%project_assignment}}`.
 */
class m260119_000005_create_project_assignment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%project_assignment}}', [
            'id' => $this->primaryKey(),
            'project_id' => $this->string(50)->notNull(),
            'project_name' => $this->string(255)->notNull(),
            'user_id' => $this->integer()->notNull(),
            'is_active' => $this->tinyInteger(1)->defaultValue(1),
            'sort_order' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Create index for project_id
        $this->createIndex(
            'idx-project_assignment-project_id',
            '{{%project_assignment}}',
            'project_id'
        );

        // Create index for user_id
        $this->createIndex(
            'idx-project_assignment-user_id',
            '{{%project_assignment}}',
            'user_id'
        );

        // Create index for active status
        $this->createIndex(
            'idx-project_assignment-is_active',
            '{{%project_assignment}}',
            'is_active'
        );

        // Create index for sort order
        $this->createIndex(
            'idx-project_assignment-sort_order',
            '{{%project_assignment}}',
            'sort_order'
        );

        // Add foreign key for user_id
        $this->addForeignKey(
            'fk-project_assignment-user_id',
            '{{%project_assignment}}',
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
        $this->dropForeignKey('fk-project_assignment-user_id', '{{%project_assignment}}');
        $this->dropTable('{{%project_assignment}}');
    }
}
