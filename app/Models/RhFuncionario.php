<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RhFuncionario extends Model
{

    protected $keyType = 'string';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    use HasFactory;
    public $table = 'rhFuncionarios';
    
    protected $fillable = ['empresa', 'numero', 'nome', 'local', 'categoria', 'funcao', 'BI',];
}
