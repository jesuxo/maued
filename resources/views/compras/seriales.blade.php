{{-- resources/views/compras/seriales.blade.php --}}
@extends('layouts.master')

@section('title')
    Seriales de Compra {{ $documento->numerod }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        .table-light {
            --tb-table-hover-bg: #e3f2fd !important;
            --tb-table-hover-color: #000 !important;
        }
        .card-header-serial {
            background: linear-gradient(45deg, #0072c5, #00a3ff);
            color: white;
        }

        /* Estilos para la tabla */
        .table-serial {
            border: 1px solid #dee2e6;
        }

        .table-serial thead th {
            background-color: #0072c5 !important;
            color: white !important;
            font-weight: 600;
            border-color: #0056a3 !important;
            padding: 12px 8px;
            vertical-align: middle;
        }

        .table-serial tbody td {
            vertical-align: middle;
            padding: 10px 8px;
        }

        .table-serial tbody tr:hover {
            background-color: rgba(0, 114, 197, 0.05) !important;
        }

        /* Estilos para badges de estado */
        .badge-estado {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-vendido {
            background-color: #28a745;
            color: white;
        }

        .badge-stock {
            background-color: #17a2b8;
            color: white;
        }

        /* Estilos para badges de facturas */
        .badge-factura {
            display: inline-block;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 4px;
            background-color: #e3f2fd;
            color: #0056b3;
            border: 1px solid #0072c5;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .badge-factura:hover {
            background-color: #0072c5;
            color: white !important;
            border-color: #0056a3;
        }

        .badge-factura i {
            margin-right: 3px;
            font-size: 10px;
        }

        /* Estilos para badges de devolución */
        .badge-devolucion {
            display: inline-block;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 4px;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .badge-devolucion:hover {
            background-color: #ffc107;
            color: #000 !important;
        }

        .badge-devolucion i {
            margin-right: 3px;
            font-size: 10px;
        }

        /* Estilos para el modal de historial */
        .timeline-modal {
            max-height: 500px;
            overflow-y: auto;
            padding: 15px;
        }

        .timeline-item-modal {
            position: relative;
            padding-left: 40px;
            margin-bottom: 20px;
            border-left: 2px solid #0072c5;
        }

        .timeline-item-modal:last-child {
            margin-bottom: 0;
        }

        .timeline-badge-modal {
            position: absolute;
            left: -12px;
            top: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: white;
            border: 2px solid #0072c5;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }

        .timeline-badge-modal i {
            font-size: 12px;
            color: #0072c5;
        }

        .timeline-content-modal {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 5px;
            border: 1px solid #e9ecef;
        }

        .modal-serial-titulo {
            font-size: 1.1rem;
            font-weight: 600;
            color: #0072c5;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .btn-historial {
            cursor: pointer;
            transition: all 0.3s;
            color: #0072c5 !important;
            border-color: #0072c5 !important;
        }

        .btn-historial:hover {
            transform: scale(1.1);
            background-color: #0072c5 !important;
            color: white !important;
        }

        /* Estilos para el modal de factura */
        .modal-factura-content {
            max-height: 600px;
            overflow-y: auto;
            padding: 20px;
        }

        .factura-header {
            background-color: #0072c5;
            color: white;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            margin-bottom: 20px;
        }

        .factura-detalle {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .factura-tabla th {
            background-color: #e9ecef;
        }

        .loading-factura {
            text-align: center;
            padding: 40px;
        }

        .loading-factura .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-serial">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-upc-scan me-2"></i>
                            Seriales de Compra: {{ $documento->numerod }}
                        </h5>
                        <div class="text-end">
                            <!-- Estadísticas de verificación en una línea separada -->
                            <div class="mb-2">
                                <span class="badge bg-warning me-1" id="stats-pendientes">⏳ Pendientes: 0</span>
                                 <span class="badge bg-danger me-1" id="stats-descargados">⬇️ Descargados: 0</span>
                                <span class="badge bg-primary me-1" id="stats-vendidos"  >💰 Vendidos: 0</span>
                            </div>
                            <div>
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bi bi-upc-scan"></i> Total: {{ $documento->seriales->count() }}
                                </span>
                                <span class="badge bg-success me-2">
                                    <i class="bi bi-check-circle"></i> Vendidos: {{ collect($historialSeriales)->filter(function($item) { return $item['total_ventas'] > 0; })->count() }}
                                </span>
                                <span class="badge bg-info me-2">
                                    <i class="bi bi-box"></i> En Stock: {{ collect($historialSeriales)->filter(function($item) { return $item['total_ventas'] == 0; })->count() }}
                                </span>
                                <a href="{{ route('compras.documento', $documento->id) }}" class="btn btn-sm btn-light">
                                    <i class="bi bi-arrow-left"></i> Volver al Documento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información de la compra -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <small class="text-muted">Proveedor</small>
                                    <h6 class="mb-0">{{ $documento->descrip }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <small class="text-muted">Fecha</small>
                                    <h6 class="mb-0">{{ $documento->fechaformat }}</h6>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <small class="text-muted">Sucursal</small>
                                    <h6 class="mb-0">{{ $documento->sucursal->descrip ?? 'N/A' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de seriales -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-serial">
                            <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">SERIAL</th>
                                <th width="30%">PRODUCTO</th>
                                @foreach($sucursalesVenta as $sucursalId => $sucursalNombre)
                                    <th class="text-center">{{ strtoupper($sucursalNombre) }}</th>
                                @endforeach
                                <th width="10%" class="text-center">ESTADO</th>
                                <th width="10%" class="text-center">VERIFICACIÓN</th>
                                <th width="8%" class="text-center">ACCIONES</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($documento->seriales as $index => $serial)
                                @php
                                    $dataSerial = $historialSeriales[$serial->nroserial] ?? null;
                                    $ventasCount = $dataSerial['total_ventas'] ?? 0;
                                    $sucursalesSerial = $dataSerial['sucursales'] ?? [];
                                @endphp
                                <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                    <td class="fw-bold">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>{{ $index + 1 }}</span>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary btn-historial"
                                                    onclick="verHistorial('{{ $serial->nroserial }}', '{{ addslashes($serial->producto->descrip ?? 'N/A') }}', '{{ $serial->coditem }}')"
                                                    title="Ver historial del serial">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $serial->nroserial }}</strong>
                                        <br>
                                        <small class="text-muted">Cód: {{ $serial->coditem }}</small>
                                    </td>
                                    <td>
                                        {{ $serial->producto->descrip ?? 'N/A' }}
                                    </td>

                                    @foreach($sucursalesVenta as $sucursalId => $sucursalNombre)
                                        <td class="text-center">
                                            @if(isset($sucursalesSerial[$sucursalId]))
                                                @foreach($sucursalesSerial[$sucursalId] as $venta)
                                                    @php
                                                        $esFactura = in_array($venta->tipofac, ['A', 'Z']);
                                                        $esDevolucion = in_array($venta->tipofac, ['B', 'W']);
                                                        $badgeClass = $esFactura ? 'badge-factura' : ($esDevolucion ? 'badge-devolucion' : 'badge-factura');
                                                        $icono = $esFactura ? 'bi-receipt' : 'bi-arrow-return-left';
                                                    @endphp
                                                    <span onclick="verFactura('{{ $venta->tipofac }}', '{{ $venta->numerod }}', '{{ $venta->fk_sucursal }}')"
                                                          class="{{ $badgeClass }} mb-1">
                                                            <i class="bi {{ $icono }}"></i>
                                                            {{ $venta->tipofac }}-{{ $venta->numerod }}
                                                        </span>
                                                    <br>
                                                @endforeach
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <td class="text-center">
                                        @if($ventasCount > 0)
                                            <span class="badge badge-estado badge-vendido">
                                                    <i class="bi bi-check-circle"></i> Vendido
                                                </span>
                                        @else
                                            <span class="badge badge-estado badge-stock">
                                                    <i class="bi bi-box"></i> En Stock
                                                </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                            <span class="badge bg-{{ $serial->status_color }}" style="font-size: 11px;">
                                                <i class="bi {{ $serial->status_icon }} me-1"></i>
                                                {{ $serial->status_text }}
                                            </span>

                                    </td>

                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-{{ $serial->status_color }} btn-verificar"
                                                    onclick="verificarSerial({{ $serial->id }}, '{{ $serial->nroserial }}', {{ $serial->checked }})"
                                                    title="Verificar serial"
                                                    data-serial-id="{{ $serial->id }}">
                                                <i class="bi {{ $serial->checked == 0 ? 'bi-check-circle' : 'bi-pencil' }}"></i>
                                            </button>
                                            @if($serial->check_comment)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-info btn-comentario"
                                                        onclick="verComentario('{{ $serial->nroserial }}', '{{ addslashes($serial->check_comment) }}', '{{ $serial->checker->name ?? 'Sistema' }}', '{{ $serial->checked_at ? $serial->checked_at->format('d/m/Y H:i') : '' }}')"
                                                        title="Ver comentario"
                                                        data-serial-id="{{ $serial->id }}">
                                                    <i class="bi bi-chat-dots"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 4 + count($sucursalesVenta) }}" class="text-center py-4">
                                        <i class="bi bi-upc-scan" style="font-size: 2rem; color: #ccc;"></i>
                                        <p class="mt-2 mb-0">No hay seriales registrados en esta compra</p>
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

    {{-- Modal para verificar serial --}}
    <div class="modal fade" id="verificarModal" tabindex="-1" aria-labelledby="verificarModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="verificarModalLabel">
                        <i class="bi bi-check-circle me-2"></i>
                        Verificar Serial
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="serialId" value="">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Serial:</label>
                        <p class="form-control-plaintext" id="serialNumero"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado de Verificación:</label>
                        <select class="form-select" id="estadoVerificacion">
                            <option value="0">⏳ Pendiente</option>

                            <option value="2">⬇️ Descargado (Baja/Inventario)</option>
                            <option value="3">💰 Vendido</option>
                        </select>
                    </div>
<!--  <option value="1">✅ Verificado - En Stock</option>-->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comentario / Observación:</label>
                        <textarea class="form-control" id="comentarioSerial" rows="4"
                                  placeholder="Ingrese cualquier observación sobre este serial..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Al marcar como "Descargado" o "Vendido", se registrará la acción para control de inventario.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarVerificacion()">
                        <i class="bi bi-save me-2"></i>
                        Guardar Verificación
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para ver comentario --}}
    <div class="modal fade" id="comentarioModal" tabindex="-1" aria-labelledby="comentarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="comentarioModalLabel">
                        <i class="bi bi-chat-dots me-2"></i>
                        Comentario del Serial
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Serial:</label>
                        <p class="form-control-plaintext" id="comentarioSerialNumero"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Comentario:</label>
                        <div class="p-3 bg-light rounded" id="comentarioTexto" style="white-space: pre-wrap;"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Verificado por:</label>
                            <p id="comentarioUsuario"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha:</label>
                            <p id="comentarioFecha"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para historial del serial -->
    <div class="modal fade" id="historialModal" tabindex="-1" aria-labelledby="historialModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="historialModalLabel">
                        <i class="bi bi-clock-history me-2"></i>
                        Historial del Serial
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalSerialInfo" class="modal-serial-titulo"></div>
                    <div id="modalHistorialContent" class="timeline-modal">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando historial...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver factura -->
    <div class="modal fade" id="facturaModal" tabindex="-1" aria-labelledby="facturaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="facturaModalLabel">
                        <i class="bi bi-receipt me-2"></i>
                        Detalle de Factura
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="facturaModalBody">
                    <div class="loading-factura">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando factura...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('build/js/app.js') }}"></script>
    <script>
        // Configuración de toastr
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        // Variables globales
        let serialActual = null;

        function cargarEstadisticasVerificacion() {
            let compraId = {{ $documento->id }};

            $.ajax({
                url: `/seriales/estadisticas-compra/${compraId}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        actualizarContador('stats-pendientes', response.data.pendientes, '⏳ Pendientes');
                       // actualizarContador('stats-verificados', response.data.verificados, '✅ Verificados');
                        actualizarContador('stats-descargados', response.data.descargados, '⬇️ Descargados');
                        actualizarContador('stats-vendidos', response.data.vendidos, '💰 Vendidos');
                    }
                },
                error: function(xhr) {
                    console.error('Error al cargar estadísticas:', xhr);
                }
            });
        }

        function actualizarContador(elementId, nuevoValor, textoBase) {
            let elemento = $(`#${elementId}`);
            let textoActual = elemento.text();
            let match = textoActual.match(/\d+/);

            if (match) {
                let valorActual = parseInt(match[0]);

                if (valorActual !== nuevoValor) {
                    elemento.fadeOut(200, function() {
                        $(this).text(`${textoBase}: ${nuevoValor}`).fadeIn(200);
                    });
                }
            } else {
                elemento.text(`${textoBase}: ${nuevoValor}`);
            }
        }

        $(document).ready(function() {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Cargar estadísticas iniciales
            cargarEstadisticasVerificacion();

            // Refrescar estadísticas cada 30 segundos
            setInterval(cargarEstadisticasVerificacion, 30000);
        });

        function verificarSerial(id, numero, estadoActual) {
            serialActual = id;
            $('#serialId').val(id);
            $('#serialNumero').text(numero);
            $('#estadoVerificacion').val(estadoActual);

            $.ajax({
                url: `/seriales/${id}/comentario`,
                type: 'GET',
                success: function(response) {
                    if (response.success && response.data.comentario) {
                        $('#comentarioSerial').val(response.data.comentario);
                    } else {
                        $('#comentarioSerial').val('');
                    }
                },
                error: function() {
                    $('#comentarioSerial').val('');
                }
            });

            $('#verificarModal').modal('show');
        }

        function guardarVerificacion() {
            let id = $('#serialId').val();
            let estado = $('#estadoVerificacion').val();
            let comentario = $('#comentarioSerial').val();

            if (!id) {
                mostrarMensaje('error', 'Error: ID de serial no válido');
                return;
            }

            if (estado === null || estado === undefined) {
                mostrarMensaje('error', 'Debe seleccionar un estado de verificación');
                return;
            }

            let btn = $('#verificarModal').find('.btn-primary');
            let originalText = btn.html();
            btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...').prop('disabled', true);

            $.ajax({
                url: `/seriales/${id}/verificar`,
                type: 'POST',
                data: {
                    estado: estado,
                    comentario: comentario,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#verificarModal').modal('hide');
                        actualizarFilaSerial(id, response.data);
                        cargarEstadisticasVerificacion();

                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message);
                        } else {
                            mostrarMensaje('success', response.message);
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message);
                        } else {
                            mostrarMensaje('error', response.message);
                        }
                    }
                },
                error: function(xhr) {
                    let mensaje = 'Error al guardar la verificación';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        mensaje = xhr.responseJSON.message;
                    }

                    if (typeof toastr !== 'undefined') {
                        toastr.error(mensaje);
                    } else {
                        mostrarMensaje('error', mensaje);
                    }
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                }
            });
        }

        function mostrarMensaje(tipo, mensaje) {
            let alerta = $(`
                <div class="alert alert-${tipo === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed top-0 end-0 m-3"
                     style="z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-${tipo === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>${tipo === 'success' ? 'Éxito' : 'Error'}</strong><br>
                            <small>${mensaje}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);

            $('body').append(alerta);

            setTimeout(function() {
                alerta.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        function actualizarFilaSerial(serialId, data) {
            console.log('Actualizando fila para serial ID:', serialId, data);

            let fila = null;

            $(`.btn-verificar`).each(function() {
                let onclick = $(this).attr('onclick');
                if (onclick && onclick.includes(`verificarSerial(${serialId}`)) {
                    fila = $(this).closest('tr');
                    return false;
                }
            });

            if (!fila) {
                fila = $(`button[data-serial-id="${serialId}"]`).closest('tr');
            }

            if (!fila && data.nroserial) {
                fila = $(`td:contains("${data.nroserial}")`).closest('tr');
            }

            if (!fila || fila.length === 0) {
                console.error('No se encontró la fila para el serial ID:', serialId);
                return;
            }

            let icono = '';
            let colorClass = '';
            let textEstado = '';

            switch(parseInt(data.estado)) {
                case 0:
                    icono = 'bi-clock-history';
                    colorClass = 'bg-warning';
                    textEstado = 'Pendiente';
                    break;
                case 1:
                    icono = 'bi-check-circle';
                    colorClass = 'bg-success';
                    textEstado = 'Verificado';
                    break;
                case 2:
                    icono = 'bi-arrow-down-circle';
                    colorClass = 'bg-danger';
                    textEstado = 'Descargado';
                    break;
                case 3:
                    icono = 'bi-cart-check';
                    colorClass = 'bg-primary';
                    textEstado = 'Vendido';
                    break;
            }

            let badgeHtml = `<span class="badge ${colorClass}" style="font-size: 11px; padding: 5px 8px;">
                <i class="bi ${icono} me-1"></i> ${textEstado}
            </span>`;


            let columnas = fila.find('td');
            let indiceVerificacion = columnas.length - 2;

            fila.find('td:eq(' + indiceVerificacion + ')').html(badgeHtml);

            let botoneraHtml = `<div class="btn-group" role="group">`;
            botoneraHtml += `<button type="button"
                class="btn btn-sm btn-outline-${colorClass.replace('bg-', '')} btn-verificar"
                onclick="verificarSerial(${serialId}, '${data.nroserial || ''}', ${data.estado})"
                title="Verificar serial"
                data-serial-id="${serialId}">
                <i class="bi ${parseInt(data.estado) === 0 ? 'bi-check-circle' : 'bi-pencil'}"></i>
            </button>`;



            botoneraHtml += `</div>`;

            fila.find('td:last').html(botoneraHtml);

            console.log('Fila actualizada correctamente');
        }

        function verComentario(serial, comentario, usuario, fecha) {
            let comentarioEscapado = (comentario || '')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/\n/g, '<br>');

            $('#comentarioSerialNumero').text(serial || '');
            $('#comentarioTexto').html(comentarioEscapado || '<em class="text-muted">Sin comentario</em>');
            $('#comentarioUsuario').text(usuario || 'N/A');
            $('#comentarioFecha').text(fecha || 'N/A');
            $('#comentarioModal').modal('show');
        }

        function verHistorial(serial, producto, codprod) {
            $('#modalSerialInfo').html(`
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-primary p-2">
                            <i class="bi bi-upc-scan"></i>
                        </span>
                    </div>
                    <div>
                        <strong>Serial:</strong> <span class="text-primary">${serial}</span><br>
                        <strong>Producto:</strong> ${producto}
                    </div>
                </div>
            `);

            $('#modalHistorialContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Cargando historial...</p>
                </div>
            `);

            $('#historialModal').modal('show');

            $.ajax({
                url: `/seriales/historial-json/${codprod}/${encodeURIComponent(serial)}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderizarHistorial(response.data);
                    } else {
                        $('#modalHistorialContent').html(`
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Error al cargar el historial: ${response.message}
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#modalHistorialContent').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error al cargar el historial. Intente nuevamente.
                        </div>
                    `);
                }
            });
        }

        function renderizarHistorial(historial) {
            if (!historial || historial.length === 0) {
                $('#modalHistorialContent').html(`
                    <div class="text-center py-4">
                        <i class="bi bi-clock" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="mt-3">No hay movimientos registrados para este serial</p>
                    </div>
                `);
                return;
            }

            let html = '';

            historial.forEach(function(mov) {
                let icono = 'bi-cart';
                let badgeClass = 'bg-success';
                let tipoMovimiento = 'COMPRA';

                if (mov.tipo_movimiento === 'VENTA') {
                    icono = 'bi-receipt';
                    badgeClass = 'bg-primary';
                    tipoMovimiento = 'VENTA';
                }
                if (mov.tipo_movimiento === 'OPERACION') {
                    icono = 'bi-arrow-repeat';
                    badgeClass = 'bg-warning text-dark';
                    tipoMovimiento = 'OPERACIÓN';
                }

                let documentoHtml = mov.numerod;
                if (mov.tipo_movimiento === 'VENTA' && ['A','B','Z','W'].includes(mov.tipo)) {
                    documentoHtml = `<span onclick="verFactura('${mov.tipo}', '${mov.numerod}', '${mov.fk_sucursal}')" class="text-primary fw-bold" style="cursor: pointer; text-decoration: underline;">${mov.numerod}</span>`;
                }

                html += `
                    <div class="timeline-item-modal">
                        <div class="timeline-badge-modal">
                            <i class="bi ${icono}"></i>
                        </div>
                        <div class="timeline-content-modal">
                            <div class="row">
                                <div class="col-md-3">
                                    <span class="badge ${badgeClass}">${tipoMovimiento}</span>
                                </div>
                                <div class="col-md-2">
                                    <strong>Fecha:</strong> ${mov.fecha}
                                </div>
                                <div class="col-md-3">
                                    <strong>Tipo:</strong> ${mov.tipo_descripcion || mov.tipo}
                                </div>
                                <div class="col-md-2">
                                    <strong>Doc:</strong> ${documentoHtml}
                                </div>
                                <div class="col-md-2">
                                    <strong>Suc:</strong> ${mov.sucursal_nombre || 'N/A'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#modalHistorialContent').html(html);
        }

        function verFactura(tipo, numero, sucursal) {
            let titulo = (tipo === 'A' || tipo === 'Z') ? 'VENTA' : 'DEVOLUCIÓN';

            $('#facturaModalLabel').html(`
                <i class="bi bi-receipt me-2"></i>
                ${titulo} ${tipo}-${numero}
            `);

            $('#facturaModalBody').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando factura...</p>
                </div>
            `);

            $('#facturaModal').modal('show');

            $.ajax({
                url: `/facturas/detalle-vista/${tipo}/${numero}/${sucursal}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#facturaModalBody').html(response.html);
                    } else {
                        $('#facturaModalBody').html(`
                            <div class="alert alert-danger m-4">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Error al cargar la factura: ${response.message}
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#facturaModalBody').html(`
                        <div class="alert alert-danger m-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error al cargar la factura. Intente nuevamente.
                        </div>
                    `);
                }
            });
        }

        function formatNumber(numero) {
            return new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(numero);
        }
    </script>
@endsection
