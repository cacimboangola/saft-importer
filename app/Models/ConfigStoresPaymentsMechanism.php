<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigStoresPaymentsMechanism extends Model
{
    use HasFactory;

    protected $fillable = ['Mechanism', 'Description'];
}
