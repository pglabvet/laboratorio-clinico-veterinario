<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogDescarga extends Model
{
    use HasFactory;

    protected $table = 'logs_descarga';

    protected $fillable = [
        'token_id',
        'ip',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    /**
     * Relación con token de descarga
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(TokenDescarga::class, 'token_id');
    }
}
