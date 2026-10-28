@extends('layouts.master')
@section('title')
    Proveedores
@endsection
@section('css')
    <style>
        .search-box { position: relative; }
        #predictive-results {
            border: 1px solid rgba(0,0,0,0.1);
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 400px;
            overflow-y: auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        #predictive-results .list-group-item {
            border-left: none; border-right: none; border-radius: 0;
            cursor: pointer; transition: all 0.2s;
        }
        #predictive-results .list-group-item:first-child { border-top: none; }
        #predictive-results .list-group-item:last-child { border-bottom: none; }
        #predictive-results .list-group-item:hover {
            background-color: #f8f9fa; transform: translateX(5px);
        }
        #predictive-results .list-group-item.active {
            background-color: #0072c5 !important;
            border-color: #0072c5 !important;
            color: white;
        }
        #predictive-results .list-group-item.active small { color: rgba(255,255,255,0.9) !important; }
        .search-box.loading::after {
            content: ''; position: absolute; right: 40px; top: 50%;
            transform: translateY(-50%); width: 20px; height: 20px;
            border: 2px solid #f3f3f3; border-top: 2px solid #0072c5;
            border-radius: 50%; animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }
        .btn-soft-light:hover, .codprovseleted { background-color: #e0f2ff !important; }
        .nav-pills .nav-link {
            background: #eee !important;
            border-bottom-right-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
        }
        .nav-pills .nav-link.active  {
            background: #0072c5 !important;
            border-bottom-right-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            color: white !important;
        }
        .nav-pills { border-bottom: 1px solid #0072c5; }
        .tdline { border:1px solid #0072c5 !important; font-size: 12px; }
        .tdlineff {
            border-left:1px solid #fff !important; font-size: 12px;
            color: white !important; background-color: #0072c5 !important;
        }
        .monto-pendiente { color: #f59e0b; font-weight: bold; }
        .monto-conciliado { color: #10b981; font-weight: bold; }
        .btn-analisis {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; padding: 5px 15px; border-radius: 20px;
            font-size: 12px; transition: all 0.3s; text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .btn-analisis:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .badge-analisis {
            background: #28a745; color: white; font-size: 10px;
            padding: 2px 8px; border-radius: 10px; margin-left: 5px;
        }
        .nav-link i { margin-right: 5px; }
        .kpi-card-mini {
            background: white; border-radius: 10px; padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 15px;
            transition: transform 0.3s;
        }
        .kpi-card-mini:hover {
            transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .kpi-valor-mini { font-size: 24px; font-weight: bold; margin: 5px 0; }
        .text-primary-custom { color: #667eea; }
        .text-success-custom { color: #28a745; }
        .text-warning-custom { color: #f59e0b; }
        .table-danger-row { background-color: #fff5f5 !important; }
        .progress-bar-custom { transition: width 0.5s ease; }
        .cxp-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        #resumenGeneralCxpCard { transition: all 0.3s ease; }
        #resumenGeneralCxpCard.hidden { display: none; }
        .sortable-cxp {
            user-select: none; cursor: pointer !important;
            transition: background-color 0.15s;
        }
        .sortable-cxp:hover { background-color: #e9ecef !important; }
        .sortable-cxp i { margin-left: 3px; }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-xxl-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0" id="addCategoryLabel">Buscar Proveedores</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('proveedores.index') }}" method="GET" autocomplete="off" class="needs-validation" id="proveedorForm">
                        <input type="hidden" id="codprov" name="codprov" value="{{ $codprov ?? '' }}">
                        <div class="row">
                            <div class="col-xxl-12">
                                <div class="search-box mb-3 position-relative">
                                    <input type="text" class="form-control search" id="busqueda" name="busqueda"
                                           value="{{ $busqueda ?? '' }}"
                                           placeholder="Buscar por nombre, RIF, teléfono..."
                                           autocomplete="off">
                                    <i class="ri-search-line search-icon"></i>
                                    <div id="predictive-results" class="list-group position-absolute w-100 shadow"
                                         style="z-index: 1000; max-height: 400px; overflow-y: auto; display: none; background: white; border-radius: 0 0 8px 8px;">
                                    </div>
                                </div>
                                <div class="invalid-feedback">Ingrese el nombre o código del proveedor</div>
                            </div>

                            <div class="col-xxl-12 col-lg-6">
                                @if($busqueda != '')
                                    <div class="accordion accordion-flush filter-accordion">
                                        <div class="card-body border-bottom p-0">
                                            @if(isset($proveedores) and count($proveedores)>0)
                                                <div>
                                                    <p class="text-muted fs-13 mb-3">Resultados para: {{$busqueda}}</p>
                                                    @foreach($proveedores as $prov)
                                                        <div class="d-flex align-items-center">
                                                            <a href="javascript:;" onclick="$('#codprov').val('{{$prov->codprov}}'); $('#proveedorForm').submit()"
                                                               class="card btn btn-soft-light d-flex p-2
                                                               {{(isset($codprov) and $codprov != '' and $codprov == $prov->codprov) ? 'codprovseleted' : ''}}
                                                               border-bottom border-bottom-dashed cursor-pointer"
                                                               style="text-align: left; flex-grow: 1;">
                                                                <div class="flex-grow-1">
                                                                    <h5>{{$prov->descrip}}</h5>
                                                                    <p class="text-muted mb-0">{{$prov->codprov}}</p>
                                                                </div>
                                                            </a>
                                                            <a href="{{ route('proveedores.productos-panel', $prov->codprov) }}"
                                                               class="btn btn-sm btn-outline-info ms-2"
                                                               target="_blank"
                                                               data-bs-toggle="tooltip"
                                                               title="Ver análisis de compras">
                                                                <i class="ri-bar-chart-2-line"></i>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div>
                                                    <p class="text-muted fs-13 mb-3">No se encontraron proveedores para la búsqueda: <b>{{$busqueda}}</b></p>
                                                    <a class="card btn btn-soft-light d-flex p-2 border-bottom border-bottom-dashed cursor-pointer"
                                                       style="text-align: left; display: none">
                                                        <div class="flex-grow-1" href="#modalProveedor" data-bs-toggle="modal">
                                                            <h6>+1 PROVEEDOR NUEVO</h6>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </form>

                    <form name="form2" id="form2" class="tablelist-form"
                          action="{{(isset($proveedor->codprov) and $proveedor->codprov != '') ? route('proveedoresupdate') : route('proveedores.store')}}"
                          autocomplete="off" method="post">
                        @csrf
                        @method('POST')
                        <div class="modal fade" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedor" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header px-4 pt-4">
                                        <h5 class="modal-title" id="exampleModalLabel">
                                            Información del Proveedor
                                            {{(isset($proveedor->codprov) and $proveedor->codprov != '') ? $proveedor->descrip : ' nuevo' }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="close-modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row">
                                            <div id="alert-error-msg" class="d-none alert alert-danger py-2"></div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="proveedor-cod-field" class="form-label">Código</label>
                                                    <input type="text" id="proveedor-cod-field" name="codprov"
                                                           value="{{(isset($proveedor->codprov) and $proveedor->codprov !== '') ? $proveedor->codprov : ((isset($busqueda) and $busqueda != '') ? $busqueda : '')}}"
                                                           class="form-control" placeholder="Ej: PROV001" readonly required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="id3-cod-field" class="form-label">RIF / Documento</label>
                                                    <input type="text" id="id3-cod-field" name="id3" class="form-control"
                                                           value="{{(isset($proveedor->codprov) and $proveedor->codprov !== '') ? $proveedor->id3 : ''}}"
                                                           placeholder="Ej: J-12345678-5">
                                                </div>
                                            </div>
                                            <div class="col-lg-12">
                                                <div class="mb-2">
                                                    <label for="descrip-name-field" class="form-label">Nombre / Razón Social</label>
                                                    <input type="text" id="descrip-name-field" value="{{(isset($proveedor->descrip)) ? $proveedor->descrip : ''}}"
                                                           name="descrip" class="form-control" placeholder="Ej: Proveedores C.A." required>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="email-field" class="form-label">Email</label>
                                                    <input type="email" name="email" id="email-field" class="form-control"
                                                           value="{{(isset($proveedor->codprov)) ? $proveedor->email : ''}}"
                                                           placeholder="Ej: correo@proveedor.com">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="clase-field" class="form-label">Clase</label>
                                                    <input type="text" name="clase" id="clase-field"
                                                           value="{{(isset($proveedor->codprov)) ? $proveedor->clase : ''}}"
                                                           class="form-control" placeholder="">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="phone-field" class="form-label">Teléfono</label>
                                                    <input type="text" name="telef" id="phone-field" class="form-control"
                                                           value="{{(isset($proveedor->codprov)) ? $proveedor->telef : ''}}"
                                                           placeholder="Ej: 0414-12345678">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="movil-field" class="form-label">Celular</label>
                                                    <input type="text" name="movil" id="movil-field" class="form-control"
                                                           value="{{(isset($proveedor->codprov)) ? $proveedor->movil : ''}}"
                                                           placeholder="Ej: 5841412345678">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="represent-field" class="form-label">Representante</label>
                                                    <input type="text" name="represent" id="represent-field"
                                                           value="{{(isset($proveedor->codprov)) ? $proveedor->represent : ''}}"
                                                           class="form-control" placeholder="">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="direc1-field" class="form-label">Dirección 1</label>
                                                    <input type="text" name="direc1" id="direc1-field" class="form-control"
                                                           value="{{(isset($proveedor->codprov)) ? $proveedor->direc1 : ''}}"
                                                           placeholder="Dirección principal">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="mb-2">
                                                    <label for="direc2-field" class="form-label">Dirección 2</label>
                                                    <input type="text" name="direc2" id="direc2-field" class="form-control"
                                                           value="{{(isset($proveedor->codprov)) ? $proveedor->direc2 : ''}}"
                                                           placeholder="">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-check mt-3">
                                                    <input class="form-check-input" type="checkbox" name="activo" id="activo-field" value="1"
                                                        {{ !isset($proveedor->activo) || $proveedor->activo == 1 ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="activo-field">
                                                        Proveedor Activo
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="button" class="btn btn-ghost-danger" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="button" onclick="$('#form2').submit()" class="btn btn-success">
                                                {{(isset($proveedor->codprov) and $proveedor->codprov !== '') ? 'Modificar' : 'Crear'}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xxl-9">
            @if(isset($proveedor) and isset($proveedor->descrip))
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title mb-0 flex-grow-1">{{$proveedor->descrip}}</h5>
                            <div class="flex-shrink-0">
                                <p class="mb-0">RIF: <b>{{$proveedor->id3 ?? 'N/A'}}</b></p>
                            </div>
                            <div class="flex-shrink-0" style="margin-left: 15px;">
                                <a href="{{ route('proveedores.productos-panel', $proveedor->codprov) }}"
                                   class="btn btn-primary"
                                   target="_blank"
                                   data-bs-toggle="tooltip"
                                   title="Ver análisis detallado de compras vs ventas">
                                    <i class="ri-bar-chart-2-line"></i>
                                    Análisis de Compras
                                    <span class="badge-analisis">Nuevo</span>
                                </a>
                            </div>
                            <div class="flex-shrink-0" style="margin-left: 20px;">
                                <a class="btn btn-primary" href="#modalProveedor" data-bs-toggle="modal">Modificar</a>
                            </div>
                        </div>
                        @if($tab == 'tab1')
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tbody>
                                            @if(isset($proveedor->direc1) and strlen($proveedor->direc1) > 2)
                                                <tr bgcolor="#eee"><td width="25%">Dirección 1</td><td width="75%" class="fw-medium">{{$proveedor->direc1}}</td></tr>
                                            @endif
                                            @if(isset($proveedor->direc2) and strlen($proveedor->direc2) > 2)
                                                <tr><td>Dirección 2</td><td class="fw-medium">{{$proveedor->direc2}}</td></tr>
                                            @endif
                                            @if(isset($proveedor->email) and strlen($proveedor->email) > 2)
                                                <tr bgcolor="#eee"><td>Email</td><td class="fw-medium">{{$proveedor->email}}</td></tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tbody>
                                            @if(isset($proveedor->telef) and strlen($proveedor->telef) > 2)
                                                <tr bgcolor="#eee"><td width="25%">Teléfono</td><td width="75%" class="fw-medium">{{$proveedor->telef}}</td></tr>
                                            @endif
                                            @if(isset($proveedor->movil) and strlen($proveedor->movil) > 2)
                                                <tr><td>Celular</td><td class="fw-medium">{{$proveedor->movil}}</td></tr>
                                            @endif
                                            @if(isset($proveedor->represent) and strlen($proveedor->represent) > 2)
                                                <tr bgcolor="#eee"><td>Representante</td><td class="fw-medium">{{$proveedor->represent}}</td></tr>
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="d-flex align-items-center flex-wrap gap-3 mb-4">
                            <ul class="nav nav-pills flex-grow-1 mb-0" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ ($tab == 'tab5') ? 'active' : '' }}"
                                       href="{{ route('proveedores.index', ['codprov' => $proveedor->codprov, 'tab' => 'tab5']) }}"
                                       role="tab">
                                        <i class="ri-shopping-cart-line"></i> Compras
                                        @if(isset($compras) && $compras instanceof \Illuminate\Pagination\LengthAwarePaginator && $compras->total() > 0)
                                            <span class="badge bg-info ms-1">{{ $compras->total() }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ ($tab == 'tab6') ? 'active' : '' }}"
                                       href="{{ route('proveedores.index', ['codprov' => $proveedor->codprov, 'tab' => 'tab6']) }}"
                                       role="tab">
                                        <i class="ri-bank-card-line"></i> Cuentas por Pagar
                                        @if(isset($totalCxpDeuda) && $totalCxpDeuda > 0)
                                            <span class="badge bg-warning ms-1">${{ number_format($totalCxpDeuda, 0) }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="nav-item d-none">
                                    <a class="nav-link {{ ($tab == 'tab1') ? 'active' : '' }}" href="{{ route('proveedores.index', ['codprov' => $proveedor->codprov, 'tab' => 'tab1']) }}" role="tab">Pagos Pendientes</a>
                                </li>
                                <li class="nav-item d-none">
                                    <a class="nav-link {{ ($tab == 'tab2') ? 'active' : '' }}" href="{{ route('proveedores.index', ['codprov' => $proveedor->codprov, 'tab' => 'tab2']) }}" role="tab">Pagos Realizados</a>
                                </li>
                                <li class="nav-item d-none">
                                    <a class="nav-link {{ ($tab == 'tab3') ? 'active' : '' }}" href="{{ route('proveedores.index', ['codprov' => $proveedor->codprov, 'tab' => 'tab3']) }}" role="tab">Resumen</a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content">
                            {{-- TAB 1 --}}
                            @if($tab == 'tab1')
                                <div class="tab-pane active" role="tabpanel">
                                    <div class="card"><div class="card-body">
                                            <div class="table-responsive table-card mb-1">
                                                <table width="100%" border="0" class="table align-middle table-nowrap">
                                                    <tr bgcolor="#fff">
                                                        <td width="10%" height="30" align="center" class="tdlineff">Fecha Viaje</td>
                                                        <td width="8%" align="center" class="tdlineff">Viaje</td>
                                                        <td width="15%" align="center" class="tdlineff">Cliente</td>
                                                        <td width="12%" align="center" class="tdlineff">Modelo</td>
                                                        <td width="5%" align="center" class="tdlineff">Cant.</td>
                                                        <td width="10%" align="center" class="tdlineff">Transporte</td>
                                                        <td width="10%" align="center" class="tdlineff">Retención</td>
                                                        <td width="10%" align="center" class="tdlineff">Monto a Pagar</td>
                                                        <td width="10%" align="center" class="tdlineff">Acciones</td>
                                                    </tr>
                                                    @php $totalPendiente = 0; @endphp
                                                    @forelse($pagosPendientes as $pago)
                                                        @php $totalPendiente += $pago->monto_esperado_cliente; @endphp
                                                        <tr>
                                                            <td height="30" align="center">{{ $pago->viaje->fecha_inicio->format('d/m/Y') }}</td>
                                                            <td align="center"><a href="#" onclick="verViaje({{ $pago->viaje_id }})">{{ $pago->viaje->folio ?? $pago->viaje_id }}</a></td>
                                                            <td align="left">{{ $pago->cliente->descrip ?? 'N/A' }}</td>
                                                            <td align="left">{{ $pago->modelo_moto }}</td>
                                                            <td align="center">{{ $pago->cantidad }}</td>
                                                            <td align="right">${{ number_format($pago->monto_transporte_proveedor, 2) }}</td>
                                                            <td align="right">${{ number_format($pago->retencion_proveedor, 2) }}</td>
                                                            <td align="right" class="monto-pendiente">${{ number_format($pago->monto_esperado_cliente, 2) }}</td>
                                                            <td align="center">
                                                                <button class="btn btn-sm btn-success" onclick="marcarPagado({{ $pago->id }}, {{ $pago->monto_esperado_cliente }})">
                                                                    <i class="ri-check-line"></i> Pagar
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="9" align="center" height="50">No hay pagos pendientes</td></tr>
                                                    @endforelse
                                                    @if($pagosPendientes->count() > 0)
                                                        <tr bgcolor="#eee">
                                                            <td colspan="7" align="right"><strong>TOTAL PENDIENTE:</strong></td>
                                                            <td align="right" class="monto-pendiente"><strong>${{ number_format($totalPendiente, 2) }}</strong></td>
                                                            <td></td>
                                                        </tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div></div>
                                </div>
                            @endif

                            {{-- TAB 2 --}}
                            @if($tab == 'tab2')
                                <div class="tab-pane active" role="tabpanel">
                                    <div class="card"><div class="card-body">
                                            <div class="table-responsive table-card mb-1">
                                                <table width="100%" border="0" class="table align-middle table-nowrap">
                                                    <tr bgcolor="#fff">
                                                        <td width="10%" height="30" align="center" class="tdlineff">Fecha Pago</td>
                                                        <td width="10%" align="center" class="tdlineff">Fecha Viaje</td>
                                                        <td width="8%" align="center" class="tdlineff">Viaje</td>
                                                        <td width="15%" align="center" class="tdlineff">Cliente</td>
                                                        <td width="12%" align="center" class="tdlineff">Modelo</td>
                                                        <td width="5%" align="center" class="tdlineff">Cant.</td>
                                                        <td width="10%" align="center" class="tdlineff">Esperado</td>
                                                        <td width="10%" align="center" class="tdlineff">Pagado</td>
                                                        <td width="10%" align="center" class="tdlineff">Diferencia</td>
                                                        <td width="10%" align="center" class="tdlineff">Notas</td>
                                                    </tr>
                                                    @php $totalEsperado = 0; $totalPagado = 0; @endphp
                                                    @forelse($pagosRealizados as $pago)
                                                        @php $totalEsperado += $pago->monto_esperado_cliente; $totalPagado += $pago->monto_real_cliente; @endphp
                                                        <tr>
                                                            <td height="30" align="center">{{ $pago->fecha_conciliacion ? \Carbon\Carbon::parse($pago->fecha_conciliacion)->format('d/m/Y') : 'N/A' }}</td>
                                                            <td align="center">{{ $pago->viaje->fecha_inicio->format('d/m/Y') }}</td>
                                                            <td align="center"><a href="#" onclick="verViaje({{ $pago->viaje_id }})">{{ $pago->viaje->folio ?? $pago->viaje_id }}</a></td>
                                                            <td align="left">{{ $pago->cliente->descrip ?? 'N/A' }}</td>
                                                            <td align="left">{{ $pago->modelo_moto }}</td>
                                                            <td align="center">{{ $pago->cantidad }}</td>
                                                            <td align="right">${{ number_format($pago->monto_esperado_cliente, 2) }}</td>
                                                            <td align="right" class="monto-conciliado">${{ number_format($pago->monto_real_cliente, 2) }}</td>
                                                            <td align="right" class="{{ ($pago->diferencia ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($pago->diferencia ?? 0, 2) }}</td>
                                                            <td align="left"><small>{{ Str::limit($pago->notas_conciliacion, 20) }}</small></td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="10" align="center" height="50">No hay pagos realizados</td></tr>
                                                    @endforelse
                                                    @if($pagosRealizados->count() > 0)
                                                        <tr bgcolor="#eee">
                                                            <td colspan="6" align="right"><strong>TOTALES:</strong></td>
                                                            <td align="right"><strong>${{ number_format($totalEsperado, 2) }}</strong></td>
                                                            <td align="right" class="monto-conciliado"><strong>${{ number_format($totalPagado, 2) }}</strong></td>
                                                            <td align="right"><strong class="{{ ($totalPagado - $totalEsperado) >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($totalPagado - $totalEsperado, 2) }}</strong></td>
                                                            <td></td>
                                                        </tr>
                                                    @endif
                                                </table>
                                            </div>
                                        </div></div>
                                </div>
                            @endif

                            {{-- TAB 3 --}}
                            @if($tab == 'tab3')
                                <div class="tab-pane active" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-info text-white"><h6 class="mb-0">Resumen de Pagos por Mes</h6></div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <thead><tr><th>Período</th><th class="text-end">Pendiente</th><th class="text-end">Pagado</th><th class="text-end">Total</th></tr></thead>
                                                            <tbody>
                                                            @forelse($resumenPagos as $resumen)
                                                                <tr>
                                                                    <td>{{ \Carbon\Carbon::create()->month($resumen->mes)->format('F') }} {{ $resumen->anio }}</td>
                                                                    <td class="text-end monto-pendiente">${{ number_format($resumen->total_pendiente ?? 0, 2) }}</td>
                                                                    <td class="text-end monto-conciliado">${{ number_format($resumen->total_pagado ?? 0, 2) }}</td>
                                                                    <td class="text-end">${{ number_format(($resumen->total_pendiente ?? 0) + ($resumen->total_pagado ?? 0), 2) }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr><td colspan="4" class="text-center">No hay datos de pagos</td></tr>
                                                            @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-success text-white"><h6 class="mb-0">Estadísticas</h6></div>
                                                <div class="card-body">
                                                    <div class="mb-3"><label>Total Pagos Pendientes</label><h3 class="monto-pendiente">${{ number_format($pagosPendientes->sum('monto_esperado_cliente'), 2) }}</h3></div>
                                                    <div class="mb-3"><label>Total Pagos Realizados</label><h3 class="monto-conciliado">${{ number_format($pagosRealizados->sum('monto_real_cliente'), 2) }}</h3></div>
                                                    <div><label>Cantidad de Viajes</label><h3>{{ $pagosPendientes->count() + $pagosRealizados->count() }}</h3></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- TAB 5 --}}
                            @if($tab == 'tab5')
                                <div class="tab-pane active" role="tabpanel">
                                    <div class="card">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="ri-shopping-cart-line"></i> Compras realizadas a {{ $proveedor->descrip }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-12">
                                                    <form method="GET" action="{{ route('proveedores.index') }}" class="row g-2">
                                                        <input type="hidden" name="codprov" value="{{ $proveedor->codprov }}">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Fecha Desde</label>
                                                            <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label">Fecha Hasta</label>
                                                            <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
                                                        </div>
                                                        <div class="col-md-2 d-flex align-items-end">
                                                            <button type="submit" class="btn btn-primary btn-sm"><i class="ri-filter-3-line"></i> Filtrar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover table-bordered">
                                                    <thead class="table-light">
                                                    <tr>
                                                        <th width="8%">Fecha</th>
                                                        <th width="10%">Documento</th>
                                                        <th width="8%">Tipo</th>
                                                        <th width="10%">Unidades</th>
                                                        <th width="12%">Monto</th>
                                                        <th width="15%">Sucursal</th>
                                                        <th width="15%">Observaciones</th>
                                                        <th width="12%">Acciones</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @forelse($compras ?? [] as $compra)
                                                        @php
                                                            $tipoTexto = $compra->tipocom == 'H' ? 'Compra' : 'Devolución';
                                                            $tipoColor = $compra->tipocom == 'H' ? 'success' : 'danger';
                                                        @endphp
                                                        <tr>
                                                            <td>{{ \Carbon\Carbon::parse($compra->fechae)->format('d/m/Y') }}</td>
                                                            <td><strong>{{ $compra->numerod }}</strong></td>
                                                            <td><span class="badge bg-{{ $tipoColor }}">{{ $tipoTexto }}</span></td>
                                                            <td class="text-end {{ $compra->tipocom == 'I' ? 'text-danger' : '' }}">{{ number_format($compra->total_unidades_calculado ?? $compra->totalprd, 0) }}</td>
                                                            <td class="text-end {{ $compra->tipocom == 'I' ? 'text-danger' : '' }}">${{ number_format($compra->total_monto_calculado ?? $compra->monto, 2) }}</td>
                                                            <td>{{ $compra->sucursal->descrip ?? 'N/A' }}</td>
                                                            <td><small>{{ Str::limit($compra->descrip ?? '', 30) }}</small></td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-outline-primary ver-compra" data-id="{{ $compra->id }}" data-bs-toggle="modal" data-bs-target="#compraModal">
                                                                    <i class="ri-file-list-line"></i> Ver
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="8" class="text-center py-4">
                                                                <i class="ri-inbox-line fs-1 text-muted"></i>
                                                                <p class="text-muted mt-2">No hay compras registradas para este proveedor</p>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- TAB 6 --}}
                            @if($tab == 'tab6')
                                <div class="tab-pane active" role="tabpanel">
                                    <div class="card">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                                            <h6 class="mb-0"><i class="ri-bank-card-line"></i> Cuentas por Pagar - {{ $proveedor->descrip }}</h6>
                                            <div class="d-flex gap-2 mt-2 mt-sm-0">
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" class="form-check-input" id="solovencidosSwitch">
                                                    <label class="form-check-label" for="solovencidosSwitch">Solo vencidos</label>
                                                </div>
                                                <div>
                                                    <input type="date" id="fechavenceFilter" class="form-control form-control-sm" style="width: 150px;">
                                                </div>
                                                <button type="button" id="btnFiltrarCxp" class="btn btn-primary btn-sm">
                                                    <i class="ri-filter-3-line"></i> Filtrar
                                                </button>
                                                <button type="button" id="btnActualizarCxp" class="btn btn-secondary btn-sm">
                                                    <i class="ri-refresh-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div id="cxp-loading" class="text-center py-4 d-none">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                                <p class="mt-2">Cargando cuentas por pagar...</p>
                                            </div>
                                            <div id="cxp-content"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                {{-- Resumen General --}}
                <div class="card mt-3" id="resumenGeneralCxpCard">
                    <div class="card-header bg-warning bg-opacity-25">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">
                                <i class="ri-bank-card-line"></i> Resumen General - Cuentas por Pagar
                                <span class="badge bg-warning ms-2" id="totalProveedoresCxp">0</span>
                            </h6>
                            <div class="d-flex gap-2 mt-2 mt-sm-0 flex-wrap">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="resumenSoloVencidosSwitch">
                                    <label class="form-check-label" for="resumenSoloVencidosSwitch">Solo vencidos</label>
                                </div>
                                <div>
                                    <input type="date" id="resumenFechavenceFilter" class="form-control form-control-sm" style="width: 150px;">
                                </div>
                                <button type="button" id="btnFiltrarResumenGeneral" class="btn btn-primary btn-sm">
                                    <i class="ri-filter-3-line"></i> Filtrar
                                </button>
                                <button type="button" id="btnActualizarResumenGeneral" class="btn btn-secondary btn-sm">
                                    <i class="ri-refresh-line"></i>
                                </button>
                                <button type="button" id="btnAnalisisGlobalCxp" class="btn btn-dark btn-sm">
                                    <i class="ri-bar-chart-box-line"></i> Análisis Gerencial Global
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="resumen-general-loading" class="text-center py-4 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando resumen de cuentas por pagar...</p>
                        </div>
                        <div id="resumen-general-content"></div>
                        <div id="analisis-global-cxp" class="mt-3" style="display:none;"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal compra --}}
    <div class="modal fade" id="compraModal" tabindex="-1" aria-labelledby="compraModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="compraModalLabel"><i class="ri-file-list-line"></i> Detalle de Compra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="compraModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
                        <p class="mt-2">Cargando información de la compra...</p>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
            </div>
        </div>
    </div>

    {{-- Modal pago --}}
    <div class="modal fade" id="modalPago" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pago a Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('proveedores.marcar-pagado') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="pago_id" id="pago_id">
                        <div class="mb-3"><label class="form-label">Monto Esperado</label><input type="text" class="form-control" id="monto_esperado" readonly></div>
                        <div class="mb-3"><label class="form-label">Monto Real <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" class="form-control" name="monto_real" id="monto_real" required></div>
                        <div class="mb-3"><label class="form-label">Fecha de Pago <span class="text-danger">*</span></label><input type="date" class="form-control" name="fecha_pago" value="{{ date('Y-m-d') }}" required></div>
                        <div class="mb-3"><label class="form-label">Notas</label><textarea class="form-control" name="notas" rows="2"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Registrar Pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ============================================
        // HELPERS GLOBALES (declarados primero)
        // ============================================
        function formatNumber(value) {
            if (value === undefined || value === null || isNaN(value)) return '0,00';
            return parseFloat(value).toLocaleString('es-VE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function parseFechaCxp(str) {
            if (!str) return 0;
            const partes = String(str).split('/');
            if (partes.length !== 3) return 0;
            const d = parseInt(partes[0], 10);
            const m = parseInt(partes[1], 10) - 1;
            const y = parseInt(partes[2], 10);
            return new Date(y, m, d).getTime();
        }

        // ============================================
        // ESTADO GLOBAL
        // ============================================
        let cxpSortState = { column: null, direction: 'asc' };
        let cxpDataCache = null;
        let cxpTotalsCache = { monto: 0, abonado: 0, deuda: 0 };
        let rgSortState  = { column: null, direction: 'asc' };
        let rgDataCache  = null;
        let rgTotalsCache = { monto: 0, abonado: 0, deuda: 0 };
        let rgFiltroTexto = '';
        // ============================================
        // CONFIGURACIÓN AJAX
        // ============================================
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // ============================================
        // FUNCIONES BÁSICAS
        // ============================================
        function marcarPagado(id, montoEsperado) {
            $('#pago_id').val(id);
            $('#monto_esperado').val('$' + montoEsperado.toFixed(2));
            $('#monto_real').val(montoEsperado.toFixed(2));
            $('#modalPago').modal('show');
        }

        function verViaje(id) {
            window.open(`/viajes/${id}/ver`, '_blank');
        }

        // ============================================
        // BÚSQUEDA PREDICTIVA
        // ============================================
        $(document).ready(function() {
            const $searchInput = $('#busqueda');
            const $resultsContainer = $('#predictive-results');
            const $searchBox = $('.search-box');
            const $proveedorForm = $('#proveedorForm');
            const $codprovInput = $('#codprov');

            function cargarTodosProveedores() {
                $searchBox.addClass('loading');
                $.ajax({
                    url: '{{ route("proveedores.buscarPredictivo") }}',
                    type: 'POST',
                    data: { term: $searchInput.val() },
                    success: function(data) {
                        $searchBox.removeClass('loading');
                        mostrarResultados(data);
                    },
                    error: function(xhr) {
                        $searchBox.removeClass('loading');
                        console.error('Error al cargar proveedores:', xhr);
                    }
                });
            }

            function seleccionarProveedor(codprov, descrip) {
                $codprovInput.val(codprov);
                $searchInput.val(descrip);
                $resultsContainer.hide();
                $proveedorForm.submit();
            }

            function mostrarResultados(proveedores) {
                if (proveedores.length === 0) {
                    $resultsContainer.html(`
                        <div class="list-group-item text-muted py-3 text-center">
                            <i class="ri-inbox-line fs-4"></i><br>No se encontraron proveedores
                        </div>
                    `).show();
                    return;
                }

                let html = '';
                proveedores.forEach(function(prov, index) {
                    const descripEscaped = prov.descrip ? prov.descrip.replace(/"/g, '&quot;') : '';
                    html += `
                        <a href="javascript:void(0);" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3 proveedor-item"
                           data-index="${index}"
                           data-codprov="${prov.codprov || ''}"
                           data-descrip="${descripEscaped}">
                            <div>
                                <strong>${prov.descrip || 'Sin nombre'}</strong><br>
                                <small class="text-muted">
                                    <i class="ri-price-tag-3-line"></i> ${prov.codprov || 'N/A'}
                                    ${prov.id3 ? '| <i class="ri-id-card-line"></i> ' + prov.id3 : ''}
                                    ${prov.telef ? '| <i class="ri-phone-line"></i> ' + prov.telef : ''}
                                </small>
                            </div>
                            <i class="ri-arrow-right-s-line fs-4 text-primary"></i>
                        </a>
                    `;
                });

                $resultsContainer.html(html).show();

                $resultsContainer.find('.proveedor-item').click(function() {
                    const codprov = $(this).data('codprov');
                    const descrip = $(this).data('descrip');
                    seleccionarProveedor(codprov, descrip);
                });
            }

            $searchInput.on('focus', cargarTodosProveedores);

            let busquedaRgTimer;
            $searchInput.on('keyup', function() {
                cargarTodosProveedores();

                // Filtro en tiempo real sobre la tabla de Cuentas por Pagar (Resumen General)
                clearTimeout(busquedaRgTimer);
                busquedaRgTimer = setTimeout(function() {
                    rgFiltroTexto = $searchInput.val().trim();
                    if (rgDataCache && rgDataCache.length > 0) {
                        renderResumenGeneral(rgDataCache, rgTotalsCache);
                    }
                }, 250); // debounce
            });

            // También escuchar input (por si pegan o borran)
            $searchInput.on('input', function() {
                clearTimeout(busquedaRgTimer);
                busquedaRgTimer = setTimeout(function() {
                    rgFiltroTexto = $searchInput.val().trim();
                    if (rgDataCache && rgDataCache.length > 0) {
                        renderResumenGeneral(rgDataCache, rgTotalsCache);
                    }
                }, 250);
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-box').length) {
                    $resultsContainer.hide();
                }
            });

            $resultsContainer.on('click', function(e) { e.stopPropagation(); });

            if ($searchInput.val()) cargarTodosProveedores();

            $searchInput.select();

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // ============================================
        // DETALLE DE COMPRA
        // ============================================
        $(document).on('click', '.ver-compra', function() {
            const compraId = $(this).data('id');
            const $modalBody = $('#compraModalBody');
            $modalBody.html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
                    <p class="mt-2">Cargando información de la compra...</p>
                </div>
            `);
            $.ajax({
                url: '{{ route("compras.documento-ajax") }}',
                type: 'POST',
                data: { id: compraId, _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        mostrarDetalleCompra(response.compra, response.items);
                    } else {
                        $modalBody.html(`<div class="alert alert-danger"><i class="ri-error-warning-line"></i> Error al cargar el detalle de la compra</div>`);
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    $modalBody.html(`<div class="alert alert-danger"><i class="ri-error-warning-line"></i> Error al cargar el detalle de la compra</div>`);
                }
            });
        });

        function mostrarDetalleCompra(compra, items) {
            const signo = compra.tipocom == 'I' ? -1 : 1;
            const tipoTexto = compra.tipocom == 'H' ? 'Compra' : 'Devolución';
            const tipoColor = compra.tipocom == 'H' ? 'success' : 'danger';

            let totalCalculado = 0;
            let totalUnidades = 0;

            items.forEach(function(item) {
                totalCalculado += item.cantidad * item.preciod;
                totalUnidades += item.cantidad;
            });

            let itemsHtml = '';
            items.forEach(function(item) {
                const itemTotal = item.cantidad * item.preciod;
                itemsHtml += `
                    <tr>
                        <td><small>${item.coditem}</small></td>
                        <td>${item.descrip1 || 'Sin descripción'}</td>
                        <td class="text-center">${item.cantidad}</td>
                        <td class="text-end">$${Number(item.preciod).toFixed(2)}</td>
                        <td class="text-end">$${Number(item.costod).toFixed(2)}</td>
                        <td class="text-end">$${Number(item.costod2).toFixed(2)}</td>
                        <td class="text-end">$${Number(item.costod3).toFixed(2)}</td>
                        <td class="text-end">$${itemTotal.toFixed(2)}</td>
                    </tr>
                `;
            });

            const html = `
                <div class="mb-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Documento:</strong> ${compra.numerod}</p>
                            <p><strong>Fecha:</strong> ${new Date(compra.fechae).toLocaleDateString('es-VE')}</p>
                            <p><strong>Tipo:</strong> <span class="badge bg-${tipoColor}">${tipoTexto}</span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Proveedor:</strong> ${compra.codprov}</p>
                            <p><strong>Sucursal:</strong> ${compra.sucursal ? compra.sucursal.descrip : 'N/A'}</p>
                            <p><strong>Total Unidades:</strong> <span class="fw-bold">${totalUnidades}</span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Total monto:</strong> <span class="fw-bold">$${totalCalculado.toFixed(2)}</span></p>
                        </div>
                    </div>
                    ${compra.descrip ? `<div class="mt-2"><strong>Observaciones:</strong> ${compra.descrip}</div>` : ''}
                </div>
                <h6 class="mb-2">Productos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th><th>Producto</th><th class="text-center">Cantidad</th>
                                <th class="text-end">Precio</th><th class="text-end">Costo</th>
                                <th class="text-end">Costo2</th><th class="text-end">Costo3</th>
                                <th class="text-end">Total Item</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">TOTALES:</td>
                                <td class="text-end">-</td><td class="text-end">-</td>
                                <td class="text-end">-</td><td class="text-end">-</td>
                                <td class="text-end ${signo < 0 ? 'text-danger' : 'text-success'}">$${totalCalculado.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;

            $('#compraModalBody').html(html);
        }

        // ============================================
        // CXP DEL PROVEEDOR (TAB 6)
        // ============================================
        function cargarCuentasPorPagar(codprov, soloResumen = false, solovencidos = false, fechavence = '') {
            const $content = $('#cxp-content');
            const $loading = $('#cxp-loading');

            $loading.removeClass('d-none');
            $content.addClass('d-none').html('');

            $.ajax({
                url: `/proveedores/${codprov}/cuentas-por-pagar`,
                type: 'GET',
                data: {
                    soloResumen  : soloResumen  ? 1 : 0,
                    solovencidos : solovencidos ? 1 : 0,
                    fechavence   : fechavence
                },
                success: function(response) {
                    $loading.addClass('d-none');
                    $content.removeClass('d-none');
                    if (response.success) {
                        if (response.soloResumen) {
                            renderResumenCuentas(response.data);
                        } else {
                            renderDetalleCuentas(response.data, response.total_monto, response.total_abonado, response.total_deuda);
                        }
                    } else {
                        $content.html(`<div class="alert alert-danger"><i class="ri-error-warning-line"></i> Error al cargar los datos</div>`);
                    }
                },
                error: function(xhr) {
                    $loading.addClass('d-none');
                    $content.removeClass('d-none');
                    let errorMsg = 'Error al cargar las cuentas por pagar';
                    if (xhr.responseJSON && xhr.responseJSON.message) errorMsg = xhr.responseJSON.message;
                    $content.html(`<div class="alert alert-danger"><i class="ri-error-warning-line"></i> ${errorMsg}</div>`);
                }
            });
        }

        function renderResumenCuentas(data) {
            if (!data || !data.codprov) {
                $('#cxp-content').html(`
                    <div class="text-center py-4">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No hay cuentas por pagar para este proveedor</p>
                    </div>
                `);
                return;
            }
            const deuda = parseFloat(data.deuda) || 0;
            const monto = parseFloat(data.monto) || 0;
            const abonado = parseFloat(data.abonado) || 0;
            const porcentajePagado = monto > 0 ? (abonado / monto * 100) : 0;
            const porcentajeDeuda = monto > 0 ? (deuda / monto * 100) : 0;

            $('#cxp-content').html(`
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card shadow-sm border-0 cxp-card">
                            <div class="card-header bg-primary text-white text-center"><h5 class="mb-0">Resumen de Cuentas por Pagar</h5></div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <h4>${escapeHtml(data.empresa) || escapeHtml(data.codprov)}</h4>
                                    <small class="text-muted">Código: ${escapeHtml(data.codprov)}</small>
                                </div>
                                <div class="row text-center mb-4">
                                    <div class="col-4"><div class="border-end"><small class="text-muted d-block">Total Facturado</small><h4 class="text-primary mb-0">$${formatNumber(monto)}</h4></div></div>
                                    <div class="col-4"><div class="border-end"><small class="text-muted d-block">Total Abonado</small><h4 class="text-success mb-0">$${formatNumber(abonado)}</h4></div></div>
                                    <div class="col-4"><small class="text-muted d-block">Saldo Pendiente</small><h4 class="${deuda > 0 ? 'text-danger' : 'text-success'} mb-0">$${formatNumber(deuda)}</h4></div>
                                </div>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1"><span>Pagado: ${porcentajePagado.toFixed(1)}%</span><span>Pendiente: ${porcentajeDeuda.toFixed(1)}%</span></div>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success progress-bar-custom" style="width: ${porcentajePagado}%">$${formatNumber(abonado)}</div>
                                        <div class="progress-bar bg-danger progress-bar-custom" style="width: ${porcentajeDeuda}%">$${formatNumber(deuda)}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }

        function aplicarOrdenamientoCxp(data) {
            if (!cxpSortState.column) return data;
            const col = cxpSortState.column;
            const dir = cxpSortState.direction === 'asc' ? 1 : -1;

            return [...data].sort(function(a, b) {
                let va, vb;
                if (['monto', 'abonado', 'deuda'].includes(col)) {
                    va = parseFloat(a[col]) || 0;
                    vb = parseFloat(b[col]) || 0;
                    return (va - vb) * dir;
                }
                if (col === 'fecha2') {
                    va = parseFechaCxp(a.fecha2); vb = parseFechaCxp(b.fecha2);
                    return (va - vb) * dir;
                }
                if (col === 'fechav') {
                    va = parseFechaCxp(a.fechav2); vb = parseFechaCxp(b.fechav2);
                    return (va - vb) * dir;
                }
                if (col === 'concepto') {
                    va = (a.concepto || '').toString().toLowerCase();
                    vb = (b.concepto || '').toString().toLowerCase();
                    if (va < vb) return -1 * dir;
                    if (va > vb) return 1 * dir;
                    return 0;
                }
                return 0;
            });
        }

        function sortIcon(col) {
            if (cxpSortState.column !== col) return '<i class="ri-arrow-up-down-line text-muted" style="font-size:11px;"></i>';
            return cxpSortState.direction === 'asc'
                ? '<i class="ri-arrow-up-line text-primary"></i>'
                : '<i class="ri-arrow-down-line text-primary"></i>';
        }

        function renderDetalleCuentas(data, totalMonto, totalAbonado, totalDeuda) {
            if (!data || data.length === 0) {
                $('#cxp-content').html(`
                    <div class="text-center py-4">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No hay cuentas por pagar para este proveedor</p>
                    </div>
                `);
                return;
            }

            cxpDataCache = data;
            cxpTotalsCache = { monto: totalMonto, abonado: totalAbonado, deuda: totalDeuda };

            const sortedData = aplicarOrdenamientoCxp(data);
            const hoy = new Date(); hoy.setHours(0, 0, 0, 0);

            let rows = '';
            sortedData.forEach(function(item) {
                let isOverdue = false, diasVencido = 0;
                if (item.fechav2) {
                    const partes = item.fechav2.split('/');
                    if (partes.length === 3) {
                        const fechaVence = new Date(partes[2], partes[1] - 1, partes[0]);
                        isOverdue = fechaVence < hoy;
                        if (isOverdue) diasVencido = Math.ceil((hoy - fechaVence) / (1000 * 60 * 60 * 24));
                    }
                }
                const deuda   = parseFloat(item.deuda) || 0;
                const monto   = parseFloat(item.monto) || 0;
                const abonado = parseFloat(item.abonado) || 0;

                rows += `
                    <tr class="${isOverdue ? 'table-danger' : ''}">
                        <td class="text-center">${item.fecha2 || '-'}</td>
                        <td class="text-center">
                            ${item.fechav2 || '-'}
                            ${isOverdue ? `<br><small class="text-danger">Vencido hace ${diasVencido} día(s)</small>` : ''}
                        </td>
                        <td class="text-wrap" style="max-width: 250px;">
                            <strong>${escapeHtml(item.concepto || '-')}</strong>
                            ${item.numerod ? `<br><small class="text-muted">Doc: ${escapeHtml(item.numerod)}</small>` : ''}
                        </td>
                        <td class="text-end">$${formatNumber(monto)}</td>
                        <td class="text-end">$${formatNumber(abonado)}</td>
                        <td class="text-end fw-bold ${deuda > 0 ? 'text-danger' : 'text-success'}">$${formatNumber(deuda)}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary ver-detalle-cxp"
                                    data-numerod="${escapeHtml(item.numerod || '')}"
                                    data-concepto="${escapeHtml(item.concepto || '')}"
                                    data-monto="${monto}"
                                    data-abonado="${abonado}"
                                    data-deuda="${deuda}"
                                    data-fecha2="${item.fecha2 || ''}"
                                    data-fechav="${item.fechav2 || ''}">
                                <i class="ri-eye-line"></i> Ver
                            </button>
                        </td>
                    </tr>
                `;
            });

            $('#cxp-content').html(`
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap">
                            <span>
                                <i class="ri-information-line"></i>
                                <strong>Resumen:</strong>
                                Total Facturado: <strong class="text-primary">$${formatNumber(totalMonto)}</strong> |
                                Total Abonado: <strong class="text-success">$${formatNumber(totalAbonado)}</strong> |
                                Saldo Pendiente: <strong class="text-danger">$${formatNumber(totalDeuda)}</strong>
                            </span>
                            <span class="mt-2 mt-sm-0"><small class="text-muted"><i class="ri-file-list-line"></i> ${data.length} documento(s)</small></span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="10%" class="sortable-cxp" data-col="fecha2">Fecha Emisión ${sortIcon('fecha2')}</th>
                                <th width="12%" class="sortable-cxp" data-col="fechav">Fecha Vence ${sortIcon('fechav')}</th>
                                <th width="35%" class="sortable-cxp" data-col="concepto">Concepto / Documento ${sortIcon('concepto')}</th>
                                <th width="12%" class="text-end sortable-cxp" data-col="monto">Monto ${sortIcon('monto')}</th>
                                <th width="12%" class="text-end sortable-cxp" data-col="abonado">Abonado ${sortIcon('abonado')}</th>
                                <th width="12%" class="text-end sortable-cxp" data-col="deuda">Saldo ${sortIcon('deuda')}</th>
                                <th width="7%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                        <tfoot class="table-secondary">
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">TOTALES:</td>
                                <td class="text-end">$${formatNumber(totalMonto)}</td>
                                <td class="text-end">$${formatNumber(totalAbonado)}</td>
                                <td class="text-end text-danger">$${formatNumber(totalDeuda)}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `);

            $(document)
                .off('click.cxpSort', '#cxp-content .sortable-cxp')
                .on('click.cxpSort', '#cxp-content .sortable-cxp', function() {
                    const col = $(this).data('col');
                    if (cxpSortState.column === col) {
                        cxpSortState.direction = cxpSortState.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        cxpSortState.column = col;
                        cxpSortState.direction = 'asc';
                    }
                    renderDetalleCuentas(cxpDataCache, cxpTotalsCache.monto, cxpTotalsCache.abonado, cxpTotalsCache.deuda);
                });
        }

        function initDatepicker() {
            // No hacer nada: usamos <input type="date"> nativos
            // Si en el futuro se carga bootstrap-datepicker, esta función puede inicializarlo.
        }

        function initCuentasPorPagar() {
            const codprov = '{{ $proveedor->codprov ?? "" }}';

            function recargar() {
                if (!codprov) return;
                const solovencidos = $('#solovencidosSwitch').is(':checked');
                const fechavence   = $('#fechavenceFilter').val();
                cargarCuentasPorPagar(codprov, false, solovencidos, fechavence);
            }

            $('#btnFiltrarCxp').off('click').on('click', recargar);
            $('#solovencidosSwitch').off('change').on('change', recargar);
            $('#fechavenceFilter').off('change blur').on('change blur', function() {
                if ($(this).val()) recargar();
            });

            $('#btnActualizarCxp').off('click').on('click', function() {
                $('#solovencidosSwitch').prop('checked', false);
                $('#fechavenceFilter').val('');
                if (codprov) cargarCuentasPorPagar(codprov, false, false, '');
            });

            if (codprov && '{{ $tab }}' === 'tab6') {
                cargarCuentasPorPagar(codprov, false, false, '');
            }
        }

        function observeTabChanges() {
            $(document).on('click', 'a[href*="tab=tab6"]', function() {
                const codprov = '{{ $proveedor->codprov ?? "" }}';
                if (codprov) {
                    setTimeout(function() {
                        const solovencidos = $('#solovencidosSwitch').is(':checked');
                        const fechavence   = $('#fechavenceFilter').val();
                        cargarCuentasPorPagar(codprov, false, solovencidos, fechavence);
                    }, 100);
                }
            });
        }

        // ============================================
        // RESUMEN GENERAL CxP
        // ============================================
        function cargarResumenGeneralCuentas(solovencidos = false, fechavence = '') {
            const $content = $('#resumen-general-content');
            const $loading = $('#resumen-general-loading');

            $loading.removeClass('d-none');
            $content.addClass('d-none').html('');

            $.ajax({
                url: '{{ route("proveedores.cuentas-por-pagar.resumen-general") }}',
                type: 'GET',
                data: {
                    solovencidos : solovencidos ? 1 : 0,
                    fechavence   : fechavence
                },
                success: function(response) {
                    $loading.addClass('d-none');
                    $content.removeClass('d-none');
                    if (response.success) {
                        // Sincronizar filtro texto con el input por si cambió
                        rgFiltroTexto = ($('#busqueda').val() || '').trim();

                        const data = (response.data || []).map(function(item, i) {
                            return Object.assign({}, item, { _idx: i + 1 });
                        });
                        rgSortState = { column: null, direction: 'asc' };
                        renderResumenGeneral(data, response.totales);
                    } else {
                        $content.html(`<div class="alert alert-danger"><i class="ri-error-warning-line"></i> Error al cargar los datos</div>`);
                    }
                },
                error: function(xhr) {
                    console.error('[RG] error:', xhr);
                    $loading.addClass('d-none');
                    $content.removeClass('d-none');
                    $content.html(`<div class="alert alert-danger"><i class="ri-error-warning-line"></i> Error al cargar el resumen de cuentas por pagar</div>`);
                }
            });
        }

        function sortIconRg(col) {
            if (rgSortState.column !== col) return '<i class="ri-arrow-up-down-line text-muted" style="font-size:11px;"></i>';
            return rgSortState.direction === 'asc'
                ? '<i class="ri-arrow-up-line text-primary"></i>'
                : '<i class="ri-arrow-down-line text-primary"></i>';
        }

        function aplicarOrdenamientoRg(data) {
            // 1) Aplicar filtro de texto
            let filtrados = data;
            if (rgFiltroTexto && rgFiltroTexto.length > 0) {
                const tokens = rgFiltroTexto.toLowerCase().split(/\s+/).filter(t => t.length > 0);
                if (tokens.length > 0) {
                    filtrados = data.filter(function(item) {
                        const texto = (
                            (item.empresa || '') + ' ' +
                            (item.codprov || '') + ' ' +
                            (item.empresa || '') + ' ' +      // por si buscan por nombre
                            (item.id3 || '')                   // RIF si lo tuvieras
                        ).toLowerCase();
                        return tokens.every(t => texto.includes(t));
                    });
                }
            }

            // 2) Aplicar ordenamiento
            if (!rgSortState.column) return filtrados;

            const col = rgSortState.column;
            const dir = rgSortState.direction === 'asc' ? 1 : -1;

            return [...filtrados].sort(function(a, b) {
                let va, vb;
                if (col === 'idx') {
                    va = a._idx || 0; vb = b._idx || 0;
                    return (va - vb) * dir;
                }
                if (['monto', 'abonado', 'deuda'].includes(col)) {
                    va = parseFloat(a[col]) || 0; vb = parseFloat(b[col]) || 0;
                    return (va - vb) * dir;
                }
                if (col === 'progreso') {
                    const ma = parseFloat(a.monto) || 0;
                    const mb = parseFloat(b.monto) || 0;
                    va = ma > 0 ? (parseFloat(a.abonado) || 0) / ma : 0;
                    vb = mb > 0 ? (parseFloat(b.abonado) || 0) / mb : 0;
                    return (va - vb) * dir;
                }
                if (col === 'empresa') {
                    va = (a.empresa || a.codprov || '').toString().toLowerCase();
                    vb = (b.empresa || b.codprov || '').toString().toLowerCase();
                    if (va < vb) return -1 * dir;
                    if (va > vb) return 1 * dir;
                    return 0;
                }
                return 0;
            });
        }

        function renderResumenGeneral(data, totales) {
            if (!data || data.length === 0) {
                $('#resumen-general-content').html(`
                    <div class="text-center py-4">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No hay cuentas por pagar registradas</p>
                    </div>
                `);
                $('#totalProveedoresCxp').text('0');
                return;
            }

            rgDataCache = data;
            rgTotalsCache = { monto: totales.monto, abonado: totales.abonado, deuda: totales.deuda };

            const sortedData = aplicarOrdenamientoRg(data);

            if (sortedData.length === 0) {
                $('#totalProveedoresCxp').text('0');
                $('#resumen-general-content').html(`
                    <div class="alert alert-warning py-2 mb-2 d-flex justify-content-between align-items-center">
                        <span><i class="ri-filter-3-line"></i> Filtrando por: <strong>${escapeHtml(rgFiltroTexto)}</strong></span>
                        <button type="button" id="btnLimpiarFiltroRg" class="btn btn-sm btn-outline-dark">
                            <i class="ri-close-line"></i> Quitar filtro
                        </button>
                    </div>
                    <div class="text-center py-4">
                        <i class="ri-search-eye-line fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No hay proveedores que coincidan con "<strong>${escapeHtml(rgFiltroTexto)}</strong>"</p>
                    </div>
                `);
                return;
            }

            // Recalcular totales sobre las filas visibles (filtradas)
            let sumMonto = 0, sumAbonado = 0, sumDeuda = 0;
            sortedData.forEach(function(it) {
                sumMonto    += parseFloat(it.monto) || 0;
                sumAbonado  += parseFloat(it.abonado) || 0;
                sumDeuda    += parseFloat(it.deuda) || 0;
            });

            $('#totalProveedoresCxp').text(sortedData.length);

            let rows = '';
            sortedData.forEach(function(item) {
                const deuda = parseFloat(item.deuda) || 0;
                const monto = parseFloat(item.monto) || 0;
                const abonado = parseFloat(item.abonado) || 0;
                const porcentajePagado = monto > 0 ? (abonado / monto * 100) : 0;

                rows += `
                    <tr>
                        <td class="text-center">${item._idx != null ? item._idx : ''}</td>
                        <td>
                            <strong>${escapeHtml(item.empresa)}</strong>
                            <br><small class="text-muted">Código: ${escapeHtml(item.codprov)}</small>
                        </td>
                        <td class="text-end">$${formatNumber(monto)}</td>
                        <td class="text-end">$${formatNumber(abonado)}</td>
                        <td class="text-end fw-bold ${deuda > 0 ? 'text-danger' : 'text-success'}">$${formatNumber(deuda)}</td>
                        <td class="text-center" style="min-width: 100px;">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: ${porcentajePagado}%"></div>
                                <div class="progress-bar bg-danger" style="width: ${100 - porcentajePagado}%"></div>
                            </div>
                            <small>${porcentajePagado.toFixed(0)}% pagado</small>
                        </td>
                        <td class="text-center" style="min-width: 130px;">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button"
                                        class="btn btn-outline-primary btn-accion-proveedor"
                                        data-codprov="${item.codprov}"
                                        data-descrip="${escapeHtml(item.empresa)}"
                                        data-accion="detalle"
                                        title="Ir al proveedor">
                                    <i class="ri-user-line"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-outline-info btn-accion-proveedor"
                                        data-codprov="${item.codprov}"
                                        data-descrip="${escapeHtml(item.empresa)}"
                                        data-accion="analisis"
                                        title="Ir al análisis de compras">
                                    <i class="ri-bar-chart-2-line"></i>
                                </button>
                                <button type="button"
                                        class="btn btn-outline-warning btn-accion-proveedor"
                                        data-codprov="${item.codprov}"
                                        data-descrip="${escapeHtml(item.empresa)}"
                                        data-accion="cxp"
                                        title="Ir a Cuentas por Pagar">
                                    <i class="ri-bank-card-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            const totalDeuda    = sumDeuda;
            const totalMonto    = sumMonto;
            const totalAbonado  = sumAbonado;
            const porcentajeGeneral = totalMonto > 0 ? (totalAbonado / totalMonto * 100) : 0;

            // Aviso de filtro activo
            const filtroActivo = rgFiltroTexto && rgFiltroTexto.length > 0;
            const avisoFiltro = filtroActivo
                ? `<div class="alert alert-warning py-2 mb-2 d-flex justify-content-between align-items-center">
                       <span><i class="ri-filter-3-line"></i> Filtrando por: <strong>${escapeHtml(rgFiltroTexto)}</strong></span>
                       <button type="button" id="btnLimpiarFiltroRg" class="btn btn-sm btn-outline-dark">
                           <i class="ri-close-line"></i> Quitar filtro
                       </button>
                   </div>`
                : '';

            $('#resumen-general-content').html(`
                ${avisoFiltro}
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="ri-information-line"></i>
                            <strong>Resumen General:</strong>
                            Total Facturado: <strong class="text-primary">$${formatNumber(totalMonto)}</strong> |
                            Total Abonado: <strong class="text-success">$${formatNumber(totalAbonado)}</strong> |
                            Saldo Pendiente Total: <strong class="text-danger">$${formatNumber(totalDeuda)}</strong>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span>Porcentaje General Pagado: ${porcentajeGeneral.toFixed(1)}%</span>
                        <span>Porcentaje General Pendiente: ${(100 - porcentajeGeneral).toFixed(1)}%</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-success" style="width: ${porcentajeGeneral}%">$${formatNumber(totalAbonado)}</div>
                        <div class="progress-bar bg-danger" style="width: ${100 - porcentajeGeneral}%">$${formatNumber(totalDeuda)}</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="sortable-cxp" data-col="idx"># ${sortIconRg('idx')}</th>
                                <th width="35%" class="sortable-cxp" data-col="empresa">Proveedor ${sortIconRg('empresa')}</th>
                                <th width="15%" class="text-end sortable-cxp" data-col="monto">Total Facturado ${sortIconRg('monto')}</th>
                                <th width="15%" class="text-end sortable-cxp" data-col="abonado">Total Abonado ${sortIconRg('abonado')}</th>
                                <th width="15%" class="text-end sortable-cxp" data-col="deuda">Saldo Pendiente ${sortIconRg('deuda')}</th>
                                <th width="10%" class="text-center sortable-cxp" data-col="progreso">Progreso ${sortIconRg('progreso')}</th>
                                <th width="12%" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                        <tfoot class="table-secondary">
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">TOTALES:</td>
                                <td class="text-end">$${formatNumber(totalMonto)}</td>
                                <td class="text-end">$${formatNumber(totalAbonado)}</td>
                                <td class="text-end text-danger">$${formatNumber(totalDeuda)}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `);

            $(document)
                .off('click.rgSort', '#resumen-general-content .sortable-cxp')
                .on('click.rgSort', '#resumen-general-content .sortable-cxp', function() {
                    const col = $(this).data('col');
                    if (rgSortState.column === col) {
                        rgSortState.direction = rgSortState.direction === 'asc' ? 'desc' : 'asc';
                    } else {
                        rgSortState.column = col;
                        rgSortState.direction = 'asc';
                    }
                    renderResumenGeneral(rgDataCache, rgTotalsCache);
                });

            // Acciones por proveedor (3 botones)
            $(document)
                .off('click.rgAccion', '#resumen-general-content .btn-accion-proveedor')
                .on('click.rgAccion', '#resumen-general-content .btn-accion-proveedor', function(e) {
                    e.stopPropagation();
                    const codprov = $(this).data('codprov');
                    const descrip = $(this).data('descrip');
                    const accion  = $(this).data('accion');

                    if (accion === 'analisis') {
                        // Ir al panel de análisis de compras (nueva pestaña)
                        window.open(`/proveedores/${codprov}/productos-panel`, '_blank');
                    } else if (accion === 'cxp') {
                        // Ir directo al tab de Cuentas por Pagar
                        $('#codprov').val(codprov);
                        $('#busqueda').val(descrip);
                        if ($('#tab').length === 0) {
                            $('<input>').attr({type: 'hidden', id: 'tab', name: 'tab'}).val('tab6').appendTo('#proveedorForm');
                        } else {
                            $('#tab').val('tab6');
                        }
                        $('#proveedorForm').submit();
                    } else {
                        // Ir al detalle del proveedor (tab por defecto = tab5)
                        $('#codprov').val(codprov);
                        $('#busqueda').val(descrip);
                        if ($('#tab').length === 0) {
                            $('<input>').attr({type: 'hidden', id: 'tab', name: 'tab'}).val('tab5').appendTo('#proveedorForm');
                        } else {
                            $('#tab').val('tab5');
                        }
                        $('#proveedorForm').submit();
                    }
                });
            // Quitar filtro del buscador
            $(document)
                .off('click.rgLimpiar', '#btnLimpiarFiltroRg')
                .on('click.rgLimpiar', '#btnLimpiarFiltroRg', function() {
                    rgFiltroTexto = '';
                    $('#busqueda').val('');
                    renderResumenGeneral(rgDataCache, rgTotalsCache);
                });
        }

        function initResumenGeneral() {
            // Carga inicial
            cargarResumenGeneralCuentas(false, '');

            function recargar() {
                const solovencidos = $('#resumenSoloVencidosSwitch').is(':checked');
                const fechavence   = $('#resumenFechavenceFilter').val();
                cargarResumenGeneralCuentas(solovencidos, fechavence);
            }

            $('#btnFiltrarResumenGeneral').off('click').on('click', recargar);
            $('#resumenSoloVencidosSwitch').off('change').on('change', recargar);
            $('#resumenFechavenceFilter').off('change blur').on('change blur', function() {
                if ($(this).val()) recargar();
            });

            $('#btnActualizarResumenGeneral').off('click').on('click', function() {
                $('#resumenSoloVencidosSwitch').prop('checked', false);
                $('#resumenFechavenceFilter').val('');
                cargarResumenGeneralCuentas(false, '');
            });
        }

        // ============================================
        // ANÁLISIS GERENCIAL GLOBAL
        // ============================================
        $(document).on('click', '#btnAnalisisGlobalCxp', function() {
            const $btn  = $(this);
            const $cont = $('#analisis-global-cxp');

            $btn.prop('disabled', true).html('<i class="ri-loader-4-line"></i> Cargando...');

            const solovencidos = $('#resumenSoloVencidosSwitch').is(':checked');
            const fechavence   = $('#resumenFechavenceFilter').val();

            $.ajax({
                url: '{{ route("proveedores.analisis-gerencial-global-cxp") }}',
                type: 'GET',
                data: { solovencidos: solovencidos ? 1 : 0, fechavence },
                success: function(response) {
                    $btn.prop('disabled', false).html('<i class="ri-bar-chart-box-line"></i> Análisis Gerencial Global');
                    if (response.success) {
                        renderAnalisisGlobal(response.data);
                        $cont.slideDown();
                        $('html, body').animate({ scrollTop: $cont.offset().top - 100 }, 400);
                    }
                },
                error: function(xhr) {
                    console.error('[AnalisisGlobal] error:', xhr);
                    $btn.prop('disabled', false).html('<i class="ri-bar-chart-box-line"></i> Análisis Gerencial Global');
                    Swal.fire('Error', 'No se pudo cargar el análisis global. Revisa la consola.', 'error');
                }
            });
        });

        function renderAnalisisGlobal(d) {
            const k = d.kpis;
            const catColors = { critica: 'danger', alta: 'warning', media: 'info', baja: 'secondary' };
            const catLabels = { critica: 'CRÍTICA', alta: 'ALTA', media: 'MEDIA', baja: 'BAJA' };

            const kpisHtml = `
                <div class="row g-3 mb-3">
                    <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">Pasivo Total</small>
                        <h4 class="text-primary mb-0">$${formatNumber(k.total_deuda)}</h4>
                        <small class="text-muted">${k.total_proveedores} proveedores</small>
                    </div></div></div>
                    <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100 ${k.total_vencido > 0 ? 'bg-danger bg-opacity-10' : ''}"><div class="card-body p-3">
                        <small class="text-muted d-block">Vencido</small>
                        <h4 class="text-danger mb-0">$${formatNumber(k.total_vencido)}</h4>
                        <small class="text-muted">${k.porcentaje_vencido}%</small>
                    </div></div></div>
                    <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">Vence 7 días</small>
                        <h4 class="text-warning mb-0">$${formatNumber(k.total_por_vencer_7d)}</h4>
                    </div></div></div>
                    <div class="col-md-3 col-lg-2"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">Críticos</small>
                        <h4 class="text-danger mb-0">${k.proveedores_criticos}</h4>
                    </div></div></div>
                    <div class="col-md-6 col-lg-2"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">Top 5</small>
                        <h4 class="text-info mb-0">$${formatNumber(k.deuda_top5)}</h4>
                        <small class="text-muted">${k.porcentaje_top5}%</small>
                    </div></div></div>
                    <div class="col-md-6 col-lg-2"><div class="card border-0 shadow-sm h-100"><div class="card-body p-3">
                        <small class="text-muted d-block">Facturas</small>
                        <h4 class="mb-0">${k.total_facturas}</h4>
                        <small class="text-muted">por pagar</small>
                    </div></div></div>
                </div>
            `;

            let alertasHtml = '';
            if (d.alertas.length) {
                alertasHtml = '<div class="mb-3">' + d.alertas.map(a => `
                    <div class="alert alert-${a.tipo} d-flex align-items-start mb-2 py-2">
                        <i class="${a.icono} fs-5 me-2"></i>
                        <div><strong>${a.titulo}:</strong> ${a.mensaje}</div>
                    </div>
                `).join('') + '</div>';
            }

            let rowsProv = d.proveedores.map(p => `
                <tr class="${p.categoria === 'critica' ? 'table-danger' : (p.categoria === 'alta' ? 'table-warning' : '')}">
                    <td class="text-center fw-bold">${p.ranking}</td>
                    <td><span class="badge bg-${catColors[p.categoria]}">${catLabels[p.categoria]}</span></td>
                    <td><strong>${escapeHtml(p.empresa)}</strong><br><small class="text-muted">${escapeHtml(p.codprov)}</small></td>
                    <td class="text-end fw-bold text-danger">$${formatNumber(p.deuda)}</td>
                    <td class="text-end">${p.deuda_vencida > 0
                ? `<span class="text-danger fw-bold">$${formatNumber(p.deuda_vencida)}</span><br><small>${p.porcentaje_vencido}% del prov.</small>`
                : '<span class="text-success">$0</span>'}</td>
                    <td class="text-center">
                        <span class="badge bg-${p.dias_vencido_max > 0 ? 'danger' : 'success'}">
                            ${p.dias_vencido_max > 0 ? p.dias_vencido_max + 'd' : 'Al día'}
                        </span>
                    </td>
                    <td class="text-center">${p.num_facturas}</td>
                    <td class="text-center small">
                        ${p.dias_ultima_compra !== null ? p.dias_ultima_compra + 'd' : 'N/A'}
                        <br><small class="text-muted">${p.facturas_12m} fact/12m</small>
                    </td>
                    <td class="text-center">
                        <div class="progress" style="height: 6px; width: 60px; margin: 0 auto;">
                            <div class="progress-bar bg-${catColors[p.categoria]}" style="width: ${p.score}%"></div>
                        </div>
                        <small>${p.score}</small>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary ver-detalle-global"
                                data-codprov="${p.codprov}" data-descrip="${escapeHtml(p.empresa)}">
                            <i class="ri-eye-line"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

            const rankingHtml = `
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-dark text-white d-flex justify-content-between">
                        <h6 class="mb-0"><i class="ri-trophy-line"></i> Ranking de proveedores a pagar primero</h6>
                        <small>${d.proveedores.length} proveedor(es)</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="4%">#</th>
                                        <th width="8%">Prioridad</th>
                                        <th width="22%">Proveedor</th>
                                        <th width="10%" class="text-end">Deuda Total</th>
                                        <th width="12%" class="text-end">Vencido</th>
                                        <th width="8%" class="text-center">Máx Venc.</th>
                                        <th width="6%" class="text-center"># Fact.</th>
                                        <th width="10%" class="text-center">Últ. Compra</th>
                                        <th width="8%" class="text-center">Score</th>
                                        <th width="5%">Ver</th>
                                    </tr>
                                </thead>
                                <tbody>${rowsProv}</tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light small text-muted">
                        <i class="ri-information-line"></i> Score = antigüedad vencida + % vencido + peso relativo + criticidad + penalización inactividad. Ordenado por urgencia de pago.
                    </div>
                </div>
            `;

            let rowsFact = d.top_facturas.map((f, i) => `
                <tr>
                    <td class="text-center">${i + 1}</td>
                    <td><strong>${escapeHtml(f.empresa)}</strong><br><small>${escapeHtml(f.codprov)}</small></td>
                    <td>${escapeHtml(f.numerod || '-')}<br><small class="text-muted">${escapeHtml(f.concepto || '')}</small></td>
                    <td class="text-center">${f.fechav_fmt}</td>
                    <td class="text-center">${f.dias_vencidos > 0
                ? `<span class="badge bg-danger">${f.dias_vencidos}d</span>`
                : `<span class="badge bg-secondary">${f.dias_por_vencer}d</span>`}</td>
                    <td class="text-end fw-bold text-danger">$${formatNumber(f.deuda)}</td>
                    <td class="text-center">
                        <div class="progress" style="height: 6px; width: 50px; margin: 0 auto;">
                            <div class="progress-bar bg-danger" style="width: ${f.score_factura}%"></div>
                        </div>
                        <small>${f.score_factura}</small>
                    </td>
                </tr>
            `).join('');

            const topFactHtml = `
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-danger text-white"><h6 class="mb-0"><i class="ri-flashlight-line"></i> Top 20 facturas para pago inmediato (todas las empresas)</h6></div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="4%">#</th>
                                        <th width="22%">Proveedor</th>
                                        <th width="25%">Documento</th>
                                        <th width="10%">Vence</th>
                                        <th width="10%">Estado</th>
                                        <th width="12%" class="text-end">Saldo</th>
                                        <th width="10%" class="text-center">Score</th>
                                    </tr>
                                </thead>
                                <tbody>${rowsFact}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;

            const maxProy = Math.max(...d.proyeccion.map(p => p.monto), 1);
            const proyHtml = `
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-info text-white"><h6 class="mb-0"><i class="ri-line-chart-line"></i> Proyección global de vencimientos (30 días)</h6></div>
                    <div class="card-body">
                        <div class="d-flex align-items-end gap-1" style="height: 100px;">
                            ${d.proyeccion.map(p => {
                const h = (p.monto / maxProy) * 100;
                return `<div class="flex-fill bg-primary rounded-top"
                                             style="height: ${h}%; min-height: ${p.monto > 0 ? 4 : 0}px;"
                                             title="${p.label}: $${formatNumber(p.monto)}"></div>`;
            }).join('')}
                        </div>
                        <div class="d-flex justify-content-between mt-1 small text-muted"><span>Hoy</span><span>+15d</span><span>+30d</span></div>
                    </div>
                </div>
            `;

            $('#analisis-global-cxp').html(kpisHtml + alertasHtml + rankingHtml + topFactHtml + proyHtml);

            $(document).off('click', '.ver-detalle-global').on('click', '.ver-detalle-global', function() {
                const codprov = $(this).data('codprov');
                const descrip = $(this).data('descrip');
                $('#codprov').val(codprov);
                $('#busqueda').val(descrip);
                $('#proveedorForm').submit();
            });
        }

        // ============================================
        // INIT
        // ============================================
        function toggleResumenGeneral() {
            const hasProveedor = '{{ isset($proveedor) && $proveedor->codprov ? 1 : 0 }}';
            if (hasProveedor == 1) {
                $('#resumenGeneralCxpCard').addClass('hidden');
            } else {
                $('#resumenGeneralCxpCard').removeClass('hidden');
                cargarResumenGeneralCuentas(false, '');
            }
        }

        $(document).ready(function() {
            initDatepicker();
            initCuentasPorPagar();
            observeTabChanges();
            initResumenGeneral();
            toggleResumenGeneral();
        });
    </script>
@endsection
