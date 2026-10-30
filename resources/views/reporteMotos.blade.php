@extends('layouts.master')

@section('title')
    Reporte de Ventas por Sucursal
@endsection

@section('css')
    <style>
        .table-motos {
            --tb-table-hover-bg: #e3f2fd !important;
            --tb-table-hover-color: #000 !important;
        }

        .table-hover > tbody > tr:hover > * {
            --tb-table-accent-bg: #e3f2fd !important;
            color: #000 !important;
        }
        .table-motos th {
            background-color: #0072c5 !important;
            color: white !important;
        }

        .table-motos td {
            border-color: #0072c5 !important;
            font-size: 12px;
        }

        .nav-pills .nav-link {
            background: #eee !important;
            border-bottom-right-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            color: #555;
        }

        .nav-pills .nav-link.active {
            background: #0072c5 !important;
            color: white !important;
            border-bottom-right-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }

        .nav-pills {
            border-bottom: 1px solid #0072c5;
        }

        .card-header-motos {
            background-color: #0072c5;
            color: white;
        }

        .card-header-motos h4 {
            color: white !important;
        }

        .card-header-motos small {
            color: rgba(255,255,255,0.8);
        }

        .btn-outline-motos {
            background: transparent;
            border: 1px solid #0072c5;
            color: #0072c5;
        }

        .btn-outline-motos:hover {
            background: #0072c5;
            color: white;
        }

        .badge-motos {
            background-color: #0072c5;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .badge-motos-light {
            background-color: #e3f2fd;
            color: #0072c5;
        }

        .link-sucursal {
            color: #0072c5;
            text-decoration: none;
        }

        .link-sucursal:hover {
            text-decoration: underline;
        }

        .stats-total {
            font-size: 1.2rem;
            color: #0072c5;
        }

        /* === KPIs GERENCIALES === */
        .kpi-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }

        .kpi-value {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1.1;
            margin: 2px 0;
        }

        .kpi-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            font-weight: 600;
        }

        .kpi-sub {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .kpi-icon-bg {
            position: absolute;
            right: 12px;
            top: 12px;
            font-size: 2.5rem;
            opacity: 0.12;
            pointer-events: none;
        }

        /* === NAVEGACIÓN DE FECHAS === */
        .nav-fechas {
            background: white;
            border-radius: 10px;
            padding: 12px 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .nav-fechas .fecha-actual {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0072c5;
            text-align: center;
        }

        .nav-fechas .fecha-actual small {
            display: block;
            font-size: 0.72rem;
            color: #6c757d;
            font-weight: 400;
        }

        .nav-fechas .btn-nav {
            border: 1px solid #0072c5;
            background: white;
            color: #0072c5;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 0.82rem;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .nav-fechas .btn-nav:hover {
            background: #0072c5;
            color: white;
        }

        .nav-fechas .btn-nav.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        /* === TOP CLIENTES === */
        .rank-badge {
            display: inline-flex;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #e3f2fd;
            color: #0072c5;
            font-weight: 700;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .rank-badge.gold   { background: #ffd700; color: #7a5c00; }
        .rank-badge.silver { background: #c0c0c0; color: #4a4a4a; }
        .rank-badge.bronze { background: #cd7f32; color: #fff; }
    </style>
@endsection

@section('content')

    {{-- ============================================================ --}}
    {{-- BARRA DE NAVEGACIÓN DE FECHAS --}}
    {{-- ============================================================ --}}
    <div class="nav-fechas mb-3">
        <div>
            @if($puedeAnterior)
                <a href="?{{ $qsNav($rangoAnterior[0], $rangoAnterior[1]) }}" class="btn-nav">
                    <i class="bi bi-chevron-double-left"></i>
                    {{ $rangoAnterior[0] }}
                    @if($rangoAnterior[0] != $rangoAnterior[1])
                        al {{ $rangoAnterior[1] }}
                    @endif
                </a>
            @else
                <span class="btn-nav disabled">
                    <i class="bi bi-chevron-double-left"></i> Sin anterior
                </span>
            @endif
        </div>

        <div class="fecha-actual">
            <i class="bi bi-calendar-range"></i>
            {{ $fecha1 }}
            @if($fecha1 != $fecha2)
                al {{ $fecha2 }}
            @endif
            <small>
                {{ $diasRango }} {{ $diasRango == 1 ? 'día' : 'días' }}
                · Comparado con {{ number_format($unidadesAnterior, 0) }} moto(s) del período anterior
            </small>
        </div>

        <div>
            @if($puedeSiguiente)
                <a href="?{{ $qsNav($rangoSiguiente[0], $rangoSiguiente[1]) }}" class="btn-nav">
                    @if($rangoSiguiente[0] != $rangoSiguiente[1])
                        {{ $rangoSiguiente[0] }} al {{ $rangoSiguiente[1] }}
                    @else
                        {{ $rangoSiguiente[0] }}
                    @endif
                    <i class="bi bi-chevron-double-right"></i>
                </a>
            @else
                <span class="btn-nav disabled">
                    Hoy <i class="bi bi-chevron-double-right"></i>
                </span>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- KPIs GERENCIALES --}}
    {{-- ============================================================ --}}
    <div class="row g-2 mb-3">

        {{-- KPI 1: Motos Vendidas --}}
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card p-3 position-relative" style="border-left: 4px solid #0072c5;">
                <i class="bi bi-bicycle kpi-icon-bg text-primary"></i>
                <div class="kpi-label">Motos Vendidas</div>
                <div class="kpi-value text-primary">{{ number_format($totalUnidades, 0) }}</div>
                <div class="kpi-sub">
                    en {{ $totalFacturas }} factura(s)
                </div>
                <div class="mt-2">
                    @if($variacionUnidades >= 0)
                        <span class="badge bg-success">
                            <i class="bi bi-arrow-up"></i>
                            +{{ number_format($variacionUnidades, 1) }}%
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="bi bi-arrow-down"></i>
                            {{ number_format($variacionUnidades, 1) }}%
                        </span>
                    @endif
                    <small class="text-muted ms-1">vs período ant.</small>
                </div>
            </div>
        </div>

        {{-- KPI 2: Monto Total --}}
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card p-3 position-relative" style="border-left: 4px solid #28a745;">
                <i class="bi bi-currency-dollar kpi-icon-bg text-success"></i>
                <div class="kpi-label">Monto Total</div>
                <div class="kpi-value text-success">${{ number_format($totalMonto, 2, ',', '.') }}</div>
                <div class="kpi-sub">
                    Costo de ventas
                </div>
                <div class="mt-2">
                    @if($variacionMonto >= 0)
                        <span class="badge bg-success">
                            <i class="bi bi-arrow-up"></i>
                            +{{ number_format($variacionMonto, 1) }}%
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="bi bi-arrow-down"></i>
                            {{ number_format($variacionMonto, 1) }}%
                        </span>
                    @endif
                    <small class="text-muted ms-1">vs ${{ number_format($montoAnterior, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>

        {{-- KPI 3: Ticket Promedio --}}
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card p-3 position-relative" style="border-left: 4px solid #ffc107;">
                <i class="bi bi-receipt kpi-icon-bg text-warning"></i>
                <div class="kpi-label">Ticket Promedio</div>
                <div class="kpi-value text-warning">${{ number_format($ticketPromedio, 2, ',', '.') }}</div>
                <div class="kpi-sub">
                    {{ number_format($unidadesPorFactura, 1) }} moto(s) por factura
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bi bi-calculator"></i>
                        ${{ number_format($totalMonto, 0, ',', '.') }} / {{ $totalFacturas }} fact.
                    </small>
                </div>
            </div>
        </div>

        {{-- KPI 4: Líderes del período --}}
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card p-3 position-relative" style="border-left: 4px solid #6f42c1;">
                <i class="bi bi-trophy kpi-icon-bg" style="color: #6f42c1;"></i>
                <div class="kpi-label">Top del período</div>

                @if($sucursalLider)
                    <div class="d-flex align-items-center mt-2 small">
                        <i class="bi bi-shop text-primary me-2"></i>
                        <span class="text-truncate" style="max-width: 110px;" title="{{ $sucursalLider['nombre'] }}">
                            {{ $sucursalLider['nombre'] }}
                        </span>
                        <span class="badge bg-primary ms-auto">{{ $sucursalLider['unidades'] }}</span>
                    </div>
                @endif

                @if($modeloLider)
                    <div class="d-flex align-items-center mt-1 small">
                        <i class="bi bi-bicycle text-success me-2"></i>
                        <span class="text-truncate" style="max-width: 110px;" title="{{ $modeloLider['nombre'] }}">
                            {{ $modeloLider['nombre'] }}
                        </span>
                        <span class="badge bg-success ms-auto">{{ $modeloLider['unidades'] }}</span>
                    </div>
                @endif

                @if($mejorDia)
                    <div class="d-flex align-items-center mt-1 small">
                        <i class="bi bi-calendar-check text-warning me-2"></i>
                        <span>{{ $mejorDia['fecha'] }}</span>
                        <span class="badge bg-warning text-dark ms-auto">{{ $mejorDia['unidades'] }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- CONTENIDO PRINCIPAL --}}
    {{-- ============================================================ --}}
    <div class="row">
        <div class="col-lg-8">
            {{-- Pestañas de navegación --}}
            <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
                <ul class="nav nav-pills flex-grow-1 mb-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" onclick="tabchangeto('1')" href="javascript:;" role="tab" id="tab1">
                            <i class="bi bi-bicycle"></i> Motos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="tabchangeto('2')" href="javascript:;" role="tab" id="tab2">
                            <i class="bi bi-grid-3x3-gap-fill"></i> Modelos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="tabchangeto('3')" href="javascript:;" role="tab" id="tab3">
                            <i class="bi bi-people-fill"></i> Top Clientes
                        </a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                {{-- ==================================================== --}}
                {{-- PESTAÑA 1: MOTOS VENDIDAS --}}
                {{-- ==================================================== --}}
                <div class="tab-pane active" id="tabcontent1" role="tabpanel">
                    <div class="card">
                        <div class="card-header card-header-motos">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1" style="color: #0072c5 !important;">
                                    Motos Vendidas
                                    <small class="text-muted small">
                                        <i class="bi bi-calendar-range"></i> {{ str_replace('to', ' al ', $fechasreport) }}
                                    </small>
                                </h4>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="capture('#tablapormoto')">
                                    <i class="bi bi-camera"></i> Capturar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportarExcel('tablapormoto')">
                                    <i class="bi bi-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 10px 0;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-motos" id="tablapormoto">
                                    <thead>
                                    <tr>
                                        <th class="text-start" style="width: 200px;" valign="middle">MOTO</th>
                                        @foreach($sucursales as $sucursal)
                                            <th class="text-center" valign="middle" style="width: 100px;">{{ $sucursal }}</th>
                                        @endforeach
                                        <th class="text-center" valign="middle" style="width: 100px;">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $tantosprd = 0;
                                        $tantassucuprod = [];
                                        if(isset($ventaProductos)){
                                            foreach ($ventaProductos as $cant){
                                                $tantosprd += $cant;
                                            }
                                        }
                                    @endphp

                                    @if(isset($productos))
                                        @foreach($productos as $indexprod => $producto)
                                            <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                                <td class="text-start">
                                                    {{ $producto }}
                                                </td>
                                                @foreach($sucursales as $indexsucu => $sucursal)
                                                    @php
                                                        if(!isset($tantassucuprod[$indexsucu])) $tantassucuprod[$indexsucu] = 0;
                                                        $cantidad = isset($ventaSucuProd[$indexprod][$indexsucu]) ? $ventaSucuProd[$indexprod][$indexsucu] : 0;
                                                        $tantassucuprod[$indexsucu] += $cantidad;
                                                    @endphp
                                                    <td class="text-center">
                                                        {{ $cantidad > 0 ? number_format($cantidad, 0) : '' }}
                                                    </td>
                                                @endforeach
                                                <td class="text-center fw-bold">
                                                    {{ number_format($ventaProductos[$indexprod], 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr style="background-color: #0072c5;">
                                        <td class="text-end fw-bold text-white">TOTALES</td>
                                        @foreach($sucursales as $indexsucu => $sucursal)
                                            <td class="text-center fw-bold text-white">
                                                {{ isset($tantassucuprod[$indexsucu]) ? number_format($tantassucuprod[$indexsucu], 0) : '' }}
                                            </td>
                                        @endforeach
                                        <td class="text-center fw-bold text-white">
                                            {{ number_format($tantosprd, 0) }}
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================================================== --}}
                {{-- PESTAÑA 2: MODELOS DE MOTOS VENDIDOS --}}
                {{-- ==================================================== --}}
                <div class="tab-pane" id="tabcontent2" role="tabpanel">
                    <div class="card">
                        <div class="card-header card-header-motos">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1" style="color: #0072c5 !important;">
                                    Modelos de Motos Vendidos
                                    <small class="text-muted small">
                                        <i class="bi bi-calendar-range"></i> {{ str_replace('to', ' al ', $fechasreport) }}
                                    </small>
                                </h4>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="capture('#tablamodelosmotos')">
                                    <i class="bi bi-camera"></i> Capturar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportarExcel('tablamodelosmotos')">
                                    <i class="bi bi-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 10px 0;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-motos" id="tablamodelosmotos">
                                    <thead>
                                    <tr>
                                        <th class="text-start" style="width: 150px;" valign="middle">MODELO</th>
                                        @foreach($sucursales as $sucursal)
                                            <th class="text-center" valign="middle" style="width: 100px;">{{ $sucursal }}</th>
                                        @endforeach
                                        <th class="text-center" valign="middle" style="width: 100px;">TOTAL</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $tantosprd = 0;
                                        $tantassucuprod = [];
                                        if(isset($ventaInstancia)){
                                            foreach ($ventaInstancia as $cant){
                                                $tantosprd += $cant;
                                            }
                                        }
                                    @endphp

                                    @if(isset($instancias))
                                        @foreach($instancias as $indexinst => $instancia)
                                            <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                                <td class="text-start">
                                                    {{ $instancia }}
                                                </td>
                                                @foreach($sucursales as $indexsucu => $sucursal)
                                                    @php
                                                        if(!isset($tantassucuprod[$indexsucu])) $tantassucuprod[$indexsucu] = 0;
                                                        $cantidad = isset($ventainsprodsucu[$indexinst][$indexsucu]) ? $ventainsprodsucu[$indexinst][$indexsucu] : 0;
                                                        $tantassucuprod[$indexsucu] += $cantidad;
                                                    @endphp
                                                    <td class="text-center">
                                                        {{ $cantidad > 0 ? number_format($cantidad, 0) : '' }}
                                                    </td>
                                                @endforeach
                                                <td class="text-center fw-bold">
                                                    {{ number_format($ventaInstancia[$indexinst], 0) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr style="background-color: #0072c5;">
                                        <td class="text-end fw-bold text-white">TOTALES</td>
                                        @foreach($sucursales as $indexsucu => $sucursal)
                                            <td class="text-center fw-bold text-white">
                                                {{ isset($tantassucuprod[$indexsucu]) ? number_format($tantassucuprod[$indexsucu], 0) : '' }}
                                            </td>
                                        @endforeach
                                        <td class="text-center fw-bold text-white">
                                            {{ number_format($tantosprd, 0) }}
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================================================== --}}
                {{-- PESTAÑA 3: TOP CLIENTES --}}
                {{-- ==================================================== --}}
                <div class="tab-pane" id="tabcontent3" role="tabpanel">
                    <div class="card">
                        <div class="card-header card-header-motos">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1" style="color: #0072c5 !important;">
                                    Top 10 Clientes — Motos
                                    <small class="text-muted small">
                                        <i class="bi bi-calendar-range"></i> {{ str_replace('to', ' al ', $fechasreport) }}
                                    </small>
                                </h4>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportarExcel('tablatopclientes')">
                                    <i class="bi bi-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 10px 0;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-motos" id="tablatopclientes">
                                    <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th>Cliente</th>
                                        <th style="width: 100px;" class="text-center">Código</th>
                                        <th style="width: 100px;" class="text-center">Unidades</th>
                                        <th style="width: 140px;" class="text-end">Monto USD</th>
                                        <th style="width: 100px;" class="text-center">% del Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($topClientes as $i => $cli)
                                        <tr class="{{ $i % 2 == 0 ? 'table-light' : '' }}">
                                            <td class="text-center">
                                                @if($i == 0)
                                                    <span class="rank-badge gold">1</span>
                                                @elseif($i == 1)
                                                    <span class="rank-badge silver">2</span>
                                                @elseif($i == 2)
                                                    <span class="rank-badge bronze">3</span>
                                                @else
                                                    <span class="rank-badge">{{ $i + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $cli['nombre'] }}</td>
                                            <td class="text-center"><code style="font-size: 11px;">{{ $cli['codclie'] }}</code></td>
                                            <td class="text-center fw-bold">{{ number_format($cli['unidades'], 0) }}</td>
                                            <td class="text-end">${{ number_format($cli['monto'], 2, ',', '.') }}</td>
                                            <td class="text-center">
                                                @if($totalUnidades > 0)
                                                    <span class="badge bg-primary">
                                                        {{ number_format(($cli['unidades'] / $totalUnidades) * 100, 1) }}%
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <i class="bi bi-inbox fs-3 d-block"></i>
                                                <p class="mt-2 mb-0">Sin ventas en el período</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    <tfoot>
                                    <tr style="background-color: #0072c5;">
                                        <td colspan="3" class="text-end fw-bold text-white">TOTALES</td>
                                        <td class="text-center fw-bold text-white">{{ number_format(collect($topClientes)->sum('unidades'), 0) }}</td>
                                        <td class="text-end fw-bold text-white">${{ number_format(collect($topClientes)->sum('monto'), 2, ',', '.') }}</td>
                                        <td class="text-center fw-bold text-white">100%</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- PANEL LATERAL --}}
        {{-- ============================================================ --}}
        <div class="col-lg-4">
            {{-- Filtro de fechas --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-funnel"></i> Filtros de Búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" name="form1" id="form1" action="/reporte/motos">
                        @csrf
                        @method('POST')

                        <div class="mb-3">
                            <label class="form-label">Rango de Fechas</label>
                            <div class="input-group">
                                <input type="text" class="form-control" data-provider="flatpickr"
                                       data-range-date="true" data-date-format="d/m/Y"
                                       name="fechasreport" readonly value="{{ $fechasreport }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Consultar
                                </button>
                            </div>
                        </div>

                        <input type="hidden" value="{{ $inspadre ?? 0 }}" id="inspadre" name="inspadre">
                    </form>
                </div>
            </div>

            {{-- Resumen por sucursal --}}
            @foreach($sucursales as $indexsucu => $sucursal)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-1">{{ $sucursal }}</h5>
                                <span class="stats-total">{{ number_format($ventaSucursal[$indexsucu], 0) }}</span>
                                <small class="text-muted"> motos vendidas</small>
                                @if($totalUnidades > 0)
                                    <span class="badge bg-primary ms-2">
                                        {{ number_format(($ventaSucursal[$indexsucu] / $totalUnidades) * 100, 1) }}%
                                    </span>
                                @endif
                            </div>
                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#ver{{ $indexsucu }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Ver Detalle
                            </a>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="ver{{ $indexsucu }}" aria-hidden="true" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #0072c5; color: white;">
                                <h5 class="modal-title text-white">
                                    <i class="bi bi-shop"></i> Detalle de Ventas - {{ $sucursal }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-primary">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Documento</th>
                                            <th>Producto</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Precio</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($ventaSucursalClientes[$indexsucu]['clientes']) && count($ventaSucursalClientes[$indexsucu]['clientes']) > 0)
                                            @foreach($ventaSucursalClientes[$indexsucu]['clientes'] as $row)
                                                <tr>
                                                    <td>{{ $row['fecha'] }}</td>
                                                    <td>{{ $row['cliente'] }}</td>
                                                    <td class="text-center">{{ $row['NumeroD'] }}</td>
                                                    <td>{{ $row['producto'] }}</td>
                                                    <td class="text-center">{{ number_format($row['cantidad'], 0) }}</td>
                                                    <td class="text-end">$ {{ number_format($row['costod'], 2, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                    <p class="mt-2">No hay ventas registradas</p>
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="bi bi-x-circle"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Filtro por marca padre --}}
            @if(isset($instanciaspadre) && count($instanciaspadre) > 0)
                <hr>
                <div class="card mt-3" id="modelosvendidos">
                    <div class="card-header bg-primary text-white d-flex justify-content-between">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel"></i> <span id="titulocontentmarcas">Filtrar por Marca</span>
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-primary me-2"
                                style="color: white !important;"
                                onclick="$('#titulocontentmarcas').html('Marcas Vendidas'); $(this).hide(); capture('#modelosvendidos')">
                            <i class="bi bi-camera"></i> Capturar
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($instanciaspadre as $indexpadre => $padre)
                                <div class="col-sm-12 col-lg-6 mb-2 text-start">
                                    <button type="button" style="font-size: 11px; text-align: left"
                                            onclick="filtrarPorPadre({{ $indexpadre }})"
                                            class="btn w-100 {{ (isset($inspadre) && $inspadre == $indexpadre) ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ $padre }}
                                        <span style="float:right;" class="badge {{ (isset($inspadre) && $inspadre == $indexpadre) ? 'bg-light text-primary' : 'bg-primary text-white' }} ms-2">
                                            {{ $ventainspadre[$indexpadre] ?? 0 }}
                                        </span>
                                    </button>
                                </div>
                            @endforeach
                            @if(isset($inspadre) && $inspadre > 0)
                                <div class="col-12 mt-2">
                                    <button type="button" onclick="limpiarFiltro()" class="btn btn-sm btn-secondary w-100">
                                        <i class="bi bi-x-circle"></i> Limpiar Filtro
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('build/js/app.js') }}"></script>
    <script>
        // Inicializar flatpickr
        $(document).ready(function() {
            if ($('[data-provider="flatpickr"]').length) {
                flatpickr("[data-provider='flatpickr']", {
                    mode: "range",
                    dateFormat: "d/m/Y",
                    @if(isset($fechasreport) && $fechasreport)
                    defaultDate: "{{ $fechasreport }}"
                    @endif
                });
            }
        });

        // Cambiar entre pestañas
        function tabchangeto(number) {
            $('.nav-link').removeClass('active');
            $('#tab' + number).addClass('active');
            $('.tab-pane').removeClass('active');
            $('#tabcontent' + number).addClass('active');
        }

        // Filtrar por modelo padre
        function filtrarPorPadre(index) {
            $('#inspadre').val(index);
            $('#form1').submit();
        }

        // Limpiar filtro
        function limpiarFiltro() {
            $('#inspadre').val(0);
            $('#form1').submit();
        }

        // Exportar a Excel
        function exportarExcel(table) {
            var table = document.getElementById(table);
            var html = table.outerHTML;
            var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
            var link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'reporte_motos_' + new Date().toISOString().slice(0, 10) + '.xls');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Inicializar pestaña
        tabchangeto(1);
    </script>
@endsection
