<?php

namespace App\Http\Controllers;

use App\Models\Cwbancos;
use App\Models\Cwcuentas;
use App\Models\Cwcuentasucursal;
use Illuminate\Http\Request;

class CwcuentasController extends Controller
{
    public function buscarcuentaajax(Request $request)
    {
        $iii          = (isset($request->iii)         )? $request->iii          : '';
        $buscarcuenta = (isset($request->buscarcuenta))? $request->buscarcuenta : '';

        $buscarcuenta = str_replace("*", " ", $buscarcuenta);
        $buscarcuenta = str_replace("\"", "", $buscarcuenta);
        $buscarcuenta = str_replace("'", "",  $buscarcuenta);

        $cadena   = '';
        $numerito = 0;
        $cuentas  = [];

        if($buscarcuenta != '') {
            $vector = explode(" ", $buscarcuenta);

            if ($vector) {
                foreach ($vector as $value) {
                    if ($numerito > 0) {
                        $cadena .= ' AND ';
                    }
                    $cadena .= "(descrip like '%$value%' or numero like '%$value%')";

                    $numerito++;
                }
            }

            if ($cadena) $cadena = " and ($cadena)";
            $cuentas = Cwcuentas::whereRaw("detalle = 1 and web = 1  $cadena")->limit(50)->get();
        }
        return view('layouts.buscarcuentas', compact('cuentas', 'iii', 'buscarcuenta'))->render();
    }

    public function index()
    {

    }

    public function json()
    {

    }

    public function list(Request $request)
    {
        $sucursalid = str_replace("300","",$request->sucursal);
        $cuentas   = $request->cuentas;
        $cuentas   = json_decode($cuentas);

        if(isset($cuentas))
            foreach ($cuentas as $cuenta){
                $aux = Cwcuentas::where(['id' => $cuenta->id])->first();

                if(!$aux){
                    $new = new Cwcuentas();
                    $new->id           = ($cuenta->id)           ?$cuenta->id        : '';
                    $new->numero       = ($cuenta->numero   !='')?$cuenta->numero    : '';
                    $new->descrip      = ($cuenta->descrip  !='')?$cuenta->descrip   : '';
                    $new->nivel        = ($cuenta->nivel     > 0)?$cuenta->nivel     : 0;
                    $new->numpadre     = ($cuenta->numpadre !='')?$cuenta->numpadre  : '';
                    $new->detalle      = ($cuenta->detalle   > 0)?$cuenta->detalle   : 0;
                    $new->banco        = ($cuenta->banco     > 0)?$cuenta->banco     : 0;
                    $new->noeliminar   = ($cuenta->noeliminar> 0)?$cuenta->noeliminar: 0;
                    $new->save();

                    if($cuenta->banco > 0){
                        $newb = new Cwbancos();
                        $newb->id           =  $cuenta->datosbanco->id;
                        $newb->descrip      = ($cuenta->datosbanco->descrip  !='')?$cuenta->datosbanco->descrip   : '';
                        $newb->fksucursal   =  0;
                        $newb->fk_cuenta    =  $cuenta->id;
                        $newb->bs           = (isset($cuenta->datosbanco->bs)           and $cuenta->datosbanco->bs           !='')?$cuenta->datosbanco->bs           : 0;
                        $newb->dolares      = (isset($cuenta->datosbanco->dolares)      and $cuenta->datosbanco->dolares      !='')?$cuenta->datosbanco->dolares      : 0;
                        $newb->pesos        = (isset($cuenta->datosbanco->pesos)        and $cuenta->datosbanco->pesos        !='')?$cuenta->datosbanco->pesos        : 0;
                        $newb->recibetransf = (isset($cuenta->datosbanco->recibetransf) and $cuenta->datosbanco->recibetransf !='')?$cuenta->datosbanco->recibetransf : '';
                        $newb->instpago     = (isset($cuenta->datosbanco->instpago)     and $cuenta->datosbanco->instpago     !='')?$cuenta->datosbanco->instpago      : '';
                        $newb->telefono     = (isset($cuenta->datosbanco->telefono )    and $cuenta->datosbanco->telefono     !='')?$cuenta->datosbanco->telefono     : '';
                        $newb->abrev        = (isset($cuenta->datosbanco->abrev)        and $cuenta->datosbanco->abrev        !='')?$cuenta->datosbanco->abrev        : '';

                        $newb->save();
                    }

                    $rel              = new Cwcuentasucursal();
                    $rel->fk_cuenta   = $cuenta->id;
                    $rel->fk_sucursal = $sucursalid;
                    $rel->save();
                }else{

                    $aux->descrip      = ($cuenta->descrip  !='')?$cuenta->descrip   : '';
                    $aux->save();

                    if($aux->banco){
                        $banco = Cwbancos::where([  'fk_cuenta'=>$aux->id])->first();
                        if(isset($banco) and isset($banco->fk_cuenta)) {
                            $banco->descrip      = (isset($cuenta->descrip)) ? $cuenta->descrip : '';
                            $banco->bs           = (isset($cuenta->datosbanco->bs)           and $cuenta->datosbanco->bs           !='')?$cuenta->datosbanco->bs           : 0;
                            $banco->dolares      = (isset($cuenta->datosbanco->dolares)      and $cuenta->datosbanco->dolares      !='')?$cuenta->datosbanco->dolares      : 0;
                            $banco->pesos        = (isset($cuenta->datosbanco->pesos)        and $cuenta->datosbanco->pesos        !='')?$cuenta->datosbanco->pesos        : 0;
                            $banco->recibetransf = (isset($cuenta->datosbanco->recibetransf) and $cuenta->datosbanco->recibetransf !='')?$cuenta->datosbanco->recibetransf : '';
                            $banco->instpago     = (isset($cuenta->datosbanco->instpago)     and $cuenta->datosbanco->instpago     !='')?$cuenta->datosbanco->instpago      : '';
                            $banco->save();
                        }else{
                            $newb = new Cwbancos();
                            $newb->id           = $cuenta->datosbanco->id;
                            $newb->descrip      = ($cuenta->datosbanco->descrip  !='')?$cuenta->datosbanco->descrip   : '';
                            $newb->fksucursal   = 0;
                            $newb->fk_cuenta    = $cuenta->id;
                            $newb->bs           = (isset($cuenta->datosbanco->bs)           and $cuenta->datosbanco->bs           !='')?$cuenta->datosbanco->bs           : 0;
                            $newb->dolares      = (isset($cuenta->datosbanco->dolares)      and $cuenta->datosbanco->dolares      !='')?$cuenta->datosbanco->dolares      : 0;
                            $newb->pesos        = (isset($cuenta->datosbanco->pesos)        and $cuenta->datosbanco->pesos        !='')?$cuenta->datosbanco->pesos        : 0;
                            $newb->recibetransf = (isset($cuenta->datosbanco->recibetransf) and $cuenta->datosbanco->recibetransf !='')?$cuenta->datosbanco->recibetransf : '';
                            $newb->instpago     = (isset($cuenta->datosbanco->instpago)     and $cuenta->datosbanco->instpago     !='')?$cuenta->datosbanco->instpago      : '';
                            $newb->telefono     = (isset($cuenta->datosbanco->telefono )    and $cuenta->datosbanco->telefono     !='')?$cuenta->datosbanco->telefono     : '';
                            $newb->abrev        = (isset($cuenta->datosbanco->abrev)        and $cuenta->datosbanco->abrev        !='')?$cuenta->datosbanco->abrev        : '';

                            $newb->save();
                        }
                    }

                    $aux = Cwcuentasucursal::where(['fk_cuenta' => $cuenta->id, 'fk_sucursal'=>$sucursalid])->first();
                    if(!$aux){
                        $rel              = new Cwcuentasucursal();
                        $rel->fk_cuenta   = $cuenta->id;
                        $rel->fk_sucursal = $sucursalid;
                        $rel->save();
                    }
                }
            }

        $cuentas = Cwcuentas::with('bancorel')
            ->whereRaw("id not in (select fk_cuenta from cwcuentasucursal where fk_sucursal=$sucursalid )")->get();

        return response()->json(['success'=>'success', 'cuentas' => $cuentas]);
    }

    public function cuentassucu(Request $request){
        $sucursalid = 5;
        $cuentas = Cwcuentas::with('bancorel')
            ->whereRaw("id not in (select fk_cuenta from cwcuentasucursal where fk_sucursal = $sucursalid )")
            ->get();

        dd($cuentas[0]->bancorel);
    }
    public function cwcuentasucursal(Request $request)
    {
        $sucursalid = str_replace("300", "", $request->sucursal);
        $cuentas = $request->cuentas;
        $cuentas = json_decode($cuentas);

        if (isset($cuentas))
            foreach ($cuentas as $cuenta){
                $aux = Cwcuentasucursal::where(['fk_cuenta' => $cuenta->id, 'fk_sucursal'=>$sucursalid])->first();
                if(!$aux){
                    $rel              = new Cwcuentasucursal();
                    $rel->fk_cuenta   = $cuenta->id;
                    $rel->fk_sucursal = $sucursalid;
                    $rel->save();
                }
            }

        return response()->json(['success'=>'success']);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
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
        //
    }

    public function destroy($id)
    {
    }
}
