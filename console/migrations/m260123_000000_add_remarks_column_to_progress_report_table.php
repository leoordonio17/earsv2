<?php

use yii\db\Migration;

/**
 * Handles adding remarks column to table `progress_report`.
 */
class m260123_000000_add_remarks_column_to_progress_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%progress_report}}', 'remarks', $this->text()->null()->after('status'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%progress_report}}', 'remarks');
    }
}
