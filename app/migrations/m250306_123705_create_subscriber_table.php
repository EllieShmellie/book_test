<?php

use yii\db\Migration;

class m250306_123705_create_subscriber_table extends Migration
{
    protected string $table = '{{%subscriber}}';
    public function safeUp(): void
    {
        $this->createTable($this->table, [
            'subscriber_id'  => $this->primaryKey(),
            'phone'          => $this->string(20)->notNull(),
            'author_id'      => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            'idx-unique-phone-author_id', 
            $this->table, 
            ['phone', 'author_id'], 
            true
        );
    }
    
    public function safeDown(): void
    {
        $this->dropTable($this->table);
    }
}
