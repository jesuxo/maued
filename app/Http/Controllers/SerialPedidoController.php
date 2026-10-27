<?php
// app/Http/Controllers/SerialPedidoController.php
namespace App\Http\Controllers;

use App\Models\PedidoItem;
use App\Models\PedidoSerial;
use App\Models\Saprod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SerialPedidoController extends Controller
{
    public function index($pedidoId, $itemId)
    {
        $item = PedidoItem::with(['producto', 'seriales'])->findOrFail($itemId);
        $serialesExistentes = $item->seriales;

        // Seriales temporales en sesión
        $tempseriales = session('tempseriales', []);

        return view('pedidos.seriales', compact('item', 'serialesExistentes', 'tempseriales'));
    }

    public function agregarSerial(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:pedido_items,id',
            'serial' => 'required|string|max:50',
        ]);

        $item = PedidoItem::findOrFail($request->item_id);

        // Verificar que el producto requiere seriales
        $producto = Saprod::where('codprod', $item->codprod)->first();
        if (!$producto || !$producto->instancia->desseri) {
            return response()->json(['success' => false, 'message' => 'Este producto no requiere seriales']);
        }

        // Verificar duplicado en la sesión
        $tempseriales = session('tempseriales', []);
        if (isset($tempseriales[$request->serial])) {
            return response()->json(['success' => false, 'message' => 'Serial ya está en la lista temporal']);
        }

        // Agregar a sesión temporal
        $tempseriales[$request->serial] = '1';
        session(['tempseriales' => $tempseriales]);

        return response()->json([
            'success' => true,
            'message' => 'Serial agregado temporalmente',
            'tempseriales' => $tempseriales
        ]);
    }

    public function guardarSeriales(Request $request, $itemId)
    {
        $item = PedidoItem::findOrFail($itemId);
        $tempseriales = session('tempseriales', []);

        DB::beginTransaction();

        try {
            foreach ($tempseriales as $serial => $estado) {
                if ($estado != '1') continue;

                // Verificar que el serial no exista en inventario (SASERI)
                // TODO: Implementar verificación en SASERI

                // Verificar que no esté ya agregado
                $exists = PedidoSerial::where('fk_itemid', $itemId)
                    ->where('serial', $serial)
                    ->exists();

                if (!$exists) {
                    PedidoSerial::create([
                        'fk_itemid' => $itemId,
                        'serial' => $serial,
                        'fk_sucursal' => session('sucursal_id', 1),
                    ]);
                }
            }

            // Limpiar sesión temporal
            session(['tempseriales' => []]);

            DB::commit();

            return redirect()->route('pedidos.seriales', ['pedidoId' => $item->fk_pedido, 'itemId' => $itemId])
                ->with('success', 'Seriales guardados exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar seriales: ' . $e->getMessage());
        }
    }

    public function eliminarSerial($serialId)
    {
        $serial = PedidoSerial::findOrFail($serialId);
        $serial->delete();

        return response()->json(['success' => true, 'message' => 'Serial eliminado']);
    }

    public function eliminarTemporal(Request $request)
    {
        $request->validate(['serial' => 'required|string']);

        $tempseriales = session('tempseriales', []);
        unset($tempseriales[$request->serial]);
        session(['tempseriales' => $tempseriales]);

        return response()->json(['success' => true]);
    }

    public function limpiarTemporal()
    {
        session(['tempseriales' => []]);
        return response()->json(['success' => true]);
    }
}
