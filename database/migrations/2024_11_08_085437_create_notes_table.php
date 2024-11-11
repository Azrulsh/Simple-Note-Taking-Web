<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //Run the migrations.
    public function up(): void
    {
        Schema::create('note_record', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->datetime('created_at');
            $table->datetime('updated_at');
        });
    }

    //Reverse the migrations.
    public function down(): void
    {
        Schema::dropIfExists('note_record');
    }
};
