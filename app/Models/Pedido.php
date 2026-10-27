<?php
// app/Models/Pedido.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'codprov',
        'num',
        'fk_usuario',
        'fecha',
        'fk_empresa',
        'monto',
        'total',
        'montoadicional',
        'status',
        'recepcion',
        'unidades',
        'observaciones',
        'porc1',
        'porc2',
        'porc3',
        'fk_sucursal',
        'sync',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'recepcion' => 'date',
        'monto' => 'decimal:2',
        'total' => 'decimal:2',
        'montoadicional' => 'decimal:2',
        'unidades' => 'decimal:2',
        'porc1' => 'decimal:2',
        'porc2' => 'decimal:2',
        'porc3' => 'decimal:2',
        'sync' => 'boolean',
    ];

    /**
     * Relación con los items del pedido
     */
    public function items(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'fk_pedido');
    }

    /**
     * Relación con el proveedor
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Saprov::class, 'codprov', 'codprov');
    }

    /**
     * Relación con los costos adicionales
     */
    public function costosAdicionales(): HasMany
    {
        return $this->hasMany(CostoAdicional::class, 'fk_pedido');
    }

    /**
     * Relación con la compra generada
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Sacomp::class, 'fk_pedido');
    }

    /**
     * Scope para pedidos activos (no procesados)
     */
    public function scopeActivos($query)
    {
        return $query->where('status', '0');
    }

    /**
     * Scope para pedidos por proveedor
     */
    public function scopePorProveedor($query, $codprov)
    {
        return $query->where('codprov', $codprov);
    }

    /**
     * Recalcular totales del pedido
     */
    public function recalcularTotales(): array
    {
        $monto = 0;
        $totalResult = 0;
        $montoadicional = 0;

        foreach ($this->items as $item) {
            $costo = $item->costo + $item->costadicional;
            $monto += $costo * $item->cantidad;
            $montoadicional += $item->costadicional * $item->cantidad;
            $totalResult += $item->result * $item->cantidad;
        }

        $this->monto = $monto;
        $this->montoadicional = $montoadicional;
        $this->total = $totalResult;
        $this->save();

        return [
            'monto' => $monto,
            'montoadicional' => $montoadicional,
            'total' => $totalResult,
        ];
    }

    /**
     * Obtener items marcados para compra
     */
    public function getItemsParaCompra()
    {
        $itemsCompra = session('itemcompra', []);
        return $this->items()
            ->whereIn('codprod', array_keys($itemsCompra))
            ->where('codprod', '!=', 'CODPROD')
            ->get();
    }

    /**
     * Verificar si tiene items para comprar
     */
    public function tieneItemsParaCompra(): bool
    {
        return $this->getItemsParaCompra()->count() > 0;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de guardar, sincronizar totales
        static::saving(function ($pedido) {
            if ($pedido->isDirty(['monto', 'total', 'montoadicional'])) {
                // Ya se están actualizando manualmente
            }
        });
    }
}
