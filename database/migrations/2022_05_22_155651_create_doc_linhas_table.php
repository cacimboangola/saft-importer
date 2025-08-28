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
        Schema::create('doc_linhas', function (Blueprint $table) {
            $table->string('LineNumber')->primary();
            $table->string('ProductCode', 100)->nullable();
            $table->double('Quantity', 15, 2)->nullable()->default(0.0);
            $table->string('UnitOfMeasure', 100)->nullable()->default('Un');
            $table->double('UnitPrice', 15, 2)->nullable()->default(0.0);
            $table->date('TaxPointDate')->nullable();
            $table->string('Description')->nullable();
            $table->double('CreditAmount', 15, 2)->nullable()->default(0.0);
            $table->double('DebitAmount', 15, 2)->nullable()->default(0.0);
            $table->double('SettlementAmount', 15, 2)->nullable()->default(0.0);
            $table->string('TaxType', 100)->nullable();
            $table->string('TaxCountryRegion', 100)->nullable();
            $table->string('TaxCode', 100)->nullable();
            $table->double('TaxPercentage', 8, 2)->nullable()->default(0.0);
            $table->string('TaxExemptionReason', 100)->nullable();
            $table->string('TaxExmptionCode', 100)->nullable();
            $table->bigInteger('idVendedor')->nullable();
            $table->bigInteger('idUserCacimboAT')->nullable();
            $table->string('InvoiceId');
            $table->foreign('InvoiceId')->references('InvoiceId')->on('docs');
            $table->string('InvoiceNo')->nullable();
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
        Schema::dropIfExists('doc_linhas');
    }
};
