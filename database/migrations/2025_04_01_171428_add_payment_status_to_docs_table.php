<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('docs', function (Blueprint $table) {
            // Adiciona a coluna PaymentStatus se não existir
            if (!Schema::hasColumn('docs', 'PaymentStatus')) {
                $table->string('PaymentStatus')->nullable();
            }

            // Adiciona outras colunas que podem estar faltando
            if (!Schema::hasColumn('docs', 'IRT_WithholdingTaxType')) {
                $table->string('IRT_WithholdingTaxType')->nullable();
            }
            if (!Schema::hasColumn('docs', 'IRT_WithholdingTaxDescription')) {
                $table->string('IRT_WithholdingTaxDescription')->nullable();
            }
            if (!Schema::hasColumn('docs', 'IRT_WithholdingTaxAmount')) {
                $table->decimal('IRT_WithholdingTaxAmount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('docs', 'IS_WithholdingTaxType')) {
                $table->string('IS_WithholdingTaxType')->nullable();
            }
            if (!Schema::hasColumn('docs', 'IS_WithholdingTaxDescription')) {
                $table->string('IS_WithholdingTaxDescription')->nullable();
            }
            if (!Schema::hasColumn('docs', 'IS_WithholdingTaxAmount')) {
                $table->decimal('IS_WithholdingTaxAmount', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('docs', 'idVeiculo')) {
                $table->string('idVeiculo')->nullable();
            }
            if (!Schema::hasColumn('docs', 'idVendedor')) {
                $table->string('idVendedor')->nullable();
            }
            if (!Schema::hasColumn('docs', 'idUser')) {
                $table->string('idUser')->nullable();
            }
            if (!Schema::hasColumn('docs', 'idApiUser')) {
                $table->string('idApiUser')->nullable();
            }
            if (!Schema::hasColumn('docs', 'DocDescription')) {
                $table->text('DocDescription')->nullable();
            }
            if (!Schema::hasColumn('docs', 'DocObs')) {
                $table->text('DocObs')->nullable();
            }
            if (!Schema::hasColumn('docs', 'DocRef')) {
                $table->string('DocRef')->nullable();
            }
            if (!Schema::hasColumn('docs', 'idRetornoApi')) {
                $table->string('idRetornoApi')->nullable();
            }
            if (!Schema::hasColumn('docs', 'PontosTotal')) {
                $table->decimal('PontosTotal', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('docs', 'Settlement')) {
                $table->decimal('Settlement', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('docs', 'hash_sinc')) {
                $table->string('hash_sinc')->nullable();
            }
            if (!Schema::hasColumn('docs', 'sync_status')) {
                $table->integer('sync_status')->default(0);
            }
            if (!Schema::hasColumn('docs', 'doc_status')) {
                $table->string('doc_status')->default('active');
            }
            if (!Schema::hasColumn('docs', 'OnlineStorePO')) {
                $table->string('OnlineStorePO')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('docs', function (Blueprint $table) {
            $table->dropColumn([
                'PaymentStatus',
                'IRT_WithholdingTaxType',
                'IRT_WithholdingTaxDescription',
                'IRT_WithholdingTaxAmount',
                'IS_WithholdingTaxType',
                'IS_WithholdingTaxDescription',
                'IS_WithholdingTaxAmount',
                'idVeiculo',
                'idVendedor',
                'idUser',
                'idApiUser',
                'DocDescription',
                'DocObs',
                'DocRef',
                'idRetornoApi',
                'PontosTotal',
                'Settlement',
                'hash_sinc',
                'sync_status',
                'doc_status',
                'OnlineStorePO'
            ]);
        });
    }
};
