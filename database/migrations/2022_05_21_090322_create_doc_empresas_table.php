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
        Schema::create('doc_empresas', function (Blueprint $table) {
            $table->string('CompanyID')->primary();
            $table->string('TaxRegistrationNumber', 100)->nullable();
            $table->string('CompanyName', 100)->nullable();
            $table->string('CurrencyCode', 100)->nullable();
            $table->bigInteger('UserId')->nullable();
            $table->string('UserEmail', 100)->nullable();
            $table->string('UserName')->nullable();
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
        Schema::dropIfExists('doc_empresas');
    }
};
