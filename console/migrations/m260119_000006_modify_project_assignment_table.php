<?php

use yii\db\Migration;

/**
 * Handles modifications to table `{{%project_assignment}}`.
 */
class m260119_000006_modify_project_assignment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Drop indexes first if they exist
        try {
            $this->dropIndex('idx-project_assignment-is_active', '{{%project_assignment}}');
        } catch (\Exception $e) {
            // Index may not exist, continue
        }
        
        try {
            $this->dropIndex('idx-project_assignment-sort_order', '{{%project_assignment}}');
        } catch (\Exception $e) {
            // Index may not exist, continue
        }
        
        // Drop columns if they exist
        $tableSchema = Yii::$app->db->schema->getTableSchema('{{%project_assignment}}');
        
        if (isset($tableSchema->columns['is_active'])) {
            $this->dropColumn('{{%project_assignment}}', 'is_active');
        }
        
        if (isset($tableSchema->columns['sort_order'])) {
            $this->dropColumn('{{%project_assignment}}', 'sort_order');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Add back the columns
        $this->addColumn('{{%project_assignment}}', 'is_active', $this->tinyInteger(1)->defaultValue(1));
        $this->addColumn('{{%project_assignment}}', 'sort_order', $this->integer()->defaultValue(0));
        
        // Recreate indexes
        $this->createIndex('idx-project_assignment-is_active', '{{%project_assignment}}', 'is_active');
        $this->createIndex('idx-project_assignment-sort_order', '{{%project_assignment}}', 'sort_order');
    }
}
