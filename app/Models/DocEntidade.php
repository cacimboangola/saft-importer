<?php

namespace App\Models;

use App\Models\Docs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocEntidade extends Model
{
    use HasFactory;
    protected $connection = "mysql";
    protected $keyType = 'string';
    
    public $incrementing = false;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'CustomerID';

    protected $fillable = ['CustomerID','CompanyID', 'AccountID', 'CustomerTaxID', 'CompanyName', 
    'Contact', 'AddressDetail', 'City', 'Country', 'Telefone', 'Email','whatsapp', 'temp_CustomerID'];
    /**
     * Get all of the docs for the DocEmpresa
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function docs()
    {
        return $this->hasMany(Docs::class, 'InvoiceId');
    }
}
