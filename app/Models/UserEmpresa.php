<?php

namespace App\Models;

use App\Models\DocEmpresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmpresa extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'CompanyID'];

     /**
     * Get the customer that owns the Docs
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(DocEmpresa::class, 'CompanyID');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
