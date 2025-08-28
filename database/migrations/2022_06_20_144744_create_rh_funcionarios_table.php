<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRhFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rhFuncionarios', function (Blueprint $table) {
            $table->string('id',30)->primary();
            $table->string('empresa',20)->nullable();
            $table->string('numero',10)->nullable();
            $table->string('nome')->nullable();
            $table->string('local',50)->nullable();
            $table->string('categoria',50)->nullable();
            $table->string('funcao',50)->nullable();
            $table->string('BI',20)->nullable();
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
        Schema::dropIfExists('rhFuncionarios');
    }
}
