<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRhRubricasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rhRubricas', function (Blueprint $table) {
            $table->string('id',30)->primary();
            $table->string('empresa',20)->nullable();
            $table->string('rubrica',20)->nullable();
            $table->string('descricao',50)->nullable();
            $table->string('un',10)->nullable();
            $table->boolean('fixo')->nullable()->default(false);
            $table->boolean('manual')->nullable()->default(false);
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
        Schema::dropIfExists('rhRubricas');
    }
}
