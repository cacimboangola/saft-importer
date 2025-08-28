<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompositePrimaryKeyToDocLinhasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('cacimbodocs')->table('doc_linhas', function (Blueprint $table) {
            // Remove a chave primária atual
            $table->dropPrimary();
            
            // Adiciona a chave primária composta
            $table->primary(['InvoiceId', 'LineNumber']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('cacimbodocs')->table('doc_linhas', function (Blueprint $table) {
            // Remove a chave primária composta
            $table->dropPrimary();
            
            // Restaura a chave primária original (LineNumber)
            $table->primary('LineNumber');
        });
    }
}
