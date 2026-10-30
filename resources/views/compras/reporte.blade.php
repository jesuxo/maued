{{-- resources/views/compras/reporte.blade.php --}}
@extends('layouts.master')

@section('title')
    Reporte de Compras
@endsection

@section('css')
    <style>
        .table-light {
            --tb-table-hover-bg: #e3f2fd !important;
            --tb-table-hover-color: #000 !important;
        }
        .badge-cerrada { background-color: #28a745; color: white; }
        .badge-abierta { background-color: #007bff; color: white; }
        .badge-pendiente { background-color: #dc3545; color: white; }
        .table-compra th { background-color: #0072c5; color: white; }
        .link-serial { color: #0072c5; text-decoration: none; }
        .link-serial:hover { text-decoration: underline; }

        /* Estilos para las estadísticas de verificación */
        .stats-verificacion {
            font-size: 0.85rem;
        }
        .stats-verificacion .progress {
            border-radius: 10px;
            background-color: #e9ecef;
        }
        .stats-verificacion .badge {
            font-size: 0.7rem;
            padding: 3px 6px;
        }
        .verificacion-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 5px;
        }
    </style>
@endsection

@section('content')
    {{-- Fila de botones de acceso rápido (FUERA del card de filtros) --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <h6 class="card-title mb-0 me-3">Reportes Rápidos:</h6>
                        <a href="{{ route('reportecompra', ['tipo_reporte' => 'descargados']) }}"
                           class="btn btn-sm {{ request('tipo_reporte') == 'descargados' ? 'btn-danger' : 'btn-outline-danger' }} me-2">
                            <i class="bi bi-arrow-down-circle"></i> Ver Descargados
                        </a>
                        <a href="{{ route('reportecompra', ['tipo_reporte' => 'vendidos']) }}"
                           class="btn btn-sm {{ request('tipo_reporte') == 'vendidos' ? 'btn-primary' : 'btn-outline-primary' }} me-2">
                            <i class="bi bi-cart-check"></i> Ver Vendidos
                        </a>
                        @if(request('tipo_reporte'))
                            <a href="/reporte/compra" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Limpiar Filtro
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas de estadísticas (solo cuando se aplica el filtro) --}}
    @if(isset($statsDescargados) && request('tipo_reporte') == 'descargados')
        <div class="row mb-3" style="display: none">
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white">Total Descargados</h5>
                        <h2>{{ $statsDescargados['total_descargados'] }}</h2>
                        <small>Seriales marcados como descargados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning">
                    <div class="card-body">
                        <h5 class="card-title">Compras Afectadas</h5>
                        <h2>{{ $statsDescargados['compras_con_descargados'] }}</h2>
                        <small>Compras con al menos un descargado</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white">Promedio x Compra</h5>
                        <h2>
                            @php
                                $promedio = $statsDescargados['compras_con_descargados'] > 0
                                    ? round($statsDescargados['total_descargados'] / $statsDescargados['compras_con_descargados'], 1)
                                    : 0;
                            @endphp
                            {{ $promedio }}
                        </h2>
                        <small>Descargados por compra</small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Card de filtros --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Filtros de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('compras.reporte') }}" id="form-filtros">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Sucursal</label>
                                <select class="form-select" name="fksucursal" onchange="$('#form-filtros').submit()">
                                    <option value="0" {{ $fksucursal == 0 ? 'selected' : '' }}>Todas las sucursales</option>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ $sucursal->id == $fksucursal ? 'selected' : '' }}>
                                            {{ $sucursal->descrip }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Búsqueda</label>
                                <input type="text" class="form-control"
                                       placeholder="Proveedor, N° Compra..."
                                       name="busqueda" value="{{ $busqueda }}">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Verificación</label>
                                <select class="form-select" name="verificacion" onchange="$('#form-filtros').submit()">
                                    <option value="" {{ ($verificacion ?? '') === '' ? 'selected' : '' }}>Todos</option>
                                    <option value="completa" {{ ($verificacion ?? '') == 'completa' ? 'selected' : '' }}>Completas</option>
                                    <option value="pendiente" {{ ($verificacion ?? '') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                                    <option value="parcial" {{ ($verificacion ?? '') == 'parcial' ? 'selected' : '' }}>Parciales</option>
                                    <option value="sin_seriales" {{ ($verificacion ?? '') == 'sin_seriales' ? 'selected' : '' }}>Sin seriales</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="status" onchange="$('#form-filtros').submit()">
                                    <option value="" {{ $status === '' ? 'selected' : '' }}>Todos</option>
                                    <option value="1" {{ $status == '1' ? 'selected' : '' }}>Abiertas</option>
                                    <option value="0" {{ $status == '0' ? 'selected' : '' }}>Cerradas</option>
                                    <option value="2" {{ $status == '2' ? 'selected' : '' }}>Pendientes</option>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Rango de Fechas</label>
                                <div class="input-group">
                                    <input type="text" class="form-control"
                                           data-provider="flatpickr" data-range-date="true"
                                           data-date-format="d/m/Y" name="fechasreport"
                                           readonly value="{{ $fechasreport }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> Consultar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla de resultados --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-compra">
                            <thead class="table-primary">
                            <tr>
                                <th class="text-start" style="color: #0072c5">PROVEEDOR</th>
                                <th class="text-center"style="color: #0072c5">FECHA</th>
                                <th class="text-center"style="color: #0072c5">DOCUMENTO</th>
                                <th class="text-center"style="color: #0072c5">UNDS</th>
                                <th class="text-center"style="color: #0072c5">VERIFICACIÓN</th>
                                <th class="text-center"style="color: #0072c5">MONTO</th>
                                <th class="text-center"style="color: #0072c5">ACCIONES</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($compras as $compra)
                                @php
                                    $totalUnidades = $compra->items->sum('cantidad');
                                    $totalSeriales = $compra->seriales->count();
                                    $totalMonto = $compra->items->sum(function($item) {
                                        return ($item->preciod ?? 0) * ($item->cantidad ?? 0);
                                    });
                                    $stats = $compra->stats_verificacion;
                                @endphp
                                <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                    <td>
                                        <strong>{{ $compra->descrip }}</strong>
                                        @if($compra->tipocom == 'Y')
                                            <span class="badge bg-danger ms-1">DEV</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $compra->notas1 }} {{ $compra->notas2 }}</small>
                                    </td>
                                    <td class="text-center">{{ $compra->fechaformat }}</td>
                                    <td class="text-center">
                                        @if($compra->tipocom == 'U')
                                            <a href="{{ route('compras.documento', $compra->id) }}" target="_blank" class="fw-bold">
                                                {{ $compra->numerod }}
                                            </a>
                                        @else
                                            {{ $compra->numerod }}
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($totalUnidades, 0) }}</td>

                                    {{-- Columna de VERIFICACIÓN --}}
                                    <td class="text-center">
                                        @if($totalSeriales > 0)
                                            <div class="stats-verificacion">
                                                {{-- Barra de progreso --}}
                                                <div class="progress mb-1" style="height: 6px;">
                                                    @if($stats['verificados'] > 0)
                                                        <div class="progress-bar bg-success"
                                                             style="width: {{ ($stats['verificados']/$totalSeriales)*100 }}%"
                                                             title="Verificados: {{ $stats['verificados'] }}"></div>
                                                    @endif
                                                    @if($stats['descargados'] > 0)
                                                        <div class="progress-bar bg-danger"
                                                             style="width: {{ ($stats['descargados']/$totalSeriales)*100 }}%"
                                                             title="Descargados: {{ $stats['descargados'] }}"></div>
                                                    @endif
                                                    @if($stats['vendidos'] > 0)
                                                        <div class="progress-bar bg-primary"
                                                             style="width: {{ ($stats['vendidos']/$totalSeriales)*100 }}%"
                                                             title="Vendidos: {{ $stats['vendidos'] }}"></div>
                                                    @endif
                                                </div>

                                                {{-- Contadores compactos --}}
                                                <div class="d-flex flex-wrap gap-1 justify-content-center mt-1">
                                                    @if($stats['pendientes'] > 0)
                                                        <span class="badge bg-warning" title="Pendientes">
                                                            <i class="bi bi-clock-history"></i> {{ $stats['pendientes'] }}
                                                        </span>
                                                    @endif
                                                    @if($stats['verificados'] > 0)
                                                        <span class="badge bg-success" title="Verificados">
                                                            <i class="bi bi-check-circle"></i> {{ $stats['verificados'] }}
                                                        </span>
                                                    @endif
                                                    @if($stats['descargados'] > 0)
                                                        <span class="badge bg-danger" title="Descargados">
                                                            <i class="bi bi-arrow-down-circle"></i> {{ $stats['descargados'] }}
                                                        </span>
                                                    @endif
                                                    @if($stats['vendidos'] > 0)
                                                        <span class="badge bg-primary" title="Vendidos">
                                                            <i class="bi bi-cart-check"></i> {{ $stats['vendidos'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Porcentaje de avance --}}
                                                <small class="text-muted d-block mt-1">
                                                    {{ $stats['porcentaje_avance'] }}% completado
                                                </small>
                                            </div>
                                        @else
                                            <span class="text-muted">Sin seriales</span>
                                        @endif
                                    </td>

                                    <td class="text-end">$ {{ number_format($totalMonto, 2, ',', '.') }}</td>

                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-gear"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if($compra->tipocom == 'U')
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('compras.documento', $compra->id) }}" target="_blank">
                                                            <i class="bi bi-file-text"></i> Ver Documento
                                                        </a>
                                                    </li>
                                                    @if($totalSeriales > 0)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('compras.seriales', $compra->id) }}" target="_blank">
                                                                <i class="bi bi-upc-scan"></i> Ver Seriales
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mt-2">No hay compras para mostrar</p>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('build/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Inicializar flatpickr si existe
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
    </script>
@endsection
