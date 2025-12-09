<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ears_access}}`.
 */
class m241209_000000_create_ears_access_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ears_access}}', [
            'id' => $this->primaryKey(),
            'pids_id' => $this->integer()->notNull()->unique()->comment('PIDS Personnel ID'),
            'username' => $this->string(255)->notNull(),
            'email' => $this->string(255)->notNull(),
            'full_name' => $this->string(255)->notNull(),
            'profile_picture' => $this->string(255)->null(),
            'department' => $this->string(255)->null(),
            'position' => $this->string(255)->null(),
            'has_access' => $this->tinyInteger(1)->notNull()->defaultValue(0)->comment('1 = granted, 0 = revoked'),
            'created_by' => $this->integer()->notNull()->comment('Admin user ID who granted access'),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex(
            'idx-ears_access-pids_id',
            '{{%ears_access}}',
            'pids_id'
        );

        $this->createIndex(
            'idx-ears_access-has_access',
            '{{%ears_access}}',
            'has_access'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ears_access}}');
    }
}
