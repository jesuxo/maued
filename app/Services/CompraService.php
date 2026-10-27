<?php
// app/Services/CompraService.php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\CxCProveedor;
use App\Models\Sacomp;
use App\Models\Saitemcom;
use App\Models\Saprod;
use App\Models\Saexis;
use App\Models\Saprov;
use App\Helpers\ParametrosSistema;
use App\Models\Saseprcom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompraService
{
    protected $parametros;
    protected $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->parametros    = ParametrosSistema::getParametrosSesion();
        $this->pedidoService = $pedidoService;
    }

    /**
     * Procesar la compra de los items seleccionados
     */
    public function procesarCompra($pedidoId, $itemsSeleccionados, $datosCompra)
    {
        DB::beginTransaction();

        try {
            $pedido = Pedido::with(['proveedor', 'items'])->findOrFail($pedidoId);
            $proveedor = $pedido->proveedor;

            // Validaciones
            $this->validarCompra($pedido, $itemsSeleccionados, $datosCompra);

            // Obtener items seleccionados
            $items = PedidoItem::whereIn('id', $itemsSeleccionados)->get();

            // Preparar datos de la compra
            $compraData = $this->prepararDatosCompra($pedido, $proveedor, $items, $datosCompra);

            // Crear la compra
            $compra = $this->crearCompra($compraData);

            // Procesar cada item
            $totalResult = 0;
            $gsumaiva = 0;
            $gbasetotal = 0;
            $gexento = 0;

            $lineaNum = 0;
            foreach ($items as $item) {
                $lineaNum++;
                $itemData = $this->prepararItemCompra($item, $compra, $datosCompra, $lineaNum);

                // Procesar item (actualizar inventario)
                $this->procesarItemCompra($itemData, $datosCompra);

                // Crear registro del item
                $compraItem = $this->crearCompraItem($itemData);

                // Procesar seriales si tiene
                if ($item->seriales && $item->seriales->count() > 0) {
                    $this->procesarSerialesCompra($item, $compraItem, $compra, $datosCompra);
                }

                // Acumular totales
                $totalResult += $item->result * $item->cantidad;
                if (!$item->producto->esexento) {
                    $gbasetotal += $item->costo * $item->cantidad;
                    $gsumaiva += ($item->costo * $item->cantidad) * ($item->iva / 100);
                } else {
                    $gexento += $item->costo * $item->cantidad;
                }
            }

            // Actualizar totales de la compra
            $this->actualizarTotalesCompra($compra, $totalResult, $gbasetotal, $gsumaiva, $gexento, $datosCompra);

            // Procesar costos adicionales del pedido
            if ($pedido->costosAdicionales && $pedido->costosAdicionales->count() > 0) {
                $this->procesarCostosAdicionales($pedido, $compra, $datosCompra);
            }

            // Generar CxP si aplica
            if ($datosCompra['decontado'] == 0) {
                $this->generarCxP($compra, $proveedor, $datosCompra);
            }

            // Marcar items como comprados
            foreach ($items as $item) {
                $item->update(['und_compradas' => $item->und_compradas + $item->cantidad]);
            }

            // Marcar pedido como procesado
            $pedido->update(['status' => '1']);

            DB::commit();

            return $compra;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en procesarCompra: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validar datos de la compra
     */
    protected function validarCompra($pedido, $itemsSeleccionados, $datos)
    {
        if (empty($itemsSeleccionados)) {
            throw new \Exception("No hay items seleccionados para la compra");
        }

        if (empty($datos['numerod'])) {
            throw new \Exception("Número de documento requerido");
        }

        if (empty($datos['codubic'])) {
            throw new \Exception("Depósito requerido");
        }

        if (empty($datos['fechae'])) {
            throw new \Exception("Fecha de emisión requerida");
        }

        if (empty($datos['tasa']) || $datos['tasa'] <= 0) {
            throw new \Exception("Tasa de cambio requerida");
        }

        // Validar que no exista duplicado
        $exists = Sacomp::where('numerod', $datos['numerod'])
            ->where('codprov', $pedido->codprov)
            ->where('tipocom', $datos['tipocom'])
            ->exists();

        if ($exists) {
            throw new \Exception("El número de documento ya existe");
        }
    }

    /**
     * Preparar datos de la compra
     */
    protected function prepararDatosCompra($pedido, $proveedor, $items, $datos)
    {
        $emision = $datos['fechae'];
        $fechav  = $datos['fechav'] ?? $emision;

        list($d, $m, $y) = explode('/', $emision);
        $fechae = "$y-$m-$d";

        list($dv, $mv, $yv) = explode('/', $fechav);
        $fechav = "$yv-$mv-$dv";

        return [
            'tipocom'     => $datos['tipocom'] ?? 'H',
            'numerod'     => $datos['numerod'],
            'codprov'     => $pedido->codprov,
            'fk_pedido'   => $pedido->id,
            'codusua'     => Auth::user()->name ?? 'admin',
            'codesta'     => 'ADMIN08',
            'codoper'     => $datos['codoper'] ?? 'C01',
            'codubic'     => $datos['codubic'],
            'descrip'     => substr($proveedor->descrip, 0, 60),
            'direc1'      => substr($proveedor->direc1, 0, 60),
            'direc2'      => substr($proveedor->direc2, 0, 60),
            'id3'         => $proveedor->id3,
            'fechat'      => now(),
            'fechai'      => $fechae,
            'fechae'      => $fechae,
            'fechav'      => $fechav,
            'signo'       => 1,
            'fk_sucursal' => $this->parametros['sucursal_id'],
            'credito'     => $datos['decontado'] == 0 ? 1 : 0,
            'descto1'     => $datos['descuentoc'] ?? 0,
            'fletes'      => $datos['fletecompra'] ?? 0,
            'notas1'      => substr($datos['notas1'] ?? '', 0, 60),
            'notas2'      => substr($datos['notas2'] ?? '', 0, 60),
            'notas3'      => substr($datos['notas3'] ?? '', 0, 60),
            'notas5'      => substr($datos['notas5'] ?? '', 0, 60),
            'porcret'     => $datos['retencion'] ?? 0,
        ];
    }

    /**
     * Crear la compra
     */
    protected function crearCompra($data)
    {
        return Sacomp::create($data);
    }

    /**
     * Preparar item para compra
     */
    protected function prepararItemCompra($item, $compra, $datos, $lineaNum)
    {
        $producto = Saprod::where('codprod', $item->codprod)->first();

        $costo    = $item->costo;
        $result   = $item->result - $item->costadicional;

        // Aplicar descuento
        if ($datos['descuentoc'] ?? 0 > 0) {
            $descuento = $datos['descuentoc'] / 100;
            $costo     = $costo * (1 - $descuento);
            $result    = $result * (1 - $descuento);
        }

        // Aplicar flete
        if ($compra->fletes > 0 && $compra->totalprd > 0) {
            $porcFlete = $compra->fletes / $compra->totalprd;
            $costo     = $costo * (1 + $porcFlete);
            $result    = $result * (1 + $porcFlete);
        }

        return [
            'item'       => $item,
            'producto'   => $producto,
            'compra'     => $compra,
            'linea_num'  => $lineaNum,
            'costo'      => $costo,
            'result'     => $result,
            'costo_orig' => $item->costo,
            'cantidad'   => $item->cantidad,
            'codubic'    => $datos['codubic'],
            'fechae'     => $compra->fechae,
            'iva'        => $item->iva,
        ];
    }

    /**
     * Procesar item de compra (actualizar inventario)
     */
    protected function procesarItemCompra($itemData, $datos)
    {
        $codprod  = $itemData['item']->codprod;
        $cantidad = $itemData['cantidad'];
        $codubic  = $itemData['codubic'];
        $costo    = $itemData['costo'];
        $result   = $itemData['result'];

        // Actualizar Saexiss
        $existencia = Saexis::where('codprod', $codprod)
            ->where('codubic', $codubic)
            ->where('fk_sucursal', $this->parametros['sucursal_id'])
            ->first();

        if ($existencia) {
            $existencia->existen += $cantidad;
            $existencia->save();
        } else {
            Saexis::create([
                'codprod' => $codprod,
                'codubic' => $codubic,
                'fk_sucursal' => $this->parametros['sucursal_id'],
                'existen' => $cantidad,
            ]);
        }

        // Actualizar producto (costos promedios)
        $producto = Saprod::where('codprod', $codprod)->first();
        if ($producto) {
            $existenActual = $producto->existen ?? 0;
            $nuevoExisten = $existenActual + $cantidad;

            // Actualizar costos promedios
            if ($nuevoExisten > 0) {
                $costoPromedio = (($producto->costpro ?? 0) * $existenActual + $costo * $cantidad) / $nuevoExisten;
                $precioPromedio = (($producto->preciodpro ?? 0) * $existenActual + $result * $cantidad) / $nuevoExisten;

                $producto->costpro = $costoPromedio;
                $producto->preciodpro = $precioPromedio;
            }

            $producto->existen = $nuevoExisten;
            $producto->costact = $costo;
            $producto->preciod = $result;
            $producto->save();
        }
    }

    /**
     * Crear el registro del item en la compra
     */
    protected function crearCompraItem($itemData)
    {
        $item     = $itemData['item'];
        $producto = $itemData['producto'];
        $compra   = $itemData['compra'];
        $cantidad = $itemData['cantidad'];
        $costo    = $itemData['costo'];
        $result   = $itemData['result'];

        $ivaMonto = 0;
        if (!$producto->esexento) {
            $ivaMonto = ($costo * $cantidad) * ($itemData['iva'] / 100);
        }

        $totalItem = $costo * $cantidad;

        return Saitemcom::create([
            'tipocom'     => $compra->tipocom,
            'esserv'      => 0,
            'numerod'     => $compra->numerod,
            'codprov'     => $compra->codprov,
            'nrolinea'    => $itemData['linea_num'],
            'signo'       => 1,
            'fechae'      => $compra->fechae,
            'coditem'     => $item->codprod,
            'refere'      => $producto->refere ?? '',
            'codubic'     => $itemData['codubic'],
            'descrip1'    => substr($producto->descrip, 0, 60),
            'descrip2'    => substr($producto->descrip2, 0, 60),
            'descrip3'    => substr($producto->descrip3, 0, 60),
            'cantidad'    => $cantidad,
            'costo'       => $costo,
            'totalitem'   => $totalItem,
            'precio'      => $result,
            'mtotax'      => $ivaMonto,
            'costorg'     => $itemData['costo_orig'],
            'existant'    => $producto->existen ?? 0,
            'esexento'    => $producto->esexento ? 1 : 0,
            'esunid'      => $producto->exdecimal ? 1 : 0,
            'preciod'     => $result,
            'preciod2'    => $item->costadicional,
            'costod'      => $item->precio,
            'costod2'     => $item->precio2,
            'costod3'     => $item->precio3,
            'result'      => $result,
            'costoorig'   => $itemData['costo_orig'],
            'porc1'       => $item->porc1,
            'porc2'       => $item->porc2,
            'porc3'       => $item->porc3,
            'fk_sucursal' => $this->parametros['sucursal_id'],
        ]);
    }

    /**
     * Procesar seriales de la compra
     */
    protected function procesarSerialesCompra($item, $compraItem, $compra, $datos)
    {
        $lineaNum = 0;
        foreach ($item->seriales as $serial) {
            $lineaNum++;
            Saseprcom::create([
                'codsucu'     => '00000',
                'tipocom'     => $compra->tipocom,
                'numerod'     => $compra->numerod,
                'codprov'     => $compra->codprov,
                'nrolinea'    => $compraItem->nrolinea,
                'nrolineac'   => $lineaNum,
                'nroserial'   => $serial->serial,
                'coditem'     => $item->codprod,
                'codubic'     => $datos['codubic'],
                'fk_sucursal' => $this->parametros['sucursal_id'],
            ]);
        }
    }

    /**
     * Actualizar totales de la compra
     */
    protected function actualizarTotalesCompra($compra, $totalResult, $gbasetotal, $gsumaiva, $gexento, $datos)
    {
        $flete = $datos['fletecompra'] ?? 0;
        $descuento = $datos['descuentoc'] ?? 0;

        // Aplicar descuento a los totales si existe
        if ($descuento > 0) {
            $descuentoFactor = 1 - ($descuento / 100);
            $gbasetotal      = $gbasetotal * $descuentoFactor;
            $gsumaiva        = $gsumaiva * $descuentoFactor;
            $gexento         = $gexento * $descuentoFactor;
            $totalResult     = $totalResult * $descuentoFactor;
        }

        // Convertir flete a moneda local si es necesario
        $fleteLocal = $flete * ($datos['tasa'] ?? 1);

        $totalDeuda = $gbasetotal + $gsumaiva + $gexento + $fleteLocal;
        $total = $totalResult + $flete;

        $compra->update([
            'monto'      => $gbasetotal,
            'totalprd'   => $gbasetotal,
            'tgravable'  => $gbasetotal,
            'texento'    => $gexento,
            'fletes'     => $fleteLocal,
            'mtotax'     => $gsumaiva,
            'totaldeuda' => $totalDeuda,
            'mtototal'   => $total,
        ]);
    }

    /**
     * Procesar costos adicionales del pedido
     */
    protected function procesarCostosAdicionales($pedido, $compra, $datos)
    {
        foreach ($pedido->costosAdicionales as $costoAdicional) {
            if ($costoAdicional->codprov && $costoAdicional->codprov != 'CODPROV') {
                CxCProveedor::create([
                    'concepto' => $costoAdicional->descripcion ?? 'Costo adicional',
                    'monto' => $costoAdicional->monto,
                    'signo' => 1,
                    'numerod' => $compra->numerod,
                    'tipo' => 'estructura',
                    'fecha' => $costoAdicional->fecha ?? now(),
                    'codprov' => $costoAdicional->codprov,
                    'tipocomp' => $compra->tipocom,
                    'fk_sucursal' => $this->parametros['sucursal_id'],
                ]);
            }
        }
    }

    /**
     * Generar CxP (Cuenta por Pagar)
     */
    protected function generarCxP($compra, $proveedor, $datos)
    {
        $totalDeuda = $compra->totaldeuda;

        // Crear CxP
        $cxc = CxCProveedor::create([
            'concepto'    => 'Compra mercancia',
            'monto'       => $totalDeuda,
            'signo'       => 1,
            'numerod'     => $compra->numerod,
            'tipo'        => 'compra',
            'fecha'       => $compra->fechae,
            'codprov'     => $compra->codprov,
            'tipocomp'    => $compra->tipocom,
            'fk_sucursal' => $this->parametros['sucursal_id'],
            'fechav'      => $compra->fechav,
        ]);

        // Si es compra de contado, crear también el pago
        if ($datos['decontado'] == 1) {
            CxCProveedor::create([
                'concepto' => 'PAGO FACT ' . $compra->numerod,
                'monto' => $totalDeuda,
                'signo' => 0,
                'tipo' => 'compra',
                'fecha' => $compra->fechae,
                'codprov' => $compra->codprov,
                'fk_sucursal' => $this->parametros['sucursal_id'],
            ]);
        }

        return $cxc;
    }

    /**
     * Procesar Nota de Entrega (similar a compra pero sin incrementar inventario)
     */
    public function procesarNotaEntrega($pedidoId, $itemsSeleccionados, $datosCompra)
    {
        DB::beginTransaction();

        try {
            // Similar a procesarCompra pero con tipocom = 'U'
            $datosCompra['tipocom'] = 'U';
            $compra = $this->procesarCompra($pedidoId, $itemsSeleccionados, $datosCompra);

            // Marcar items como nota de entrega
            $items = PedidoItem::whereIn('id', $itemsSeleccionados)->get();
            foreach ($items as $item) {
                $item->update(['und_notaentrega' => $item->und_notaentrega + $item->cantidad]);
            }

            DB::commit();
            return $compra;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

