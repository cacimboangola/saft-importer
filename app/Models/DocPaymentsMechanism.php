<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocPaymentsMechanism extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    
    protected $fillable = ['CompanyID', 'Mechanism', 'Description'];
}
