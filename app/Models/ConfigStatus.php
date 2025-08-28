<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigStatus extends Model
{
    use HasFactory;

    public $table = "config_status";
    
    protected $primaryKey = "status_id";

    protected $fillable = ['status_id', 'status_description', 'status_color_text', 'status_color_back', 'status_rule_before', 'status_rule_after'];
}
