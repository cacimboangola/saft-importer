<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $fillable = ['app_version', 
    'user_id', 'CompanyID', 'company_tax_registration_number', 
    'error_message', 'app_name', 'error_date', 'error_id', 'error_description'];
}
