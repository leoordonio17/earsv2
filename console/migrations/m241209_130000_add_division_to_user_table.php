<?php

use yii\db\Migration;

/**
 * Add division column to user table
 */
class m241209_130000_add_division_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'division', $this->string(255)->after('department'));
        
        echo "Added division column to user table.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'division');
    }
}
