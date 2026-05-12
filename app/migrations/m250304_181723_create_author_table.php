<?php

use yii\db\Migration;

class m250304_181723_create_author_table extends Migration
{
    private string $table = '{{%author}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable($this->table, [
            'author_id'  => $this->primaryKey(),
            'last_name'  => $this->string()->notNull(), 
            'first_name' => $this->string()->notNull(),
            'patronymic' => $this->string(),
            'created_at'  => $this->timestamp()->notNull(),
            'updated_at'  => $this->timestamp()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable($this->table);
    }
}
