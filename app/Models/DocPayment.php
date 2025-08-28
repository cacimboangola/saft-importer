<?php

namespace App\Models;

use App\Models\DocEmpresa;
use App\Models\DocEntidade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocPayment extends Model
{
    use HasFactory;

    protected $connection = "mysql";
    protected $keyType = 'string';

    /**
     * The primary key associated with the table.
     *
     * @var long
     */
    protected $primaryKey = 'id';

    protected $fillable = ['id', 'PaymentDate', 'PaymentRefNo', 'PaymentType', 
    'PaymentStatus', 'SourceDocumentID', 'OriginatingON', 'PaymentMechanism', 
    'PaymentAmount', 'CreditAmount', 'DebitAmount', 'PaymentMechanismDescription', 
    'CustomerID', 'CompanyID', 'AccountID', 'AccountNum', 'AccountBank', 'hash_sinc'];

    /**
     * Get the customer that owns the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(DocEntidade::class, 'CustomerID');
    }


     /**
     * Get the customer that owns the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(DocEmpresa::class, 'CompanyID');
    }

    /**
     * Get the doc that owns the DocPayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doc()
    {
        return $this->belongsTo(User::class, 'OriginatingON', 'InvoiceId');
    }
}
