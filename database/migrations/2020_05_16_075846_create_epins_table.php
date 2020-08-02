<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEpinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('epins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('epin');
            $table->integer('amount');
            $table->integer('issue_to');
            $table->integer('generated_by');
            $table->integer('transfer_by')->nullable();
            $table->timestamp('transfer_time')->nullable();
            $table->integer('used_by')->nullable();
            $table->timestamp('used_time')->nullable();
            $table->enum('status', ["used","un-use"])->default('un-use');
            $table->enum('type', ["single-use","multi-use"])->default('single-use');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('epins');
    }
}
