<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersOnlineStore extends Model
{
     protected $fillable = [
        "user_id",
        "user_nif",
        "online_store_id"
    ];

    /**
     * Get the expositores that owns the UsersOnlineStore
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expositor()
    {
        return $this->belongsTo(OnlineStore::class, 'online_store_id', 'id');
    }
}