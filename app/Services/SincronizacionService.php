<?php
// app/Services/SincronizacionService.php

namespace App\Services;

use App\Models\Saseprcom;
use App\Models\Sacomp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SincronizacionService
{
    protected $webUrl;
    protected $webSucursal;
    protected $comercialId;

    public function __construct()
    {
        $this->comercialId = session('comercialid', 1);
        $this->webUrl = config('app.web_url');
        $this->webSucursal = config('app.web_sucursal', '');
    }

    /**
     * Sincronizar compras pendientes a la plataforma web
     */
    public function sincronizarCompras()
    {
        $sucursalId = session('sucursal_id', 1);

        // Obtener compras no sincronizadas
        $compras = Sacomp::where('sync', false)
            ->where('fk_sucursal', $sucursalId)
            ->whereIn('tipocom', ['H', 'U', 'I', 'Y'])
            ->orderBy('id')
            ->limit(50)
            ->get();

        if ($compras->isEmpty()) {
            return ['message' => 'No hay compras pendientes de sincronizar'];
        }

        $comprasData = [];

        foreach ($compras as $compra) {
            $items = $compra->items;
            $seriales = $compra->seriales;

            $compraData = [
                'tipocom' => $compra->tipocom,
                'nrounico' => $compra->nrounico,
                'numerod' => $compra->numerod,
                'numeron' => $compra->numeron,
                'nroctrol' => $compra->nroctrol,
                'numeror' => $compra->numeror,
                'fechat' => $compra->fechat,
                'fechai' => $compra->fechai,
                'fechae' => $compra->fechae,
                'fechav' => $compra->fechav,
                'codusua' => $compra->codusua,
                'codesta' => $compra->codesta,
                'signo' => $compra->signo,
                'codoper' => $compra->codoper,
                'codprov' => $compra->codprov,
                'codubic' => $compra->codubic,
                'descrip' => $compra->descrip,
                'direc1' => $compra->direc1,
                'direc2' => $compra->direc2,
                'telef' => $compra->telef,
                'id3' => $compra->id3,
                'monto' => $compra->monto,
                'totalprd' => $compra->totalprd,
                'totalsrv' => $compra->totalsrv,
                'tgravable' => $compra->tgravable,
                'fletes' => $compra->fletes,
                'mtotax' => $compra->mtotax,
                'reteniva' => $compra->reteniva,
                'descto1' => $compra->descto1,
                'descto2' => $compra->descto2,
                'totaldeuda' => $compra->totaldeuda,
                'mtototal' => $compra->mtototal,
                'texento' => $compra->texento,
                'porcret' => $compra->porcret,
                'credito' => $compra->credito,
                'notas1' => $compra->notas1,
                'notas2' => $compra->notas2,
                'notas3' => $compra->notas3,
                'notas5' => $compra->notas5,
                'fk_sucursal' => $this->webSucursal,
                'allitems' => [],
                'seriales' => [],
            ];

            // Agregar items
            foreach ($items as $item) {
                $compraData['allitems'][] = [
                    'tipocom' => $item->tipocom,
                    'esserv' => $item->esserv,
                    'numerod' => $item->numerod,
                    'codprov' => $item->codprov,
                    'nrolinea' => $item->nrolinea,
                    'nrolineac' => $item->nrolineac,
                    'signo' => $item->signo,
                    'fechae' => $item->fechae,
                    'coditem' => $item->coditem,
                    'refere' => $item->refere,
                    'codubic' => $item->codubic,
                    'otipo' => $item->otipo,
                    'onumero' => $item->onumero,
                    'descrip1' => $item->descrip1,
                    'descrip2' => $item->descrip2,
                    'descrip3' => $item->descrip3,
                    'cantidad' => $item->cantidad,
                    'costo' => $item->costo,
                    'totalitem' => $item->totalitem,
                    'precio' => $item->precio,
                    'mtotax' => $item->mtotax,
                    'costorg' => $item->costorg,
                    'nrounicol' => $item->nrounicol,
                    'existantu' => $item->existantu,
                    'existant' => $item->existant,
                    'precio1' => $item->precio1,
                    'precio2' => $item->precio2,
                    'fechal' => $item->fechal,
                    'fechav' => $item->fechav,
                    'nrolote' => $item->nrolote,
                    'esunid' => $item->esunid,
                    'esexento' => $item->esexento,
                    'usaserv' => $item->usaserv,
                    'preciod' => $item->preciod,
                    'preciod2' => $item->preciod2,
                    'costod' => $item->costod,
                    'costod2' => $item->costod2,
                    'costod3' => $item->costod3,
                    'result' => $item->result,
                    'precioanterior' => $item->precioanterior,
                    'costoanterior' => $item->costoanterior,
                    'costoorig' => $item->costoorig,
                    'porc1' => $item->porc1,
                    'porc2' => $item->porc2,
                    'porc3' => $item->porc3,
                    'fk_sucursal' => $this->webSucursal,
                ];
            }

            // Agregar seriales
            foreach ($seriales as $serial) {
                $compraData['seriales'][] = [
                    'codsucu'     => $serial->codsucu,
                    'tipocom'     => $serial->tipocom,
                    'numerod'     => $serial->numerod,
                    'codprov'     => $serial->codprov,
                    'nrolinea'    => $serial->nrolinea,
                    'nrolineac'   => $serial->nrolineac,
                    'nroserial'   => $serial->nroserial,
                    'coditem'     => $serial->coditem,
                    'codubic'     => $serial->codubic,
                    'fk_sucursal' => $this->webSucursal,
                ];
            }

            $comprasData[] = $compraData;
        }

        // Enviar al web service
        $response = Http::post($this->webUrl . '/sacomp/sync/doc', [
            'compras'  => json_encode($comprasData),
            'dato'     => config('app.sync_token'),
            'sucursal' => '300' . $this->webSucursal,
        ]);

        if ($response->successful()) {
            $result = $response->json();

            if (isset($result['updated']) && $result['updated']) {
                // Marcar como sincronizadas
                foreach ($compras as $compra) {
                    $compra->update(['sync' => true]);
                }
            }

            return [
                'success'  => true,
                'message'  => 'Compras sincronizadas exitosamente',
                'count'    => $compras->count(),
                'response' => $result,
            ];
        }

        Log::error('Error en sincronización de compras', [
            'url'      => $this->webUrl,
            'response' => $response->body(),
        ]);

        return [
            'success' => false,
            'message' => 'Error al sincronizar compras',
            'error'   => $response->body(),
        ];
    }

    /**
     * Sincronizar una compra específica
     */
    public function sincronizarCompra($numerod, $codprov, $tipocom = 'H')
    {
        $compra = Sacomp::where('numerod', $numerod)
            ->where('codprov', $codprov)
            ->where('tipocom', $tipocom)
            ->first();

        if (!$compra) {
            return ['success' => false, 'message' => 'Compra no encontrada'];
        }

        // Marcar como sincronizada
        $compra->update(['sync' => false]);

        // Ejecutar sincronización general
        return $this->sincronizarCompras();
    }
}
