{{-- resources/views/pedidos/seriales.blade.php --}}
@extends('layouts.app')

@section('title', 'Seriales - ' . $item->codprod)

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Generar Seriales</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('pedidos.guardar-seriales', $item->id) }}">
                            @csrf
                            <input type="hidden" name="pedido_id" value="{{ $pedidoId }}">

                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="number" name="cantidad" id="cantidad-seriales"
                                       class="form-control" min="1" max="100" value="1">
                            </div>

                            <div class="form-group">
                                <label>Talla</label>
                                <select name="talla" id="talla-seriales" class="form-control">
                                    <option value="">Seleccionar talla</option>
                                    @foreach([18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,35.5,36,36.5,37,37.5,38,38.5,39,39.5,40,40.5,41,41.5,42,42.5,43,43.5,44,44.5,45,46] as $talla)
                                        <option value="{{ $talla }}">{{ $talla }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block">Generar Seriales</button>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Agregar Serial Manual</h5>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" class="form-control" id="agregar-serial-input"
                                   placeholder="Ingrese serial" maxlength="40">
                            <div class="input-group-append">
                                <button class="btn btn-primary" id="btn-agregar-serial">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <span>Seriales Temporales</span>
                        <button class="btn btn-sm btn-danger" id="btn-limpiar-temporales">Limpiar</button>
                    </div>
                    <div class="card-body" style="max-height:400px; overflow-y:auto;">
                        @if(empty($tempseriales))
                            <p class="text-muted">No hay seriales temporales</p>
                        @else
                            <ul class="list-group">
                                @foreach($tempseriales as $serial => $estado)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $serial }}
                                        <button class="btn btn-sm btn-danger btn-eliminar-temporal" data-serial="{{ $serial }}">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Seriales Guardados</h5>
                    </div>
                    <div class="card-body" style="max-height:400px; overflow-y:auto;">
                        @if($item->seriales->isEmpty())
                            <p class="text-muted">No hay seriales guardados</p>
                        @else
                            <ul class="list-group">
                                @foreach($item->seriales as $serial)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $serial->serial }}
                                        <button class="btn btn-sm btn-danger btn-eliminar-serial-guardado"
                                                data-id="{{ $serial->id }}">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-12">
                <a href="{{ route('pedidos.show', $pedidoId) }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Volver al Pedido
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {

                // Agregar serial temporal
                $('#btn-agregar-serial').click(function() {
                    var serial = $('#agregar-serial-input').val().trim();
                    if (!serial) {
                        alert('Ingrese un serial');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('pedidos.agregar-serial-temporal') }}",
                        method: 'POST',
                        data: {
                            item_id: {{ $item->id }},
                            serial: serial,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert(response.message || 'Error al agregar serial');
                            }
                        },
                        error: function(xhr) {
                            alert('Error al agregar serial');
                        }
                    });
                });

                // Enter para agregar serial
                $('#agregar-serial-input').on('keypress', function(e) {
                    if (e.which === 13) {
                        $('#btn-agregar-serial').click();
                    }
                });

                // Eliminar serial temporal
                $(document).on('click', '.btn-eliminar-temporal', function() {
                    var serial = $(this).data('serial');

                    $.ajax({
                        url: "{{ route('pedidos.eliminar-serial-temporal') }}",
                        method: 'DELETE',
                        data: {
                            serial: serial,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            alert('Error al eliminar serial temporal');
                        }
                    });
                });

                // Limpiar temporales
                $('#btn-limpiar-temporales').click(function() {
                    if (!confirm('¿Limpiar todos los seriales temporales?')) return;

                    $.ajax({
                        url: "{{ route('pedidos.limpiar-seriales-temporales') }}",
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
                            alert('Error al limpiar seriales');
                        }
                    });
                });

                // Eliminar serial guardado
                $(document).on('click', '.btn-eliminar-serial-guardado', function() {
                    if (!confirm('¿Eliminar este serial?')) return;

                    var id = $(this).data('id');

                    $.ajax({
                        url: "{{ route('pedidos.eliminar-serial', id) }}",
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Error al eliminar serial');
                        }
                    });
                });

            });
        </script>
    @endpush
@endsection
