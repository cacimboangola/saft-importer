<?php

namespace App\Models;

use App\Models\Docs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocStatus extends Model
{
    use HasFactory;
    protected $connection = "mysql";
    public $table = "doc_status";
    
    protected $fillable = ['status_id', 'InvoiceID', 'status_date', 'status_obs', 'last_status_id'];

    /**
     * Get the doc that owns the DocLinha
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doc()
    {
        return $this->belongsTo(Docs::class, 'InvoiceID');
    } 
}
