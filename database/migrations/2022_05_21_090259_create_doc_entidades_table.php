<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('doc_entidades', function (Blueprint $table) {
            $table->string('CustomerID', 100)->primary();
            $table->bigInteger('AccountID')->nullable();
            $table->string('CustomerTaxID', 100)->nullable();
            $table->string('CompanyName', 100)->nullable();
            $table->string('Contact', 100)->nullable();
            $table->string('addressDetail', 100)->nullable();
            $table->string('City', 100)->nullable();
            $table->string('Country', 100)->nullable();
            $table->string('Telefone', 100)->nullable();
            $table->string('Email', 100)->nullable();
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
        Schema::dropIfExists('doc_entidades');
    }
};
