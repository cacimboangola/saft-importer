<?php

namespace App\Models;

use App\Models\Docs;
use App\Models\OnlineStore;
use App\Models\UserEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocEmpresa extends Model
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
    protected $primaryKey = 'CompanyID';
    protected $fillable = ['CompanyID', 'TaxRegistrationNumber', 
    'CompanyName', 'CurrencyCode', 'UserId', 'UserEmail', 'UserName'];

    /**
     * Get all of the docs for the DocEmpresa
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function docs()
    {
        return $this->hasMany(Docs::class, 'InvoiceId');
    }

    public function onlineStores()
    {
        return $this->hasMany(OnlineStore::class, 'CompanyID');
    }

    public function onlinePaymentsMechanisms() {
        return $this->hasMany(OnlinePaymentsMechanism::class, 'CompanyID');
        
    }
    
    public function user_empresas()
    {
        return $this->hasMany(UserEmpresa::class, 'CompanyID');
    }
}
