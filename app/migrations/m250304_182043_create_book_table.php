<?php

use yii\db\Migration;

class m250304_182043_create_book_table extends Migration
{
    private string $table = '{{%book}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable($this->table, [
            'book_id'     => $this->primaryKey(),
            'title'       => $this->string()->notNull(),
            'year'        => $this->integer()->notNull(),
            'description' => $this->text(),
            'isbn'        => $this->string(13)->unique(),
            'cover'       => $this->string(),
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
