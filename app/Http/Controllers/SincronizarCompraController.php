<?php
// app/Http/Controllers/SincronizarCompraController.php
namespace App\Http\Controllers;

use App\Services\SincronizacionService;
use Illuminate\Http\Request;

class SincronizarCompraController extends Controller
{
    protected $sincronizacionService;

    public function __construct(SincronizacionService $sincronizacionService)
    {
        $this->sincronizacionService = $sincronizacionService;
    }

    public function sincronizar()
    {
        $result = $this->sincronizacionService->sincronizarCompras();

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 500);
    }

    public function sincronizarUna(Request $request)
    {
        $request->validate([
            'numerod' => 'required|string',
            'codprov' => 'required|string',
            'tipocom' => 'sometimes|in:H,U,I,Y',
        ]);

        $result = $this->sincronizacionService->sincronizarCompra(
            $request->numerod,
            $request->codprov,
            $request->tipocom ?? 'H'
        );

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 500);
    }
}
