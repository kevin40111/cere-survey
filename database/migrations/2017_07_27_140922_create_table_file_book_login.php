<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableFileBookLogin extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_book_login', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('book_id');
            $table->string('login_id', 50);
            $table->string('encrypt_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('file_book_login');
    }

}