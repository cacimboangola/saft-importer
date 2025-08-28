<?php

namespace App\Models;

use App\Models\DocEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlinePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'CompanyID', 
        'PaymentDate', 
        'SourceDocumentID', 
        'PaymentMechanism', 
        'PaymentAmount'
    ];

    /**
     * Get the user that owns the OnlinePaymentsMechanism
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(DocEmpresa::class, 'CompanyID');
    }

    public function doc()
    {
        return $this->belongsTo(User::class, 'SourceDocumentID', 'InvoiceId');
    }
}
