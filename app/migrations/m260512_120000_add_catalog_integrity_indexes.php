<?php

use yii\db\Migration;

class m260512_120000_add_catalog_integrity_indexes extends Migration
{
    public function safeUp(): void
    {
        $this->createIndex(
            '{{%idx-book-year}}',
            '{{%book}}',
            'year'
        );

        $this->createIndex(
            '{{%idx-book-isbn}}',
            '{{%book}}',
            'isbn',
            true
        );

        $this->createIndex(
            '{{%idx-subscriber-author_id}}',
            '{{%subscriber}}',
            'author_id'
        );

        $this->addForeignKey(
            '{{%fk-subscriber-author_id}}',
            '{{%subscriber}}',
            'author_id',
            '{{%author}}',
            'author_id',
            'CASCADE'
        );
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('{{%fk-subscriber-author_id}}', '{{%subscriber}}');
        $this->dropIndex('{{%idx-subscriber-author_id}}', '{{%subscriber}}');
        $this->dropIndex('{{%idx-book-isbn}}', '{{%book}}');
        $this->dropIndex('{{%idx-book-year}}', '{{%book}}');
    }
}
