<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //Run the migrations.
    public function up(): void
    {
        Schema::create('remark_record', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            //Adding a foreign key for note_record table
            $table->unsignedBigInteger('note_id');  
            //Adding a foreign key for login_record table
            $table->unsignedBigInteger('user_id');  
            $table->timestamps();
            //Set up a foreign key constraint linking note_id to the note_record table
            $table->foreign('note_id')->references('id')->on('note_record')->onDelete('cascade');
            //Set up a foreign key constraint linking user_id to the login_record table
            $table->foreign('user_id')->references('id')->on('login_record')->onDelete('cascade');
        });
    }

    //Reverse the migrations.
    public function down(): void
    {
        Schema::dropIfExists('remark_record');
    }
};
