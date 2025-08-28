<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocType extends Model
{
    use HasFactory;

    protected $keyType = 'string';

    protected $fillable = ['InvoiceType', 'InvoiceTypeSerie', 'SourceDocuments', 'CompanyID','DocDescription','DocDesrciptionERP'];
}
