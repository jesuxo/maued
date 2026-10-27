@extends('layouts.master')
@section('title')
    Venta de productos por sucursal
@endsection
@section('css')
    <style>
        :root{
            --brand: #0072c5;
            --brand-dark: #005a9e;
            --brand-soft: #e8f2fa;
            --border: #d9e2ec;
            --text: #2c3e50;
            --muted: #6b7c8c;
        }

        .report-header{
            background: var(--brand);
            color: #fff;
            padding: 18px 24px;
            border-radius: 6px 6px 0 0;
        }
        .report-header h4{
            margin: 0;
            font-weight: 600;
            letter-spacing: .3px;
        }
        .report-header p{
            margin: 4px 0 0;
            font-size: .85rem;
            opacity: .85;
        }

        .report-card{
            border: 1px solid var(--border);
            border-radius: 6px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            margin-bottom: 20px;
        }
        .report-card .card-body{
            padding: 18px 20px;
        }

        .filters-bar{
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            padding: 16px 20px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .filters-bar .form-select,
        .filters-bar .form-control{
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: .9rem;
            color: var(--text);
            height: 38px;
        }
        .filters-bar .form-select:focus,
        .filters-bar .form-control:focus{
            border-color: var(--brand);
            box-shadow: 0 0 0 .15rem rgba(0,114,197,.15);
        }

        .btn-brand{
            background: var(--brand);
            color: #fff;
            border: 1px solid var(--brand);
            border-radius: 4px;
            padding: 8px 18px;
            font-size: .875rem;
            font-weight: 500;
            transition: background .15s ease;
        }
        .btn-brand:hover{
            background: var(--brand-dark);
            color: #fff;
        }
        .btn-brand-outline{
            background: #fff;
            color: var(--brand);
            border: 1px solid var(--brand);
            border-radius: 4px;
            padding: 8px 18px;
            font-size: .875rem;
            font-weight: 500;
        }
        .btn-brand-outline:hover{
            background: var(--brand-soft);
            color: var(--brand-dark);
        }

        /* Tablas */
        .tbl-report{
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
            color: var(--text);
        }
        .tbl-report thead th{
            background: var(--brand);
            color: #fff;
            font-weight: 600;
            font-size: .78rem;
            letter-spacing: .4px;
            text-transform: uppercase;
            padding: 10px 12px;
            text-align: center;
            border: 1px solid var(--brand);
            white-space: nowrap;
        }
        .tbl-report thead th.text-left{ text-align: left; }
        .tbl-report thead th.text-right{ text-align: right; }

        .tbl-report tbody td{
            padding: 9px 12px;
            border: 1px solid var(--border);
            vertical-align: middle;
        }
        .tbl-report tbody tr:nth-child(odd){
            background: #fff;
        }
        .tbl-report tbody tr:nth-child(even){
            background: #f7fafd;
        }
        .tbl-report tbody tr:hover{
            background: var(--brand-soft);
        }
        .tbl-report tbody td.num{
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-feature-settings: "tnum";
        }
        .tbl-report tbody td.center{
            text-align: center;
        }

        .tbl-report tfoot td,
        .tbl-report tr.total-row td{
            background: var(--brand-soft);
            color: var(--brand-dark);
            font-weight: 700;
            border: 1px solid var(--border);
            padding: 10px 12px;
        }
        .tbl-report tr.total-row td.num{
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .section-title{
            background: var(--brand);
            color: #fff;
            font-weight: 600;
            font-size: .85rem;
            letter-spacing: .5px;
            text-transform: uppercase;
            padding: 10px 16px;
            border-radius: 6px 6px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .section-title small{
            font-weight: 400;
            font-size: .78rem;
            opacity: .9;
            text-transform: none;
            letter-spacing: 0;
        }

        .empty-state{
            padding: 30px;
            text-align: center;
            color: var(--muted);
            font-size: .9rem;
        }
    </style>
@endsection

@section('content')

    {{-- ==================== FILTROS ==================== --}}
    <div class="report-header">
        <h4 style="color: white">Reporte de Ventas</h4>
        <p>Desde {{$fecha1}} hasta {{$fecha2}}</p>
    </div>

    <form method="post" name="form1" id="form1" action="/reporte/venta" class="filters-bar">
        <div style="min-width: 240px;">
            <select class="form-select" data-choices onchange="$('#form1').submit()"
                    id="idsucu" name="fksucursal">
                <option {{($fksucursal == '' or $fksucursal == 0 )?'selected':''}} value="0">Todas las Sucursales</option>
                @foreach($allsucursales as $sucu)
                    <option {{($sucu->id == $fksucursal)?'selected':''}} value="{{$sucu->id}}">{!! $sucu->descrip !!}</option>
                @endforeach
            </select>
        </div>

        <div style="min-width: 220px;">
            <input type="text" class="form-control" data-provider="flatpickr"
                   data-range-date="true" data-date-format="d/m/Y"
                   data-deafult-date="" name="fechasreport" readonly="readonly"
                   value="{{$fechasreport}}">
        </div>

        <button type="submit" class="btn-brand">Consultar</button>

        <button onclick="loadingreport('/resumenVentas')" type="button" class="btn-brand-outline ms-auto">
            Ver Resumen
        </button>

        @csrf
        @method('POST')
    </form>

    <div id="contentReport"></div>

    {{-- ==================== RESUMEN POR SUCURSAL ==================== --}}
    <div class="report-card">
        <div class="section-title">
            <span>Resumen por Sucursal</span>
            <small>{{$fecha1}} — {{$fecha2}}</small>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="tbl-report">
                    <thead>
                    <tr>
                        <th class="text-left" style="width:34%">Sucursal</th>
                        <th style="width:9%">BS</th>
                        <th style="width:9%">BS.T</th>
                        <th style="width:8%">USD</th>
                        <th style="width:8%">USD.T</th>
                        <th style="width:9%">Crédito</th>
                        <th style="width:11%">Total USD</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $tcancele = $tcancelt = $tdolares = $ttransf = 0;
                        $tpesos = $tpeso_tranf = $teuros = $tcredito = $ttotalventa = 0;
                    @endphp

                    @if(isset($sucursales) && count($sucursales) > 0)
                        @foreach($sucursales as $indexsuc => $sucursal)
                            @php
                                $row = $listado[$indexsuc] ?? [];
                                $tcancele    += $row['cancele']    ?? 0;
                                $tcancelt    += $row['cancelt']    ?? 0;
                                $tdolares    += $row['dolares']    ?? 0;
                                $ttransf     += $row['transf']     ?? 0;
                                $tpesos      += $row['pesos']      ?? 0;
                                $tpeso_tranf += $row['peso_tranf'] ?? 0;
                                $teuros      += $row['euros']      ?? 0;
                                $tcredito    += $row['credito']    ?? 0;
                                $ttotalventa += $row['totalventa'] ?? 0;
                            @endphp
                            <tr>
                                <td>{{$sucursal}}</td>
                                <td class="num">{{ !empty($row['cancele'])   ? number_format($row['cancele'],2,',','.')   : '' }}</td>
                                <td class="num">{{ !empty($row['cancelt'])   ? number_format($row['cancelt'],2,',','.')   : '' }}</td>
                                <td class="num">{{ !empty($row['dolares'])   ? number_format($row['dolares'],2,',','.')   : '' }}</td>
                                <td class="num">{{ !empty($row['transf'])    ? number_format($row['transf'],2,',','.')    : '' }}</td>
                                <td class="num">{{ !empty($row['credito'])   ? number_format($row['credito'],2,',','.')   : '' }}</td>
                                <td class="num">{{ !empty($row['totalventa'])? number_format($row['totalventa'],2,',','.'): '' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="empty-state">Sin datos para el rango seleccionado.</td>
                        </tr>
                    @endif
                    </tbody>
                    <tfoot>
                    <tr class="total-row">
                        <td>Totales</td>
                        <td class="num">{{ $tcancele    != 0 ? number_format($tcancele,2,',','.')    : '' }}</td>
                        <td class="num">{{ $tcancelt    != 0 ? number_format($tcancelt,2,',','.')    : '' }}</td>
                        <td class="num">{{ $tdolares    != 0 ? number_format($tdolares,2,',','.')    : '' }}</td>
                        <td class="num">{{ $ttransf     != 0 ? number_format($ttransf,2,',','.')     : '' }}</td>
                        <td class="num">{{ $tcredito    != 0 ? number_format($tcredito,2,',','.')    : '' }}</td>
                        <td class="num">{{ $ttotalventa != 0 ? number_format($ttotalventa,2,',','.') : '' }}</td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== VENTA POR INSTANCIAS ==================== --}}
    <div class="report-card">
        <div class="section-title">
            <span>Venta por Instancias</span>
            <small>{{$fecha1}} — {{$fecha2}}</small>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="tbl-report">
                    <thead>
                    <tr>
                        <th class="text-left" style="width:26%">Instancia de Inventario</th>
                        <th style="width:10%">Cantidad</th>
                        <th style="width:14%">Total Venta</th>
                        <th style="width:14%">Base Venta</th>
                        <th style="width:14%">Total Costo</th>
                        <th style="width:14%">Utilidad</th>
                        <th style="width:8%">% Utilidad</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $totalcant = $tprecioventa = $tbasesuma = 0;
                        $costoxcantidad = $restaxcantidad = 0;
                    @endphp

                    @if(isset($arrayinsta) && count($arrayinsta) > 0)
                        @foreach ($arrayinsta as $listavend)
                            @php
                                if(($listavend['cant'] ?? 0) == 0) continue;

                                $totalcant      += $listavend['cant'];
                                $tprecioventa   += $listavend['precioventa'];
                                $tbasesuma      += $listavend['basesuma'];
                                $costoxcantidad += $listavend['preciodpro'];
                                $restaxcantidad += $listavend['resta'];

                                $utilidad = 0;
                                if(!empty($listavend['preciodpro']) && $listavend['preciodpro'] > 0){
                                    $utilidad = $listavend['resta'] / $listavend['preciodpro'];
                                }
                            @endphp
                            <tr>
                                <td>{{$listavend['descrip']}}</td>
                                <td class="center">{{ number_format($listavend['cant'],0,',','.') }}</td>
                                <td class="num">{{ number_format($listavend['precioventa'],2,',','.') }}</td>
                                <td class="num">{{ number_format($listavend['basesuma'],2,',','.') }}</td>
                                <td class="num">{{ number_format($listavend['preciodpro'],2,',','.') }}</td>
                                <td class="num">{{ number_format($listavend['resta'],2,',','.') }}</td>
                                <td class="num">{{ number_format($utilidad*100,2,',','.') }} %</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="empty-state">Sin datos para el rango seleccionado.</td>
                        </tr>
                    @endif
                    </tbody>
                    <tfoot>
                    <tr class="total-row">
                        <td>Totales</td>
                        <td class="center">{{ number_format($totalcant,0,',','.') }}</td>
                        <td class="num">$ {{ number_format($tprecioventa,2,',','.') }}</td>
                        <td class="num">$ {{ number_format($tbasesuma,2,',','.') }}</td>
                        <td class="num">$ {{ number_format($costoxcantidad,2,',','.') }}</td>
                        <td class="num">$ {{ number_format($restaxcantidad,2,',','.') }}</td>
                        <td class="num">
                            @if($costoxcantidad > 0)
                                {{ number_format(($restaxcantidad/$costoxcantidad)*100,2,',','.') }} %
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- ==================== AGRUPADOS ==================== --}}
    <div class="row">
        <div class="col-md-6">
            <div class="report-card">
                <div class="section-title">
                    <span>Ventas por Vendedor</span>
                    <small>{{$fecha1}} — {{$fecha2}}</small>
                </div>
                <div class="card-body">
                    <table class="tbl-report">
                        <thead>
                        <tr>
                            <th class="text-left" style="width:50%">Vendedor</th>
                            <th style="width:25%">Cantidad</th>
                            <th style="width:25%">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $tcant = $tventa = 0; @endphp
                        @if(isset($vendedores) && count($vendedores) > 0)
                            @foreach($vendedores as $vendedor)
                                @php
                                    $tcant  += $vendedor['cant'];
                                    $tventa += $vendedor['venta'];
                                @endphp
                                <tr>
                                    <td>{{ $vendedor['descrip'] }}</td>
                                    <td class="center">{{ $vendedor['cant'] }}</td>
                                    <td class="num">{{ number_format($vendedor['venta'],2,',','.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="empty-state">Sin datos.</td>
                            </tr>
                        @endif
                        </tbody>
                        <tfoot>
                        <tr class="total-row">
                            <td>Totales</td>
                            <td class="center">{{ $tcant }}</td>
                            <td class="num">{{ number_format($tventa,2,',','.') }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="report-card">
                <div class="section-title">
                    <span>Ventas por Sucursal</span>
                    <small>{{$fecha1}} — {{$fecha2}}</small>
                </div>
                <div class="card-body">
                    <table class="tbl-report">
                        <thead>
                        <tr>
                            <th class="text-left" style="width:50%">Sucursal</th>
                            <th style="width:25%">Cantidad</th>
                            <th style="width:25%">Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php $tcant = $tventa = 0; @endphp
                        @if(isset($vsucursal) && count($vsucursal) > 0)
                            @foreach($vsucursal as $vendedor)
                                @php
                                    $tcant  += $vendedor['cant'];
                                    $tventa += $vendedor['venta'];
                                @endphp
                                <tr>
                                    <td>{{ $vendedor['descrip'] }}</td>
                                    <td class="center">{{ $vendedor['cant'] }}</td>
                                    <td class="num">{{ number_format($vendedor['venta'],2,',','.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="empty-state">Sin datos.</td>
                            </tr>
                        @endif
                        </tbody>
                        <tfoot>
                        <tr class="total-row">
                            <td>Totales</td>
                            <td class="center">{{ $tcant }}</td>
                            <td class="num">{{ number_format($tventa,2,',','.') }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        function loadingreport(action){
            $('#form1').attr('action', action);
            $('#contentReport').html(
                '<div style="padding:16px; text-align:center;">' +
                '<div class="spinner-border text-primary" role="status">' +
                '<span class="visually-hidden">Cargando...</span>' +
                '</div>' +
                '<div style="margin-top:8px; color:#6b7c8c; font-size:.85rem;">Cargando...</div>' +
                '</div>'
            );
            $('#form1').submit();
        }
    </script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
