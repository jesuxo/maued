<?php
// app/Models/PedidoItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PedidoItem extends Model
{
    use HasFactory;

    protected $table = 'pedido_items';

    protected $fillable = [
        'fk_pedido',
        'codprod',
        'cantidad',
        'costo',
        'precio',
        'precio2',
        'precio3',
        'precio1pesos',
        'precio2pesos',
        'precio3pesos',
        'iva',
        'porc1',
        'porc2',
        'porc3',
        'utilidad',
        'result',
        'costopesos',
        'costadicional',
        'und_compradas',
        'und_notaentrega',
        'referencia',
        'producto',
        'precio_fixed',
        'precio2_fixed',
        'precio3_fixed',
        'fk_sucursal',
    ];

    protected $casts = [
        'cantidad' => 'decimal:4',
        'costo' => 'decimal:4',
        'precio' => 'decimal:4',
        'precio2' => 'decimal:4',
        'precio3' => 'decimal:4',
        'precio1pesos' => 'decimal:4',
        'precio2pesos' => 'decimal:4',
        'precio3pesos' => 'decimal:4',
        'iva' => 'decimal:2',
        'porc1' => 'decimal:2',
        'porc2' => 'decimal:2',
        'porc3' => 'decimal:2',
        'utilidad' => 'decimal:2',
        'result' => 'decimal:4',
        'costopesos' => 'decimal:4',
        'costadicional' => 'decimal:4',
        'und_compradas' => 'decimal:4',
        'und_notaentrega' => 'decimal:4',
        'precio_fixed' => 'boolean',
        'precio2_fixed' => 'boolean',
        'precio3_fixed' => 'boolean',
    ];

    /**
     * Relación con el pedido
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'fk_pedido');
    }

    /**
     * Relación con el producto
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Saprod::class, 'codprod', 'codprod');
    }

    /**
     * Relación con los seriales del pedido
     */
    public function seriales(): HasMany
    {
        return $this->hasMany(PedidoSerial::class, 'fk_itemid');
    }

    /**
     * Calcular el resultado (con descuentos aplicados)
     */
    public function calcularResultado(): float
    {
        $costo = $this->costo + $this->costadicional;

        $result = $costo;
        $result = $result - ($result * ($this->porc1 / 100));
        $result = $result - ($result * ($this->porc2 / 100));
        $result = $result - ($result * ($this->porc3 / 100));

        return $result;
    }

    /**
     * Calcular precio desde el costo
     */
    public function calcularPrecio(): float
    {
        $result = $this->calcularResultado();

        // Obtener parámetros de sesión
        $preciobase = session('preciobase', 0);
        $utilidad = $this->utilidad ?: 0;
        $iva = $this->iva ?: 16;

        if ($preciobase == 0) {
            // Margen sobre precio de venta
            $calc = (100 - $utilidad) / 100;
            $precio = $calc > 0 ? $result / $calc : 0;
        } else {
            // Margen sobre costo
            $precio = $result * (1 + ($utilidad / 100));
        }

        // Aplicar IVA
        $precio = $precio * (1 + ($iva / 100));

        return round($precio, 4);
    }

    /**
     * Recalcular utilidad a partir del precio
     */
    public function recalcularUtilidad($precio = null): float
    {
        if ($precio === null) {
            $precio = $this->precio;
        }

        $costo = $this->calcularResultado();

        if ($precio > 0 && $costo > 0) {
            $preciobase = session('preciobase', 0);

            if ($preciobase == 0) {
                // Margen sobre precio de venta
                $utilidad = (($precio - $costo) / $precio) * 100;
            } else {
                // Margen sobre costo
                $utilidad = (($precio - $costo) / $costo) * 100;
            }

            $this->utilidad = round($utilidad, 2);
            $this->save();

            return $this->utilidad;
        }

        return 0;
    }

    /**
     * Scope para items marcados para compra
     */
    public function scopeParaCompra($query)
    {
        $itemsCompra = session('itemcompra', []);
        return $query->whereIn('codprod', array_keys($itemsCompra));
    }

    /**
     * Scope para items con seriales
     */
    public function scopeConSeriales($query)
    {
        return $query->whereHas('producto.instancia', function ($q) {
            $q->where('desseri', 1);
        });
    }

    /**
     * Verificar si el item requiere seriales
     */
    public function requiereSeriales(): bool
    {
        if ($this->codprod == 'CODPROD') {
            return false;
        }

        $instancia = $this->producto?->instancia;
        return $instancia ? (bool) $instancia->desseri : false;
    }

    /**
     * Obtener el total del item (costo * cantidad)
     */
    public function getTotalAttribute(): float
    {
        return ($this->costo + $this->costadicional) * $this->cantidad;
    }

    /**
     * Obtener el resultado total (result * cantidad)
     */
    public function getTotalResultAttribute(): float
    {
        return $this->result * $this->cantidad;
    }

    /**
     * Obtener el precio en pesos (según configuración)
     */
    public function getPrecioPesosAttribute()
    {
        $precioenpesos = session('precioenpesos', 0);

        if ($precioenpesos) {
            return $this->precio1pesos;
        }

        return $this->precio;
    }

    /**
     * Obtener el precio 2 en pesos (según configuración)
     */
    public function getPrecio2PesosAttribute()
    {
        $precioenpesos = session('precioenpesos', 0);

        if ($precioenpesos) {
            return $this->precio2pesos;
        }

        return $this->precio2;
    }

    /**
     * Obtener el precio 3 en pesos (según configuración)
     */
    public function getPrecio3PesosAttribute()
    {
        $precioenpesos = session('precioenpesos', 0);

        if ($precioenpesos) {
            return $this->precio3pesos;
        }

        return $this->precio3;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Antes de guardar, recalcular resultado
        static::saving(function ($item) {
            if ($item->isDirty(['costo', 'costadicional', 'porc1', 'porc2', 'porc3'])) {
                $item->result = $item->calcularResultado();
            }
        });
    }
}
