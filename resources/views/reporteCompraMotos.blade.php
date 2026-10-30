@extends('layouts.master')

@section('title')
    Reporte de Compras de Motos por Sucursal
@endsection

@section('css')
    <style>
        .table-motos-compras {
            --tb-table-hover-bg: #e3f2fd !important;
            --tb-table-hover-color: #000 !important;
        }

        .table-hover > tbody > tr:hover > * {
            --tb-table-accent-bg: #e3f2fd !important;
            color: #000 !important;
        }

        .table-motos-compras th {
            background-color: #0072c5 !important;
            color: white !important;
            font-weight: 500;
            white-space: nowrap;
        }

        .table-motos-compras td {
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

        .card-header-motos-compras {
            background-color: #0072c5;
            color: white;
        }

        .card-header-motos-compras h4 {
            color: white !important;
        }

        .card-header-motos-compras small {
            color: rgba(255, 255, 255, 0.8);
        }

        .stats-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0072c5;
        }

        .link-sucursal {
            color: #0072c5;
            text-decoration: none;
        }

        .link-sucursal:hover {
            text-decoration: underline;
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
                            <i class="bi bi-bicycle"></i> Motos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" onclick="tabchangeto('2')" href="javascript:;" role="tab" id="tab2">
                            <i class="bi bi-grid-3x3-gap-fill"></i> Modelos de Motos
                        </a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                {{-- Pestaña 1: Motos Compradas --}}
                <div class="tab-pane active" id="tabcontent1" role="tabpanel">
                    <div class="card">
                        <div class="card-header card-header-motos-compras">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1"  style="color: #0072c5 !important;">
                                    Motos Compradas
                                    <small class="text-muted small"><i class="bi bi-calendar-range"></i> {{ str_replace('to', ' al ', $fechasreport) }}</small>
                                </h4>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportarExcel('tablaMotosCompradas')">
                                    <i class="bi bi-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 10px 0;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-motos-compras" id="tablaMotosCompradas">
                                    <thead>
                                    <tr>
                                        <th class="text-start" style="min-width: 200px;">MOTO</th>
                                        @foreach($sucursales as $sucursal)
                                            <th class="text-center" style="min-width: 100px;">{{ $sucursal }}</th>
                                        @endforeach
                                        <th class="text-center" style="min-width: 100px; {{ count($sucursales) == 1 ? 'display: none' : '' }}">TOTAL</th>
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
                                    @if(isset($productosOrdenados))
                                        @foreach($productosOrdenados as $indexprod => $producto)
                                            <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                                <td class="text-start">
                                                    {{ $producto }}
                                                </td>
                                                @foreach($sucursales as $indexsucu => $sucursal)
                                                    @php
                                                        if(!isset($tantassucuprod[$indexsucu])) $tantassucuprod[$indexsucu] = 0;
                                                        $cantidad = isset($ventaSucuProdOrdenado[$indexprod][$indexsucu]) ? $ventaSucuProdOrdenado[$indexprod][$indexsucu] : 0;
                                                        $tantassucuprod[$indexsucu] += $cantidad;
                                                    @endphp
                                                    <td class="text-center">
                                                        {{ $cantidad > 0 ? number_format($cantidad, 0) : '' }}
                                                    </td>
                                                @endforeach
                                                <td class="text-center fw-bold" style="{{ count($sucursales) == 1 ? 'display: none' : '' }}">
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
                                        <td class="text-center fw-bold text-white" style="{{ count($sucursales) == 1 ? 'display: none' : '' }}">
                                            {{ number_format($tantosprd, 0) }}
                                        </td>
                                </table>
                                </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pestaña 2: Modelos de Motos Compradas --}}
                <div class="tab-pane" id="tabcontent2" role="tabpanel">
                    <div class="card">
                        <div class="card-header card-header-motos-compras">
                            <div class="d-flex align-items-center">
                                <h4 class="card-title mb-0 flex-grow-1" style="color: #0072c5 !important;">
                                    Modelos de Motos Compradas
                                    <small class="text-muted small"><i class="bi bi-calendar-range"></i> {{ str_replace('to', ' al ', $fechasreport) }}</small>
                                </h4>
                                <button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="exportarExcel('tablaModelosCompradas')">
                                    <i class="bi bi-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="padding: 10px 0;">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-motos-compras" id="tablaModelosCompradas">
                                    <thead>
                                    <tr>
                                        <th class="text-start" style="min-width: 200px;">MODELO</th>
                                        @foreach($sucursales as $sucursal)
                                            <th class="text-center" style="min-width: 100px;">{{ $sucursal }}</th>
                                        @endforeach
                                        <th class="text-center" style="min-width: 100px; {{ count($sucursales) == 1 ? 'display: none' : '' }}">TOTAL</th>
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
                                    @if(isset($instanciasOrdenadas))
                                        @foreach($instanciasOrdenadas as $indexinst => $instancia)
                                            <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                                <td class="text-start">
                                                   {{ $instancia }}
                                                </td>
                                                @foreach($sucursales as $indexsucu => $sucursal)
                                                    @php
                                                        if(!isset($tantassucuprod[$indexsucu])) $tantassucuprod[$indexsucu] = 0;
                                                        $cantidad = isset($ventainsprodsucuOrdenado[$indexinst][$indexsucu]) ? $ventainsprodsucuOrdenado[$indexinst][$indexsucu] : 0;
                                                        $tantassucuprod[$indexsucu] += $cantidad;
                                                    @endphp
                                                    <td class="text-center">
                                                        {{ $cantidad > 0 ? number_format($cantidad, 0) : '' }}
                                                    </td>
                                                @endforeach
                                                <td class="text-center fw-bold" style="{{ count($sucursales) == 1 ? 'display: none' : '' }}">
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
                                        <td class="text-center fw-bold text-white" style="{{ count($sucursales) == 1 ? 'display: none' : '' }}">
                                            {{ number_format($tantosprd, 0) }}
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
                    <form method="post" name="form1" id="form1" action="/reporte/compra/motos">
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
                                <small class="text-muted"> motos compradas</small>
                            </div>
                            <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#ver{{ $indexsucu }}"
                               class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Ver Detalle
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Modal de detalle por sucursal --}}
                <div class="modal fade" id="ver{{ $indexsucu }}" aria-hidden="true" tabindex="-1">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #0072c5; color: white;">
                                <h5 class="modal-title text-white">
                                    <i class="bi bi-shop"></i> Detalle de Compras - {{ $sucursal }}
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-primary">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Proveedor</th>
                                            <th class="text-center">Documento</th>
                                            <th>Producto</th>
                                            <th class="text-center">Cantidad</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(isset($ventaSucursalProveedor[$indexsucu]['proveedores']) && count($ventaSucursalProveedor[$indexsucu]['proveedores']) > 0)
                                            @foreach($ventaSucursalProveedor[$indexsucu]['proveedores'] as $row)
                                                <tr>
                                                    <td>{{ $row['fecha'] }}</td>
                                                    <td>{{ $row['proveedor'] }}</td>
                                                    <td class="text-center">{{ $row['NumeroD'] }}</td>
                                                    <td>{{ $row['producto'] }}</td>
                                                    <td class="text-center">{{ number_format($row['cantidad'], 0) }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                    <p class="mt-2">No hay compras registradas</p>
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

            <hr>

            {{-- Filtro por modelo padre --}}
            @if(isset($instanciaspadre) && count($instanciaspadre) > 0)
                <div class="card mt-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel"></i> Filtrar por Modelo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($instanciaspadre as $indexpadre => $padre)
                                <div class="col-sm-12 col-lg-6 mb-2">
                                    <button type="button"
                                            onclick="filtrarPorPadre({{ $indexpadre }})"
                                            style="font-size: 11px; text-align: left"
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
        function exportarExcel(tableId) {
            var table = document.getElementById(tableId);
            if (!table) return;

            var html = table.outerHTML;
            var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
            var link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'reporte_compras_motos.xls');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Inicializar pestaña
        tabchangeto(1);
    </script>
@endsection
