{{-- resources/views/pedidos/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Pedido ' . $pedido->num)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4>Orden de Compra #{{ $pedido->num }}</h4>
                                <small>
                                    Proveedor: <a href="{{ route('proveedores.show', $pedido->codprov) }}">
                                        {{ $pedido->proveedor->descrip ?? $pedido->codprov }}
                                    </a>
                                </small>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('pedidos.colocar-costos', $pedido->id) }}" class="btn btn-warning btn-sm">
                                    Colocar Costos
                                </a>
                                <a href="#" class="btn btn-secondary btn-sm" onclick="window.print()">
                                    <i class="fa fa-print"></i> Imprimir
                                </a>
                                <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary btn-sm">
                                    Volver
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Campos generales del pedido --}}
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Fecha Recepción</label>
                                <input type="text"
                                       class="form-control form-control-sm update-variable"
                                       data-variable="recepcion"
                                       data-pedido="{{ $pedido->id }}"
                                       value="{{ $pedido->recepcion ? date('d/m/Y', strtotime($pedido->recepcion)) : '' }}"
                                       placeholder="dd/mm/yyyy">
                            </div>
                            <div class="col-md-2">
                                <label>Flete %</label>
                                <input type="number" step="0.01"
                                       class="form-control form-control-sm update-variable"
                                       data-variable="flete"
                                       data-pedido="{{ $pedido->id }}"
                                       value="{{ session('fleteenpedido', 0) }}">
                            </div>
                            <div class="col-md-2">
                                <label>Dólar COP</label>
                                <input type="number" step="0.01"
                                       class="form-control form-control-sm update-variable"
                                       data-variable="dolarpesocompra"
                                       data-pedido="{{ $pedido->id }}"
                                       value="{{ session('dolarpesocompra', 0) }}">
                            </div>
                            <div class="col-md-3">
                                <label>Observaciones</label>
                                <input type="text"
                                       class="form-control form-control-sm update-variable"
                                       data-variable="observaciones"
                                       data-pedido="{{ $pedido->id }}"
                                       value="{{ $pedido->observaciones }}">
                            </div>
                            <div class="col-md-2">
                                <label>Unidades</label>
                                <input type="number"
                                       class="form-control form-control-sm update-variable"
                                       data-variable="unidades"
                                       data-pedido="{{ $pedido->id }}"
                                       value="{{ $pedido->unidades }}">
                            </div>
                        </div>

                        {{-- Tabla de items --}}
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover" id="items-table">
                                <thead class="thead-light">
                                <tr>
                                    <th width="5%">Cant</th>
                                    <th width="10%">Código</th>
                                    <th width="25%">Producto</th>
                                    <th width="8%">Costo $</th>
                                    <th width="8%">Costo Adic.</th>
                                    <th width="6%">Desc1 %</th>
                                    <th width="6%">Desc2 %</th>
                                    <th width="6%">Desc3 %</th>
                                    <th width="8%">Result</th>
                                    <th width="10%">Precio1</th>
                                    <th width="10%">Precio2</th>
                                    <th width="10%">Precio3</th>
                                    <th width="5%">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($pedido->items as $item)
                                    <tr data-item-id="{{ $item->id }}" class="item-row">
                                        <td>
                                            <input type="number" step="1"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="cantidad"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->cantidad }}">
                                        </td>
                                        <td>
                                            @if($item->codprod == 'CODPROD')
                                                <input type="text"
                                                       class="form-control form-control-sm item-field"
                                                       data-name="referencia"
                                                       data-item="{{ $item->id }}"
                                                       value="{{ $item->referencia }}"
                                                       placeholder="Referencia">
                                            @else
                                                <a href="#" class="btn-cambiar-producto"
                                                   data-item="{{ $item->id }}"
                                                   data-codprod="{{ $item->codprod }}">
                                                    {{ $item->codprod }}
                                                </a>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->codprod == 'CODPROD')
                                                <input type="text"
                                                       class="form-control form-control-sm item-field"
                                                       data-name="producto"
                                                       data-item="{{ $item->id }}"
                                                       value="{{ $item->producto }}"
                                                       placeholder="Producto">
                                            @else
                                                {{ $item->producto->descrip ?? $item->codprod }}
                                                @if($item->seriales->count() > 0)
                                                    <span class="badge badge-info">
                                                    Seriales: {{ $item->seriales->count() }}
                                                </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" step="0.01"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="costo"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->costo }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="costadicional"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->costadicional }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="porc1"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->porc1 }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="porc2"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->porc2 }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.1"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="porc3"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->porc3 }}">
                                        </td>
                                        <td class="text-right">
                                            {{ number_format($item->result, 4) }}
                                        </td>
                                        <td>
                                            <input type="number" step="0.01"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="precio"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->precio }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="precio2"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->precio2 }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01"
                                                   class="form-control form-control-sm item-field"
                                                   data-name="precio3"
                                                   data-item="{{ $item->id }}"
                                                   value="{{ $item->precio3 }}">
                                        </td>
                                        <td>
                                            @if($item->codprod != 'CODPROD')
                                                <button class="btn btn-danger btn-sm btn-eliminar-item"
                                                        data-item="{{ $item->id }}">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr class="table-active">
                                    <td colspan="4">
                                        <strong>Total Items: {{ $pedido->items->count() }}</strong>
                                    </td>
                                    <td class="text-right">
                                        <strong>Total: {{ number_format($pedido->total, 2) }}</strong>
                                    </td>
                                    <td colspan="7"></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <button class="btn btn-primary" id="btn-agregar-item">
                                    <i class="fa fa-plus"></i> Agregar Item
                                </button>
                                <button class="btn btn-info" id="btn-buscar-producto">
                                    <i class="fa fa-search"></i> Buscar Producto
                                </button>
                                <a href="{{ route('compras.tramitar', $pedido->id) }}"
                                   class="btn btn-success"
                                   id="btn-procesar-compra">
                                    <i class="fa fa-check"></i> Procesar Compra
                                </a>
                                <a href="{{ route('compras.tramitar-nota', $pedido->id) }}"
                                   class="btn btn-warning"
                                   id="btn-procesar-nota">
                                    <i class="fa fa-file"></i> Nota de Entrega
                                </a>
                            </div>
                        </div>

                        {{-- Costos Adicionales --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Costos Adicionales</h5>
                                <table class="table table-sm table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Proveedor</th>
                                        <th>Descripción</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    <tbody id="costos-adicionales-body">
                                    @foreach($pedido->costosAdicionales as $costo)
                                        <tr data-costo-id="{{ $costo->id }}">
                                            <td>
                                                <input type="text"
                                                       class="form-control form-control-sm costo-field"
                                                       data-name="codprov"
                                                       data-id="{{ $costo->id }}"
                                                       value="{{ $costo->codprov }}"
                                                       placeholder="CODPROV">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       class="form-control form-control-sm costo-field"
                                                       data-name="descripcion"
                                                       data-id="{{ $costo->id }}"
                                                       value="{{ $costo->descripcion }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       class="form-control form-control-sm costo-field"
                                                       data-name="fecha"
                                                       data-id="{{ $costo->id }}"
                                                       value="{{ $costo->fecha ? date('d/m/Y', strtotime($costo->fecha)) : '' }}"
                                                       placeholder="dd/mm/yyyy">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01"
                                                       class="form-control form-control-sm costo-field"
                                                       data-name="monto"
                                                       data-id="{{ $costo->id }}"
                                                       value="{{ $costo->monto }}">
                                            </td>
                                            <td>
                                                <button class="btn btn-danger btn-sm btn-eliminar-costo"
                                                        data-id="{{ $costo->id }}">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <button class="btn btn-outline-primary btn-sm" id="btn-agregar-costo">
                                    <i class="fa fa-plus"></i> Agregar Costo Adicional
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para buscar productos --}}
    <div class="modal fade" id="modalBuscarProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buscar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control form-control-lg"
                           id="input-buscar-producto"
                           placeholder="Buscar por código, descripción, marca, referencia..."
                           autofocus>
                    <div id="resultados-busqueda" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para cambiar producto --}}
    <div class="modal fade" id="modalCambiarProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Producto</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cambiar-item-id">
                    <input type="text" class="form-control form-control-lg"
                           id="input-cambiar-producto"
                           placeholder="Buscar producto..."
                           autofocus>
                    <div id="resultados-cambiar" class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {

                // =============================================
                // ACTUALIZAR CAMPOS DEL ITEM (AJAX)
                // =============================================
                $(document).on('change', '.item-field', function() {
                    var itemId = $(this).data('item');
                    var name = $(this).data('name');
                    var value = $(this).val();

                    $.ajax({
                        url: "{{ route('pedidos.actualizar-item') }}",
                        method: 'POST',
                        data: {
                            item_id: itemId,
                            name: name,
                            value: value,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Actualizar fila si es necesario
                                console.log('Item actualizado');
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al actualizar');
                        }
                    });
                });

                // =============================================
                // ACTUALIZAR VARIABLES GENERALES (AJAX)
                // =============================================
                $(document).on('change', '.update-variable', function() {
                    var pedidoId = $(this).data('pedido');
                    var name = $(this).data('variable');
                    var value = $(this).val();

                    $.ajax({
                        url: "{{ route('pedidos.update-variable') }}",
                        method: 'POST',
                        data: {
                            pedido_id: pedidoId,
                            name: name,
                            value: value,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                console.log('Variable actualizada');
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al actualizar');
                        }
                    });
                });

                // =============================================
                // AGREGAR ITEM
                // =============================================
                $('#btn-agregar-item').click(function() {
                    var pedidoId = {{ $pedido->id }};

                    $.ajax({
                        url: "{{ route('pedidos.agregar-item', $pedido->id) }}",
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Recargar la página para mostrar el nuevo item
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al agregar item');
                        }
                    });
                });

                // =============================================
                // ELIMINAR ITEM
                // =============================================
                $(document).on('click', '.btn-eliminar-item', function() {
                    if (!confirm('¿Está seguro de eliminar este item?')) return;

                    var itemId = $(this).data('item');

                    $.ajax({
                        url: "{{ route('pedidos.eliminar-item') }}",
                        method: 'DELETE',
                        data: {
                            item_id: itemId,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al eliminar');
                        }
                    });
                });

                // =============================================
                // BUSCAR PRODUCTOS (MODAL)
                // =============================================
                $('#btn-buscar-producto').click(function() {
                    $('#modalBuscarProducto').modal('show');
                    setTimeout(function() {
                        $('#input-buscar-producto').focus();
                    }, 300);
                });

                var buscarTimeout;

                $('#input-buscar-producto').on('input', function() {
                    clearTimeout(buscarTimeout);
                    var query = $(this).val();

                    if (query.length < 2) {
                        $('#resultados-busqueda').html('');
                        return;
                    }

                    buscarTimeout = setTimeout(function() {
                        $.ajax({
                            url: "{{ route('pedidos.buscar-productos') }}",
                            method: 'POST',
                            data: {
                                q: query,
                                pedido_id: {{ $pedido->id }},
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                var html = '';
                                if (response.length === 0) {
                                    html = '<div class="alert alert-info">No se encontraron productos</div>';
                                } else {
                                    html = '<div class="list-group">';
                                    response.forEach(function(p) {
                                        var btnClass = p.ya_agregado ? 'btn-secondary disabled' : 'btn-primary';
                                        var label = p.ya_agregado ? '✓ Agregado' : 'Agregar';
                                        html += `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${p.codprod}</strong> - ${p.descrip}
                                        <br><small>Marca: ${p.marca || '-'} | Ref: ${p.refere || '-'} | Stock: ${p.existen}</small>
                                    </div>
                                    <button class="btn btn-sm ${btnClass} btn-agregar-producto"
                                            data-codprod="${p.codprod}"
                                            ${p.ya_agregado ? 'disabled' : ''}>
                                        ${label}
                                    </button>
                                </div>
                            `;
                                    });
                                    html += '</div>';
                                }
                                $('#resultados-busqueda').html(html);
                            }
                        });
                    }, 500);
                });

                // =============================================
                // AGREGAR PRODUCTO DESDE BÚSQUEDA
                // =============================================
                $(document).on('click', '.btn-agregar-producto', function() {
                    var codprod = $(this).data('codprod');
                    var pedidoId = {{ $pedido->id }};

                    $.ajax({
                        url: "{{ route('pedidos.agregar-item', $pedido->id) }}",
                        method: 'POST',
                        data: {
                            codprod: codprod,
                            cantidad: 1,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al agregar producto');
                        }
                    });
                });

                // =============================================
                // CAMBIAR PRODUCTO DE UN ITEM
                // =============================================
                $(document).on('click', '.btn-cambiar-producto', function(e) {
                    e.preventDefault();
                    var itemId = $(this).data('item');
                    var codprod = $(this).data('codprod');

                    $('#cambiar-item-id').val(itemId);
                    $('#modalCambiarProducto').modal('show');
                    setTimeout(function() {
                        $('#input-cambiar-producto').focus();
                    }, 300);

                    // Mostrar producto actual
                    $('#input-cambiar-producto').val(codprod);
                    $('#input-cambiar-producto').trigger('input');
                });

                $('#input-cambiar-producto').on('input', function() {
                    clearTimeout(buscarTimeout);
                    var query = $(this).val();
                    var itemId = $('#cambiar-item-id').val();

                    if (query.length < 2) {
                        $('#resultados-cambiar').html('');
                        return;
                    }

                    buscarTimeout = setTimeout(function() {
                        $.ajax({
                            url: "{{ route('pedidos.buscar-productos') }}",
                            method: 'POST',
                            data: {
                                q: query,
                                pedido_id: {{ $pedido->id }},
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                var html = '';
                                if (response.length === 0) {
                                    html = '<div class="alert alert-info">No se encontraron productos</div>';
                                } else {
                                    html = '<div class="list-group">';
                                    response.forEach(function(p) {
                                        var label = p.ya_agregado ? '✓ Ya agregado' : 'Seleccionar';
                                        var btnClass = p.ya_agregado ? 'btn-secondary disabled' : 'btn-success';
                                        html += `
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${p.codprod}</strong> - ${p.descrip}
                                        <br><small>Stock: ${p.existen}</small>
                                    </div>
                                    <button class="btn btn-sm ${btnClass} btn-seleccionar-producto-cambiar"
                                            data-codprod="${p.codprod}"
                                            data-item="${itemId}"
                                            ${p.ya_agregado ? 'disabled' : ''}>
                                        ${label}
                                    </button>
                                </div>
                            `;
                                    });
                                    html += '</div>';
                                }
                                $('#resultados-cambiar').html(html);
                            }
                        });
                    }, 500);
                });

                $(document).on('click', '.btn-seleccionar-producto-cambiar', function() {
                    var codprod = $(this).data('codprod');
                    var itemId = $(this).data('item');

                    $.ajax({
                        url: "{{ route('pedidos.cambiar-producto') }}",
                        method: 'POST',
                        data: {
                            item_id: itemId,
                            codprod: codprod,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al cambiar producto');
                        }
                    });
                });

                // =============================================
                // COSTOS ADICIONALES
                // =============================================
                $('#btn-agregar-costo').click(function() {
                    var pedidoId = {{ $pedido->id }};

                    $.ajax({
                        url: "{{ route('pedidos.agregar-costo-adicional', $pedido->id) }}",
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al agregar costo');
                        }
                    });
                });

                $(document).on('change', '.costo-field', function() {
                    var id = $(this).data('id');
                    var name = $(this).data('name');
                    var value = $(this).val();

                    $.ajax({
                        url: "{{ route('pedidos.actualizar-costo-adicional') }}",
                        method: 'POST',
                        data: {
                            id: id,
                            name: name,
                            value: value,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                console.log('Costo actualizado');
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al actualizar costo');
                        }
                    });
                });

                $(document).on('click', '.btn-eliminar-costo', function() {
                    if (!confirm('¿Está seguro de eliminar este costo adicional?')) return;

                    var id = $(this).data('id');

                    $.ajax({
                        url: "{{ route('pedidos.eliminar-costo-adicional') }}",
                        method: 'DELETE',
                        data: {
                            id: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + xhr.responseJSON?.message || 'Error al eliminar costo');
                        }
                    });
                });

                // =============================================
                // ENTER PARA BUSCAR
                // =============================================
                $('#input-buscar-producto, #input-cambiar-producto').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        $(this).trigger('input');
                    }
                });

            });
        </script>
    @endpush

    @push('styles')
        <style>
            .item-row td {
                vertical-align: middle;
                padding: 4px;
            }
            .item-row .form-control-sm {
                font-size: 12px;
                padding: 2px 4px;
            }
        </style>
    @endpush
@endsection
