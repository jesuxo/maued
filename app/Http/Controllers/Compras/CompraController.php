<?php
// app/Http/Controllers/Compras/CompraController.php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Models\Sacomp;
use App\Models\Saitemcom;
use App\Models\Saseprcom;
use App\Models\Sasucursal;
use App\Traits\ComercialTrait;
use App\Services\Compra\CompraProcessorService;
use App\Services\Serial\SerialTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompraController extends Controller
{
    use ComercialTrait;

    protected $compraProcessor;
    protected $serialTracker;

    public function __construct(
        CompraProcessorService $compraProcessor,
        SerialTrackerService $serialTracker
    ) {
        $this->compraProcessor = $compraProcessor;
        $this->serialTracker = $serialTracker;
    }

    /**
     * Procesar documento de compra
     */
    public function procesarDocumento(Request $request)
    {
        try {
            DB::beginTransaction();

            $sucursalId = str_replace("300", "", $request->sucursal);
            $comprasData = json_decode($request->compras);

            if (!$comprasData) {
                throw new \Exception('No se recibieron datos de compras');
            }

            $resultados = $this->compraProcessor->procesarCompras($comprasData, $sucursalId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compras procesadas correctamente',
                'data' => [
                    'procesadas' => count($resultados),
                    'detalles' => $resultados
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error procesando compras: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar compras: ' . $e->getMessage()
            ], 500);
        }
    }


    public function reporte(Request $request)
    {
        $comercialId = $this->getComercialId();
        $sucursales = $this->getSucursalesComercial();

        // Preparar filtros
        $filtros = $this->prepararFiltros($request);

        // Construir query
        $query = Sacomp::with(['sucursal.comercial', 'items', 'seriales'])
            ->whereHas('sucursal.comercial', function($q) use ($comercialId) {
                $q->where('fk_comercial', $comercialId);
            });

        // Aplicar filtro especial de tipo_reporte
        if ($request->has('tipo_reporte')) {
            if ($request->tipo_reporte == 'descargados') {
                $query->whereHas('seriales', function($q) {
                    $q->where('checked', 2); // 2 = descargado
                });
            } elseif ($request->tipo_reporte == 'vendidos') {
                $query->whereHas('seriales', function($q) {
                    $q->where('checked', 3); // 3 = vendido
                });
            }
        } else {
            // Aplicar filtros normales
            $query = $this->aplicarFiltros($query, $filtros);
        }

        // Ejecutar query
        $compras = $query->orderByDesc('id')->get();

        // Actualizar status de compras sin seriales
        $this->actualizarStatusCompras($compras);

        // Obtener estadísticas de descargados si se solicitó
        $statsDescargados = null;
        if ($request->tipo_reporte == 'descargados') {
            $statsDescargados = $this->getEstadisticasDescargados($comercialId);
        }

        return view('compras.reporte', array_merge(
            $filtros,
            [
                'sucursales' => $sucursales,
                'compras' => $compras,
                'comercialId' => $comercialId,
                'statsDescargados' => $statsDescargados
            ]
        ));
    }

    private function getEstadisticasDescargados($comercialId)
    {
        // Total de seriales descargados
        $totalDescargados = DB::table('saseprcom')
            ->join('sacomp', function($join) {
                $join->on('saseprcom.numerod', '=', 'sacomp.numerod')
                    ->on('saseprcom.tipocom', '=', 'sacomp.tipocom');
            })
            ->join('sasucursal', 'sacomp.fk_sucursal', '=', 'sasucursal.id')
            ->where('sasucursal.fk_comercial', $comercialId)
            ->where('saseprcom.checked', 2)
            ->count();

        // Compras con descargados
        $comprasConDescargados = DB::table('sacomp')
            ->join('sasucursal', 'sacomp.fk_sucursal', '=', 'sasucursal.id')
            ->where('sasucursal.fk_comercial', $comercialId)
            ->whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('saseprcom')
                    ->whereColumn('saseprcom.numerod', 'sacomp.numerod')
                    ->whereColumn('saseprcom.tipocom', 'sacomp.tipocom')
                    ->where('saseprcom.checked', 2);
            })
            ->count();

        // Descargados por mes (últimos 6 meses)
        $descargadosPorMes = DB::table('saseprcom')
            ->join('sacomp', function($join) {
                $join->on('saseprcom.numerod', '=', 'sacomp.numerod')
                    ->on('saseprcom.tipocom', '=', 'sacomp.tipocom');
            })
            ->join('sasucursal', 'sacomp.fk_sucursal', '=', 'sasucursal.id')
            ->where('sasucursal.fk_comercial', $comercialId)
            ->where('saseprcom.checked', 2)
            ->where('saseprcom.checked_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw('DATE_FORMAT(saseprcom.checked_at, "%Y-%m") as mes'),
                DB::raw('count(*) as total')
            )
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return [
            'total_descargados' => $totalDescargados,
            'compras_con_descargados' => $comprasConDescargados,
            'descargados_por_mes' => $descargadosPorMes
        ];
    }

    /**
     * Ver documento de compra
     */
    public function verDocumento($id)
    {
        $comercialId = $this->getComercialId();

        $documento = Sacomp::where('id', $id)
            ->with(['items.producto.instancia', 'sucursal'])
            ->firstOrFail();

        // Cargar seriales
        $seriales = Saseprcom::where('numerod', $documento->numerod)
            ->where('tipocom', $documento->tipocom)
            ->with('producto')
            ->get();

        $documento->setRelation('seriales', $seriales);

        return view('compras.documento', [ // ← Aquí debe ser 'compras.documento'
            'documento' => $documento,
            'numerod' => $documento->numerod,
            'tipocom' => $documento->tipocom
        ]);
    }

    /**
     * Ver seriales de compra
     */
    /**
     * Ver seriales de compra - VERSIÓN CORREGIDA
     */
    public function verSeriales($id)
    {
        $comercialId = $this->getComercialId();

        $documento = Sacomp::where('id', $id)
            ->with(['items.producto.instancia', 'sucursal'])
            ->firstOrFail();

        if ($documento->status == 2) {
            $documento->status = 1;
            $documento->save();
        }

        // Cargar seriales por separado (sin usar relación que no existe)
        $seriales = Saseprcom::where('numerod', $documento->numerod)
            ->where('tipocom', $documento->tipocom)
            ->with('producto')
            ->get();

        // Agregar los seriales al documento para la vista
        $documento->setRelation('seriales', $seriales);

        // Usar el servicio para obtener el historial
        $historialData = $this->serialTracker->obtenerHistorialSerialesCompra($id);

        return view('compras.seriales', [
            'documento' => $documento,
            'numerod' => $documento->numerod,
            'tipocom' => $documento->tipocom,
            'sucursalesVenta' => $historialData['sucursales_venta'],
            'historialSeriales' => $historialData['historial']
        ]);
    }


    /**
     * Cambiar status de compra
     */
    public function cambiarStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|numeric',
                'status' => 'required|in:0,1,2'
            ]);

            $documento = Sacomp::with('seriales')->find($request->id);

            if (!$documento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], 404);
            }

            // Validar que no se cierre una compra con seriales
            if ($request->status == 0 && $documento->seriales->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cerrar una compra que tiene seriales asociados'
                ], 422);
            }

            $statusAnterior = $documento->status;
            $documento->status = $request->status;

            // Guardar motivo en notas si se proporcionó
            if ($request->motivo) {
                $documento->notas8 = $this->generarNotaCambioStatus(
                    $statusAnterior,
                    $request->status,
                    $request->motivo,
                    auth()->user()
                );
            }

            $documento->save();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estatus actualizado correctamente',
                    'data' => [
                        'nuevo_status' => $request->status,
                        'status_text' => $this->getStatusText($request->status)
                    ]
                ]);
            }

            return redirect()->back()->with('success', 'Estatus actualizado correctamente');

        } catch (\Exception $e) {
            Log::error('Error cambiando status: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Preparar filtros del request
     */
    protected function prepararFiltros(Request $request)
    {
        $filtros = [
            'fksucursal' => $request->fksucursal ?? 0,
            'status' => $request->status ?? '',
            'verificacion' => $request->verificacion ?? '', // Nuevo filtro
            'busqueda' => $request->busqueda ?? '',
            'fechasreport' => $request->fechasreport ?? '',
            'fecha1' => '',
            'fecha2' => ''
        ];

        // Procesar fechas
        $fechasAux = str_replace(' ', '', $filtros['fechasreport']);

        if (strpos($fechasAux, "to")) {
            list($filtros['fecha1'], $filtros['fecha2']) = explode("to", $fechasAux);
        } elseif ($filtros['fechasreport'] != '') {
            $filtros['fecha1'] = $filtros['fecha2'] = $filtros['fechasreport'];
            $filtros['fechasreport'] = $filtros['fechasreport'] . " to " . $filtros['fechasreport'];
        }

        return $filtros;
    }

    /**
     * Aplicar filtros al query
     */
    protected function aplicarFiltros($query, $filtros)
    {
        // Filtro de búsqueda
        if ($filtros['busqueda']) {
            $query = $this->aplicarFiltroBusqueda($query, $filtros['busqueda']);
        }

        // Filtro de fechas
        if ($filtros['fecha1']) {
            $query = $this->aplicarFiltroFechas($query, $filtros['fecha1'], $filtros['fecha2']);
        }

        // Filtro de status
        $query = $this->aplicarFiltroStatus($query, $filtros['status']);

        // Filtro de sucursal
        if ($filtros['fksucursal'] > 0) {
            $query->where('fk_sucursal', $filtros['fksucursal']);
        }

        return $query;
    }

    /**
     * Aplicar filtro de búsqueda
     */
    protected function aplicarFiltroBusqueda($query, $busqueda)
    {
        $busqueda = str_replace('*', ' ', $busqueda);
        $terminos = explode(" ", $busqueda);

        $query->where(function($q) use ($terminos) {
            foreach ($terminos as $termino) {
                $termino = trim($termino);
                if (empty($termino)) continue;

                $q->where(function($subq) use ($termino) {
                    $subq->where('numerod', 'like', "%$termino%")
                        ->orWhere('notas1', 'like', "%$termino%")
                        ->orWhere('notas2', 'like', "%$termino%")
                        ->orWhere('descrip', 'like', "%$termino%")
                        ->orWhere('codprov', 'like', "%$termino%");
                });
            }
        });

        return $query;
    }

    /**
     * Aplicar filtro de fechas
     */
    protected function aplicarFiltroFechas($query, $fecha1, $fecha2)
    {
        list($d1, $m1, $y1) = explode("/", $fecha1);
        list($d2, $m2, $y2) = explode("/", $fecha2);

        $fechaInicio = "$y1-$m1-$d1 00:00:00";
        $fechaFin = "$y2-$m2-$d2 23:59:59";

        return $query->whereBetween('fechat', [$fechaInicio, $fechaFin]);
    }

    /**
     * Aplicar filtro de status
     */
    protected function aplicarFiltroStatus($query, $status)
    {
        if ($status !== '') {
            if ($status == 2) {
                $query->where('status', $status)
                    ->whereIn('tipocom', ['U'])
                    ->limit(50);
            } else {
                $query->whereIn('tipocom', ['U', 'Y'])
                    ->where('status', $status)
                    ->limit(500);
            }
        } else {
            $query->whereIn('tipocom', ['U', 'Y'])->limit(50);
        }

        return $query;
    }

    /**
     * Actualizar status de compras sin seriales
     */
    protected function actualizarStatusCompras($compras)
    {
        foreach ($compras as $compra) {
            if ($compra->seriales->isEmpty() && $compra->status != 0) {
                $compra->status = 0;
                $compra->save();
            }
        }
    }

    /**
     * Generar nota de cambio de status
     */
    protected function generarNotaCambioStatus($statusAnterior, $nuevoStatus, $motivo, $usuario)
    {
        return sprintf(
            'Cambio status: %s -> %s - Motivo: %s (Usuario: %s - %s)',
            $this->getStatusText($statusAnterior),
            $this->getStatusText($nuevoStatus),
            $motivo,
            $usuario->first_name ?? 'Sistema',
            now()->format('d/m/Y H:i')
        );
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
