<?php
// app/Http/Controllers/Facturas/FacturaController.php

namespace App\Http\Controllers\Facturas;

use App\Http\Controllers\Controller;
use App\Models\Safact;
use App\Models\Saipavta;
use App\Models\Saitemfac;
use App\Models\Saseprfac;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    public function detalleVista($tipofac, $numerod, $fksucu)
    {
        try {
            $ajax = 1; // Para indicar que es una petición AJAX

            $documento = Safact::selectRaw("date_format(fechat,'%d/%m/%Y') as fecha, date_format(fechat,'%h:%i %p') as hora,
                                           notas1, notas2, notas3, safact.NumeroD, TipoFac, fk_sucursal, cancelausd, codesta, creddolar_pagado as pagado,
                                           dolares, pesos, (cancele - efectivosumado) as cancele, (cancelt - tarjetasumado) as cancelt,
                                           vuelto_cancele, vuelto_dolares, vuelto_pesos, dolar_transf as transf, descrip,
                                           (credito/tasa_dolar) as credito, (contado/tasa_dolar) as contado, tasa_dolar, id3, telef, direc1, direc2
                                 ")
                ->whereRaw("NumeroD = '$numerod' and TipoFac = '$tipofac' and fk_sucursal = $fksucu")
                ->with(['items.producto.instancia', 'seriales'])
                ->whereHas('items', function ($q) use ($fksucu, $tipofac, $numerod) {
                    $q->whereRaw("saitemfac.TipoFac = '$tipofac' and saitemfac.NumeroD='$numerod' and saitemfac.fk_sucursal = $fksucu");
                })
                ->first();

            if (!$documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Factura no encontrada'
                ], 404);
            }

            $instpago = [];
            if ($documento && ($documento->cancelt > 0 || $documento->transf > 0)) {
                $instpago = Saipavta::with('satarj')
                    ->where([
                        'NumeroD' => $numerod,
                        'TipoFac' => $tipofac,
                        'fk_sucursal' => $fksucu
                    ])
                    ->get();
            }

            // Renderizar la vista y capturar el HTML
            $html = view('facturas.modal-detalle', compact('ajax', 'instpago', 'numerod', 'tipofac', 'documento'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'titulo' => ($tipofac == 'A' or $tipofac == 'Z') ? 'VENTA' : 'DEVOLUCIÓN'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
