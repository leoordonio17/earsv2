<?php

use yii\db\Migration;

/**
 * Handles adding mode_of_delivery column to table `{{%accomplishment}}`.
 */
class m260119_150000_add_mode_of_delivery_column_to_accomplishment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%accomplishment}}', 'mode_of_delivery', $this->string(20)->after('status_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%accomplishment}}', 'mode_of_delivery');
    }
}
