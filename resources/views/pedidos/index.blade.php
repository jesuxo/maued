{{-- resources/views/pedidos/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Órdenes de Compra')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Órdenes de Compra</h4>
                        <a href="{{ route('pedidos.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Nueva Orden
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($pedidos as $pedido)
                                <tr>
                                    <td>{{ $pedido->num }}</td>
                                    <td>{{ $pedido->proveedor->descrip ?? $pedido->codprov }}</td>
                                    <td>{{ $pedido->fecha->format('d/m/Y H:i') }}</td>
                                    <td>{{ $pedido->items->count() }}</td>
                                    <td>$ {{ number_format($pedido->total, 2) }}</td>
                                    <td>
                                        @if($pedido->status == '0')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif($pedido->status == '1')
                                            <span class="badge badge-success">Procesado</span>
                                        @else
                                            <span class="badge badge-secondary">{{ $pedido->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('pedidos.show', $pedido->id) }}" class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay órdenes de compra</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>

                        {{ $pedidos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
