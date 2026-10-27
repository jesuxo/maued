<?php
// app/Models/CostoAdicional.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CostoAdicional extends Model
{
    protected $table = 'costos_adicionales';

    protected $fillable = [
        'fk_pedido', 'codprov', 'monto', 'descripcion', 'fecha', 'fk_sucursal'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'fk_pedido');
    }

    public function proveedor()
    {
        return $this->belongsTo(Saprov::class, 'codprov', 'codprov');
    }
}
