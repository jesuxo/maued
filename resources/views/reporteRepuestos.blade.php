@extends('layouts.master')

@section('title')
    Reporte de Ventas de Repuestos por Sucursal
@endsection

@section('css')
    <style>
        .table-repuestos {
            --tb-table-hover-bg: #e3f2fd !important;
            --tb-table-hover-color: #000 !important;
        }

        .table-hover > tbody > tr:hover > * {
            --tb-table-accent-bg: #e3f2fd !important;
            color: #000 !important;
        }

        .table-repuestos th {
            background-color: #0072c5 !important;
            color: white !important;
            font-weight: 500;
            white-space: nowrap;
        }

        .table-repuestos td {
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

        .card-header-repuestos {
            background-color: #0072c5;
            color: white;
        }

        .card-header-repuestos h4 {
            color: white !important;
        }

        .card-header-repuestos small {
            color: rgba(255, 255, 255, 0.8);
        }

        .btn-outline-repuestos {
            background: transparent;
            border: 1px solid #0072c5;
            color: #0072c5;
        }

        .btn-outline-repuestos:hover {
            background: #0072c5;
            color: white;
        }

        .badge-repuestos {
            background-color: #0072c5;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .badge-repuestos-light {
            background-color: #e3f2fd;
            color: #0072c5;
        }

        .stats-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0072c5;
        }

        .switch-custom {
            background-color: #0072c5;
        }

        .form-switch .form-check-input:checked {
            background-color: #0072c5;
            border-color: #0072c5;
        }

        .text-primary-custom {
            color: #0072c5 !important;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            {{-- Pestañas de navegación --}}
            <div class="d-flex align-items-center flex-wrap gap-3 mb-3">
                <ul class="nav nav-pills flex-grow-1 mb-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" onclick="tabchangeto('1')" href="javascript:;" role="tab" id="tab1">
                            <i class="bi bi-tools"></i> Repuestos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="tabchangeto('2')" href="javascript:;" role="tab" id="tab2">
                            <i class="bi bi-grid-3x3-gap-fill"></i> Área de Repuestos
                        </a>
                    </li>
                    @php
                        $ii = 3;
                    @endphp
                    @foreach($saoper as $oper)
                        @if($oper['montovta'] > 0 && isset($oper['descrip']) && $oper['descrip'] != '')
                            <li class="nav-item">
                                <a class="nav-link" onclick="tabchangeto('{{ $ii }}')" href="javascript:;" role="tab" id="tab{{ $ii }}">
                                    <i class="bi bi-box-seam"></i> {{ $oper['descrip'] }}
                                </a>
                            </li>
                            @php $ii++; @endphp
                        @endif
                    @endforeach
                </ul>
            </div>

            <div class="tab-content">
                {{-- Pestaña 1: Repuestos --}}
                <div class="tab-pane active" id="tabcontent1" role="tabpanel">
                    <div class="card">
                        <div class="card-header card-header-repuestos">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1"  style="color: #0072c5 !important;">
                                    Ventas de Repuestos
                                    <small class="text-muted small"><i class="bi bi-calendar-range"></i> {{ str_replace('to', ' al ', $fechasreport) }}</small>
                                </h4>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportarExcel('tablaRepuestos')">
                                    <i class="bi bi-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 10px 0;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-repuestos" id="tablaRepuestos">
                                    <thead>
                                    <tr>
                                        <th class="text-start" style="min-width: 200px;">REPUESTO</th>
                                        @foreach($sucursales as $sucursal)
                                            <th class="text-center" style="min-width: 100px;">{{ $sucursal }}</th>
                                        @endforeach
                                        <th class="text-center" style="min-width: 100px;">TOTAL</th>
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
                                                        $value = isset($ventaSucuProd[$indexprod][$indexsucu]) ? $ventaSucuProd[$indexprod][$indexsucu] : 0;
                                                        $tantassucuprod[$indexsucu] += $value;
                                                        $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : ($value > 0 ? number_format($value, 0, ',', '.') : '');
                                                    @endphp
                                                    <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }}">
                                                        {{ ($value != 0) ? $auxval : '' }}
                                                    </td>
                                                @endforeach
                                                @php
                                                    $value = isset($ventaProductos[$indexprod]) ? $ventaProductos[$indexprod] : 0;
                                                    $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                                @endphp
                                                <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold">
                                                    {{ ($value != 0) ? $auxval : '' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr style="background-color: #0072c5;">
                                        <td class="text-end fw-bold text-white">TOTALES</td>
                                        @foreach($sucursales as $indexsucu => $sucursal)
                                            @php
                                                $value = isset($tantassucuprod[$indexsucu]) ? $tantassucuprod[$indexsucu] : 0;
                                                $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                            @endphp
                                            <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold text-white">
                                                {{ ($value != 0) ? $auxval : '' }}
                                            </td>
                                        @endforeach
                                        @php
                                            $value = isset($tantosprd) ? $tantosprd : 0;
                                            $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                        @endphp
                                        <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold text-white">
                                            {{ ($value != 0) ? $auxval : '' }}
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pestaña 2: Área de Repuestos --}}
                <div class="tab-pane" id="tabcontent2" role="tabpanel">
                    <div class="card">
                        <div class="card-header card-header-repuestos">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1" style="color: #0072c5 !important;">
                                    Repuestos Vendidos por Área
                                    <small class="text-muted small"><i class="bi bi-calendar-range"></i> {{ str_replace('to', ' al ', $fechasreport) }}</small>
                                </h4>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportarExcel('tablaAreaRepuestos')">
                                    <i class="bi bi-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 10px 0;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-repuestos" id="tablaAreaRepuestos">
                                    <thead>
                                    <tr>
                                        <th class="text-start" style="min-width: 200px;">ÁREA</th>
                                        @foreach($sucursales as $sucursal)
                                            <th class="text-center" style="min-width: 100px;">{{ $sucursal }}</th>
                                        @endforeach
                                        <th class="text-center" style="min-width: 100px;">TOTAL</th>
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
                                                        $value = isset($ventainsprodsucu[$indexinst][$indexsucu]) ? $ventainsprodsucu[$indexinst][$indexsucu] : 0;
                                                        $tantassucuprod[$indexsucu] += $value;
                                                        $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : ($value > 0 ? number_format($value, 0, ',', '.') : '');
                                                    @endphp
                                                    <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }}">
                                                        {{ ($value != 0) ? $auxval : '' }}
                                                    </td>
                                                @endforeach
                                                @php
                                                    $value = isset($ventaInstancia[$indexinst]) ? $ventaInstancia[$indexinst] : 0;
                                                    $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                                @endphp
                                                <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold">
                                                    {{ ($value != 0) ? $auxval : '' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr style="background-color: #0072c5;">
                                        <td class="text-end fw-bold text-white">TOTALES</td>
                                        @foreach($sucursales as $indexsucu => $sucursal)
                                            @php
                                                $value = isset($tantassucuprod[$indexsucu]) ? $tantassucuprod[$indexsucu] : 0;
                                                $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                            @endphp
                                            <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold text-white">
                                                {{ ($value != 0) ? $auxval : '' }}
                                            </td>
                                        @endforeach
                                        @php
                                            $value = isset($tantosprd) ? $tantosprd : 0;
                                            $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                        @endphp
                                        <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold text-white">
                                            {{ ($value != 0) ? $auxval : '' }}
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pestañas dinámicas por operador --}}
                @php
                    $ii = 3;
                @endphp
                @foreach($saoper as $indexoper => $oper)
                    @if($oper['montovta'] > 0 && isset($oper['descrip']) && $oper['descrip'] != '')
                        <div class="tab-pane" id="tabcontent{{ $ii }}" role="tabpanel">
                            <div class="card">
                                <div class="card-header card-header-repuestos">
                                    <div class="d-flex align-items-center">
                                        <h4 class="card-title mb-0 flex-grow-1" style="color: #0072c5 !important;">
                                            {{ $oper['descrip'] }}
                                            <small class="text-muted small"><i class="bi bi-calendar-range"></i> {{ str_replace('to', ' al ', $fechasreport) }}</small>
                                        </h4>
                                        <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportarExcel('tablaOperador{{ $ii }}')">
                                            <i class="bi bi-file-excel"></i> Exportar
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="padding: 10px 0;">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-repuestos" id="tablaOperador{{ $ii }}">
                                            <thead>
                                            <tr>
                                                <th class="text-start" style="min-width: 200px;">REPUESTO</th>
                                                @foreach($sucursales as $indexsucu => $sucursal)
                                                    <th class="text-center" style="min-width: 100px;">{{ $sucursal }}</th>
                                                @endforeach
                                                <th class="text-center" style="min-width: 100px;">TOTAL</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php
                                                $arrayprods = [];
                                                $tantassucuprod = [];
                                                $tantosprd = 0;
                                                foreach($oper['productos'] as $indexsucu => $prodsucus){
                                                    foreach($prodsucus as $indexprd => $cantprod){
                                                        if(!in_array($indexprd, array_column($arrayprods, 'codprod'))){
                                                            array_push($arrayprods, ['codprod' => $indexprd]);
                                                        }
                                                        if(!isset($tantassucuprod[$indexsucu])) $tantassucuprod[$indexsucu] = 0;
                                                        $tantassucuprod[$indexsucu] += $cantprod;
                                                        $tantosprd += $cantprod;
                                                    }
                                                }
                                            @endphp
                                            @if(isset($arrayprods))
                                                @foreach($arrayprods as $indexprd => $item)
                                                    @php
                                                        $sumandotr = 0;
                                                    @endphp
                                                    <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                                        <td class="text-start">
                                                            <strong>{{ isset($productos[$item['codprod']]) ? $productos[$item['codprod']] : '' }}</strong>
                                                        </td>
                                                        @foreach($sucursales as $indexsucu => $sucursal)
                                                            @php
                                                                $value = isset($saoper[$indexoper]['productos'][$indexsucu][$item['codprod']]) ? $saoper[$indexoper]['productos'][$indexsucu][$item['codprod']] : 0;
                                                                $sumandotr += $value;
                                                                $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : ($value > 0 ? number_format($value, 0, ',', '.') : '');
                                                            @endphp
                                                            <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }}">
                                                                {{ ($value != 0) ? $auxval : '' }}
                                                            </td>
                                                        @endforeach
                                                        @php
                                                            $auxval = ($viewtype == 'monto') ? number_format($sumandotr, 2, ',', '.') : number_format($sumandotr, 0, ',', '.');
                                                        @endphp
                                                        <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold">
                                                            {{ ($sumandotr != 0) ? $auxval : '' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                            <tfoot>
                                            <tr style="background-color: #0072c5;">
                                                <td class="text-end fw-bold text-white">TOTALES</td>
                                                @foreach($sucursales as $indexsucu => $sucursal)
                                                    @php
                                                        $value = isset($tantassucuprod[$indexsucu]) ? $tantassucuprod[$indexsucu] : 0;
                                                        $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                                    @endphp
                                                    <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold text-white">
                                                        {{ ($value != 0) ? $auxval : '' }}
                                                    </td>
                                                @endforeach
                                                @php
                                                    $auxval = ($viewtype == 'monto') ? number_format($tantosprd, 2, ',', '.') : number_format($tantosprd, 0, ',', '.');
                                                @endphp
                                                <td class="{{ $viewtype == 'monto' ? 'text-end' : 'text-center' }} fw-bold text-white">
                                                    {{ ($tantosprd != 0) ? $auxval : '' }}
                                                </td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php $ii++; @endphp
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Panel lateral --}}
        <div class="col-lg-4">
            {{-- Filtro de fechas --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-funnel"></i> Filtros de Búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post" name="form1" id="form1" action="/reporte/repuestos">
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

                        <!-- Switch para cambiar entre cantidad y monto -->
                        <div class="mb-3">
                            <label class="form-label">Visualizar como:</label>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <span class="fw-semibold {{ $viewtype == 'cantidad' ? 'text-primary-custom' : 'text-muted' }}">
                                    <i class="bi bi-hash"></i> Cantidad
                                </span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           id="viewtypeSwitch"
                                           {{ $viewtype == 'monto' ? 'checked' : '' }}
                                           onchange="cambiarViewType()">
                                    <label class="form-check-label" for="viewtypeSwitch"></label>
                                </div>
                                <span class="fw-semibold {{ $viewtype == 'monto' ? 'text-primary-custom' : 'text-muted' }}">
                                    <i class="bi bi-currency-dollar"></i> Monto
                                </span>
                            </div>
                        </div>

                        <input type="hidden" value="{{ $inspadre ?? 0 }}" id="inspadre" name="inspadre">
                        <input type="hidden" name="viewtype" id="viewtype" value="{{ $viewtype }}">
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
                                @php
                                    $value = isset($ventaSucursal[$indexsucu]) ? $ventaSucursal[$indexsucu] : 0;
                                    $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                @endphp
                                <span class="stats-total">{{ $auxval }}</span>
                                <small class="text-muted">{{ $viewtype == 'monto' ? 'en ventas' : 'unidades' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <hr>

            {{-- Filtro por categoría padre --}}
            @if(isset($instanciaspadre) && count($instanciaspadre) > 0)
                <div class="card mt-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel"></i> Filtrar por Categoría
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($instanciaspadre as $indexpadre => $padre)
                                <div class="col-sm-12 col-lg-6 mb-2">
                                    <button type="button"
                                            onclick="filtrarPorPadre({{ $indexpadre }})" style="font-size: 11px; text-align: left"
                                            class="btn w-100 {{ (isset($inspadre) && $inspadre == $indexpadre) ? 'btn-primary' : 'btn-outline-primary' }}">
                                        {{ $padre }}
                                        @php
                                            $value = isset($ventainspadre[$indexpadre]) ? $ventainspadre[$indexpadre] : 0;
                                            $auxval = ($viewtype == 'monto') ? number_format($value, 2, ',', '.') : number_format($value, 0, ',', '.');
                                        @endphp
                                        <span style="float:right;" class="badge {{ (isset($inspadre) && $inspadre == $indexpadre) ? 'bg-light text-primary' : 'bg-primary text-white' }} ms-2 float-end">
                                            {{ $auxval }}
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

        // Cambiar entre vista cantidad/monto
        function cambiarViewType() {
            const switchElement = document.getElementById('viewtypeSwitch');
            const viewtypeInput = document.getElementById('viewtype');
            viewtypeInput.value = switchElement.checked ? 'monto' : 'cantidad';
            document.getElementById('form1').submit();
        }

        // Filtrar por categoría padre
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
        function exportarExcel(tableId) {
            var table = document.getElementById(tableId);
            if (!table) return;

            var html = table.outerHTML;
            var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
            var link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'reporte_repuestos.xls');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Inicializar pestaña
        tabchangeto(1);
    </script>
@endsection
