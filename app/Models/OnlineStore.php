<?php

namespace App\Models;

use App\Models\DocEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineStore extends Model
{
    use HasFactory;
    
    protected $fillable =[
        'CompanyID',
        "ArmazemID",
        'StoreName', 
        'StoreLogoUrl', 
        'StoreSlogan',
        'gps_lat',
        'gps_long',
        'categoria'
    ];

       /**
     * Get the customer that owns the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(DocEmpresa::class, 'CompanyID');
    }

}
