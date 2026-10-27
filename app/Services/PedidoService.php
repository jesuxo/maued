<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\PedidoSerial;
use App\Models\CostoAdicional;
use App\Models\Saprod;
use App\Models\Saexis;
use App\Models\Saprov;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PedidoService
{
    protected $parametros;

    public function __construct()
    {
        $this->parametros = \App\Helpers\ParametrosSistema::getParametrosSesion();
    }

    /**
     * Crear un nuevo pedido (orden de compra)
     */
    public function crearPedido($codprov, $fk_empresa = null)
    {
        $fk_empresa = $fk_empresa ?: $this->parametros['empresa_id'];

        // Validar que el proveedor existe
        $proveedor = Saprov::where('codprov', $codprov)->first();
        if (!$proveedor) {
            throw new \Exception("Proveedor no encontrado");
        }

        // Generar número de pedido secuencial
        $lastPedido = Pedido::where('codprov', $codprov)
            ->where('fk_empresa', $fk_empresa)
            ->orderBy('id', 'desc')
            ->first();

        $num = $lastPedido ? intval($lastPedido->num) + 1 : 1;
        $num = str_pad($num, 8, '0', STR_PAD_LEFT);

        $pedido = Pedido::create([
            'codprov' => $codprov,
            'num' => $num,
            'fk_usuario' => Auth::id(),
            'fecha' => now(),
            'fk_empresa' => $fk_empresa,
            'fk_sucursal' => $this->parametros['sucursal_id'],
            'status' => '0',
        ]);

        // Agregar item por defecto (como en listaPedido.php)
        $this->agregarItemDefault($pedido->id);

        return $pedido;
    }

    /**
     * Agregar un item temporal al pedido
     */
    public function agregarItemDefault($pedidoId)
    {
        return PedidoItem::create([
            'fk_pedido' => $pedidoId,
            'codprod' => 'CODPROD',
            'cantidad' => 1,
            'costo' => 0,
            'precio' => 0,
            'precio2' => 0,
            'precio3' => 0,
            'iva' => 16,
            'fk_sucursal' => $this->parametros['sucursal_id'],
        ]);
    }

    /**
     * Agregar un producto al pedido
     */
    public function agregarProducto($pedidoId, $codprod)
    {
        // Verificar si ya existe
        $existing = PedidoItem::where('fk_pedido', $pedidoId)
            ->where('codprod', $codprod)
            ->first();

        if ($existing) {
            return $existing;
        }

        $producto = Producto::where('codprod', $codprod)->first();
        if (!$producto) {
            throw new \Exception("Producto no encontrado");
        }

        $iva = $producto->esexento ? 0 : 16;

        return PedidoItem::create([
            'fk_pedido' => $pedidoId,
            'codprod' => $codprod,
            'cantidad' => 0,
            'costo' => 0,
            'precio' => 0,
            'precio2' => 0,
            'precio3' => 0,
            'iva' => $iva,
            'fk_sucursal' => $this->parametros['sucursal_id'],
        ]);
    }

    /**
     * Actualizar un item del pedido
     */
    public function actualizarItem($itemId, $campo, $valor)
    {
        $item = PedidoItem::with('pedido')->findOrFail($itemId);

        switch ($campo) {
            case 'cantidad':
                $item->cantidad = $valor;
                break;

            case 'costo':
                $item->costo = $valor;
                $this->recalcularItem($item);
                break;

            case 'costopesos':
                $item->costopesos = $valor;
                $dolar = $this->parametros['dolarpesocompra'];
                if ($dolar > 0) {
                    $item->costo = $valor / $dolar;
                }
                $this->recalcularItem($item);
                break;

            case 'costadicional':
                $item->costadicional = $valor;
                $this->recalcularItem($item);
                break;

            case 'porc1':
            case 'porc2':
            case 'porc3':
                $item->$campo = $valor;
                $this->recalcularItem($item);
                break;

            case 'precio':
            case 'precio2':
            case 'precio3':
                $item->$campo = $valor;
                $fixedName = $campo . '_fixed';
                $item->$fixedName = true;
                $this->recalcularUtilidad($item);
                break;

            case 'precio1pesos':
            case 'precio2pesos':
            case 'precio3pesos':
                $item->$campo = $valor;
                $pesoxdolar = \App\Helpers\ParametrosSistema::getPesoXDolar();
                if ($pesoxdolar > 0) {
                    $precioName = str_replace('pesos', '', $campo);
                    $item->$precioName = ($valor / $pesoxdolar) * 1.16;
                }
                break;

            case 'preciotodos':
                $this->actualizarTodosPrecios($item, $valor);
                break;

            case 'codprod':
                $producto = Producto::where('codprod', $valor)->first();
                if ($producto) {
                    $item->codprod = $valor;
                    $item->iva = $producto->esexento ? 0 : 16;
                    $item->cantidad = 0;
                    $item->referencia = '';
                    $item->producto = '';
                }
                break;

            case 'referencia':
            case 'producto':
                $item->$campo = $valor;
                break;

            case 'iva':
                $item->iva = $valor;
                $this->recalcularItem($item);
                break;

            default:
                throw new \Exception("Campo no soportado: $campo");
        }

        $item->save();

        // Recalcular totales del pedido
        $this->recalcularPedido($item->pedido);

        return $item;
    }

    /**
     * Recalcular un item (resultados y precios)
     */
    protected function recalcularItem(PedidoItem $item)
    {
        $costo = $item->costo + $item->costadicional;

        // Aplicar descuentos
        $result = $costo;
        $result = $result - ($result * ($item->porc1 / 100));
        $result = $result - ($result * ($item->porc2 / 100));
        $result = $result - ($result * ($item->porc3 / 100));

        $item->result = $result;

        // Recalcular precios solo si no están fijos
        if (!$item->precio_fixed) {
            $precio = $this->calcularPrecioDesdeCosto($result, $item->utilidad, $item->iva);
            $item->precio = round($precio, 4);

            if (!$item->precio2_fixed) {
                $item->precio2 = $item->precio;
            }
            if (!$item->precio3_fixed) {
                $item->precio3 = $item->precio;
            }
        }

        $item->save();

        return $item;
    }

    /**
     * Calcular precio desde costo
     */
    protected function calcularPrecioDesdeCosto($costo, $utilidad, $iva)
    {
        $preciobase = $this->parametros['preciobase'];
        $precioLineal = $this->parametros['precioLineal'];

        if ($preciobase == 0) {
            // Margen sobre precio de venta
            $calc = (100 - $utilidad) / 100;
            if ($calc <= 0) {
                $calc = 0.01;
            }
            $precio = $costo / $calc;
        } else {
            // Margen sobre costo
            $precio = $costo * (1 + ($utilidad / 100));
        }

        // Aplicar IVA
        $precio = $precio * (1 + ($iva / 100));

        return $precio;
    }

    /**
     * Calcular utilidad desde precio
     */
    protected function recalcularUtilidad(PedidoItem $item)
    {
        $preciobase = $this->parametros['preciobase'];
        $precio = $item->precio;
        $costo = $item->result;

        if ($precio > 0 && $costo > 0) {
            if ($preciobase == 0) {
                // Margen sobre precio de venta
                $utilidad = (($precio - $costo) / $precio) * 100;
            } else {
                // Margen sobre costo
                $utilidad = (($precio - $costo) / $costo) * 100;
            }
            $item->utilidad = round($utilidad, 2);
        }

        $item->save();
    }

    /**
     * Actualizar todos los precios a un mismo valor
     */
    protected function actualizarTodosPrecios(PedidoItem $item, $valor)
    {
        $pesoxdolar = \App\Helpers\ParametrosSistema::getPesoXDolar();

        if ($this->parametros['precioenpesos'] && $pesoxdolar > 0) {
            $precioDolar = $valor / $pesoxdolar;
            $item->precio1pesos = $valor;
            $item->precio2pesos = $valor;
            $item->precio3pesos = $valor;
            $item->precio = $precioDolar;
            $item->precio2 = $precioDolar;
            $item->precio3 = $precioDolar;
        } else {
            $item->precio = $valor;
            $item->precio2 = $valor;
            $item->precio3 = $valor;
        }

        $item->precio_fixed = true;
        $item->precio2_fixed = true;
        $item->precio3_fixed = true;

        $item->save();
    }

    /**
     * Recalcular totales del pedido
     */
    public function recalcularPedido(Pedido $pedido)
    {
        $items = $pedido->items;

        $totalResult = 0;
        $monto = 0;
        $montoadicional = 0;

        foreach ($items as $item) {
            $costo = $item->costo + $item->costadicional;
            $monto += $costo * $item->cantidad;
            $montoadicional += $item->costadicional * $item->cantidad;
            $totalResult += ($item->result) * $item->cantidad;
        }

        $pedido->monto = $monto;
        $pedido->montoadicional = $montoadicional;
        $pedido->total = $totalResult;

        $pedido->save();

        return $pedido;
    }

    /**
     * Colocar costos y precios actuales (desde SAPROD)
     */
    public function colocarCostosPrecios($pedidoId)
    {
        $items = PedidoItem::where('fk_pedido', $pedidoId)->get();

        foreach ($items as $item) {
            $producto = Producto::where('codprod', $item->codprod)->first();
            if (!$producto) {
                continue;
            }

            $updateData = [];

            // Costos desde el producto
            if (!$item->costo || $item->costo == 0) {
                $updateData['costo'] = $producto->preciod;
            }

            // Precios desde el producto
            $updateData['precio'] = $producto->costod;
            $updateData['precio2'] = $producto->costod2;
            $updateData['precio3'] = $producto->costod3;

            if ($this->parametros['precioenpesos']) {
                $updateData['precio1pesos'] = $producto->prepesos1 ?? 0;
                $updateData['precio2pesos'] = $producto->prepesos2 ?? 0;
                $updateData['precio3pesos'] = $producto->prepesos3 ?? 0;
            }

            $item->update($updateData);

            // Recalcular item
            $this->recalcularItem($item);
        }

        return $this->recalcularPedido(Pedido::find($pedidoId));
    }

    /**
     * Eliminar un item del pedido
     */
    public function eliminarItem($itemId)
    {
        $item = PedidoItem::findOrFail($itemId);
        $pedidoId = $item->fk_pedido;

        // Eliminar seriales asociados
        PedidoSerial::where('fk_itemid', $itemId)->delete();

        $item->delete();

        // Recalcular pedido
        $pedido = Pedido::find($pedidoId);
        if ($pedido) {
            $this->recalcularPedido($pedido);
        }

        return true;
    }

    /**
     * Obtener items marcados para compra
     */
    public function getItemsParaCompra($pedidoId, $itemsSeleccionados)
    {
        return PedidoItem::where('fk_pedido', $pedidoId)
            ->whereIn('id', $itemsSeleccionados)
            ->get();
    }
}
