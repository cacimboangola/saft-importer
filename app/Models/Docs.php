<?php

namespace App\Models;

use App\Models\DocEmpresa;
use App\Models\DocEntidade;
use App\Models\DocLinha;
use App\Models\DocPayment;
use App\Models\DocStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docs extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    protected $table = 'docs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'InvoiceId';

    public $incrementing = false;

    protected $fillable = [
        'InvoiceId',
        'InvoiceNo',
        'InvoiceStatus',
        'InvoiceStatusDate',
        'SourceBilling',
        'HASH',
        'Period',
        'InvoiceDate',
        'SourceDocuments',
        'InvoiceType',
        'InvoiceTypeSerie',
        'SystemEntryDate',
        'CustomerID',
        'CustomerComID',
        'CompanyID',
        'AddressDetail',
        'City',
        'Country',
        'TaxPayable',
        'NetTotal',
        'GrossTotal',
        'IRT_WithholdingTaxType',
        'IRT_WithholdingTaxDescription',
        'IRT_WithholdingTaxAmount',
        'IS_WithholdingTaxType',
        'IS_WithholdingTaxDescription',
        'IS_WithholdingTaxAmount',
        'idVeiculo',
        'idVendedor',
        'idProjecto',
        'idUser',
        'idApiUser',
        'DocDescription',
        'DocObs',
        'DocRef',
        'idRetornoApi',
        'PontosTotal',
        'Settlement',
        'sync_status',
        'doc_status',
        'OnlineStorePO',
        'grossWeight',
        'grossVolume',
        'hash_sinc'
    ];

    protected $attributes = [
        'InvoiceStatus' => 'N',
        'TaxPayable' => '0.00',
        'NetTotal' => '0.00',
        'GrossTotal' => '0.00',
        'IRT_WithholdingTaxAmount' => '0.00',
        'IS_WithholdingTaxAmount' => '0.00',
        'PontosTotal' => '0.00',
        'Settlement' => '0',
        'sync_status' => '0',
        'doc_status' => 'Pendente',
        'OnlineStorePO' => '0',
        'grossWeight' => '0.00',
        'grossVolume' => '0.00',
        'SourceDocuments' => 'N',
        'InvoiceTypeSerie' => 'A',
        'Period' => null,
        'CustomerComID' => null,
        'AddressDetail' => null,
        'City' => null,
        'Country' => 'AO',
        'IRT_WithholdingTaxType' => null,
        'IRT_WithholdingTaxDescription' => null,
        'IS_WithholdingTaxType' => null,
        'IS_WithholdingTaxDescription' => null,
        'idVeiculo' => null,
        'idVendedor' => null,
        'idProjecto' => null,
        'idUser' => null,
        'idApiUser' => null,
        'DocDescription' => null,
        'DocObs' => null,
        'DocRef' => null,
        'idRetornoApi' => null,
        'hash_sinc' => null
    ];

    protected $casts = [
        'InvoiceDate' => 'datetime',
        'InvoiceStatusDate' => 'datetime',
        'SystemEntryDate' => 'datetime',
        'TaxPayable' => 'float',
        'NetTotal' => 'float',
        'GrossTotal' => 'float',
        'IRT_WithholdingTaxAmount' => 'float',
        'IS_WithholdingTaxAmount' => 'float',
        'PontosTotal' => 'float',
        'Settlement' => 'float',
        'grossWeight' => 'float',
        'grossVolume' => 'float',
        'sync_status' => 'integer'
    ];

    /**
     * Get the customer that owns the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entidade()
    {
        return $this->belongsTo(DocEntidade::class, 'CustomerID', 'CustomerID');
    }

    /**
     * Get the customer that owns the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(DocEmpresa::class, 'CompanyID', 'CompanyID');
    }

    /**
     * Get all of the docLinhas for the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function linhas()
    {
        return $this->hasMany(DocLinha::class, 'InvoiceId', 'InvoiceId');
    }

    /**
     * Get all of the docLinhas for the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function status()
    {
        return $this->hasMany(DocStatus::class, 'InvoiceID');
    }

    /**
     * Get all of the docLinhas for the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(DocPayment::class, 'InvoiceId', 'InvoiceId');
    }

    public function onlinePayments()
    {
        return $this->hasMany(OnlinePayment::class, 'SourceDocumentID', 'InvoiceId');
    }
}
