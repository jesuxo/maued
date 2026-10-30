{{-- resources/views/compras/reporte-descargados.blade.php --}}
@extends('layouts.master')

@section('title')
Reporte de Seriales Descargados
@endsection

@section('css')
<style>
    .table-descargados th {
        background-color: #dc3545;
        color: white;
    }
    .badge-descargado {
        background-color: #dc3545;
        color: white;
    }
    .stats-card {
        transition: transform 0.3s;
    }
    .stats-card:hover {
        transform: translateY(-5px);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-arrow-down-circle me-2"></i>
                    Reporte de Seriales Descargados
                </h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('compras.reporte-descargados') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Sucursal</label>
                        <select class="form-select" name="fksucursal">
                            <option value="0">Todas las sucursales</option>
                            @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id }}" {{ $fksucursal == $sucursal->id ? 'selected' : '' }}>
                                {{ $sucursal->descrip }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="{{ $fechaInicio }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" value="{{ $fechaFin }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Estadísticas --}}
<div class="row mt-3">
    <div class="col-md-3">
        <div class="card bg-danger text-white stats-card">
            <div class="card-body">
                <h6 class="card-title text-white">Total Descargados</h6>
                <h2 class="text-white">{{ $estadisticas['total_descargados'] }}</h2>
                <small>Seriales marcados como descargados</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning stats-card">
            <div class="card-body">
                <h6 class="card-title">Por Sucursal</h6>
                @foreach($estadisticas['por_sucursal'] as $sucursal)
                <div class="d-flex justify-content-between">
                    <span>{{ Str::limit($sucursal->descrip, 15) }}</span>
                    <span class="badge bg-danger">{{ $sucursal->total }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stats-card">
            <div class="card-header">
                <h6 class="mb-0">Descargados por Mes</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoDescargados" height="100"></canvas>
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
                    <table class="table table-bordered table-hover table-descargados">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>FECHA</th>
                            <th>SERIAL</th>
                            <th>PRODUCTO</th>
                            <th>COMPRA</th>
                            <th>PROVEEDOR</th>
                            <th>SUCURSAL</th>
                            <th>VERIFICADO POR</th>
                            <th>COMENTARIO</th>
                            <th>ACCIONES</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($descargados as $index => $item)
                        <tr>
                            <td>{{ $descargados->firstItem() + $index }}</td>
                            <td>{{ $item->checked_at ? date('d/m/Y', strtotime($item->checked_at)) : 'N/A' }}</td>
                            <td>
                                <strong class="text-danger">{{ $item->nroserial }}</strong>
                            </td>
                            <td>
                                {{ $item->producto_descrip ?? 'N/A' }}
                                <br>
                                <small class="text-muted">Cód: {{ $item->coditem }}</small>
                            </td>
                            <td>
                                <a href="{{ route('compras.documento', ['id' => $item->compra_id ?? 0]) }}" target="_blank">
                                    {{ $item->compra_numero }}
                                </a>
                                <br>
                                <small>{{ date('d/m/Y', strtotime($item->compra_fecha)) }}</small>
                            </td>
                            <td>{{ $item->proveedor }}</td>
                            <td>{{ $item->sucursal_nombre }}</td>
                            <td>{{ $item->verificador_nombre ?? 'Sistema' }}</td>
                            <td>
                                @if($item->check_comment)
                                <span data-bs-toggle="tooltip" title="{{ $item->check_comment }}">
                                                <i class="bi bi-chat-dots text-info"></i>
                                                {{ Str::limit($item->check_comment, 30) }}
                                            </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('compras.seriales', ['id' => $item->compra_id ?? 0]) }}"
                                       class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-upc-scan"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info"
                                            onclick="verHistorial('{{ $item->nroserial }}', '{{ addslashes($item->producto_descrip) }}', '{{ $item->coditem }}')">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-arrow-down-circle" style="font-size: 2rem;"></i>
                                <p class="mt-2">No hay seriales descargados en el período seleccionado</p>
                            </td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                <div class="mt-3">
                    {{ $descargados->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para historial --}}
<div class="modal fade" id="historialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-clock-history me-2"></i>
                    Historial del Serial
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historialModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de descargados por mes
    const ctx = document.getElementById('graficoDescargados').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($estadisticas['por_mes']->pluck('mes')->map(function($m) {
        return date('M Y', strtotime($m . '-01'));
    })) !!},
    datasets: [{
        label: 'Seriales Descargados',
        data: {!! json_encode($estadisticas['por_mes']->pluck('total')) !!},
    borderColor: '#dc3545',
        backgroundColor: 'rgba(220, 53, 69, 0.1)',
        tension: 0.4,
        fill: true
    }]
    },
    options: {
        responsive: true,
            plugins: {
            legend: {
                display: false
            }
        }
    }
    });

    function verHistorial(serial, producto, codprod) {
        $('#historialModalBody').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    `);
        $('#historialModal').modal('show');

        $.ajax({
            url: `/seriales/historial-json/${codprod}/${encodeURIComponent(serial)}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let html = '<div class="timeline-modal">';
                    response.data.forEach(function(mov) {
                        let icono = mov.tipo_movimiento === 'COMPRA' ? 'bi-cart' :
                            (mov.tipo_movimiento === 'VENTA' ? 'bi-receipt' : 'bi-arrow-repeat');
                        let badgeClass = mov.tipo_movimiento === 'COMPRA' ? 'bg-success' :
                            (mov.tipo_movimiento === 'VENTA' ? 'bg-primary' : 'bg-warning');

                        html += `
                        <div class="timeline-item-modal">
                            <div class="timeline-badge-modal">
                                <i class="bi ${icono}"></i>
                            </div>
                            <div class="timeline-content-modal">
                                <div class="row">
                                    <div class="col-md-3">
                                        <span class="badge ${badgeClass}">${mov.tipo_movimiento}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Fecha:</strong> ${mov.fecha}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Tipo:</strong> ${mov.tipo_descripcion || mov.tipo}
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Doc:</strong> ${mov.numerod}
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Suc:</strong> ${mov.sucursal_nombre || 'N/A'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    });
                    html += '</div>';
                    $('#historialModalBody').html(html);
                } else {
                    $('#historialModalBody').html('<div class="alert alert-danger">Error al cargar historial</div>');
                }
            },
            error: function() {
                $('#historialModalBody').html('<div class="alert alert-danger">Error al cargar historial</div>');
            }
        });
    }

    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endsection
