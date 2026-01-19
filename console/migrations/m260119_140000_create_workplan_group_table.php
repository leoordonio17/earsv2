<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%workplan_group}}`.
 */
class m260119_140000_create_workplan_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%workplan_group}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'title' => $this->string(255)->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'description' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Add foreign key for user
        $this->addForeignKey(
            'fk-workplan_group-user_id',
            'workplan_group',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // Add workplan_group_id to workplan table
        $this->addColumn('workplan', 'workplan_group_id', $this->integer()->null()->after('id'));

        // Add foreign key
        $this->addForeignKey(
            'fk-workplan-workplan_group_id',
            'workplan',
            'workplan_group_id',
            'workplan_group',
            'id',
            'CASCADE'
        );

        // Add index
        $this->createIndex(
            'idx-workplan-workplan_group_id',
            'workplan',
            'workplan_group_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-workplan-workplan_group_id', 'workplan');
        $this->dropIndex('idx-workplan-workplan_group_id', 'workplan');
        $this->dropColumn('workplan', 'workplan_group_id');
        
        $this->dropForeignKey('fk-workplan_group-user_id', 'workplan_group');
        $this->dropTable('{{%workplan_group}}');
    }
}
