<?php

namespace App\Http\Controllers;

use App\Models\Sainsta;
use App\Models\Saprod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SainstaController extends Controller
{
    public function index()
    {
        $instanciaspadre = Sainsta::selectRaw(" CONCAT (repeat('&nbsp;',(Nivel)*4) , ' ' ,  Descrip) as label, descrip, id ")
            ->orderBy('codalte')->get();
        return view('sub-categories', compact('instanciaspadre'));
    }

    public function json()
    {
        $all = Sainsta::with(['padre', 'hijos', 'productos', 'servicios'])->orderBy('codalte','asc')->get();
        $aux = [];
        $instancias = [];

        foreach ($all as $item){

            $aux = [
                "nivel" => $item->nivel,
                "productos" => $item->productos()->count() > 0,
                "servicios" => $item->servicios()->count() > 0,
                "id"            => "$item->id",
                "hijos"         => (isset($item->hijos) and count($item->hijos) > 0)? 1: 0,
                "subcategory"   => "$item->descrip",
                "desseri"       => (isset($item->desseri))? $item->desseri: 0,
                "category"      => (isset($item->padre) and isset($item->padre->id))? $item->padre->descrip : ''
            ];

            array_push($instancias,$aux);
        }
        return response()->json($instancias );
    }
    public function lastprod()
    {
        $last   = '';
        $product = Saprod::orderBy('id','desc')->first();

        if(isset($product) and $product->codprod != '')
            $last = $product->codprod;

        return response()->json(['last' => $last ]);
    }

    public function list(Request $request)
    {
        $instancias = Sainsta::orderBy('codinst','desc')->get();
        return response()->json(['success'=>'success', 'instancias' => $instancias], 200);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $comercial  = session('comercialid') ;
        if(!$comercial) {
            session(['comercialid' => 1]);
            $comercial = 1;
        }
        $insPadre = $request->insPadre;
        $nivel    = 1;
        $padreid  = 0;
        $codalte  = '';
        if($insPadre){
            $padre   = Sainsta::where(['comercial' => $comercial, 'descrip' => $insPadre])->first();
            if(isset($padre)){
                $nivel   = $padre->nivel + 1;
                $padreid = $padre->codinst;
                $codalte = $padre->codalte;
            }
        }

        // Verificar si ya existe una instancia con el mismo nombre en esta comercial
        $existe = Sainsta::where('comercial', $comercial)
            ->where('descrip', strtoupper($request->descrip))
            ->exists();

        if ($existe) {
            return response()->json(['error' => 'Ya existe una instancia con esta descripción'], 400);
        }

        $new = new Sainsta();
        $new->insPadre = $padreid;
        $new->codinst  = 0;
        $new->codalte  = '';
        $new->desseri  = (isset($request->desseri) and $request->desseri !='') ? $request->desseri : 0;
        $new->comercial= $comercial;
        $new->descrip  = strtoupper($request->descrip);
        $new->nivel    = $nivel;
        $new->save();

        $new->codinst  = $new->id;
        if(!$codalte)
            $new->codalte = $new->id.'.';
        else
            $new->codalte = "$codalte".$new->id.'.';
        $new->save();
        return response()->json([
            'success' => true,
            'id' => $new->id,
            'subcategory' => $new->descrip,
            'category' => $insPadre ?? '',
            'desseri' => $new->desseri,
            'nivel' => $new->nivel
        ]);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $sainsta = Sainsta::find($id);
        $codaltepadre = '';

        if($request->descrip != $request->insPadre){

            $insPadre = $request->insPadre;
            $nivel    = $sainsta->nivel;
            $desseri  = $request->desseri;
            $padreid  = $sainsta->insPadre;

            if($insPadre){
                $padre   = Sainsta::where(['descrip' => $insPadre])->first();
                if(isset($padre)){
                    $nivel        = $padre->nivel + 1;
                    $padreid      = $padre->codinst;
                    $codaltepadre = $padre->codalte;
                }
            }

            $sainsta->insPadre  = $padreid;
            $sainsta->desseri   = $desseri;
            $sainsta->descrip   = strtoupper($request->descrip);
            $sainsta->nivel     = $nivel;
            if($codaltepadre)
                $sainsta->codalte = "$codaltepadre".$sainsta->id.".";

            $sainsta->save();

        }
        return response()->json(['id'=>$sainsta->id]);
    }

    public function destroy($id)
    {
        try {
            $comercial = session('comercialid');

            if (!$comercial) {
                return response()->json(['error' => 'No hay una comercial activa'], 400);
            }

            $sainsta = Sainsta::where('comercial', $comercial)->find($id);

            if (!$sainsta) {
                return response()->json(['error' => 'Instancia no encontrada'], 404);
            }

            // Verificar si tiene hijos (excluyendo otras comerciales)
            if ($sainsta->hijos()->count() > 0) {
                return response()->json([
                    'error' => 'No se puede eliminar porque tiene instancias hijas asociadas'
                ], 400);
            }

            // Verificar si tiene productos (solo de esta comercial)
            if ($sainsta->productos()->count() > 0) {
                return response()->json([
                    'error' => 'No se puede eliminar porque tiene productos asociados'
                ], 400);
            }

            // Verificar si tiene servicios (solo de esta comercial)
            if ($sainsta->servicios()->count() > 0) {
                return response()->json([
                    'error' => 'No se puede eliminar porque tiene servicios asociados'
                ], 400);
            }

            $sainsta->delete();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar: ' . $e->getMessage()], 500);
        }

    }

    public function buscarModelos(Request $request)
    {
        $term = $request->get('q', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        // Buscar en sainsta donde nivel = 2, inspadre = 1 y descrip LIKE %term%
        $modelos = DB::table('sainsta')
            ->select('codinst', 'descrip as modelo')
            ->whereRaw("codalte like '1.%'")
            ->where('descrip', 'LIKE', "%{$term}%")
            ->orderBy('descrip')
            ->limit(20)
            ->get();

        return response()->json($modelos);
    }

    public function listComercial(Request $request)
    {
        $comercialid = str_replace("700", "", $request->comercial);

        if (!$comercialid) {
            return response()->json(['error' => 'Comercial no válida'], 400);
        }

        $instancias = Sainsta::where('comercial', $comercialid)
            ->orderBy('codinst', 'desc')
            ->get();

        return response()->json(['success' => 'success', 'instancias' => $instancias], 200);
    }


}
