<?php
// app/Models/PedidoSerial.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoSerial extends Model
{
    use HasFactory;

    protected $table = 'pedido_seriales';

    protected $fillable = [
        'fk_itemid',
        'serial',
        'fk_sucursal',
    ];

    /**
     * Relación con el item del pedido
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(PedidoItem::class, 'fk_itemid');
    }

    /**
     * Scope para seriales de un item específico
     */
    public function scopePorItem($query, $itemId)
    {
        return $query->where('fk_itemid', $itemId);
    }

    /**
     * Verificar si un serial ya existe en el item
     */
    public static function existeEnItem($itemId, $serial): bool
    {
        return self::where('fk_itemid', $itemId)
            ->where('serial', $serial)
            ->exists();
    }
}
