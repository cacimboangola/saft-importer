<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRhAlteracaoMensalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rhAlteracoesMensais', function (Blueprint $table) {
            $table->id();
            $table->string('empresa', 20)->nullable();
            $table->string('funcionario',30)->nullable();
            $table->date('data')->nullable();
            $table->string('rubrica')->nullable();
            $table->double('quantidade', 15, 2)->nullable()->default(0);
            $table->double('valor', 15, 2)->nullable()->default(0);
            $table->string('motivo')->nullable();
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
        Schema::dropIfExists('rhAlteracoesMensais');
    }
}
