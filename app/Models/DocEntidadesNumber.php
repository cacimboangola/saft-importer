<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocEntidadesNumber extends Model
{
    use HasFactory;

    protected $fillable =[
        'CompanyID', 'temp_CustomerID'
    ];
}
