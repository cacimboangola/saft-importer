<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessageHistory extends Model
{
    use HasFactory;

    protected $fillable = ['from_number', 'to_number', 'message_body', 'doc_name', 'message_type', 'message_status'];
}
