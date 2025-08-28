<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
//use DateTime;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('docs', function (Blueprint $table) {
            $table->string('InvoiceId')->primary();
            $table->string('InvoiceNo');
            $table->string('InvoiceStatus')->nullable();
            $table->date('InvoiceStatusDate')->nullable();
            $table->string('SourceBilling', 255)->nullable();
            $table->text('HASH');
            $table->string('Period')->nullable();
            $table->date('InvoiceDate')->nullable();
            $table->string('InvoiceType', 100)->nullable();
            $table->date('SystemEntryDate')->nullable();
            $table->string('CustomerID')->nullable();
            $table->foreign('CustomerID')->references('CustomerID')->on('doc_entidades');
            $table->string('CustomerComID')->nullable();
            $table->foreign('CustomerComID')->references('CustomerID')->on('doc_entidades');
            $table->bigInteger('CompanyID')->nullable();
            $table->foreign('CompanyID')->references('CompanyID')->on('doc_empresas');
            $table->string('AddressDetail', 100)->nullable();
            $table->string('City', 100)->nullable();
            $table->string('Country', 100)->nullable();
            $table->double('TaxPayable', 15, 2)->nullable();
            $table->double('NetTotal', 15, 2)->nullable()->default(0.0);
            $table->double('GrossTotal', 15, 2)->nullable()->default(0.0);
            $table->string('IRT_WithholdingTaxType', 100)->nullable();
            $table->string('IRT_WithholdingTaxDescription', 100)->nullable();
            $table->double('IRT_WithholdingTaxAmount', 15 , 2)->nullable()->default(0.0);
            $table->string('IS_WithholdingTaxType', 100)->nullable();
            $table->string('IS_WithholdingTaxDescription', 100)->nullable();
            $table->double('IS_WithholdingTaxAmount', 15, 2)->nullable()->default(0.0);
            $table->bigInteger('idVeiculo')->nullable();
            $table->bigInteger('idVendedor')->nullable();
            $table->bigInteger('idUser')->nullable();
            $table->bigInteger('idApiUser')->nullable();
            $table->string('DocDescription', 100)->nullable();
            $table->string('DocObs', 100)->nullable();
            $table->string('DocRef', 100)->nullable();
            $table->bigInteger('idRetornoApi')->nullable();
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
        Schema::dropIfExists('docs');
    }
};
