@extends('layouts.master')
@section('title')
    Existencias en Consignación por Depósito
@endsection

@section('css')
    <style>
        .table-consignacion {
            font-size: 0.85rem;
        }
        .table-consignacion thead th {
            vertical-align: middle;
            text-align: center;
            padding: 10px 8px;
            background-color: #0072c5;
            color: white;
            border: 1px solid #0056a7;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .table-consignacion tbody td {
            vertical-align: middle;
            text-align: center;
            padding: 8px 6px;
            border: 1px solid #dee2e6;
        }
        .table-consignacion tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .deposito-nombre {
            font-weight: 700;
            text-align: left;
            background-color: #cfe2ff !important;
            font-size: 1.1rem;
        }
        .sucursal-nombre {
            font-weight: 600;
            text-align: left;
            background-color: #e9ecef !important;
            padding-left: 30px !important;
        }
        .marca-nombre {
            font-weight: 400;
            text-align: left;
            background-color: #f8f9fa !important;
            padding-left: 60px !important;
        }
        .total-row {
            font-weight: bold;
            background-color: #d4edda;
        }
        .total-general {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }
        .badge-cantidad {
            font-size: 0.85rem;
            padding: 5px 12px;
            border-radius: 20px;
        }
        .badge-deposito {
            background-color: #0d6efd;
        }
        .badge-sucursal {
            background-color: #6c757d;
        }
        .badge-marca {
            background-color: #17a2b8;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .card-deposito {
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .card-deposito .card-header {
            background-color: #e7f1ff;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .table-responsive {
            max-height: 800px;
            overflow-y: auto;
        }
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-0">
                                <i class="bi bi-box-seam me-2"></i>EXISTENCIAS EN CONSIGNACIÓN POR DEPÓSITO
                            </h4>
                            <p class="text-white-50 mb-0 small">Distribución de inventario en consignación por depósito, sucursal y marca</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-light me-2"   onclick="   capture('#tablaConsignacion')">
                                <i class="bi bi-camera"></i> Capturar
                            </button>
                            <button onclick="exportToExcel()" class="btn btn-sm btn-light me-2">
                                <i class="bi bi-download me-1"></i> Exportar Excel
                            </button>
                            <button onclick="window.print()" class="btn btn-sm btn-light">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Resumen rápido -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="alert alert-info text-center">
                                <i class="bi bi-boxes fs-4 d-block"></i>
                                <strong>{{ count($datosPorDeposito) }}</strong> <br> Depósitos en consignación
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-success text-center">
                                <i class="bi bi-box-seam fs-4 d-block"></i>
                                <strong>{{ number_format($totalGeneral, 0, ',', '.') }}</strong>
                                <br> Unidades en consignación
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-warning text-center">
                                <i class="bi bi-building fs-4 d-block"></i>
                                <strong>{{ count($totalesSucursal) }}</strong>
                                <br> Sucursales con consignación
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="alert alert-secondary text-center">
                                <i class="bi bi-tags fs-4 d-block"></i>
                                <strong>{{ count($totalesMarca) }}</strong>
                                <br> Marcas en consignación
                            </div>
                        </div>
                    </div>

                    @if(count($datosPorDeposito) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-consignacion" id="tablaConsignacion">
                                <thead>
                                <tr>
                                    <th style="min-width: 200px;">
                                        <i class="bi bi-box me-1"></i> DEPÓSITO / SUCURSAL / MARCA
                                    </th>
                                    @foreach($sucursales as $sucursal)
                                        <th class="text-center">
                                            <i class="bi bi-building me-1"></i>
                                            {{ str_replace('SARA', '', $sucursal->descrip) }}
                                        </th>
                                    @endforeach
                                    <th class="text-center" style="background-color: #28a745; min-width: 100px;">
                                        <i class="bi bi-calculator me-1"></i> TOTAL
                                    </th>

                                </tr>
                                </thead>
                                <tbody id="tablaBody">
                                @php
                                    $totalesSucursales = array_fill_keys($sucursales->pluck('id')->toArray(), 0);
                                    $costoTotal = 0;
                                @endphp

                                @foreach($datosPorDeposito as $depositoKey => $deposito)
                                    @php
                                        $totalDeposito = 0;
                                        $costoDeposito = 0;
                                    @endphp

                                        <!-- Fila de Depósito -->
                                    <tr class="fila-deposito" data-deposito="{{ strtolower($deposito['nombre']) }}">
                                        <td class="deposito-nombre" style="text-align: left">
                                            <i class="bi bi-box me-2 text-primary"></i>
                                            <strong>{{ $deposito['nombre'] }}</strong>
                                            <span class="badge bg-primary badge-cantidad ms-2">
                                                {{ array_sum(array_map(function($s) {
                                                    return array_sum(array_column($s['marcas'], 'cantidad'));
                                                }, $deposito['sucursales'])) }}
                                            </span>
                                        </td>
                                        @foreach($sucursales as $sucursal)
                                            <td class="text-center">
                                                @if(isset($deposito['sucursales'][$sucursal->id]))
                                                    @php
                                                        $totalSucursal = array_sum(array_column($deposito['sucursales'][$sucursal->id]['marcas'], 'cantidad'));
                                                        $totalDeposito += $totalSucursal;
                                                        $totalesSucursales[$sucursal->id] += $totalSucursal;
                                                    @endphp

                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center total-row">

                                        </td>

                                    </tr>

                                    <!-- Filas de Sucursales dentro del Depósito -->
                                    @foreach($deposito['sucursales'] as $sucursalKey => $sucursalData)
                                        <tr class="fila-sucursal" data-deposito="{{ strtolower($deposito['nombre']) }}">
                                            <td class="sucursal-nombre" style="text-align: left">
                                                <i class="bi bi-building me-2 text-secondary"></i>
                                                {{ $sucursalData['nombre'] }}
                                                <span class="badge bg-secondary badge-cantidad ms-2">
                                                    {{ array_sum(array_column($sucursalData['marcas'], 'cantidad')) }}
                                                </span>
                                            </td>
                                            @foreach($sucursales as $sucursal)
                                                <td class="text-center">
                                                    @if($sucursal->id == $sucursalKey)

                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="text-center">

                                            </td>

                                        </tr>

                                        <!-- Filas de Marcas dentro de la Sucursal -->
                                        @foreach($sucursalData['marcas'] as $marcaKey => $marcaData)
                                            <tr class="fila-marca" data-deposito="{{ strtolower($deposito['nombre']) }}">
                                                <td class="marca-nombre" style="text-align: left">
                                                    <i class="bi bi-tag me-2 text-warning"></i>
                                                    {{ $marcaData['nombre'] }}
                                                    <span class="badge bg-warning text-dark badge-cantidad ms-2">
                                                        {{ number_format($marcaData['cantidad'], 0, ',', '.') }}
                                                    </span>
                                                </td>
                                                @foreach($sucursales as $sucursal)
                                                    <td class="text-center">
                                                        @if($sucursal->id == $sucursalKey)
                                                            <span class="badge bg-warning text-dark badge-cantidad">
                                                                {{ number_format($marcaData['cantidad'], 0, ',', '.') }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark badge-cantidad">
                                                        {{ number_format($marcaData['cantidad'], 0, ',', '.') }}
                                                    </span>
                                                </td>

                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach

                                <!-- Fila de Totales Generales -->
                                <tr style="background-color: #e9ecef; font-weight: bold;">
                                    <td class="text-end fw-bold">
                                        <i class="bi bi-calculator me-1"></i> TOTAL GENERAL
                                    </td>
                                    @foreach($sucursales as $sucursal)
                                        <td class="text-center total-sucursal">

                                        </td>
                                    @endforeach
                                    <td class="text-center total-general">
                                        <span class="badge bg-light text-dark fs-6">
                                            {{ number_format($totalGeneral, 0, ',', '.') }}
                                        </span>
                                    </td>

                                </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle me-2"></i>
                            No hay productos en consignación para mostrar
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Búsqueda de depósitos
        $('#buscarDeposito').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            $('.fila-deposito').each(function() {
                var deposito = $(this).data('deposito');
                if (deposito.includes(searchTerm)) {
                    $(this).show();
                    // Mostrar también las filas hijas
                    $(this).nextUntil('.fila-deposito').show();
                } else {
                    $(this).hide();
                    // Ocultar también las filas hijas
                    $(this).nextUntil('.fila-deposito').hide();
                }
            });
        });

        // Filtro por sucursal
        $('#filtrarSucursal').on('change', function() {
            var sucursalId = $(this).val();
            if (sucursalId === '') {
                $('.fila-deposito, .fila-sucursal, .fila-marca').show();
                return;
            }

            $('.fila-deposito, .fila-sucursal, .fila-marca').hide();

            // Mostrar filas de depósito que tienen la sucursal
            $('.fila-sucursal').each(function() {
                // Esta lógica necesita ser mejorada para identificar la sucursal
            });
        });

        // Filtro por marca
        $('#filtrarMarca').on('change', function() {
            var marcaId = $(this).val();
            if (marcaId === '') {
                $('.fila-deposito, .fila-sucursal, .fila-marca').show();
                return;
            }
            // Implementar filtro por marca
        });

        // Limpiar filtros
        function limpiarFiltros() {
            $('#buscarDeposito').val('');
            $('#filtrarSucursal').val('');
            $('#filtrarMarca').val('');
            $('.fila-deposito, .fila-sucursal, .fila-marca').show();
        }

        // Exportar a Excel
        function exportToExcel() {
            var table = document.getElementById('tablaConsignacion');
            var html = table.outerHTML;
            var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
            var link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', 'existencias_consignacion_depositos.xls');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Inicializar tooltips
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    </script>
@endsection
