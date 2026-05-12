<?php

use yii\db\Migration;


class m250306_123558_create_user_table extends Migration
{
    protected string $table = '{{%user}}';
    public function safeUp(): void
    {
        $this->createTable($this->table, [
            'user_id'       => $this->primaryKey(),
            'phone'         => $this->string(20)->notNull()->unique(),
            'password_hash' => $this->string()->notNull(),
            'auth_key'      => $this->string(32)->notNull(),
            'created_at'    => $this->timestamp()->notNull(),
            'updated_at'    => $this->timestamp()->notNull(),
        ]);
    }
    
    public function safeDown(): void
    {
        $this->dropTable($this->table);
    }
}
