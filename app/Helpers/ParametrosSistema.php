<?php

namespace App\Helpers;

use App\Models\Sacomercial;
use Illuminate\Support\Facades\Session;

class ParametrosSistema
{
    public static function getParametrosSesion()
    {
        return [
            'preciobase'         => Session::get('preciobase', 0),
            'precioenpesos'      => Session::get('precioenpesos', 0),
            'precioLineal'       => Session::get('precioLineal', 0),
            'soloinvnota'        => Session::get('soloinvnota', 0),
            'nadaNota'           => Session::get('nadaNota', 0),
            'dolarpesocompra'    => Session::get('dolarpesocompra', 0),
            'fleteenpedido'      => Session::get('fleteenpedido', 0),
            'empresa_id'         => Session::get('empresa_id', 1),
            'sucursal_id'        => Session::get('sucursal_id', 1),
            'comercial_id'       => Session::get('comercialid', 1),
            'codubiccompra'      => Session::get('codubiccompra', ''),
            'descuentoc'         => Session::get('descuentoc', 0),
            'fletecompra'        => Session::get('fletecompra', 0),
            'tasa'               => Session::get('tasa', 0),
            'emision'            => Session::get('emision', date('d/m/Y')),
            'numerod'            => Session::get('numerod', ''),
            'retencion'          => Session::get('retencion', 0),
            'retencioncomp'      => Session::get('retencioncomp', 0),
            'nogenerarretencion' => Session::get('nogenerarretencion', 0),
            'decontado'          => Session::get('decontado', 0),
            'deimportacion'      => Session::get('deimportacion', 0),
            'notas1'             => Session::get('notas1', ''),
            'notas2'             => Session::get('notas2', ''),
            'notas3'             => Session::get('notas3', ''),
            'notas4'             => Session::get('notas4', ''),
            'notas5'             => Session::get('notas5', ''),
        ];
    }

    public static function getPesoXDolar()
    {
        // Obtener de configuración de la empresa
        /*$pesoxdolar = Sacomercial::where('valporc', '>', 0)
            ->orWhere('pesoxdolar', '>', 0)
            ->value('pesoxdolar');*/
        $pesoxdolar = 0;

        return $pesoxdolar ?: 0;
    }

    public static function getTasaOficial()
    {
        // Obtener tasa del día desde la tabla dicom
        $fecha = date('Y-m-d');
       /* $dolar = \DB::table('dicom')
            ->where('fk_tipo', 2)
            ->where('fecha', $fecha)
            ->first();*/
        $dolar = 0;
        return $dolar;
        //return $dolar ? $dolar->bs : 0;
    }
}
