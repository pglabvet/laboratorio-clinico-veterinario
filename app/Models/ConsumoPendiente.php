<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumoPendiente extends Model
{
    use HasFactory;

    protected $fillable = [
        'insumo_id',
        'sucursal_id',
        'cantidad',
        'usuario_id',
        'observacion',
        'estado'
    ];

    const ESTADO_PENDIENTE = 'PENDIENTE';
    const ESTADO_RESUELTO = 'RESUELTO';

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
