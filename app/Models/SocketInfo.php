<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocketInfo extends Model
{
    use HasFactory;
protected $connection = "cacimbosocket";
    public $table = "clientes";
    protected $fillable = ['CompanyID', 'posto', 'estado', 'company_name', 'server', 'so_version', 'cacimbo_version', 'data'];

}
