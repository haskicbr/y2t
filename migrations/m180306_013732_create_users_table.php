<?php

use yii\db\Migration;

/**
 * Handles the creation of table `users`.
 */
class m180306_013732_create_users_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('users', [
            'id'       => $this->primaryKey(),
            'email'    => $this->string(255),
            'password' => $this->string(500),
            'status'   => $this->integer(2)
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('users');
    }
}
