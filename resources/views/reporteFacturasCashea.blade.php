@extends('layouts.master')
@section('title')
    REPORTE DE FACTURAS PENDIENTES
@endsection
@section('css')
    <style>
        .botoncal {
            background: transparent;
            border: none;
            color: white;
        }
        .botoncal:hover {
            font-size: 13px;
        }
        .card-body {
            padding: 0.75rem !important;
        }
        .table th, .table td {
            padding: 0.4rem 0.5rem !important;
            vertical-align: middle !important;
            font-size: 0.85rem;
        }
        .table thead th {
            background: #2c3e50;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
            white-space: nowrap;
        }
        .table tbody td {
            text-align: center;
        }
        .table .text-right {
            text-align: right !important;
        }
        .card-header {
            padding: 0.6rem 1rem !important;
        }
        .card-header h4 {
            font-size: 1.1rem;
            margin: 0;
        }
        .filtros-row {
            margin-bottom: 0.5rem !important;
        }
        .filtros-row .col-md-3 {
            padding: 0 0.4rem;
        }
        .filtros-row label {
            font-size: 0.75rem;
            margin-bottom: 0.1rem;
            font-weight: 600;
            color: #555;
        }
        .filtros-row .form-control, .filtros-row .form-select {
            height: 32px;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
        }
        .filtros-row .btn {
            height: 32px;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
        }
        .totales-card {
            padding: 0.5rem 0.8rem !important;
            margin-bottom: 0.5rem;
        }
        .totales-card h5 {
            font-size: 0.7rem;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }
        .totales-card h4 {
            font-size: 1.2rem;
            margin: 0;
            font-weight: 700;
        }
        .badge-saldo {
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
        }
        .table .text-danger {
            font-weight: 700;
        }
        .fila-devolucion {
            background-color: #fff5f5 !important;
        }
        .fila-devolucion:hover {
            background-color: #ffe8e8 !important;
        }
        .link-factura {
            font-weight: 600;
            color: #2c3e50;
            text-decoration: none;
        }
        .link-factura:hover {
            color: #18a0fb;
            text-decoration: underline;
        }
        .badge-tipo {
            font-size: 0.6rem;
            padding: 0.15rem 0.4rem;
            border-radius: 8px;
        }
        .badge-factura {
            background: #28a745;
            color: white;
        }
        .badge-devolucion {
            background: #dc3545;
            color: white;
        }
        .table-scroll {
            max-height: 65vh;
            overflow-y: auto;
        }
        .table-scroll thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        @media (max-width: 768px) {
            .table th, .table td {
                font-size: 0.7rem;
                padding: 0.2rem 0.3rem !important;
            }
            .totales-card h4 {
                font-size: 0.9rem;
            }
            .filtros-row .col-md-3 {
                margin-bottom: 0.3rem;
            }
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card">

            <div class="card-body">
                <!-- Filtros Compactos -->
                <form method="GET" action="{{ route('reporte.cashea') }}" class="filtros-row">
                    <div class="row g-1">
                        <div class="col-md-2">
                            <label>Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                                   value="{{ $fechaInicio }}">
                        </div>
                        <div class="col-md-2">
                            <label>Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control form-control-sm"
                                   value="{{ $fechaFin }}">
                        </div>
                        <div class="col-md-2">
                            <label>Sucursal</label>
                            <select name="fk_sucursal" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @foreach($sucursales as $suc)
                                    <option value="{{ $suc->id }}"
                                        {{ $sucursalId == $suc->id ? 'selected' : '' }}>
                                        {{ $suc->descrip }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ route('reporte.cashea') }}" class="btn btn-secondary btn-sm w-100">
                                <i class="fas fa-undo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Tarjetas de Totales Compactas -->
                <div class="row g-1 mb-2">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white totales-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 style="color: white"><i class="fas fa-dollar-sign"></i> Facturado</h5>
                                    <h4 style="color: white">$ {{ number_format($totales['monto_total'], 2,',','.') }}</h4>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white totales-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 style="color: white"><i class="fas fa-check-circle"></i> Abonado</h5>
                                    <h4 style="color: white">$ {{ number_format($totales['abonado_total'], 2,',','.') }}</h4>
                                </div>
                                <div>
                                    <span class="badge bg-light text-dark">
                                        {{ $totales['monto_total'] > 0 ? number_format(($totales['abonado_total'] / $totales['monto_total']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark totales-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 style="color: white"><i class="fas fa-clock"></i> Pendiente</h5>
                                    <h4 style="color: white">$ {{ number_format($totales['restante_total'], 2,',','.') }}</h4>
                                </div>
                                <div>
                                    <span class="badge bg-dark text-white">
                                        {{ $totales['monto_total'] > 0 ? number_format(($totales['restante_total'] / $totales['monto_total']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Tabla de resultados con scroll -->
                <div class="table-scroll">
                    <table class="table table-striped table-bordered table-hover" id="casheatable">
                        <thead>
                        <tr>
                            <th style="width: 8%;">Fecha</th>
                            <th style="width: 10%;">N° Doc</th>
                            <th style="width: 25%;">Cliente</th>
                            <th style="width: 12%;">Cédula/RIF</th>
                            <th style="width: 12%;">Monto ($)</th>
                            <th style="width: 12%;">Abonado ($)</th>
                            <th style="width: 12%;">Saldo ($)</th>

                        </tr>
                        </thead>
                        <tbody>
                        @forelse($facturas as $factura)
                            @php
                                $esDevolucion = (isset($factura->numeror) && $factura->numeror != '');
                                $saldo = $factura->restante;
                                $claseFila = $esDevolucion ? 'fila-devolucion' : '';
                            @endphp
                            <tr class="{{ $claseFila }}">
                                <td>{{ \Carbon\Carbon::parse($factura->fechat)->format('d/m/Y') }}</td>
                                <td>
                                    <a href="{{ route('facturaver', [
                                        'tipofac' => $factura->tipofac,
                                        'numerod' => $factura->numerod,
                                        'fksucu' => $factura->fk_sucursal
                                    ]) }}" target="_blank" class="link-factura">
                                       {{ $factura->numerod }}
                                        @if( $factura->tipofac=='B')
                                            <span class="badge badge-devolucion badge-tipo">DEV</span>
                                        @endif
                                        @if($factura->tipofac == 'A')
                                            <span class="badge badge-factura badge-tipo">FAC</span>
                                        @endif
                                    </a>
                                </td>
                                <td class="text-start">{{ $factura->cliente }}</td>
                                <td>{{ $factura->cedula ?: 'N/A' }}</td>
                                <td class="text-right">  {{ number_format($factura->monto_factura, 2,',','.') }}</td>
                                <td class="text-right">  {{ number_format($factura->abonado, 2,',','.') }}</td>
                                <td class="text-right">
                                    @if($saldo > 0)
                                        <span class="text-danger font-weight-bold">
                                              {{ number_format($saldo, 2,',','.') }}
                                        </span>
                                    @else
                                        <span class="text-success">
                                            {{ number_format($saldo, 2,',','.') }}
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2 text-muted"></i>
                                    No se encontraron facturas para el período seleccionado.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                        <tfoot>
                        <tr class="table-secondary fw-bold">
                            <td colspan="4" class="text-end">TOTALES:</td>
                            <td class="text-right">$ {{ number_format($totales['monto_total'], 2,',','.') }}</td>
                            <td class="text-right">$ {{ number_format($totales['abonado_total'], 2,',','.') }}</td>
                            <td class="text-right">$ {{ number_format($totales['restante_total'], 2,',','.') }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Leyenda -->
                <div class="mt-2 small text-muted d-flex justify-content-between">

                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary me-2" style="height: 43px;"
                                onclick="exportarExcel('casheatable')">
                            <i class="bi bi-file-excel"></i> Exportar a Excel
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        // Auto-enviar formulario al cambiar selects
        document.querySelectorAll('.form-select').forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    </script>
@endsection
