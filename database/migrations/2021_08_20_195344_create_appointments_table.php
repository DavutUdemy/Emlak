<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            //Foreign Id iliskisini ekledik
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('appointment_address')->nullable();
            $table->string('appointment_date');
            $table->string('leave_time');
            $table->string('return_time');




        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');


    }
}
