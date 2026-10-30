<?php
// app/Services/Compra/CompraProcessorService.php

namespace App\Services\Compra;

use App\Models\Sacomp;
use App\Models\Saitemcom;
use App\Models\Saseprcom;
use App\Models\Saprod;
use App\Models\Saprodsucursal;
use App\Models\Sasucursal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompraProcessorService
{
    /**
     * Procesar una compra individual
     */
    public function procesarCompra($compraData, $sucursalId, $todasSucursales)
    {
        // Buscar o crear la compra
        $compra = Sacomp::firstOrNew([
            'nrounico' => $compraData->nrounico,
            'fk_sucursal' => $sucursalId
        ]);

        $esNueva = !$compra->exists;

        if ($esNueva) {
            $compra->fill((array) $compraData);
            $compra->fk_sucursal = $sucursalId;
            $compra->status = $compra->status ?? 1; // Status por defecto: Abierta
        }

        $compra->save();

        // Si es nueva, procesar items y seriales
        if ($esNueva) {
            $this->procesarItemsCompra($compraData, $sucursalId, $todasSucursales, $compra);
            $this->procesarSerialesCompra($compraData, $sucursalId, $compra);
        }

        return [
            'compra_id' => $compra->id,
            'numerod' => $compra->numerod,
            'tipo' => $compra->tipocom,
            'nuevo' => $esNueva
        ];
    }

    /**
     * Procesar múltiples compras
     */
    public function procesarCompras($comprasData, $sucursalId)
    {
        $resultados = [];
        $sucursal = Sasucursal::find($sucursalId);

        if (!$sucursal) {
            throw new \Exception('Sucursal no encontrada');
        }

        $todasSucursales = Sasucursal::where("fk_comercial", $sucursal->fk_comercial)->get();

        foreach ($comprasData as $compraData) {
            if (!isset($compraData->nrounico)) {
                Log::warning('Compra sin nrounico', ['data' => $compraData]);
                continue;
            }

            try {
                $resultado = $this->procesarCompra($compraData, $sucursalId, $todasSucursales);
                $resultados[] = $resultado;
            } catch (\Exception $e) {
                Log::error('Error procesando compra individual', [
                    'nrounico' => $compraData->nrounico ?? 'N/A',
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return $resultados;
    }

    /**
     * Procesar items de la compra
     */
    protected function procesarItemsCompra($compraData, $sucursalId, $todasSucursales, $compra)
    {
        if (!isset($compraData->allitems) || !is_array($compraData->allitems)) {
            return;
        }

        foreach ($compraData->allitems as $itemData) {
            $this->guardarItemCompra($itemData, $sucursalId, $compra);
            $this->actualizarPreciosProducto($itemData, $sucursalId);
            $this->limpiarProductosSucursal($itemData->coditem, $todasSucursales);
        }
    }

    /**
     * Guardar item de compra
     */
    protected function guardarItemCompra($itemData, $sucursalId, $compra)
    {
        $item = new Saitemcom();
        $item->fill((array) $itemData);
        $item->fk_sucursal = $sucursalId;

        // Asegurar que coincida con la compra
        $item->tipocom = $compra->tipocom;
        $item->numerod = $compra->numerod;
        $item->codprov = $compra->codprov;

        $item->save();

        return $item;
    }

    /**
     * Actualizar precios del producto
     */
    protected function actualizarPreciosProducto($itemData, $sucursalId)
    {
        $sucursal = Sasucursal::find($sucursalId);

        if (!$sucursal) {
            return;
        }

        $producto = Saprod::where([
            'codprod' => $itemData->coditem,
            'comercial' => $sucursal->fk_comercial
        ])->first();

        if ($producto) {
            $producto->costod = $itemData->costod ?? 0;
            $producto->costod2 = $itemData->costod2 ?? 0;
            $producto->costod3 = $itemData->costod3 ?? 0;
            $producto->preciod = $itemData->preciod ?? 0;
            $producto->preciod2 = $itemData->preciod2 ?? 0;
            $producto->save();

            Log::info('Precios actualizados', [
                'codprod' => $itemData->coditem,
                'costod' => $producto->costod
            ]);
        }
    }

    /**
     * Limpiar productos de sucursales
     */
    protected function limpiarProductosSucursal($coditem, $sucursales)
    {
        foreach ($sucursales as $sucursal) {
            Saprodsucursal::where([
                'codprod' => $coditem,
                'fk_sucursal' => $sucursal->id
            ])->delete();
        }
    }

    /**
     * Procesar seriales de la compra
     */
    protected function procesarSerialesCompra($compraData, $sucursalId, $compra)
    {
        if (!isset($compraData->seriales) || !is_array($compraData->seriales)) {
            return;
        }

        foreach ($compraData->seriales as $serialData) {
            $this->guardarSerialCompra($serialData, $sucursalId, $compra);
        }
    }

    /**
     * Guardar serial de compra
     */
    protected function guardarSerialCompra($serialData, $sucursalId, $compra)
    {
        // Verificar si el serial ya existe
        $existe = Saseprcom::where('nroserial', $serialData->nroserial)
            ->where('tipocom', $compra->tipocom)
            ->where('numerod', $compra->numerod)
            ->exists();

        if ($existe) {
            Log::warning('Serial ya existe en esta compra', [
                'nroserial' => $serialData->nroserial,
                'numerod' => $compra->numerod
            ]);
            return;
        }

        $serial = new Saseprcom();
        $serial->fill((array) $serialData);
        $serial->fk_sucursal = $sucursalId;

        // Asegurar que coincida con la compra
        $serial->tipocom = $compra->tipocom;
        $serial->numerod = $compra->numerod;
        $serial->codprov = $compra->codprov;

        $serial->save();

        return $serial;
    }

    /**
     * Validar que una compra se pueda cerrar
     */
    public function puedeCerrarCompra($compraId)
    {
        $compra = Sacomp::with('seriales')->find($compraId);

        if (!$compra) {
            return false;
        }

        // Una compra se puede cerrar si no tiene seriales
        return $compra->seriales->isEmpty();
    }

    /**
     * Obtener estadísticas de una compra
     */
    public function obtenerEstadisticas($compraId)
    {
        $compra = Sacomp::with(['items', 'seriales'])->find($compraId);

        if (!$compra) {
            return null;
        }

        $totalUnidades = $compra->items->sum('cantidad');
        $totalSeriales = $compra->seriales->count();
        $totalMonto = $compra->items->sum(function($item) {
            return ($item->preciod ?? 0) * ($item->cantidad ?? 0);
        });

        return [
            'total_unidades' => $totalUnidades,
            'total_seriales' => $totalSeriales,
            'total_monto' => $totalMonto,
            'tiene_seriales' => $totalSeriales > 0,
            'puede_cerrar' => $totalSeriales == 0,
            'status' => $compra->status,
            'status_texto' => $this->getStatusText($compra->status)
        ];
    }

    /**
     * Obtener texto del status
     */
    protected function getStatusText($status)
    {
        return match ($status) {
            0 => 'Cerrada',
            1 => 'Abierta',
            2 => 'Pendiente',
            default => 'Desconocido'
        };
    }
}
