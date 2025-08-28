<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocNumber extends Model
{
    use HasFactory;
    
    public $fillable = [
        'CompanyID', 'doc_erp_number', 'temp_doc_number', 'sync_status_description', 'option_after_sync','doc_user'
    ];
}
