<?php
// app/Http/Controllers/PedidoController.php
namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\PedidoSerial;
use App\Models\CostoAdicional;
use App\Models\Saprod;
use App\Models\Saprov;
use App\Models\Sadepo;
use App\Models\Sainsta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    /**
     * Mostrar lista de pedidos
     */
    public function index(Request $request)
    {
        $query = Pedido::with(['proveedor']);

        if ($request->has('codprov') && $request->codprov) {
            $query->where('codprov', $request->codprov);
        }

        $pedidos = $query->orderBy('id', 'desc')->paginate(20);
        $proveedores = Saprov::where('activo', 1)->orderBy('descrip')->get();

        return view('pedidos.index', compact('pedidos', 'proveedores'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $proveedores = Saprov::where('activo', 1)->orderBy('descrip')->get();
        return view('pedidos.create', compact('proveedores'));
    }

    /**
     * Crear nuevo pedido
     */
    public function store(Request $request)
    {
        $request->validate([
            'codprov' => 'required|exists:saprov,codprov',
        ]);

        try {
            DB::beginTransaction();

            $fk_empresa = session('empresa_id', 1);
            $sucursal_id = session('sucursal_id', 1);

            // Generar número de pedido
            $lastPedido = Pedido::where('codprov', $request->codprov)
                ->where('fk_empresa', $fk_empresa)
                ->orderBy('id', 'desc')
                ->first();

            $num = $lastPedido ? intval($lastPedido->num) + 1 : 1;
            $num = str_pad($num, 8, '0', STR_PAD_LEFT);

            // Crear pedido
            $pedido = Pedido::create([
                'codprov' => $request->codprov,
                'num' => $num,
                'fk_usuario' => Auth::id(),
                'fecha' => now(),
                'fk_empresa' => $fk_empresa,
                'fk_sucursal' => $sucursal_id,
                'status' => '0',
            ]);

            // Agregar item por defecto (CODPROD)
            PedidoItem::create([
                'fk_pedido' => $pedido->id,
                'codprod' => 'CODPROD',
                'cantidad' => 1,
                'costo' => 0,
                'precio' => 0,
                'precio2' => 0,
                'precio3' => 0,
                'iva' => 16,
                'fk_sucursal' => $sucursal_id,
            ]);

            DB::commit();

            Session::flash('pedido_id', $pedido->id);

            return redirect()->route('pedidos.show', $pedido->id)
                ->with('success', 'Pedido creado exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear pedido: ' . $e->getMessage());
            return back()->with('error', 'Error al crear el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar el pedido (listaPedido.php)
     */
    public function show($id)
    {
        $pedido = Pedido::with([
            'items.producto.instancia',
            'items.seriales',
            'proveedor',
            'costosAdicionales.proveedor'
        ])->findOrFail($id);

        // Obtener parámetros de sesión
        $dolarpesocompra = Session::get('dolarpesocompra', 0);
        $fleteenpedido = Session::get('fleteenpedido', 0);
        $precioenpesos = Session::get('precioenpesos', 0);
        $preciobase = Session::get('preciobase', 0);
        $precioLineal = Session::get('precioLineal', 0);
        $itemcompra = Session::get('itemcompra', []);
        $comercial_id = session('comercialid', 1);

        // Productos para búsqueda
        $productos = Saprod::where('activo', 1)
            ->where('comercial', $comercial_id)
            ->orderBy('descrip')
            ->limit(50)
            ->get();

        $depositos = Sadepo::where('activo', 1)
            ->where('comercial', $comercial_id)
            ->get();

        // Calcular totales
        $canttotal = 0;
        $montoantes = 0;
        $montoadicional = 0;
        $totalResult = 0;
        $ventainv1 = 0;
        $ventainv2 = 0;
        $ventainv3 = 0;
        $totaladic = $pedido->costosAdicionales->sum('monto');

        foreach ($pedido->items as $item) {
            $canttotal += $item->cantidad;
            $montoantes += $item->costo * $item->cantidad;
            $montoadicional += $item->costadicional * $item->cantidad;
            $totalResult += $item->result * $item->cantidad;
            $ventainv1 += $item->precio * $item->cantidad;
            $ventainv2 += $item->precio2 * $item->cantidad;
            $ventainv3 += $item->precio3 * $item->cantidad;
        }

        return view('pedidos.show', compact(
            'pedido',
            'productos',
            'depositos',
            'dolarpesocompra',
            'fleteenpedido',
            'precioenpesos',
            'preciobase',
            'precioLineal',
            'itemcompra',
            'canttotal',
            'montoantes',
            'montoadicional',
            'totalResult',
            'ventainv1',
            'ventainv2',
            'ventainv3',
            'totaladic'
        ));
    }

    /**
     * Agregar item al pedido (vía AJAX)
     */
    public function agregarItem(Request $request, $pedidoId)
    {
        try {
            $pedido = Pedido::findOrFail($pedidoId);
            $codprod = $request->codprod ?? 'CODPROD';
            $cantidad = $request->cantidad ?? 1;

            // Si es un producto real, obtener su IVA
            $iva = 16;
            if ($codprod != 'CODPROD') {
                $producto = Saprod::where('codprod', $codprod)->first();
                if ($producto) {
                    $iva = $producto->esexento ? 0 : 16;
                }
            }

            $item = PedidoItem::create([
                'fk_pedido' => $pedidoId,
                'codprod' => $codprod,
                'cantidad' => $cantidad,
                'costo' => 0,
                'precio' => 0,
                'precio2' => 0,
                'precio3' => 0,
                'iva' => $iva,
                'fk_sucursal' => session('sucursal_id', 1),
            ]);

            // Si es un producto real, recalcular
            if ($codprod != 'CODPROD') {
                $this->recalcularItem($item);
            }

            return response()->json([
                'success' => true,
                'item_id' => $item->id,
                'message' => 'Item agregado',
                'item' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar productos (vía AJAX)
     */
    public function buscarProductos(Request $request)
    {
        $search = $request->get('q', '');
        $pedidoId = $request->get('pedido_id');
        $comercial_id = session('comercialid', 1);

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $query = Saprod::where('activo', 1)
            ->where('comercial', $comercial_id);

        // Búsqueda en múltiples campos
        $terms = explode(' ', $search);
        foreach ($terms as $term) {
            if (strlen($term) >= 2) {
                $query->where(function ($q) use ($term) {
                    $q->where('codprod', 'LIKE', "%{$term}%")
                        ->orWhere('descrip', 'LIKE', "%{$term}%")
                        ->orWhere('descrip2', 'LIKE', "%{$term}%")
                        ->orWhere('descrip3', 'LIKE', "%{$term}%")
                        ->orWhere('marca', 'LIKE', "%{$term}%")
                        ->orWhere('refere', 'LIKE', "%{$term}%");
                });
            }
        }

        $productos = $query->limit(20)->get();

        // Productos ya en el pedido
        $itemsEnPedido = [];
        if ($pedidoId) {
            $itemsEnPedido = PedidoItem::where('fk_pedido', $pedidoId)
                ->where('codprod', '!=', 'CODPROD')
                ->pluck('codprod')
                ->toArray();
        }

        $resultados = [];
        foreach ($productos as $producto) {
            $instancia = Sainsta::where('codinst', $producto->codinst)
                ->where('comercial', $comercial_id)
                ->first();

            $resultados[] = [
                'codprod' => $producto->codprod,
                'descrip' => $producto->descrip,
                'marca' => $producto->marca,
                'refere' => $producto->refere,
                'existen' => $producto->existen ?? 0,
                'preciod' => $producto->preciod,
                'costod' => $producto->costod,
                'ya_agregado' => in_array($producto->codprod, $itemsEnPedido),
                'esexento' => $producto->esexento,
                'desseri' => $instancia ? $instancia->desseri : 0,
                'texto' => $producto->codprod . ' - ' . $producto->descrip,
            ];
        }

        return response()->json($resultados);
    }

    /**
     * Cambiar producto de un item (vía AJAX)
     */
    public function cambiarProducto(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:pedido_items,id',
            'codprod' => 'required|exists:saprod,codprod',
        ]);

        try {
            $item = PedidoItem::findOrFail($request->item_id);
            $producto = Saprod::where('codprod', $request->codprod)->first();

            if (!$producto) {
                return response()->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
            }

            // Actualizar el item
            $item->codprod = $producto->codprod;
            $item->iva = $producto->esexento ? 0 : 16;
            $item->cantidad = 0;
            $item->referencia = '';
            $item->producto = '';

            $item->save();

            // Recalcular
            $this->recalcularItem($item);

            // Verificar si requiere seriales
            $instancia = Sainsta::where('codinst', $producto->codinst)
                ->where('comercial', session('comercialid', 1))
                ->first();

            $desseri = $instancia ? $instancia->desseri : 0;

            return response()->json([
                'success' => true,
                'item' => $item,
                'desseri' => $desseri,
                'message' => 'Producto cambiado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar item (vía AJAX)
     */
    public function actualizarItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:pedido_items,id',
            'name' => 'required',
            'value' => 'required',
        ]);

        try {
            $item = PedidoItem::with('pedido')->findOrFail($request->item_id);
            $name = $request->name;
            $value = $request->value;

            switch ($name) {
                case 'cantidad':
                    $item->cantidad = $value;
                    break;

                case 'costo':
                    $item->costo = $value;
                    break;

                case 'costopesos':
                    $item->costopesos = $value;
                    $dolar = Session::get('dolarpesocompra', 0);
                    if ($dolar > 0) {
                        $item->costo = $value / $dolar;
                    }
                    break;

                case 'costadicional':
                    $item->costadicional = $value;
                    break;

                case 'porc1':
                case 'porc2':
                case 'porc3':
                    $item->$name = $value;
                    break;

                case 'precio':
                case 'precio2':
                case 'precio3':
                    $item->$name = $value;
                    $fixedName = $name . '_fixed';
                    $item->$fixedName = true;
                    break;

                case 'precio1pesos':
                case 'precio2pesos':
                case 'precio3pesos':
                    $item->$name = $value;
                    $precioName = str_replace('pesos', '', $name);
                    $dolar = Session::get('dolarpesocompra', 0);
                    if ($dolar > 0) {
                        $item->$precioName = ($value / $dolar) * 1.16;
                    }
                    break;

                case 'preciotodos':
                    $this->actualizarTodosPrecios($item, $value);
                    break;

                case 'referencia':
                case 'producto':
                    $item->$name = $value;
                    break;

                case 'iva':
                    $item->iva = $value;
                    break;

                default:
                    return response()->json(['success' => false, 'message' => 'Campo no soportado'], 400);
            }

            $item->save();

            // Recalcular item y pedido
            $this->recalcularItem($item);
            $pedido = $this->recalcularPedido($item->fk_pedido);

            return response()->json([
                'success' => true,
                'item' => $item,
                'pedido' => $pedido
            ]);

        } catch (\Exception $e) {
            Log::error('Error actualizando item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalcular un item
     */
    private function recalcularItem(PedidoItem $item)
    {
        $costo = $item->costo + $item->costadicional;

        // Aplicar descuentos
        $result = $costo;
        $result = $result - ($result * ($item->porc1 / 100));
        $result = $result - ($result * ($item->porc2 / 100));
        $result = $result - ($result * ($item->porc3 / 100));

        // Aplicar flete global
        $flete = Session::get('fleteenpedido', 0);
        $result = $result * (1 + ($flete / 100));

        $item->result = $result;

        // Recalcular precios si no están fijos
        if (!$item->precio_fixed) {
            $preciobase = Session::get('preciobase', 0);
            $utilidad = $item->utilidad ?: 0;
            $iva = $item->iva ?: 16;

            if ($preciobase == 0) {
                $calc = (100 - $utilidad) / 100;
                $precio = $calc > 0 ? $result / $calc : 0;
            } else {
                $precio = $result * (1 + ($utilidad / 100));
            }

            $precio = $precio * (1 + ($iva / 100));
            $item->precio = round($precio, 4);
        }

        if (!$item->precio2_fixed) {
            $item->precio2 = $item->precio;
        }
        if (!$item->precio3_fixed) {
            $item->precio3 = $item->precio;
        }

        $item->save();

        return $item;
    }

    /**
     * Recalcular pedido
     */
    private function recalcularPedido($pedidoId)
    {
        $items = PedidoItem::where('fk_pedido', $pedidoId)->get();
        $pedido = Pedido::find($pedidoId);

        $monto = 0;
        $totalResult = 0;
        $montoadicional = 0;

        foreach ($items as $item) {
            $costo = $item->costo + $item->costadicional;
            $monto += $costo * $item->cantidad;
            $montoadicional += $item->costadicional * $item->cantidad;
            $totalResult += $item->result * $item->cantidad;
        }

        $pedido->monto = $monto;
        $pedido->montoadicional = $montoadicional;
        $pedido->total = $totalResult;
        $pedido->save();

        return $pedido;
    }

    /**
     * Actualizar todos los precios
     */
    private function actualizarTodosPrecios(PedidoItem $item, $valor)
    {
        $precioenpesos = Session::get('precioenpesos', 0);
        $dolar = Session::get('dolarpesocompra', 0);

        if ($precioenpesos && $dolar > 0) {
            $precioDolar = $valor / $dolar;
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
     * Eliminar item
     */
    public function eliminarItem(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:pedido_items,id',
        ]);

        try {
            $item = PedidoItem::findOrFail($request->item_id);
            $pedidoId = $item->fk_pedido;

            // Eliminar seriales asociados
            PedidoSerial::where('fk_itemid', $item->id)->delete();

            $item->delete();

            // Recalcular pedido
            $this->recalcularPedido($pedidoId);

            return response()->json([
                'success' => true,
                'message' => 'Item eliminado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar costo adicional
     */
    public function agregarCostoAdicional(Request $request, $pedidoId)
    {
        try {
            $pedido = Pedido::findOrFail($pedidoId);

            $costo = CostoAdicional::create([
                'fk_pedido' => $pedidoId,
                'codprov' => 'CODPROV',
                'monto' => 0,
                'descripcion' => '',
                'fecha' => null,
                'fk_sucursal' => session('sucursal_id', 1),
            ]);

            return response()->json([
                'success' => true,
                'costo_adicional' => $costo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar costo adicional
     */
    public function actualizarCostoAdicional(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:costos_adicionales,id',
            'name' => 'required',
            'value' => 'required',
        ]);

        try {
            $costo = CostoAdicional::findOrFail($request->id);

            if ($request->name == 'fecha' && $request->value) {
                list($d, $m, $y) = explode('/', $request->value);
                $request->merge(['value' => "$y-$m-$d"]);
            }

            $costo->{$request->name} = $request->value;
            $costo->save();

            return response()->json([
                'success' => true,
                'costo' => $costo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar costo adicional
     */
    public function eliminarCostoAdicional(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:costos_adicionales,id',
        ]);

        try {
            CostoAdicional::findOrFail($request->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Costo adicional eliminado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar variable general del pedido
     */
    public function updateVariable(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'name' => 'required',
            'value' => 'required',
        ]);

        try {
            $pedido = Pedido::findOrFail($request->pedido_id);
            $name = $request->name;
            $value = $request->value;

            // Variables de sesión
            $variablesSesion = ['flete', 'dolarpesocompra', 'descuentoc', 'retencioncomp'];
            if (in_array($name, $variablesSesion)) {
                Session::put($name, $value);
                return response()->json(['success' => true]);
            }

            // Campos del pedido
            switch ($name) {
                case 'recepcion':
                    if (strlen($value) == 10) {
                        list($d, $m, $y) = explode('/', $value);
                        $pedido->recepcion = "$y-$m-$d";
                    }
                    break;

                case 'observaciones':
                    $pedido->observaciones = $value;
                    break;

                case 'unidades':
                    $pedido->unidades = $value ?: 0;
                    break;

                case 'porc1':
                case 'porc2':
                case 'porc3':
                    $pedido->$name = $value;
                    // Recalcular items
                    foreach ($pedido->items as $item) {
                        $item->$name = $value;
                        $this->recalcularItem($item);
                    }
                    break;

                case 'iva':
                    foreach ($pedido->items as $item) {
                        $item->iva = $value;
                        $this->recalcularItem($item);
                    }
                    break;

                case 'uti1':
                case 'uti2':
                case 'uti3':
                    $this->actualizarUtilidades($pedido, $name, $value);
                    break;

                default:
                    $pedido->$name = $value;
            }

            $pedido->save();
            $this->recalcularPedido($pedido->id);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar utilidades y precios
     */
    private function actualizarUtilidades($pedido, $campo, $valor)
    {
        $numUtil = substr($campo, 3, 1);
        $variablePrecio = $numUtil == 1 ? 'precio' : 'precio' . $numUtil;

        $preciobase = Session::get('preciobase', 0);
        $precioLineal = Session::get('precioLineal', 0);
        $flete = Session::get('fleteenpedido', 0);
        $precioenpesos = Session::get('precioenpesos', 0);
        $dolar = Session::get('dolarpesocompra', 0);

        foreach ($pedido->items as $item) {
            $costo = $item->costo + $item->costadicional;

            // Aplicar descuentos
            $result = $costo;
            $result = $result - ($result * ($item->porc1 / 100));
            $result = $result - ($result * ($item->porc2 / 100));
            $result = $result - ($result * ($item->porc3 / 100));

            $result = $result * (1 + ($flete / 100));

            // Calcular precio
            if (!$precioLineal) {
                $diferutil = (100 - $valor) / 100;
                if ($preciobase == 0) {
                    $precio = $diferutil > 0 ? $result / $diferutil : 0;
                } else {
                    $precio = $result * (1 + ($valor / 100));
                }
            } else {
                $precio = $result * (1 + ($valor / 100));
            }

            $precio = $precio * (1 + ($item->iva / 100));
            $item->$variablePrecio = round($precio, 4);

            // Precios en pesos
            if ($precioenpesos && $dolar > 0) {
                $precioPesos = $precio * $dolar;
                $item->{"precio{$numUtil}pesos"} = round($precioPesos, 4);
            }

            $item->save();
        }

        $this->recalcularPedido($pedido->id);
    }

    /**
     * Marcar/Desmarcar item para compra
     */
    public function toggleItemCompra(Request $request)
    {
        $request->validate([
            'codprod' => 'required',
            'flag' => 'required|boolean',
        ]);

        $itemcompra = Session::get('itemcompra', []);
        $codprodc = Session::get('codprodc', []);
        $ic = Session::get('ic', 1);

        if ($request->flag) {
            $itemcompra[$request->codprod] = '1';
            if (!in_array($request->codprod, $codprodc)) {
                $codprodc[$ic] = $request->codprod;
                $ic++;
            }
        } else {
            unset($itemcompra[$request->codprod]);
            foreach ($codprodc as $key => $value) {
                if ($value == $request->codprod) {
                    $codprodc[$key] = '';
                }
            }
        }

        Session::put('itemcompra', $itemcompra);
        Session::put('codprodc', $codprodc);
        Session::put('ic', $ic);

        return response()->json(['success' => true]);
    }

    /**
     * Colocar costos y precios actuales
     */
    public function colocarCostosPrecios($pedidoId)
    {
        try {
            $items = PedidoItem::where('fk_pedido', $pedidoId)->get();
            $comercial_id = session('comercialid', 1);

            foreach ($items as $item) {
                if ($item->codprod == 'CODPROD') continue;

                $producto = Saprod::where('codprod', $item->codprod)
                    ->where('comercial', $comercial_id)
                    ->first();

                if (!$producto) continue;

                $updates = [];

                if (!$item->costo || $item->costo == 0) {
                    $updates['costo'] = $producto->preciod ?? 0;
                }

                $updates['precio'] = $producto->costod ?? 0;
                $updates['precio2'] = $producto->costod2 ?? 0;
                $updates['precio3'] = $producto->costod3 ?? 0;

                // Precios en pesos
                if (Session::get('precioenpesos', 0)) {
                    $updates['precio1pesos'] = $producto->prepesos1 ?? 0;
                    $updates['precio2pesos'] = $producto->prepesos2 ?? 0;
                    $updates['precio3pesos'] = $producto->prepesos3 ?? 0;
                }

                $item->update($updates);
                $this->recalcularItem($item);
            }

            return redirect()->route('pedidos.show', $pedidoId)
                ->with('success', 'Costos y precios actualizados');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Seriales del pedido
     */
    public function seriales($pedidoId, $itemId)
    {
        $item = PedidoItem::with(['producto', 'seriales'])->findOrFail($itemId);
        $tempseriales = Session::get('tempseriales', []);

        // Verificar si requiere seriales
        $instancia = Sainsta::where('codinst', $item->producto?->codinst)
            ->where('comercial', session('comercialid', 1))
            ->first();

        $requiereSeriales = $instancia ? $instancia->desseri : 0;

        return view('pedidos.seriales', compact(
            'item',
            'tempseriales',
            'requiereSeriales',
            'pedidoId'
        ));
    }

    /**
     * Agregar serial temporal
     */
    public function agregarSerialTemporal(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:pedido_items,id',
            'serial' => 'required|string|max:50',
        ]);

        $tempseriales = Session::get('tempseriales', []);

        if (isset($tempseriales[$request->serial])) {
            return response()->json([
                'success' => false,
                'message' => 'Serial ya está en la lista'
            ]);
        }

        $exists = PedidoSerial::where('fk_itemid', $request->item_id)
            ->where('serial', $request->serial)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Serial ya está agregado'
            ]);
        }

        $tempseriales[$request->serial] = '1';
        Session::put('tempseriales', $tempseriales);

        return response()->json([
            'success' => true,
            'tempseriales' => $tempseriales
        ]);
    }

    /**
     * Guardar seriales
     */
    public function guardarSeriales(Request $request, $itemId)
    {
        $tempseriales = Session::get('tempseriales', []);

        try {
            DB::beginTransaction();

            $guardados = 0;
            foreach ($tempseriales as $serial => $estado) {
                if ($estado != '1') continue;

                $exists = PedidoSerial::where('fk_itemid', $itemId)
                    ->where('serial', $serial)
                    ->exists();

                if (!$exists) {
                    PedidoSerial::create([
                        'fk_itemid' => $itemId,
                        'serial' => $serial,
                        'fk_sucursal' => session('sucursal_id', 1),
                    ]);
                    $guardados++;
                }
            }

            Session::put('tempseriales', []);
            DB::commit();

            return redirect()->route('pedidos.seriales', [
                'pedidoId' => $request->pedido_id,
                'itemId' => $itemId
            ])->with('success', "$guardados seriales guardados");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar seriales: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar serial temporal
     */
    public function eliminarSerialTemporal(Request $request)
    {
        $request->validate(['serial' => 'required|string']);

        $tempseriales = Session::get('tempseriales', []);
        unset($tempseriales[$request->serial]);
        Session::put('tempseriales', $tempseriales);

        return response()->json(['success' => true]);
    }

    /**
     * Limpiar seriales temporales
     */
    public function limpiarSerialesTemporales()
    {
        Session::put('tempseriales', []);
        return response()->json(['success' => true]);
    }

    /**
     * Eliminar serial guardado
     */
    public function eliminarSerial($serialId)
    {
        try {
            $serial = PedidoSerial::findOrFail($serialId);
            $serial->delete();

            return back()->with('success', 'Serial eliminado');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
