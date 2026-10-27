<?php
// app/Helpers/Helpers.php
namespace App\Helpers;

use Illuminate\Support\Str;

if (!function_exists('completarcadena')) {
    function completarcadena($cadena, $longitud, $caracter = '0')
    {
        return str_pad($cadena, $longitud, $caracter, STR_PAD_LEFT);
    }
}

if (!function_exists('verificarFecha')) {
    function verificarFecha($fecha)
    {
        if (strlen($fecha) != 10) return false;
        list($d, $m, $y) = explode('/', $fecha);
        return checkdate($m, $d, $y);
    }
}

if (!function_exists('fecha1menorfecha2')) {
    function fecha1menorfecha2($fecha1, $fecha2)
    {
        list($d1, $m1, $y1) = explode('/', $fecha1);
        list($d2, $m2, $y2) = explode('/', $fecha2);
        $timestamp1 = mktime(0, 0, 0, $m1, $d1, $y1);
        $timestamp2 = mktime(0, 0, 0, $m2, $d2, $y2);
        return $timestamp1 <= $timestamp2;
    }
}

if (!function_exists('getFechaHoy')) {
    function getFechaHoy()
    {
        return date('Y-m-d');
    }
}

if (!function_exists('getFechaHoyRegular')) {
    function getFechaHoyRegular()
    {
        return date('d/m/Y');
    }
}
