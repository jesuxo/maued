<?php

namespace App\Http\Controllers;

use App\Models\Cwcxcprv;
use App\Models\Cwviajemoto;
use App\Models\Sacomercial;
use App\Models\Sainsta;
use App\Models\Saprodsucursal;
use App\Models\Saprov;
use App\Models\Saprovsucursal;
use App\Models\Saprod;
use App\Models\Saexis;
use App\Models\Sacomp;
use App\Models\Saitemcom;
use App\Models\Saitemfac;
use App\Models\Sasucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaprovController extends Controller
{

    public function buscarPredictivo(Request $request)
    {
        $term = $request->get('term', '');

        $query = Saprov::select('codprov', 'descrip', 'id3', 'telef')
            ->orderBy('descrip', 'asc');

        if ($term && strlen($term) >= 1) {
            $query->where(function($q) use ($term) {
                $q->where('descrip', 'LIKE', "%{$term}%")
                    ->orWhere('id3', 'LIKE', "%{$term}%")
                    ->orWhere('codprov', 'LIKE', "%{$term}%");
            });
        }

        $proveedores = $query->get();

        return response()->json($proveedores);
    }

    public function list(Request $request)
    {
        $sucursalid = str_replace("300","",$request->sucursal);
        $proveedores   = $request->proveedores;
        $proveedores   = json_decode($proveedores);

        if(isset($proveedores))
            foreach ($proveedores as $proveedor){

                $aux = Saprov::where(['codprov' => $proveedor->codprov])->first();
                if(!$aux){
                    $new = new Saprov();


                    $new->id3           = ($proveedor->id3)       ?$proveedor->id3        : '';
                    $new->fax           = ($proveedor->fax)       ?$proveedor->fax        : '';
                    $new->clase         = ($proveedor->clase)     ?$proveedor->clase      : '';
                    $new->telef         = ($proveedor->telef)     ?$proveedor->telef      : '';
                    $new->movil         = ($proveedor->movil)     ?$proveedor->movil      : '';
                    $new->email         = ($proveedor->email)     ?$proveedor->email      : '';
                    $new->direc1        = ($proveedor->direc1)    ?$proveedor->direc1     : '';
                    $new->direc2        = ($proveedor->direc2)    ?$proveedor->direc2     : '';
                    $new->activo        = ($proveedor->activo)    ?$proveedor->activo     : 0;
                    $new->codprov       = $proveedor->codprov;
                    $new->tipoprv       = ($proveedor->tipoprv)   ?$proveedor->tipoprv    : 0;
                    $new->tipoid3       = ($proveedor->tipoid3)   ?$proveedor->tipoid3    : 0;
                    $new->tipoid        = ($proveedor->tipoid)    ?$proveedor->tipoid     : 0;
                    $new->descrip       = ($proveedor->descrip)   ?$proveedor->descrip    : '';
                    $new->represent     = ($proveedor->represent) ?$proveedor->represent  : '';
                    $new->zipcode       = ($proveedor->zipcode)   ?$proveedor->zipcode    : '';
                    $new->blockdesc     = ($proveedor->blockdesc) ?$proveedor->blockdesc  : 0;
                    $new->observa       = ($proveedor->observa)   ?$proveedor->observa    : '';

                    $new->save();

                    $rel              = new Saprovsucursal();
                    $rel->codprov     = $proveedor->codprov;
                    $rel->fk_sucursal = $sucursalid;
                    $rel->save();
                }else{
                    $aux = Saprovsucursal::where(['codprov' => $proveedor->codprov, 'fk_sucursal'=>$sucursalid])->first();
                    if(!$aux){
                        $rel              = new Saprovsucursal();
                        $rel->codprov     = $proveedor->codprov;
                        $rel->fk_sucursal = $sucursalid;
                        $rel->save();
                    }
                }
            }

        $proveedores = Saprov::whereRaw("codprov not in (select codprov from saprovsucursal where fk_sucursal=$sucursalid )")->get();


        return response()->json(['success'=>'success', 'newproveedores' => $proveedores]);
    }

    public function saprovsucursal(Request $request)
    {
        $sucursalid = str_replace("300", "", $request->sucursal);
        $proveedores = $request->proveedores;
        $proveedores = json_decode($proveedores);

        if (isset($proveedores))
            foreach ($proveedores as $proveedor){
                $aux = Saprovsucursal::where(['codprov' => $proveedor->codprov, 'fk_sucursal'=>$sucursalid])->first();
                if(!$aux){
                    $rel              = new Saprovsucursal();
                    $rel->codprov     = $proveedor->codprov;
                    $rel->fk_sucursal = $sucursalid;
                    $rel->save();
                }
            }

        return response()->json(['success'=>'success']);
    }

    public function json()
    {
        $user     = User::where('id',auth()->user()->id)->with(['sucursales.sucursal.saprovsucursales.proveedor'])->first();
        $proveedores = $sucursales = $aux = $all = [];
        foreach ($user->sucursales as $rel){
            array_push($sucursales, $rel->sucursal);
            if(isset($rel->sucursal->Saprovsucursales)){
                foreach ($rel->sucursal->Saprovsucursales as $relproveedor){
                    array_push($proveedores, $relproveedor->proveedor);
                }
            }
        }

        foreach ($proveedores as $item){
            list($fecha,$hora) = explode(" ",$item->created_at);
            list($y,$m,$d) = explode("-",$fecha);
            $aux = [
                "id"        => "$item->id",
                "codprov"   => "$item->codprov",
                "descrip"   => "$item->descrip",
                "telef"     => "$item->telef"." "."$item->movil",
                "date"      => "$y-$m-$d",
                "datelabel" => "$d/$m/$y"
            ];

            array_push($all, $aux);
        }
        return response()->json($all);

    }

    public function productosPanel($codprov, Request $request)
    {
        $comercial  = session('comercialid') ;
        if(!$comercial) {
            session(['comercialid' => 1]);
            $comercial = 1;
        }

        // Obtener fechas del request o usar valores por defecto (últimos 3 meses)
        $fecha_desde = $request->fecha_desde ?? date('Y-m-d', strtotime('-3 months'));
        $fecha_hasta = $request->fecha_hasta ?? date('Y-m-d');
        $orden       = $request->orden ?? 'vendido_desc';
        $filtro      = $request->filtro ?? 'todos';

        // Obtener información del proveedor
        $proveedor = Saprov::where('codprov', $codprov)->first();

        if (!$proveedor) {
            return redirect()->route('proveedores.index')->with('error', 'Proveedor no encontrado');
        }

        $comprasData = $this->getComprasProveedor($codprov, $request,10);
        $compras = $comprasData['compras'];

        // Obtener todos los productos de este proveedor
        $productos = $this->getProductosProveedor($codprov, $fecha_desde, $fecha_hasta, $orden, $filtro);

        // Calcular KPI generales
        $kpi = $this->calcularKPIProveedor($codprov, $fecha_desde, $fecha_hasta);

        // Top productos más vendidos
        $top_productos = $this->getTopProductos($codprov, $fecha_desde, $fecha_hasta, 10);

        $sucursales = Sasucursal::where('fk_comercial', $comercial)->get();

        // === KPI Cuentas por Pagar del proveedor ===
        $cxpKpi = Cwcxcprv::where('codprov', $codprov)
            ->where('signo', 1)
            ->whereRaw('(monto - abonado) > 0')
            ->select(
                DB::raw('COUNT(*) as total_facturas'),
                DB::raw('SUM(monto - abonado) as deuda_total'),
                DB::raw('SUM(CASE WHEN fechav < CURDATE() THEN (monto - abonado) ELSE 0 END) as deuda_vencida'),
                DB::raw('SUM(CASE WHEN fechav < CURDATE() THEN 1 ELSE 0 END) as facturas_vencidas'),
                DB::raw('MIN(fechav) as vencimiento_mas_antiguo')
            )
            ->first();

        $cxpKpi = [
            'total_facturas'          => (int)   ($cxpKpi->total_facturas ?? 0),
            'deuda_total'             => (float) ($cxpKpi->deuda_total ?? 0),
            'deuda_vencida'           => (float) ($cxpKpi->deuda_vencida ?? 0),
            'facturas_vencidas'       => (int)   ($cxpKpi->facturas_vencidas ?? 0),
            'vencimiento_mas_antiguo' => $cxpKpi->vencimiento_mas_antiguo ?? null,
        ];

        return view('proveedores.productos-panel', compact(
            'proveedor',
            'fecha_desde',
            'fecha_hasta',
            'orden',
            'filtro',
            'productos',
            'kpi',
            'top_productos',
            'compras',
            'sucursales',
            'cxpKpi'
        ));
    }

    /**
     * Cuentas por pagar de un proveedor en formato simple (para modal del panel de compras)
     */
    public function cxpProveedorSimple($codprov)
    {
        $facturas = Cwcxcprv::where('codprov', $codprov)
            ->where('signo', 1)
            ->whereRaw('(monto - abonado) > 0')
            ->select(
                'id',
                'numerod',
                'concepto',
                'monto',
                'abonado',
                DB::raw('(monto - abonado) as deuda'),
                'fecha',
                'fechav'
            )
            ->orderBy('fechav', 'asc')
            ->orderBy('fecha', 'asc')
            ->get();

        $hoy = Carbon::now()->startOfDay();

        $rows = $facturas->map(function ($f) use ($hoy) {
            $vence         = Carbon::parse($f->fechav)->startOfDay();
            $diasVencidos  = $vence->lt($hoy) ? $vence->diffInDays($hoy) : 0;
            $diasPorVencer = $vence->gte($hoy) ? $hoy->diffInDays($vence) : 0;

            return [
                'id'              => $f->id,
                'numerod'         => $f->numerod,
                'concepto'        => $f->concepto,
                'monto'           => (float) $f->monto,
                'abonado'         => (float) $f->abonado,
                'deuda'           => (float) $f->deuda,
                'fecha_fmt'       => Carbon::parse($f->fecha)->format('d/m/Y'),
                'fechav_fmt'      => $vence->format('d/m/Y'),
                'dias_vencidos'   => $diasVencidos,
                'dias_por_vencer' => $diasPorVencer,
            ];
        });

        return response()->json([
            'success'       => true,
            'facturas'      => $rows,
            'total_deuda'   => $facturas->sum('deuda'),
            'total_monto'   => $facturas->sum('monto'),
            'total_abonado' => $facturas->sum('abonado'),
            'total_facturas'=> $facturas->count(),
        ]);
    }

    /**
     * Actualización rápida de producto desde el panel de proveedores
     */
    public function quickUpdateProducto(Request $request)
    {
        $comercial = session('comercialid');
        $comercialModel = Sacomercial::find($comercial);

        if (!$comercialModel) return;

        $match = $comercialModel->match;

        try {
            $request->validate([
                'id'         => 'required|exists:saprod,id',
                'descrip'    => 'required|string|max:255',
                'refere'     => 'nullable|string|max:40',
                'marca'      => 'nullable|string|max:20',
                'preciodant' => 'nullable|numeric|min:0',
                'preciodpro' => 'nullable|numeric|min:0',
                'preciod'    => 'nullable|numeric|min:0',
                'costod'     => 'nullable|numeric|min:0',
                'costod2'    => 'nullable|numeric|min:0',
                'costod3'    => 'nullable|numeric|min:0',
            ]);

            $producto = Saprod::find($request->id);

            if (!$producto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Producto no encontrado'
                ], 404);
            }

            $codprod           = $producto->codprod;
            $producto->descrip = $request->descrip;
            $producto->refere  = $request->refere;
            $producto->marca   = $request->marca;

            // Precios (costo)
            if ($request->has('preciodant')) {
                $producto->preciodant = $this->formatNumber($request->preciodant);
            }
            if ($request->has('preciodpro')) {
                $producto->preciodpro = $this->formatNumber($request->preciodpro);
            }
            if ($request->has('preciod')) {
                $producto->preciod = $this->formatNumber($request->preciod);
            }

            // Precios (venta)
            if ($request->has('costod')) {
                $producto->costod = $this->formatNumber($request->costod);
            }
            if ($request->has('costod2')) {
                $producto->costod2 = $this->formatNumber($request->costod2);
            }
            if ($request->has('costod3')) {
                $producto->costod3 = $this->formatNumber($request->costod3);
            }

            $producto->save();

            $prodsucursal = Saprodsucursal::with('producto')->where('codprod', $codprod)->get();
            if($prodsucursal)
                foreach ($prodsucursal as $item){
                    if($item->producto->comercial == $match)
                        $item->delete();
                }

            // También actualizar productos con el mismo código en otros comerciales
            $this->syncProductPrices($producto->codprod, $producto);

            return response()->json([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'producto' => $producto
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formatear número desde string con formato español
     */
    private function formatNumber($value)
    {
        if (!$value) return 0;


        return floatval($value);
    }

    /**
     * Sincronizar precios en otros comerciales (match)
     */
    private function syncProductPrices($codprod, $productoOrigen)
    {
        $comercial = session('comercialid');
        $comercialModel = Sacomercial::find($comercial);

        if (!$comercialModel) return;

        $match = $comercialModel->match;

        // Buscar otros comerciales con el mismo match
        $otrosComerciales = Sacomercial::where('match', $match)
            ->where('id', '!=', $comercial)
            ->get();

        foreach ($otrosComerciales as $comercialDestino) {
            $producto = Saprod::where('codprod', $codprod)
                ->where('comercial', $comercialDestino->id)
                ->first();

            if ($producto) {
                $producto->descrip    = $productoOrigen->descrip;
                $producto->refere     = $productoOrigen->refere;
                $producto->marca      = $productoOrigen->marca;
                $producto->save();

                $prodsucursal = Saprodsucursal::with('producto')->where('codprod', $codprod)->get();
                if($prodsucursal)
                    foreach ($prodsucursal as $item){
                        if($item->producto->comercial == $match)
                            $item->delete();
                    }

            }
        }
    }

    /**
     * Obtener el historial completo de compras de un producto a un proveedor
     * (sin filtro de fechas)
     */
    public function comprasHistoricasProducto(Request $request)
    {
        $request->validate([
            'codprod' => 'required|string',
            'codprov' => 'required|string',
        ]);

        $codprod = $request->codprod;
        $codprov = $request->codprov;

        $comercial = session('comercialid') ?: 1;

        $sucursales = Sasucursal::where('fk_comercial', $comercial)->get();
        $sucursalIds = implode(',', $sucursales->pluck('id')->toArray());
        $sucursalesMap = $sucursales->pluck('descrip', 'id')->toArray();

        // ========== TODAS LAS COMPRAS DEL PRODUCTO A ESE PROVEEDOR ==========
        $compras = Saitemcom::where('coditem', $codprod)
            ->where('codprov', $codprov)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->orderBy('fechae', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // ========== CALCULAR TOTALES ==========
        $totalUnidades     = 0;
        $totalMonto        = 0;
        $totalCompras      = 0;
        $totalDevoluciones = 0;
        $registros         = [];

        foreach ($compras as $item) {
            $signo    = $item->tipocom == 'I' ? -1 : 1;
            $cantidad = (float) $item->cantidad * $signo;
            $monto    = (float) $item->preciod * $item->cantidad * $signo;

            $sucId     = $item->fk_sucursal;
            $nombreRaw = $sucursalesMap[$sucId] ?? ('Sucursal ' . $sucId);
            $nombreSuc = trim(str_replace('SARA', '', $nombreRaw));

            $registros[] = [
                'id'         => $item->id,
                'fecha'      => $item->fechae,
                'fecha_fmt'  => Carbon::parse($item->fechae)->format('d/m/Y'),
                'documento'  => $item->numerod,
                'tipocom'    => $item->tipocom,
                'tipo_texto' => $item->tipocom == 'H' ? 'Compra' : 'Devolución',
                'cantidad'   => $cantidad,
                'preciod'    => (float) $item->preciod,
                'monto'      => $monto,
                'sucursal'   => $nombreSuc,
                'compra_id'  => $item->compra ? $item->compra->id : null,
            ];

            $totalUnidades += $cantidad;
            $totalMonto    += $monto;

            if ($item->tipocom == 'H') {
                $totalCompras += (float) $item->cantidad;
            } else {
                $totalDevoluciones += (float) $item->cantidad;
            }
        }

        // ========== PRODUCTO ==========
        $producto = Saprod::where('codprod', $codprod)
            ->where('comercial', $comercial)
            ->select('codprod', 'descrip', 'marca')
            ->first();

        return response()->json([
            'success'   => true,
            'producto'  => $producto,
            'registros' => $registros,
            'resumen'   => [
                'total_unidades'     => $totalUnidades,
                'total_monto'        => $totalMonto,
                'total_compras'      => $totalCompras,
                'total_devoluciones' => $totalDevoluciones,
                'total_registros'    => count($registros),
            ],
        ]);
    }

    /**
     * Obtener productos del proveedor con estadísticas
     */
    private function getProductosProveedor($codprov, $fecha_desde, $fecha_hasta, $orden, $filtro = 'todos')
    {
        $comercial = session('comercialid');
        if (!$comercial) {
            session(['comercialid' => 1]);
            $comercial = 1;
        }

        $sucursales  = Sasucursal::where("fk_comercial", $comercial)->get();
        $sucursalarr = $sucursales->pluck('id');
        $sucursalIds = implode(",", $sucursalarr->toArray());

        $codigos_productos = Saitemcom::where('codprov', $codprov)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->distinct()
            ->pluck('coditem')
            ->toArray();

        if (empty($codigos_productos)) {
            return collect([]);
        }

        $query = Saprod::whereIn('codprod', $codigos_productos)
            ->where('comercial', $comercial)
            ->select('codprod', 'descrip', 'marca', 'preciod', 'costod', 'costod2', 'costod3', 'existen', 'codinst');

        if ($filtro == 'con_stock') {
            $query->where('existen', '>', 0);
        } elseif ($filtro == 'sin_stock') {
            $query->where('existen', '<=', 0);
        }

        $productos = $query->get();

        $serialesPorProducto = [];
        foreach ($productos as $productoModel) {
            $serialesPorProducto[$productoModel->codprod] = $this->getSerialesCompraPorProducto(
                $codprov,
                $productoModel->codprod,
                $sucursalIds
            );
        }

        $productosArray = [];

        foreach ($productos as $productoModel) {
            $producto = new \stdClass();
            $producto->codprod    = $productoModel->codprod;
            $producto->descrip    = $productoModel->descrip;
            $producto->marca      = $productoModel->marca;
            $producto->preciod    = $productoModel->preciod;
            $producto->costod     = $productoModel->costod;
            $producto->costod2    = $productoModel->costod2;
            $producto->costod3    = $productoModel->costod3;
            $producto->existen    = $productoModel->existen;
            $producto->codinst    = $productoModel->codinst;

            $costo              = (float) $producto->preciod;
            $precioVenta        = (float) $producto->costod3;
            $producto->ganancia = $precioVenta - $costo;
            $producto->margen   = $costo > 0 ? (($precioVenta - $costo) / $costo) * 100 : 0;

            // ========== TOTAL COMPRAS ==========
            $producto->total_compras = Saitemcom::where('coditem', $producto->codprod)
                ->whereRaw("fk_sucursal in ($sucursalIds)")
                ->where('codprov', $codprov)
                ->whereIn('tipocom', ['H', 'I'])
                ->whereBetween('fechae', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
                ->select(DB::raw('SUM(preciod * cantidad * signo) as total'))
                ->value('total') ?? 0;

            // ========== UNIDADES COMPRADAS ==========
            $producto->unidades_compradas = Saitemcom::where('coditem', $producto->codprod)
                ->where('codprov', $codprov)
                ->whereRaw("fk_sucursal in ($sucursalIds)")
                ->whereIn('tipocom', ['H', 'I'])
                ->whereBetween('fechae', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
                ->select(DB::raw('SUM(cantidad * signo) as total'))
                ->value('total') ?? 0;

            // ========== VENTAS ==========
            $infoSerial = $this->getProductoSerialInfo($producto->codprod, $comercial);

            if ($infoSerial['usa_serial']) {
                $serialesCompra = $serialesPorProducto[$producto->codprod] ?? collect([]);

                if ($serialesCompra->isNotEmpty()) {
                    $itemsVenta = $this->getItemsVentaPorSeriales(
                        $producto->codprod,
                        $serialesCompra,
                        $fecha_desde,
                        $fecha_hasta,
                        $sucursalIds
                    );

                    // SUM(signo): cuenta 1 por serial vendido, resta devoluciones
                    $producto->unidades_vendidas = $itemsVenta->sum(function ($item) {
                        return (int) $item->signo;
                    });

                    $producto->total_ventas = $itemsVenta->sum(function ($item) {
                        return $item->costodoriginal * $item->signo;
                    });

                    $ultimaVentaItem = $itemsVenta->where('signo', '>', 0)->sortByDesc('FechaE')->first();
                    if ($ultimaVentaItem) {
                        $producto->dias_sin_venta = Carbon::parse($ultimaVentaItem->FechaE)->diffInDays(now());
                    } else {
                        $producto->dias_sin_venta = null;
                    }
                } else {
                    $producto->unidades_vendidas = 0;
                    $producto->total_ventas      = 0;
                    $producto->dias_sin_venta    = null;
                }
            } else {
                $producto->unidades_vendidas = Saitemfac::where('CodItem', $producto->codprod)
                    ->whereRaw("fk_sucursal in ($sucursalIds)")
                    ->whereIn('TipoFac', ['A', 'B'])
                    ->where('esserv', 0)
                    ->whereBetween('FechaE', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
                    ->select(DB::raw('SUM(cantidad * signo) as total'))
                    ->value('total') ?? 0;

                $producto->total_ventas = Saitemfac::where('CodItem', $producto->codprod)
                    ->whereRaw("fk_sucursal in ($sucursalIds)")
                    ->whereIn('TipoFac', ['A', 'B'])
                    ->where('esserv', 0)
                    ->whereBetween('FechaE', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
                    ->select(DB::raw('SUM(costodoriginal * cantidad * signo) as total'))
                    ->value('total') ?? 0;

                $ultima_venta = Saitemfac::where('CodItem', $producto->codprod)
                    ->whereRaw("fk_sucursal in ($sucursalIds)")
                    ->whereIn('TipoFac', ['A'])
                    ->where('esserv', 0)
                    ->orderBy('FechaE', 'desc')
                    ->first();

                $producto->dias_sin_venta = $ultima_venta
                    ? Carbon::parse($ultima_venta->FechaE)->diffInDays(now())
                    : null;
            }

            // ========== EXISTENCIA ACTUAL ==========
            $producto->existencia_actual = Saexis::where('codprod', $producto->codprod)
                ->whereRaw("fk_sucursal in ($sucursalIds)")
                ->sum('existen');

            // ========== ÚLTIMA COMPRA ==========
            $ultima_compra = Saitemcom::where('coditem', $producto->codprod)
                ->where('codprov', $codprov)
                ->with('compra')
                ->whereIn('tipocom', ['H'])
                ->whereRaw("fk_sucursal in ($sucursalIds)")
                ->orderBy('fechae', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($ultima_compra) {
                $producto->ultima_compra_fecha     = $ultima_compra->fechae;
                $producto->ultima_compra_dias      = Carbon::parse($ultima_compra->fechae)->diffInDays(now());
                $producto->ultima_compra_documento = $ultima_compra->numerod;
                $producto->ultima_compra_id        = $ultima_compra->compra ? $ultima_compra->compra->id : null;
                $producto->ultima_compra_cantidad  = $ultima_compra->cantidad;
                $producto->ultima_compra_precio    = $ultima_compra->preciod;
            } else {
                $producto->ultima_compra_fecha     = null;
                $producto->ultima_compra_dias      = null;
                $producto->ultima_compra_documento = null;
                $producto->ultima_compra_id        = null;
                $producto->ultima_compra_cantidad  = null;
                $producto->ultima_compra_precio    = null;
            }

            // ========== EXISTENCIAS POR SUCURSAL ==========
            $producto->existencias_por_sucursal = [];
            foreach ($sucursales as $sucursal) {
                $existencia = Saexis::where('codprod', $producto->codprod)
                    ->where('fk_sucursal', $sucursal->id)
                    ->sum('existen');

                $producto->existencias_por_sucursal[$sucursal->id] = [
                    'nombre'     => $sucursal->descrip,
                    'existencia' => $existencia,
                ];
            }

            // ========== ROTACIÓN ==========
            $producto->rotacion = $producto->existencia_actual > 0
                ? $producto->total_ventas / $producto->existencia_actual
                : 0;

            // ========== DÍAS DE STOCK ==========
            $dias_periodo = Carbon::parse($fecha_desde . ' 00:00:00.00')->diffInDays(Carbon::parse($fecha_hasta . ' 23:59:59.00')) + 1;
            $venta_diaria_promedio = $dias_periodo > 0 ? $producto->unidades_vendidas / $dias_periodo : 0;
            $producto->dias_stock = $venta_diaria_promedio > 0
                ? $producto->existencia_actual / $venta_diaria_promedio
                : 0;

            // ========== MARGEN DE CONTRIBUCIÓN ==========
            $producto->margen = $producto->costod > 0
                ? (($producto->preciod - $producto->costod) / $producto->costod) * 100
                : 0;

            $productosArray[] = $producto;
        }

        $productos = collect($productosArray);

        switch ($orden) {
            case 'vendido_desc':      $productos = $productos->sortByDesc('total_ventas'); break;
            case 'vendido_asc':       $productos = $productos->sortBy('total_ventas'); break;
            case 'comprado_desc':     $productos = $productos->sortByDesc('total_compras'); break;
            case 'comprado_asc':      $productos = $productos->sortBy('total_compras'); break;
            case 'existencia_desc':   $productos = $productos->sortByDesc('existencia_actual'); break;
            case 'existencia_asc':    $productos = $productos->sortBy('existencia_actual'); break;
            case 'rotacion_desc':     $productos = $productos->sortByDesc('rotacion'); break;
            case 'rentabilidad_desc': $productos = $productos->sortByDesc('margen'); break;
            case 'unidades_desc':     $productos = $productos->sortByDesc('unidades_vendidas'); break;
            default:                  $productos = $productos->sortByDesc('total_ventas');
        }

        view()->share('sucursales', $sucursales);

        return $productos->values();
    }

    private function getProductoSerialInfo($codprod, $comercial)
    {
        $producto = Saprod::where('codprod', $codprod)
            ->where('comercial', $comercial)
            ->first();

        if (!$producto) {
            return ['usa_serial' => false, 'codinst' => null];
        }

        $instancia = Sainsta::where('codinst', $producto->codinst)
            ->where('comercial', $comercial)
            ->first();

        $usaSerial = $instancia && $instancia->desseri == 1;

        return [
            'usa_serial' => $usaSerial,
            'codinst'    => $producto->codinst
        ];
    }

    private function getSerialesCompraPorProducto($codprov, $codprod, $sucursalIds)
    {
        $seriales = DB::table('saitemcom as ic')
            ->join('saseprcom as sc', function($join) {
                $join->on('ic.tipocom', '=', 'sc.tipocom')
                    ->on('ic.numerod' , '=', 'sc.numerod')
                    ->on('ic.coditem' , '=', 'sc.coditem');
            })
            ->where('ic.codprov', $codprov)
            ->where('ic.coditem', $codprod)
            ->whereIn('ic.tipocom', ['H'])
            ->whereRaw("ic.fk_sucursal in ($sucursalIds)")
            ->whereNotNull('sc.nroserial')
            ->select('sc.nroserial')
            ->distinct()
            ->pluck('sc.nroserial');

        return $seriales;
    }

    private function getItemsVentaPorSeriales($codprod, $seriales, $fecha_desde, $fecha_hasta, $sucursalIds)
    {
        if ($seriales->isEmpty()) {
            return collect([]);
        }

        return DB::table('saitemfac as ifac')
            ->join('saseprfac as sf', function ($join) {
                $join->on('ifac.TipoFac', '=', 'sf.TipoFac')
                    ->on('ifac.NumeroD', '=', 'sf.NumeroD')
                    ->on('ifac.NroLinea', '=', 'sf.NroLinea')
                    ->on('ifac.CodItem', '=', 'sf.CodItem');
            })
            ->whereIn('sf.NroSerial', $seriales)
            ->where('ifac.CodItem', $codprod)
            ->whereIn('ifac.TipoFac', ['A', 'B'])
            ->where('ifac.esserv', 0)
            ->whereBetween('ifac.FechaE', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
            ->whereRaw("ifac.fk_sucursal in ($sucursalIds)")
            ->select(
                'ifac.id',
                'sf.NroSerial',
                DB::raw('1 AS cantidad'),       // 1 unidad por serial (fix JOIN inflado)
                'ifac.costodoriginal',
                'ifac.signo',
                'ifac.FechaE'
            )
            ->get();
    }

    /**
     * Calcular KPI generales del proveedor
     */
    private function calcularKPIProveedor($codprov, $fecha_desde, $fecha_hasta)
    {
        $serialesCompra = [];
        $comercial = session('comercialid');
        if (!$comercial) {
            session(['comercialid' => 1]);
            $comercial = 1;
        }

        $sucursales  = Sasucursal::where("fk_comercial", $comercial)->get();
        $sucursalarr = $sucursales->pluck('id');
        $sucursalIds = implode(",", $sucursalarr->toArray());

        $codigos_productos = Saitemcom::where('codprov', $codprov)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->distinct()
            ->pluck('coditem')
            ->toArray();

        if (empty($codigos_productos)) {
            return [
                'total_productos'  => 0,
                'total_compras'    => 0,
                'total_ventas'     => 0,
                'valor_inventario' => 0,
                'existencia_total' => 0,
                'rotacion'         => 0,
                'compras_30dias'   => 0,
                'ventas_30dias'    => 0,
            ];
        }

        $total_productos = count($codigos_productos);

        $total_compras = Saitemcom::where('codprov', $codprov)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->whereBetween('fechae', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
            ->select(DB::raw('SUM(preciod * cantidad * signo) as total'))
            ->value('total') ?? 0;

        // ========== VENTAS DEL PERÍODO ==========
        $total_ventas = 0;
        foreach ($codigos_productos as $codprod) {
            $infoSerial = $this->getProductoSerialInfo($codprod, $comercial);

            if ($infoSerial['usa_serial']) {
                if (!isset($serialesCompra[$codprod]))
                    $serialesCompra[$codprod] = $this->getSerialesCompraPorProducto($codprov, $codprod, $sucursalIds);

                if ($serialesCompra[$codprod]->isNotEmpty()) {
                    $itemsVenta = $this->getItemsVentaPorSeriales(
                        $codprod,
                        $serialesCompra[$codprod],
                        $fecha_desde,
                        $fecha_hasta,
                        $sucursalIds
                    );

                    $total_ventas += $itemsVenta->sum(function ($item) {
                        return $item->costodoriginal * $item->signo;
                    });
                }
            } else {
                $ventasProducto = Saitemfac::where('CodItem', $codprod)
                    ->whereRaw("fk_sucursal in ($sucursalIds)")
                    ->whereIn('TipoFac', ['A', 'B'])
                    ->where('esserv', 0)
                    ->whereBetween('FechaE', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
                    ->select(DB::raw('SUM(costodoriginal * cantidad * signo) as total'))
                    ->value('total') ?? 0;

                $total_ventas += $ventasProducto;
            }
        }

        $valor_inventario = Saprod::whereIn('codprod', $codigos_productos)
            ->where('comercial', $comercial)
            ->select(DB::raw('SUM(existen * preciodpro) as total'))
            ->value('total') ?? 0;

        $existencia_total = Saprod::whereIn('codprod', $codigos_productos)
            ->where('comercial', $comercial)
            ->sum('existen');

        $dias_periodo = Carbon::parse($fecha_desde . ' 00:00:00.00')->diffInDays(Carbon::parse($fecha_hasta . ' 23:59:59.00')) + 1;
        $venta_diaria_promedio = $dias_periodo > 0 ? $total_ventas / $dias_periodo : 0;
        $rotacion = $venta_diaria_promedio > 0 && $existencia_total > 0
            ? $existencia_total / $venta_diaria_promedio
            : 0;

        // ========== COMPRAS Y VENTAS 30 DÍAS ==========
        $fecha_30dias = now()->subDays(30)->format('Y-m-d');
        $hoy          = now()->format('Y-m-d');

        $compras_30dias = Saitemcom::where('codprov', $codprov)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->whereBetween('fechae', [$fecha_30dias, $hoy])
            ->select(DB::raw('SUM(cantidad * preciod * signo) as total'))
            ->value('total') ?? 0;

        $ventas_30dias = 0;
        foreach ($codigos_productos as $codprod) {
            $infoSerial = $this->getProductoSerialInfo($codprod, $comercial);

            if ($infoSerial['usa_serial']) {
                if (!isset($serialesCompra[$codprod]))
                    $serialesCompra[$codprod] = $this->getSerialesCompraPorProducto($codprov, $codprod, $sucursalIds);

                if ($serialesCompra[$codprod]->isNotEmpty()) {
                    $itemsVenta30 = $this->getItemsVentaPorSeriales(
                        $codprod,
                        $serialesCompra[$codprod],
                        $fecha_30dias,
                        $hoy,
                        $sucursalIds
                    );

                    $ventas_30dias += $itemsVenta30->sum(function ($item) {
                        return $item->costodoriginal * $item->signo;
                    });
                }
            } else {
                $ventasProducto = Saitemfac::where('CodItem', $codprod)
                    ->whereRaw("fk_sucursal in ($sucursalIds)")
                    ->whereIn('TipoFac', ['A', 'B'])
                    ->where('esserv', 0)
                    ->whereBetween('FechaE', [$fecha_30dias, $hoy])
                    ->select(DB::raw('SUM(costodoriginal * cantidad * signo) as total'))
                    ->value('total') ?? 0;

                $ventas_30dias += $ventasProducto;
            }
        }

        return [
            'total_productos'  => $total_productos,
            'total_compras'    => $total_compras,
            'total_ventas'     => $total_ventas,
            'valor_inventario' => $valor_inventario,
            'existencia_total' => $existencia_total,
            'rotacion'         => $rotacion,
            'compras_30dias'   => $compras_30dias,
            'ventas_30dias'    => $ventas_30dias,
        ];
    }

    /**
     * Obtener compras diarias de un producto en un rango de fechas
     * + desglose por sucursal
     */
    public function comprasDiariasProducto(Request $request)
    {
        $request->validate([
            'codprod'    => 'required|string',
            'codprov'    => 'required|string',
            'fecha_desde'=> 'required|date',
            'fecha_hasta'=> 'required|date',
        ]);

        $codprod     = $request->codprod;
        $codprov     = $request->codprov;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $comercial = session('comercialid') ?: 1;

        $sucursales = Sasucursal::where('fk_comercial', $comercial)->get();
        $sucursalIds = implode(',', $sucursales->pluck('id')->toArray());

        $sucursalesMap = $sucursales->pluck('descrip', 'id')->toArray();

        // ----- Por día -----
        $comprasPorDia = Saitemcom::where('coditem', $codprod)
            ->where('codprov', $codprov)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->whereBetween('fechae', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:58.00'])
            ->select(
                DB::raw("DATE(fechae) as fecha"),
                DB::raw("SUM(cantidad * signo) as unidades")
            )
            ->groupBy(DB::raw("DATE(fechae)"))
            ->orderBy('fecha')
            ->get()
            ->keyBy('fecha');

        // ----- Por sucursal -----
        $comprasPorSuc = Saitemcom::where('coditem', $codprod)
            ->where('codprov', $codprov)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->whereBetween('fechae', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:58.00'])
            ->select(
                'fk_sucursal',
                DB::raw("SUM(cantidad * signo) as unidades")
            )
            ->groupBy('fk_sucursal')
            ->orderByDesc('unidades')
            ->get();

        // ============================================================
        // Armado de días (rellenando sin compras)
        // ============================================================
        $dias = [];
        $inicio = Carbon::parse($fecha_desde);
        $fin    = Carbon::parse($fecha_hasta);
        $total  = 0;

        for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $unidades = isset($comprasPorDia[$key]) ? (float) $comprasPorDia[$key]->unidades : 0;

            $dias[] = [
                'fecha'      => $key,
                'fecha_fmt'  => $d->format('d/m/Y'),
                'dia_semana' => $d->locale('es')->isoFormat('ddd'),
                'unidades'   => $unidades,
            ];

            $total += $unidades;
        }

        // ============================================================
        // Armado por sucursal
        // ============================================================
        $sucursalesData = [];
        foreach ($comprasPorSuc as $item) {
            $sucId     = $item->fk_sucursal;
            $nombreRaw = $sucursalesMap[$sucId] ?? ('Sucursal ' . $sucId);
            $nombre    = str_replace('SARA', '', $nombreRaw);

            $sucursalesData[] = [
                'id'        => $sucId,
                'nombre'    => trim($nombre),
                'unidades'  => (float) $item->unidades,
            ];
        }

        // Producto
        $producto = Saprod::where('codprod', $codprod)
            ->where('comercial', $comercial)
            ->select('codprod', 'descrip', 'marca')
            ->first();

        $mejorDia = collect($dias)->sortByDesc('unidades')->first();
        $mejorSucursal = count($sucursalesData) > 0
            ? collect($sucursalesData)->sortByDesc('unidades')->first()
            : null;

        return response()->json([
            'success'  => true,
            'producto' => $producto,
            'dias'     => $dias,
            'sucursales' => $sucursalesData,
            'resumen'  => [
                'total_unidades'  => $total,
                'promedio_diario' => count($dias) > 0 ? round($total / count($dias), 1) : 0,
                'mejor_dia'       => $mejorDia && $mejorDia['unidades'] > 0 ? $mejorDia : null,
                'mejor_sucursal'  => $mejorSucursal,
                'dias_con_compra' => collect($dias)->where('unidades', '>', 0)->count(),
                'dias_totales'    => count($dias),
            ],
        ]);
    }

    /**
     * Obtener ventas diarias de un producto en un rango de fechas
     * + desglose por sucursal
     */
    public function ventasDiariasProducto(Request $request)
    {
        $request->validate([
            'codprod'    => 'required|string',
            'codprov'    => 'required|string',
            'fecha_desde'=> 'required|date',
            'fecha_hasta'=> 'required|date',
        ]);

        $codprod     = $request->codprod;
        $codprov     = $request->codprov;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $comercial = session('comercialid') ?: 1;

        $sucursales = Sasucursal::where('fk_comercial', $comercial)->get();
        $sucursalIds = implode(',', $sucursales->pluck('id')->toArray());

        $sucursalesMap = $sucursales->pluck('descrip', 'id')->toArray();

        $infoSerial = $this->getProductoSerialInfo($codprod, $comercial);

        $ventasPorDia = collect();
        $ventasPorSuc = collect();

        if ($infoSerial['usa_serial']) {
            $serialesCompra = $this->getSerialesCompraPorProducto($codprov, $codprod, $sucursalIds);

            if ($serialesCompra->isNotEmpty()) {

                // ----- Por día -----
                // FIX: SUM(signo) en vez de SUM(cantidad * signo)
                $ventasPorDia = DB::table('saitemfac as ifac')
                    ->join('saseprfac as sf', function ($join) {
                        $join->on('ifac.TipoFac', '=', 'sf.TipoFac')
                            ->on('ifac.NumeroD', '=', 'sf.NumeroD')
                            ->on('ifac.NroLinea', '=', 'sf.NroLinea')
                            ->on('ifac.CodItem', '=', 'sf.CodItem');
                    })
                    ->whereIn('sf.NroSerial', $serialesCompra)
                    ->where('ifac.CodItem', $codprod)
                    ->whereIn('ifac.TipoFac', ['A', 'B'])
                    ->where('ifac.esserv', 0)
                    ->whereRaw("ifac.fk_sucursal in ($sucursalIds)")
                    ->whereBetween('ifac.FechaE', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59'])
                    ->select(
                        DB::raw("DATE(ifac.FechaE) as fecha"),
                        DB::raw("SUM(ifac.signo) as unidades")
                    )
                    ->groupBy(DB::raw("DATE(ifac.FechaE)"))
                    ->orderBy('fecha')
                    ->get()
                    ->keyBy('fecha');

                // ----- Por sucursal -----
                $ventasPorSuc = DB::table('saitemfac as ifac')
                    ->join('saseprfac as sf', function ($join) {
                        $join->on('ifac.TipoFac', '=', 'sf.TipoFac')
                            ->on('ifac.NumeroD', '=', 'sf.NumeroD')
                            ->on('ifac.NroLinea', '=', 'sf.NroLinea')
                            ->on('ifac.CodItem', '=', 'sf.CodItem');
                    })
                    ->whereIn('sf.NroSerial', $serialesCompra)
                    ->where('ifac.CodItem', $codprod)
                    ->whereIn('ifac.TipoFac', ['A', 'B'])
                    ->where('ifac.esserv', 0)
                    ->whereRaw("ifac.fk_sucursal in ($sucursalIds)")
                    ->whereBetween('ifac.FechaE', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59'])
                    ->select(
                        'ifac.fk_sucursal',
                        DB::raw("SUM(ifac.signo) as unidades")
                    )
                    ->groupBy('ifac.fk_sucursal')
                    ->orderByDesc('unidades')
                    ->get();
            }
        } else {
            // ----- Por día -----
            $ventasPorDia = Saitemfac::where('CodItem', $codprod)
                ->whereRaw("fk_sucursal in ($sucursalIds)")
                ->whereIn('TipoFac', ['A', 'B'])
                ->where('esserv', 0)
                ->whereBetween('FechaE', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59'])
                ->select(
                    DB::raw("DATE(FechaE) as fecha"),
                    DB::raw("SUM(cantidad * signo) as unidades")
                )
                ->groupBy(DB::raw("DATE(FechaE)"))
                ->orderBy('fecha')
                ->get()
                ->keyBy('fecha');

            // ----- Por sucursal -----
            $ventasPorSuc = Saitemfac::where('CodItem', $codprod)
                ->whereRaw("fk_sucursal in ($sucursalIds)")
                ->whereIn('TipoFac', ['A', 'B'])
                ->where('esserv', 0)
                ->whereBetween('FechaE', [$fecha_desde . ' 00:00:00', $fecha_hasta . ' 23:59:59'])
                ->select(
                    'fk_sucursal',
                    DB::raw("SUM(cantidad * signo) as unidades")
                )
                ->groupBy('fk_sucursal')
                ->orderByDesc('unidades')
                ->get();
        }

        // ============================================================
        // Armado del array de días
        // ============================================================
        $dias = [];
        $inicio = Carbon::parse($fecha_desde);
        $fin    = Carbon::parse($fecha_hasta);
        $total  = 0;

        for ($d = $inicio->copy(); $d->lte($fin); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $unidades = isset($ventasPorDia[$key]) ? (float) $ventasPorDia[$key]->unidades : 0;

            $dias[] = [
                'fecha'      => $key,
                'fecha_fmt'  => $d->format('d/m/Y'),
                'dia_semana' => $d->locale('es')->isoFormat('ddd'),
                'unidades'   => $unidades,
            ];

            $total += $unidades;
        }

        // ============================================================
        // Armado por sucursal
        // ============================================================
        $sucursalesData = [];
        foreach ($ventasPorSuc as $item) {
            $sucId     = $item->fk_sucursal;
            $nombreRaw = $sucursalesMap[$sucId] ?? ('Sucursal ' . $sucId);
            $nombre    = str_replace('SARA', '', $nombreRaw);

            $sucursalesData[] = [
                'id'        => $sucId,
                'nombre'    => trim($nombre),
                'unidades'  => (float) $item->unidades,
            ];
        }

        $producto = Saprod::where('codprod', $codprod)
            ->where('comercial', $comercial)
            ->select('codprod', 'descrip', 'marca')
            ->first();

        $mejorDia = collect($dias)->sortByDesc('unidades')->first();
        $mejorSucursal = count($sucursalesData) > 0
            ? collect($sucursalesData)->sortByDesc('unidades')->first()
            : null;

        return response()->json([
            'success'  => true,
            'producto' => $producto,
            'dias'     => $dias,
            'sucursales' => $sucursalesData,
            'resumen'  => [
                'total_unidades'  => $total,
                'promedio_diario' => count($dias) > 0 ? round($total / count($dias), 1) : 0,
                'mejor_dia'       => $mejorDia && $mejorDia['unidades'] > 0 ? $mejorDia : null,
                'mejor_sucursal'  => $mejorSucursal,
                'dias_con_venta'  => collect($dias)->where('unidades', '>', 0)->count(),
                'dias_totales'    => count($dias),
            ],
        ]);
    }

    /**
     * Obtener top productos más vendidos
     */
    private function getTopProductos($codprov, $fecha_desde, $fecha_hasta, $limite = 10)
    {
        $comercial = session('comercialid');
        if (!$comercial) {
            session(['comercialid' => 1]);
            $comercial = 1;
        }

        $sucursales  = Sasucursal::where("fk_comercial", $comercial)->get();
        $sucursalarr = $sucursales->pluck('id');
        $sucursalIds = implode(",", $sucursalarr->toArray());

        $codigos_productos = Saitemcom::where('codprov', $codprov)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->distinct()
            ->pluck('coditem')
            ->toArray();

        if (empty($codigos_productos)) {
            return collect([]);
        }

        $productosModels = Saprod::whereIn('codprod', $codigos_productos)
            ->where('comercial', $comercial)
            ->select('codprod', 'descrip', 'preciod', 'costod', 'existen', 'codinst')
            ->get();

        $serialesPorProducto = [];
        foreach ($productosModels as $productoModel) {
            $serialesPorProducto[$productoModel->codprod] = $this->getSerialesCompraPorProducto(
                $codprov,
                $productoModel->codprod,
                $sucursalIds
            );
        }

        $productosArray = [];

        foreach ($productosModels as $productoModel) {
            $producto = new \stdClass();
            $producto->codprod    = $productoModel->codprod;
            $producto->descrip    = $productoModel->descrip;
            $producto->preciod    = $productoModel->preciod;
            $producto->costod     = $productoModel->costod;
            $producto->existen    = $productoModel->existen;
            $producto->codinst    = $productoModel->codinst;

            $infoSerial = $this->getProductoSerialInfo($producto->codprod, $comercial);

            if ($infoSerial['usa_serial']) {
                $serialesCompra = $serialesPorProducto[$producto->codprod] ?? collect([]);

                if ($serialesCompra->isNotEmpty()) {
                    $itemsVenta = $this->getItemsVentaPorSeriales(
                        $producto->codprod,
                        $serialesCompra,
                        $fecha_desde,
                        $fecha_hasta,
                        $sucursalIds
                    );

                    // SUM(signo): 1 por serial vendido, resta devoluciones
                    $producto->unidades_vendidas = $itemsVenta->sum(function ($item) {
                        return (int) $item->signo;
                    });

                    $producto->monto_ventas = $itemsVenta->sum(function ($item) {
                        return $item->costodoriginal * $item->signo;
                    });
                } else {
                    $producto->unidades_vendidas = 0;
                    $producto->monto_ventas      = 0;
                }
            } else {
                $producto->unidades_vendidas = Saitemfac::where('CodItem', $producto->codprod)
                    ->whereRaw("fk_sucursal in ($sucursalIds)")
                    ->whereIn('TipoFac', ['A', 'B'])
                    ->where('esserv', 0)
                    ->whereBetween('FechaE', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
                    ->select(DB::raw('SUM(cantidad * signo) as cantidad'))
                    ->value('cantidad') ?? 0;

                $producto->monto_ventas = Saitemfac::where('CodItem', $producto->codprod)
                    ->whereRaw("fk_sucursal in ($sucursalIds)")
                    ->whereIn('TipoFac', ['A', 'B'])
                    ->where('esserv', 0)
                    ->whereBetween('FechaE', [$fecha_desde . ' 00:00:00.00', $fecha_hasta . ' 23:59:59.00'])
                    ->select(DB::raw('SUM(costodoriginal * signo) as total'))
                    ->value('total') ?? 0;
            }

            $producto->rotacion = $producto->existen > 0 ? $producto->unidades_vendidas / $producto->existen : 0;

            $dias_periodo = Carbon::parse($fecha_desde . ' 00:00:00.00')->diffInDays(Carbon::parse($fecha_hasta . ' 23:59:59.00')) + 1;
            $venta_diaria = $dias_periodo > 0 ? $producto->unidades_vendidas / $dias_periodo : 0;
            $producto->dias_stock = $venta_diaria > 0 ? $producto->existen / $venta_diaria : 0;

            $producto->margen = $producto->costod > 0
                ? (($producto->preciod - $producto->costod) / $producto->costod) * 100
                : 0;

            $productosArray[] = $producto;
        }

        $productos = collect($productosArray);

        return $productos->where('unidades_vendidas', '>', 0)
            ->sortByDesc('unidades_vendidas')
            ->take($limite)
            ->values();
    }

    /**
     * Obtener productos con stock bajo
     */
    private function getStockBajo($codprov, $limite = 10)
    {

        $comercial  = session('comercialid') ;
        if(!$comercial) {
            session(['comercialid' => 1]);
            $comercial = 1;
        }

        $sucursales  = Sasucursal::where("fk_comercial", $comercial)->get();
        $sucursalarr = $sucursales->pluck('id');
        $sucursalIds = implode(",", $sucursalarr->toArray());

        // Obtener todos los códigos de productos que ha comprado este proveedor
        $codigos_productos = Saitemcom::where('codprov', $codprov)->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->distinct()
            ->pluck('coditem')
            ->toArray();

        if (empty($codigos_productos)) {
            return collect([]);
        }

        return Saprod::whereIn('codprod', $codigos_productos)->where('comercial',$comercial)
            ->select('codprod', 'descrip', 'existen')
            ->where('existen', '<', 10) // Stock menor a 10 unidades
            ->orderBy('existen', 'asc')
            ->limit($limite)
            ->get();
    }

    /**
     * Obtener productos más rentables
     */
    private function getProductosRentables($codprov, $limite = 10)
    {

        $comercial  = session('comercialid') ;
        if(!$comercial) {
            session(['comercialid' => 1]);
            $comercial = 1;
        }

        $sucursales  = Sasucursal::where("fk_comercial", $comercial)->get();
        $sucursalarr = $sucursales->pluck('id');
        $sucursalIds = implode(",", $sucursalarr->toArray());

        // Obtener todos los códigos de productos que ha comprado este proveedor
        $codigos_productos = Saitemcom::where('codprov', $codprov)->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('tipocom', ['H', 'I'])
            ->distinct()
            ->pluck('coditem')
            ->toArray();

        if (empty($codigos_productos)) {
            return collect([]);
        }

        return Saprod::whereIn('codprod', $codigos_productos)->where('comercial',$comercial)
            ->select(
                'codprod',
                'descrip',
                'costod as costo',
                'preciod as precio',
                DB::raw('(preciod - costod) as ganancia'),
                DB::raw('((preciod - costod) / costod) * 100 as margen')
            )
            ->where('costod', '>', 0)
            ->where('preciod', '>', 0)
            ->orderBy('margen', 'desc')
            ->limit($limite)
            ->get();
    }

    /**
     * Análisis gerencial GLOBAL de Cuentas por Pagar (todos los proveedores)
     * Devuelve: ranking de proveedores, priorización de facturas, KPIs, alertas, proyección.
     */
    public function analisisGerencialGlobalCxp(Request $request)
    {
        $soloVencidos = $request->boolean('solovencidos', false);
        $fechaCorte   = $request->filled('fechavence')
            ? Carbon::createFromFormat('d/m/Y', $request->fechavence)->format('Y-m-d')
            : null;

        $comercial   = session('comercialid') ?: 1;
        $sucursalIds = Sasucursal::where('fk_comercial', $comercial)->pluck('id')->toArray();
        $sucursalStr = implode(',', $sucursalIds);
        $hoy         = Carbon::now()->startOfDay();

        // ==========================================
        // 1. TODAS LAS FACTURAS PENDIENTES (globales)
        // ==========================================
        $qFacturas = Cwcxcprv::where('signo', 1)
            ->whereRaw('(monto - abonado) > 0')
            ->select(
                'id', 'codprov', 'numerod', 'concepto', 'monto', 'abonado',
                DB::raw('(monto - abonado) as deuda'),
                'fecha', 'fechav'
            );

        if ($soloVencidos) {
            $qFacturas->whereDate('fechav', '<', $hoy);
        }
        if ($fechaCorte) {
            $qFacturas->whereDate('fechav', '>=', $fechaCorte);
        }

        $facturas = $qFacturas->get();

        if ($facturas->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => $this->estructuraGlobalVacia(),
            ]);
        }

        $codprovs = $facturas->pluck('codprov')->unique()->values()->toArray();

        // ==========================================
        // 2. DATOS DE CONTEXTO DE LOS PROVEEDORES
        // ==========================================
        $proveedoresInfo = Saprov::whereIn('codprov', $codprovs)
            ->select('codprov', 'descrip', 'id3')
            ->get()
            ->keyBy('codprov');

        // Historial de compras últimos 12 meses (criticidad)
        $hace12m = now()->subMonths(12)->format('Y-m-d');
        $historiales = Saitemcom::whereIn('codprov', $codprovs)
            ->whereRaw("fk_sucursal in ($sucursalStr)")
            ->whereIn('tipocom', ['H', 'I'])
            ->where('fechae', '>=', $hace12m)
            ->select(
                'codprov',
                DB::raw('COUNT(DISTINCT numerod) as total_facturas'),
                DB::raw('SUM(cantidad * preciod * signo) as monto_total'),
                DB::raw('MAX(fechae) as ultima_compra')
            )
            ->groupBy('codprov')
            ->get()
            ->keyBy('codprov');

        // ==========================================
        // 3. AGREGACIÓN POR PROVEEDOR
        // ==========================================
        $porProveedor = [];
        $totalDeudaGlobal = $facturas->sum('deuda');
        $totalFacturasGlobal = $facturas->count();

        foreach ($facturas->groupBy('codprov') as $codprov => $facts) {
            $info = $proveedoresInfo->get($codprov);
            $hist = $historiales->get($codprov);

            $deudaProv       = $facts->sum('deuda');
            $montoProv       = $facts->sum('monto');
            $abonadoProv     = $facts->sum('abonado');
            $numFacturas     = $facts->count();

            // Facturas vencidas
            $facturasProv    = $facts->map(function ($f) use ($hoy) {
                $fechaVence   = Carbon::parse($f->fechav)->startOfDay();
                $diasVencidos = $fechaVence->lt($hoy) ? $fechaVence->diffInDays($hoy) : 0;
                $diasPorVencer = $fechaVence->gte($hoy) ? $hoy->diffInDays($fechaVence) : 0;
                return [
                    'id'              => $f->id,
                    'numerod'         => $f->numerod,
                    'concepto'        => $f->concepto,
                    'monto'           => (float) $f->monto,
                    'abonado'         => (float) $f->abonado,
                    'deuda'           => (float) $f->deuda,
                    'fecha_fmt'       => Carbon::parse($f->fecha)->format('d/m/Y'),
                    'fechav_fmt'      => $fechaVence->format('d/m/Y'),
                    'fechav'          => $f->fechav,
                    'dias_vencidos'   => $diasVencidos,
                    'dias_por_vencer' => $diasPorVencer,
                ];
            })->sortByDesc('dias_vencidos')->values();

            $vencidoProv     = $facturasProv->where('dias_vencidos', '>', 0)->sum('deuda');
            $facturasVencidas = $facturasProv->where('dias_vencidos', '>', 0)->count();
            $diasVencidoMax  = $facturasProv->max('dias_vencidos') ?? 0;
            $edadPromedio    = $facturasProv->where('dias_vencidos', '>', 0)->avg('dias_vencidos') ?? 0;

            // Porcentaje que representa este proveedor del total global
            $porcentajeGlobal = $totalDeudaGlobal > 0
                ? round(($deudaProv / $totalDeudaGlobal) * 100, 1)
                : 0;

            $facturas12m     = (int)   ($hist->total_facturas ?? 0);
            $monto12m        = (float) ($hist->monto_total ?? 0);
            $ultimaCompra    = $hist->ultima_compra ?? null;
            $diasUltimaCompra = $ultimaCompra
                ? Carbon::parse($ultimaCompra)->diffInDays($hoy)
                : null;

            // ==========================================
            // SCORE GERENCIAL DEL PROVEEDOR (0-100)
            // ==========================================
            // a) Antigüedad máxima vencida       → 35 pts
            // b) % de deuda vencida sobre su total → 25 pts
            // c) Concentración global (peso)     → 20 pts
            // d) Criticidad (compras 12m)        → 10 pts
            // e) Penalización por inactividad    → 10 pts
            $score = 0;
            $score += min(35, $diasVencidoMax);                                       // a
            $score += $deudaProv > 0
                ? min(25, ($vencidoProv / $deudaProv) * 25)
                : 0;                                                                  // b
            $score += min(20, $porcentajeGlobal * 2);                                 // c
            if ($facturas12m >= 10)     $score += 10;
            elseif ($facturas12m >= 3)  $score += 5;
            if ($diasUltimaCompra !== null && $diasUltimaCompra > 90) $score += 10;    // e

            $score = round(min(100, $score), 1);

            // Categoría
            if ($diasVencidoMax >= 30 || $score >= 75)      $categoria = 'critica';
            elseif ($diasVencidoMax > 0 || $score >= 50)    $categoria = 'alta';
            elseif ($score >= 30)                            $categoria = 'media';
            else                                             $categoria = 'baja';

            $porProveedor[] = [
                'codprov'          => $codprov,
                'empresa'          => $info->descrip ?? $codprov,
                'id3'              => $info->id3 ?? null,
                'deuda'            => round($deudaProv, 2),
                'monto'            => round($montoProv, 2),
                'abonado'          => round($abonadoProv, 2),
                'num_facturas'     => $numFacturas,
                'num_vencidas'     => $facturasVencidas,
                'deuda_vencida'    => round($vencidoProv, 2),
                'porcentaje_global'=> $porcentajeGlobal,
                'porcentaje_vencido'=> $deudaProv > 0 ? round(($vencidoProv / $deudaProv) * 100, 1) : 0,
                'dias_vencido_max' => $diasVencidoMax,
                'edad_promedio'    => round($edadPromedio, 0),
                'facturas_12m'     => $facturas12m,
                'monto_12m'        => round($monto12m, 2),
                'dias_ultima_compra' => $diasUltimaCompra,
                'score'            => $score,
                'categoria'        => $categoria,
                'facturas'         => $facturasProv,
            ];
        }

        // Ordenar proveedores por score descendente
        usort($porProveedor, fn($a, $b) => $b['score'] <=> $a['score']);

        // Ranking
        foreach ($porProveedor as $i => &$p) {
            $p['ranking'] = $i + 1;
        }
        unset($p);

        // ==========================================
        // 4. KPIs GLOBALES
        // ==========================================
        $totalVencido     = $facturas->filter(fn($f) => Carbon::parse($f->fechav)->lt($hoy))->sum('deuda');
        $totalPorVencer7d = $facturas->filter(function ($f) use ($hoy) {
            $v = Carbon::parse($f->fechav)->startOfDay();
            return $v->gte($hoy) && $hoy->diffInDays($v) <= 7;
        })->sum('deuda');

        $kpis = [
            'total_deuda'            => round($totalDeudaGlobal, 2),
            'total_vencido'          => round($totalVencido, 2),
            'total_por_vencer_7d'    => round($totalPorVencer7d, 2),
            'total_proveedores'      => count($porProveedor),
            'total_facturas'         => $totalFacturasGlobal,
            'proveedores_criticos'   => collect($porProveedor)->where('categoria', 'critica')->count(),
            'porcentaje_vencido'     => $totalDeudaGlobal > 0
                ? round(($totalVencido / $totalDeudaGlobal) * 100, 1) : 0,
            'proveedor_mas_critico'  => $porProveedor[0]['empresa'] ?? null,
            'deuda_top5'             => round(collect($porProveedor)->take(5)->sum('deuda'), 2),
            'porcentaje_top5'        => $totalDeudaGlobal > 0
                ? round((collect($porProveedor)->take(5)->sum('deuda') / $totalDeudaGlobal) * 100, 1) : 0,
        ];

        // ==========================================
        // 5. ALERTAS GLOBALES
        // ==========================================
        $alertas = [];

        if ($totalVencido > 0) {
            $alertas[] = [
                'tipo'    => 'danger',
                'icono'   => 'ri-alarm-warning-line',
                'titulo'  => 'Deuda vencida global',
                'mensaje' => '$' . number_format($totalVencido, 2) . ' vencidos con '
                    . collect($porProveedor)->sum('num_vencidas') . ' factura(s) en '
                    . collect($porProveedor)->where('num_vencidas', '>', 0)->count() . ' proveedor(es).',
            ];
        }

        if ($kpis['porcentaje_vencido'] >= 40) {
            $alertas[] = [
                'tipo'    => 'danger',
                'icono'   => 'ri-error-warning-line',
                'titulo'  => 'Concentración crítica',
                'mensaje' => 'El ' . $kpis['porcentaje_vencido'] . '% del pasivo total está vencido. Priorizar cobranza/pagos urgente.',
            ];
        }

        if ($kpis['porcentaje_top5'] >= 60) {
            $alertas[] = [
                'tipo'    => 'warning',
                'icono'   => 'ri-pie-chart-line',
                'titulo'  => 'Riesgo de concentración',
                'mensaje' => 'Los 5 proveedores principales concentran el ' . $kpis['porcentaje_top5'] . '% del pasivo.',
            ];
        }

        $inactivosConDeuda = collect($porProveedor)->filter(
            fn($p) => $p['dias_ultima_compra'] !== null && $p['dias_ultima_compra'] > 90 && $p['deuda'] > 0
        )->count();

        if ($inactivosConDeuda > 0) {
            $alertas[] = [
                'tipo'    => 'warning',
                'icono'   => 'ri-pause-circle-line',
                'titulo'  => 'Proveedores inactivos con deuda',
                'mensaje' => $inactivosConDeuda . ' proveedor(es) sin compras hace +90 días pero con saldo pendiente.',
            ];
        }

        if ($totalPorVencer7d > 0) {
            $alertas[] = [
                'tipo'    => 'info',
                'icono'   => 'ri-calendar-event-line',
                'titulo'  => 'Vencimientos próximos',
                'mensaje' => '$' . number_format($totalPorVencer7d, 2) . ' vencen esta semana.',
            ];
        }

        if (!empty($porProveedor)) {
            $top = $porProveedor[0];
            $alertas[] = [
                'tipo'    => 'warning',
                'icono'   => 'ri-fire-line',
                'titulo'  => 'Prioridad #1',
                'mensaje' => $top['empresa'] . ' — $' . number_format($top['deuda'], 2)
                    . ' (' . $top['dias_vencido_max'] . ' días vencido, score ' . $top['score'] . ').',
            ];
        }

        // ==========================================
        // 6. PROYECCIÓN GLOBAL 30 DÍAS
        // ==========================================
        $proyeccion = [];
        for ($i = 0; $i <= 30; $i++) {
            $fecha = Carbon::now()->addDays($i)->startOfDay();
            $montoDia = $facturas->filter(function ($f) use ($fecha) {
                return Carbon::parse($f->fechav)->startOfDay()->equalTo($fecha);
            })->sum('deuda');

            $proyeccion[] = [
                'fecha' => $fecha->format('Y-m-d'),
                'label' => $fecha->format('d/m'),
                'monto' => round($montoDia, 2),
            ];
        }

        // ==========================================
        // 7. TOP 20 FACTURAS GLOBALES (para pago inmediato)
        // ==========================================
        $todasFacturas = collect();
        foreach ($porProveedor as $p) {
            foreach ($p['facturas'] as $f) {
                $f['codprov'] = $p['codprov'];
                $f['empresa'] = $p['empresa'];
                // Score individual de factura
                $scoreF = min(100, (
                    min(40, $f['dias_vencidos']) +
                    ($f['dias_vencidos'] == 0 ? max(0, 20 - $f['dias_por_vencer']) : 0) +
                    min(25, ($f['deuda'] / max(1, $totalDeudaGlobal)) * 100 * 5) +
                    ($p['categoria'] === 'critica' ? 15 : ($p['categoria'] === 'alta' ? 8 : 0))
                ));
                $f['score_factura'] = round($scoreF, 1);
                $todasFacturas->push($f);
            }
        }
        $topFacturas = $todasFacturas->sortByDesc('score_factura')->take(20)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'kpis'             => $kpis,
                'alertas'          => $alertas,
                'proveedores'      => $porProveedor,
                'top_facturas'     => $topFacturas,
                'proyeccion'       => $proyeccion,
            ],
        ]);
    }

    /**
     * Estructura vacía para reutilizar
     */
    private function estructuraGlobalVacia()
    {
        return [
            'kpis' => [
                'total_deuda' => 0, 'total_vencido' => 0, 'total_por_vencer_7d' => 0,
                'total_proveedores' => 0, 'total_facturas' => 0, 'proveedores_criticos' => 0,
                'porcentaje_vencido' => 0, 'proveedor_mas_critico' => null,
                'deuda_top5' => 0, 'porcentaje_top5' => 0,
            ],
            'alertas'      => [],
            'proveedores'  => [],
            'top_facturas' => [],
            'proyeccion'   => [],
        ];
    }

    /**
     * Listado principal de proveedores
     */
    public function index(Request $request)
    {
        $busqueda = (isset($request->busqueda))? $request->busqueda :  '';
        $codprov  = (isset($request->codprov))? $request->codprov :  '';
        $tab      = (isset($request->tab))? $request->tab :  'tab5';

        $compras         = [];
        $proveedores     = [];
        $proveedor       = null;
        $pagosPendientes = [];
        $pagosRealizados = [];
        $resumenPagos    = [];

        // Variables para el tab de análisis
        $compras_30dias      = 0;
        $ventas_30dias       = 0;
        $total_productos_stock = 0;
        $valor_inventario    = 0;
        $rotacion_inventario = 0;
        $top_productos       = [];
        $comprasData         = [];
        $totales_compras     = 0;
        $stock_bajo          = [];
        $productos_rentables = [];

        if($busqueda != '') {
            $busqueda = str_replace("\"", "", $busqueda);
            $busqueda = str_replace("'", "", $busqueda);
            $busqueda = str_replace("*", " ", $busqueda);
            $vector = explode(" ", $busqueda);

            if ($vector) {
                $numerito = 0;
                $cadena = '';
                foreach ($vector as $value) {
                    if ($numerito > 0) {
                        $cadena .= ' AND ';
                    }
                    $cadena .= "(codprov like '%$value%' or descrip like '%$value%' or id3 like '%$value%')";
                    $numerito++;
                }
            }

            $proveedores = Saprov::whereRaw($cadena)->orderBy('descrip', 'asc')->limit(60)->get();
        }

        // Si hay código seleccionado, cargar datos del proveedor
        if ($codprov != '') {
            $proveedor = Saprov::where('codprov', $codprov)->first();

            if ($proveedor) {
                // Cargar pagos pendientes de este proveedor (viajes donde paga)
                $pagosPendientes = Cwviajemoto::with(['viaje', 'cliente'])
                    ->where('proveedor_paga', true)
                    ->where('proveedor_codprov', $proveedor->codprov)
                    ->where('estado_conciliacion', 'pendiente')
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Cargar pagos realizados (conciliados)
                $pagosRealizados = Cwviajemoto::with(['viaje', 'cliente'])
                    ->where('proveedor_paga', true)
                    ->where('proveedor_codprov', $proveedor->codprov)
                    ->where('estado_conciliacion', 'conciliado')
                    ->orderBy('fecha_conciliacion', 'desc')
                    ->limit(20)
                    ->get();

                // Resumen de pagos por mes
                $resumenPagos = Cwviajemoto::select(
                    DB::raw('YEAR(created_at) as anio'),
                    DB::raw('MONTH(created_at) as mes'),
                    DB::raw('SUM(monto_esperado_cliente) as total_pendiente'),
                    DB::raw('SUM(CASE WHEN estado_conciliacion = "conciliado" THEN monto_real_cliente ELSE 0 END) as total_pagado')
                )
                    ->where('proveedor_paga', true)
                    ->where('proveedor_codprov', $proveedor->codprov)
                    ->groupBy('anio', 'mes')
                    ->orderBy('anio', 'desc')
                    ->orderBy('mes', 'desc')
                    ->limit(6)
                    ->get();

                // === NUEVO: Cargar datos para el tab de análisis ===
                $fecha_30dias = now()->subDays(30)->format('Y-m-d');
                $hoy = now()->format('Y-m-d');

                $kpi = $this->calcularKPIProveedor($codprov, $fecha_30dias, $hoy);

                $compras_30dias = $kpi['compras_30dias'];
                $ventas_30dias = $kpi['ventas_30dias'];
                $total_productos_stock = $kpi['total_productos'];
                $valor_inventario = $kpi['valor_inventario'];
                $rotacion_inventario = $kpi['rotacion'];

                $top_productos = $this->getTopProductos($codprov, $fecha_30dias, $hoy, 10);
                $stock_bajo = $this->getStockBajo($codprov, 10);
                $productos_rentables = $this->getProductosRentables($codprov, 10);
            }

            if ($tab == 'tab5') {
                $comprasData = $this->getComprasProveedor($codprov, $request);
                $compras = $comprasData['compras'];
                $totales_compras = $comprasData['totales'];
            } else {
                $compras = collect([]);
                $totales_compras = ['unidades' => 0, 'monto' => 0];
            }
        }



        return view('proveedores.index', compact(
            'proveedores',
            'proveedor',
            'busqueda',
            'codprov',
            'tab',
            'pagosPendientes',
            'pagosRealizados',
            'resumenPagos',
            'compras_30dias',
            'ventas_30dias',
            'total_productos_stock',
            'valor_inventario',
            'rotacion_inventario',
            'top_productos',
            'stock_bajo',
            'productos_rentables',
            'compras',
            'totales_compras'
        ));
    }

    public function getResumenGeneralCuentasPorPagar(Request $request)
    {
        $soloVencidos = $request->get('solovencidos', false);
        $fechavence   = $request->get('fechavence', '');

        $query = Cwcxcprv::where('signo', 1)
            ->whereRaw('(monto - abonado) != 0')
            ->select(
                'codprov',
                DB::raw('SUM(monto) as monto'),
                DB::raw('SUM(abonado) as abonado'),
                DB::raw('SUM(monto - abonado) as deuda')
            )
            ->groupBy('codprov');

        // Filtrar solo vencidos
        if ($soloVencidos) {
            $hoy = date('Y-m-d');
            $query->whereExists(function($q) use ($hoy) {
                $q->select(DB::raw(1))
                    ->from('cwcxcprv as c2')
                    ->whereRaw('c2.codprov = cwcxcprv.codprov')
                    ->where('c2.signo', 1)
                    ->whereDate('c2.fechav', '<', $hoy);
            });
        }

        // Filtrar por fecha de vencimiento (acepta dd/mm/yyyy, dd-mm-yyyy o yyyy-mm-dd)
        if ($fechavence) {
            $fechaVenceFilter = null;
            if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $fechavence, $m)) {
                $fechaVenceFilter = "{$m[3]}-{$m[2]}-{$m[1]}";
            } elseif (preg_match('/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})$/', $fechavence, $m)) {
                $fechaVenceFilter = "{$m[1]}-{$m[2]}-{$m[3]}";
            }

            if ($fechaVenceFilter) {
                $query->whereExists(function($q) use ($fechaVenceFilter) {
                    $q->select(DB::raw(1))
                        ->from('cwcxcprv as c2')
                        ->whereRaw('c2.codprov = cwcxcprv.codprov')
                        ->where('c2.signo', 1)
                        ->whereDate('c2.fechav', '>=', $fechaVenceFilter);
                });
            }
        }

        $resumen = $query->having('deuda', '>', 0)
            ->orderBy('deuda', 'desc')
            ->get();

        foreach ($resumen as $item) {
            $proveedor = Saprov::where('codprov', $item->codprov)->first();
            $item->empresa = $proveedor ? $proveedor->descrip : $item->codprov;
        }

        $totales = [
            'monto'   => $resumen->sum('monto'),
            'abonado' => $resumen->sum('abonado'),
            'deuda'   => $resumen->sum('deuda'),
        ];

        return response()->json([
            'success' => true,
            'data'    => $resumen,
            'totales' => $totales,
        ]);
    }

    public function getCuentasPorPagar($codprov, Request $request)
    {
        $soloResumen  = $request->soloResumen  ?? '';
        $solovencidos = $request->solovencidos ?? '';
        $fechavence   = $request->fechavence   ?? '';

        $query = Cwcxcprv::where('codprov', $codprov)
            ->where('signo', 1)
            ->whereRaw('(monto - abonado) != 0');

        if ($solovencidos) {
            $hoy = date('Y-m-d');
            $query->whereDate('fechav', '<', $hoy);
        }

        if ($fechavence) {
            $fechaVenceFilter = null;
            if (preg_match('/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/', $fechavence, $m)) {
                $fechaVenceFilter = "{$m[3]}-{$m[2]}-{$m[1]}";
            } elseif (preg_match('/^(\d{4})[\/\-](\d{2})[\/\-](\d{2})$/', $fechavence, $m)) {
                $fechaVenceFilter = "{$m[1]}-{$m[2]}-{$m[3]}";
            }

            if ($fechaVenceFilter) {
                $query->whereDate('fechav', '>=', $fechaVenceFilter);
            }
        }

        if ($soloResumen) {
            $resumen = $query->select(
                'codprov',
                DB::raw('SUM(monto) as monto'),
                DB::raw('SUM(abonado) as abonado'),
                DB::raw('SUM(monto - abonado) as deuda')
            )->groupBy('codprov')->first();

            if ($resumen) {
                $proveedor = Saprov::where('codprov', $codprov)->first();
                $resumen->empresa = $proveedor ? $proveedor->descrip : $codprov;
            }

            return response()->json([
                'success'     => true,
                'soloResumen' => true,
                'data'        => $resumen,
            ]);
        } else {
            $cuentas = $query->select(
                'id',
                'numerod',
                'concepto',
                'monto',
                'abonado',
                DB::raw('(monto - abonado) as deuda'),
                DB::raw("DATE_FORMAT(fecha, '%d/%m/%Y') as fecha2"),
                DB::raw("DATE_FORMAT(fechav, '%d/%m/%Y') as fechav2"),
                DB::raw("DATE_FORMAT(fechav, '%Y-%m-%d') as fechav")
            )->orderBy('fecha', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $totalMonto   = $cuentas->sum('monto');
            $totalAbonado = $cuentas->sum('abonado');
            $totalDeuda   = $cuentas->sum('deuda');

            return response()->json([
                'success'       => true,
                'soloResumen'   => false,
                'data'          => $cuentas,
                'total_monto'   => $totalMonto,
                'total_abonado' => $totalAbonado,
                'total_deuda'   => $totalDeuda,
            ]);
        }
    }

    public function show($id)
    {
        //
    }

    /**
     * Obtener compras del proveedor
     */
    public function getComprasProveedor($codprov, Request $request, $limite = 50)
    {
        $comercial = session('comercialid') ?: 1;

        $sucursales = Sasucursal::where('fk_comercial', $comercial)->pluck('id')->toArray();
        $sucursales = implode(',', $sucursales);

        $query = Sacomp::selectRaw('id,codprov,numerod,tipocom,fk_sucursal,fechae')
                ->where('codprov', $codprov)
                ->with(['sucursal'])
                ->whereIn('tipocom', ['H', 'I'])
                ->whereRaw("fk_sucursal in ($sucursales)" )
                ->orderBy('fechae', 'desc');

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fechae', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fechae', '<=', $request->fecha_hasta);
        }

            $compras = $query->limit($limite)->get();


        // Calcular totales
        $totales = [
            'unidades' => 0,
            'monto' => 0
        ];

        foreach ($compras as $compra) {
            $totalUnidades = 0;
            $totalMonto    = 0;

            $signo = $compra->tipocom == 'I' ? -1 : 1;

            foreach ($compra->items as $item) {
                $totalUnidades += $item->cantidad * $signo;
                $totalMonto    += $item->preciod  * $item->cantidad * $signo;
            }

            $compra->total_unidades_calculado = $totalUnidades;
            $compra->total_monto_calculado    = $totalMonto;

            $totales['unidades'] += $totalUnidades * $signo;
            $totales['monto']    += $totalMonto    * $signo;
        }

        return [
            'compras' => $compras,
            'totales' => $totales
        ];
    }


    public function debug($codprov, $codprod)
    {
        $comercial = session('comercialid') ?: 1;

        $sucursales = Sasucursal::where("fk_comercial", $comercial)->get();
        $sucursalarr = $sucursales->pluck('id');
        $sucursalIds = implode(",", $sucursalarr->toArray());

        // 1. Verificar seriales comprados a este proveedor
        $serialesCompra = DB::table('saitemcom as ic')
            ->join('saseprcom as sc', function($join) {
                $join->on('ic.tipocom', '=', 'sc.tipocom')
                    ->on('ic.numerod', '=', 'sc.numerod')
                    ->on('ic.coditem', '=', 'sc.coditem');
            })
            ->where('ic.codprov', $codprov)
            ->where('ic.coditem', $codprod)
            ->whereIn('ic.tipocom', ['H', 'I'])
            ->whereRaw("ic.fk_sucursal in ($sucursalIds)")
            ->whereNotNull('sc.nroserial')
            ->select('sc.nroserial')
            ->distinct()
            ->get();


        // 2. Verificar ventas de esos seriales
        $serialesList = $serialesCompra->pluck('nroserial')->toArray();

        if (!empty($serialesList)) {
            $ventas = DB::table('saitemfac as ifac')
                ->join('saseprfac as sf', function ($join) {
                    $join->on('ifac.TipoFac', '=', 'sf.TipoFac')
                        ->on('ifac.NumeroD', '=', 'sf.NumeroD')
                        ->on('ifac.NroLinea', '=', 'sf.NroLinea')
                        ->on('ifac.CodItem', '=', 'sf.CodItem');
                })
                ->whereIn('sf.NroSerial', $serialesList)
                ->where('ifac.CodItem', $codprod)
                ->whereIn('ifac.TipoFac', ['A', 'B'])
                ->whereRaw("ifac.fk_sucursal in ($sucursalIds)")
                ->select(
                    'ifac.id',
                    'ifac.NumeroD',
                    'ifac.TipoFac',
                    'ifac.cantidad',
                    'ifac.costodoriginal',
                    'ifac.signo',
                    'ifac.FechaE',
                    'sf.NroSerial'
                )
                ->get();

        }

        // 3. Verificar ventas totales del producto (sin filtrar)
        $ventasTotales = Saitemfac::where('CodItem', $codprod)
            ->whereRaw("fk_sucursal in ($sucursalIds)")
            ->whereIn('TipoFac', ['A', 'B'])
            ->sum('cantidad');



        return response()->json([
            'seriales_compra' => $serialesCompra,
            'ventas_filtradas' => $ventas ?? [],
            'ventas_totales' => $ventasTotales
        ]);
    }


}
