<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {

            $table->id();
            //Foreign Id iliskisini ekledik
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email_Address')->unique();
            $table->string('phone_Number')->unique();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
