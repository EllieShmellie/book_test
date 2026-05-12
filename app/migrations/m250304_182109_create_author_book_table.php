<?php

use yii\db\Migration;

class m250304_182109_create_author_book_table extends Migration
{
    private string $table = '{{%author_book}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable($this->table, [
            'author_book_id' => $this->primaryKey(),
            'author_id'      => $this->integer()->notNull(),
            'book_id'        => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            '{{%idx-author_book-author_id-book_id}}',
            $this->table,
            ['author_id', 'book_id'],
            true
        );

        $this->addForeignKey(
            '{{%fk-author_book-book_id}}',
            $this->table,
            'book_id',
            '{{%book}}',
            'book_id',
            'CASCADE'
        );

        $this->createIndex(
            '{{%idx-author_book-book_id}}',
            $this->table,
            'book_id'
        );

        $this->createIndex(
            '{{%idx-author_book-author_id}}',
            $this->table,
            'author_id'
        );

        $this->addForeignKey(
            '{{%fk-author_book-author_id}}',
            $this->table,
            'author_id',
            '{{%author}}',
            'author_id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey('{{%fk-author_book-book_id}}', $this->table);
        $this->dropIndex('{{%idx-author_book-book_id}}', $this->table);

        $this->dropForeignKey('{{%fk-author_book-author_id}}', $this->table);
        $this->dropIndex('{{%idx-author_book-author_id}}', $this->table);
        $this->dropIndex('{{%idx-author_book-author_id-book_id}}', $this->table);

        $this->dropTable($this->table);
    }
}
