<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_reviewers}}`.
 */
class m260212_000000_create_user_reviewers_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_reviewers}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'reviewer_id' => $this->integer()->notNull(),
            'reviewer_designation' => $this->string(255)->null(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        // Add foreign keys
        $this->addForeignKey(
            'fk-user_reviewers-user_id',
            '{{%user_reviewers}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-user_reviewers-reviewer_id',
            '{{%user_reviewers}}',
            'reviewer_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // Add unique index to prevent duplicate reviewer assignments
        $this->createIndex(
            'idx-user_reviewers-user_id-reviewer_id',
            '{{%user_reviewers}}',
            ['user_id', 'reviewer_id'],
            true
        );

        // Migrate existing reviewer data to the new table
        $this->execute("
            INSERT INTO {{%user_reviewers}} (user_id, reviewer_id, reviewer_designation, created_at, updated_at)
            SELECT id, reviewer_id, reviewer_designation, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
            FROM {{%user}}
            WHERE reviewer_id IS NOT NULL
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_reviewers}}');
    }
}
