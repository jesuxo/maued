{{-- resources/views/compras/documento.blade.php --}}
@extends('layouts.master')

@section('title')
    Compra NRO: {{ $documento->numerod }}
@endsection

@section('css')
    <style>
        .table-light {
            /* Mantén todas las variables existentes */
            --tb-table-hover-bg: #e3f2fd !important; /* Cambia black por el color que prefieras */
            --tb-table-hover-color: #000 !important; /* Ajusta el color del texto si es necesario */
        }
        .card-header-doc {
            background: linear-gradient(45deg, #0072c5, #00a3ff);
            color: white;
        }
        .table-items th {
            background-color: #0072c5;
            color: white;
            font-weight: 500;
        }
        .badge-cerrada { background-color: #28a745; color: white; }
        .badge-abierta { background-color: #007bff; color: white; }
        .badge-pendiente { background-color: #dc3545; color: white; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-header-doc">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-file-text me-2"></i>
                            Documento de Compra: {{ $documento->numerod }}
                        </h5>
                        <div>
                            <a href="/reporte/compra" class="btn btn-sm btn-light me-2">
                                <i class="bi bi-arrow-left"></i> Volver al Reporte
                            </a>
                            <a href="javascript:window.print()" class="btn btn-sm btn-light">
                                <i class="bi bi-printer"></i> Imprimir
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Información de la compra -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <small class="text-muted">Proveedor</small>
                                    <h5>{{ $documento->descrip }}</h5>
                                    @if($documento->codprov)
                                        <small>Código: {{ $documento->codprov }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <small class="text-muted">Fecha</small>
                                    <h6>{{ $documento->fechaformat }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <small class="text-muted">Estado</small>
                                    <h6>
                                        @if($documento->status == 0)
                                            <span class="badge badge-cerrada">Cerrada</span>
                                        @elseif($documento->status == 1)
                                            <span class="badge badge-abierta">Abierta</span>
                                        @elseif($documento->status == 2)
                                            <span class="badge badge-pendiente">Pendiente</span>
                                        @endif
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <small class="text-muted">Tipo</small>
                                    <h6>
                                        @if($documento->tipocom == 'U')
                                            <span class="badge bg-info">Compra</span>
                                        @elseif($documento->tipocom == 'Y')
                                            <span class="badge bg-warning">Devolución</span>
                                        @else
                                            {{ $documento->tipocom }}
                                        @endif
                                    </h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-light">
                                <div class="card-body p-3">
                                    <small class="text-muted">Sucursal</small>
                                    <h6>{{ $documento->sucursal->descrip ?? 'N/A' }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    @if($documento->notas1 || $documento->notas2 || $documento->notas3 || $documento->notas5 || $documento->notas8)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white py-2">
                                        <i class="bi bi-card-text me-2"></i> Notas
                                    </div>
                                    <div class="card-body py-2">
                                        @if($documento->notas1)
                                            <p class="mb-1"><strong>Nota 1:</strong> {{ $documento->notas1 }}</p>
                                        @endif
                                        @if($documento->notas2)
                                            <p class="mb-1"><strong>Nota 2:</strong> {{ $documento->notas2 }}</p>
                                        @endif
                                        @if($documento->notas3)
                                            <p class="mb-1"><strong>Nota 3:</strong> {{ $documento->notas3 }}</p>
                                        @endif
                                        @if($documento->notas5)
                                            <p class="mb-1"><strong>Nota 5:</strong> {{ $documento->notas5 }}</p>
                                        @endif
                                        @if($documento->notas8)
                                            <p class="mb-1"><strong>Nota 8:</strong> {{ $documento->notas8 }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Items de la compra -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">
                                <i class="bi bi-list-ul me-2"></i>
                                Items de la Compra
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-items">
                                    <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="15%">Código</th>
                                        <th width="30%">Descripción</th>
                                        <th width="10%" class="text-center">Cantidad</th>
                                        <th width="10%" class="text-end">Precio</th>
                                        <th width="10%" class="text-end">Costo</th>
                                        <th width="10%" class="text-end">Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $totalGeneral = 0;
                                    @endphp
                                    @forelse($documento->items as $index => $item)
                                        @php
                                            $totalItem = ($item->preciod ?? 0) * ($item->cantidad ?? 0);
                                            $totalGeneral += $totalItem;
                                        @endphp
                                        <tr class="{{ $loop->even ? 'table-light' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->coditem }}</td>
                                            <td>
                                                {{ $item->descrip1 ?? 'N/A' }}
                                                @if($item->descrip2)
                                                    <br><small>{{ $item->descrip2 }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ number_format($item->cantidad ?? 0, 0) }}</td>
                                            <td class="text-end">$ {{ number_format($item->preciod ?? 0, 2, ',', '.') }}</td>
                                            <td class="text-end">$ {{ number_format($item->costod ?? 0, 2, ',', '.') }}</td>
                                            <td class="text-end">$ {{ number_format($totalItem, 2, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                <p class="mt-2">No hay items registrados en esta compra</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                    @if($documento->items->count() > 0)
                                        <tfoot class="">
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>TOTAL GENERAL:</strong></td>
                                            <td class="text-end"><strong>$ {{ number_format($totalGeneral, 2, ',', '.') }}</strong></td>
                                        </tr>
                                        </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Totales adicionales -->
                    @if($documento->monto || $documento->totalprd || $documento->mtototal)
                        <div class="row mt-4">
                            <div class="col-md-6 offset-md-6">
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <i class="bi bi-calculator me-2"></i> Totales del Documento
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            @if($documento->monto)
                                                <tr>
                                                    <td>Monto:</td>
                                                    <td class="text-end">$ {{ number_format($documento->monto, 2, ',', '.') }}</td>
                                                </tr>
                                            @endif
                                            @if($documento->totalprd)
                                                <tr>
                                                    <td>Total Productos:</td>
                                                    <td class="text-end">$ {{ number_format($documento->totalprd, 2, ',', '.') }}</td>
                                                </tr>
                                            @endif
                                            @if($documento->mtototal)
                                                <tr class="fw-bold">
                                                    <td>Total General:</td>
                                                    <td class="text-end">$ {{ number_format($documento->mtototal, 2, ',', '.') }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Seriales si existen -->
                    @if(isset($documento->seriales) && $documento->seriales->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">
                                    <i class="bi bi-upc-scan me-2"></i>
                                    Seriales Asociados
                                    <a href="{{ route('compras.seriales', $documento->id) }}" class="btn btn-sm btn-primary ms-3">
                                        Ver Detalle de Seriales
                                    </a>
                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-info">
                                        <tr>
                                            <th>Serial</th>
                                            <th>Producto</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($documento->seriales->take(5) as $serial)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('seriales.historial', [$serial->coditem, urlencode($serial->nroserial)]) }}"
                                                       target="_blank" class="text-primary">
                                                        <i class="bi bi-clock-history"></i> {{ $serial->nroserial }}
                                                    </a>
                                                </td>
                                                <td>{{ $serial->producto->descrip ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                        @if($documento->seriales->count() > 5)
                                            <tr>
                                                <td colspan="2" class="text-center">
                                                    <a href="{{ route('compras.seriales', $documento->id) }}">
                                                        Ver los {{ $documento->seriales->count() }} seriales...
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12 text-end">
                            <a href="{{ route('compras.reporte') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Volver al Reporte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('build/js/app.js') }}"></script>
@endsection
