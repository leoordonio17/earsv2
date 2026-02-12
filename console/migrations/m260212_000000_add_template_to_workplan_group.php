<?php

use yii\db\Migration;

/**
 * Add template support to workplan_group table
 */
class m260212_000000_add_template_to_workplan_group extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%workplan_group}}', 'is_template', $this->boolean()->defaultValue(false)->after('description'));
        $this->addColumn('{{%workplan_group}}', 'template_name', $this->string(255)->null()->after('is_template'));
        
        // Add index for template queries
        $this->createIndex('idx-workplan_group-is_template', '{{%workplan_group}}', 'is_template');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-workplan_group-is_template', '{{%workplan_group}}');
        $this->dropColumn('{{%workplan_group}}', 'template_name');
        $this->dropColumn('{{%workplan_group}}', 'is_template');
    }
}
