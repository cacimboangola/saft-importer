<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocLinha extends Model
{
    use HasFactory;

    protected $connection = "cacimbodocs";
    protected $table = 'doc_linhas';
    
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The primary key for the model.
     *
     * @var array
     */
    protected $primaryKey = 'LineNumber';
    protected $fillable = [
        'LineNumber',
        'ProductCode',
        'Quantity',
        'UnitOfMeasure',
        'UnitPrice',
        'TaxPointDate',
        'ProductDescription',
        'Description',
        'CreditAmount',
        'DebitAmount',
        'SettlementPercentage',
        'SettlementAmount',
        'TaxType',
        'TaxCountryRegion',
        'TaxCode',
        'TaxPercentage',
        'TaxExemptionReason',
        'TaxExmptionCode',
        'idVendedor',
        'idUserCacimboAT',
        'InvoiceId',
        'InvoiceNo',
        'ArtigoPaiID',
        'IRT_WithholdingTaxAmount',
        'IRT_WithholdingTax',
        'linhaRemovida',
        'ArtigoPontos',
        'idArmazem',
        'artigo_peso',
        'artigo_volume',
        'hash_sinc'
    ];

    protected $attributes = [
        'Quantity' => '0.00',
        'UnitOfMeasure' => 'Un',
        'UnitPrice' => '0.00',
        'CreditAmount' => '0.00',
        'DebitAmount' => '0.00',
        'SettlementAmount' => '0.00',
        'TaxPercentage' => '0.00',
        'IRT_WithholdingTaxAmount' => '0.00',
        'IRT_WithholdingTax' => '0.00',
        'linhaRemovida' => '0',
        'ArtigoPontos' => '0.00',
        'artigo_peso' => '0',
        'artigo_volume' => '0'
    ];

    protected $casts = [
        'Quantity' => 'float',
        'UnitPrice' => 'float',
        'CreditAmount' => 'float',
        'DebitAmount' => 'float',
        'SettlementPercentage' => 'float',
        'SettlementAmount' => 'float',
        'TaxPercentage' => 'float',
        'IRT_WithholdingTaxAmount' => 'float',
        'IRT_WithholdingTax' => 'float',
        'ArtigoPontos' => 'float',
        'artigo_peso' => 'float',
        'artigo_volume' => 'float',
        'linhaRemovida' => 'integer',
        'TaxPointDate' => 'datetime'
    ];

    /**
     * Get the parent document that owns the line.
     */
    public function doc()
    {
        return $this->belongsTo(Docs::class, 'InvoiceId', 'InvoiceId');
    }

}