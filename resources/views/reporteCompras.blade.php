@extends('layouts.master')
@section('title')
    Venta de productos por sucursal
@endsection
@section('css')
    <style>
    .botoncal{
        background: transparent;
        border: none;
        color: white;
    }
    .botoncal:hover{
         font-size: 13px;
    }
    </style>
@endsection
@section('content')
    <style>
        .tdline{
            border:1px solid #0072c5 !important;
        }
        .tdlineff{
            border-left:1px solid #fff !important;
            color: white !important;
            background-color: #0072c5 !important;
        }
    </style>
    <form  method="post" name="form1" id="form1" action="/reporte/compra">
        <div class="row">
            <div class="col-md-2 mb-2">
                <label class="form-label">Sucursal</label>
                <select class="form-select" onChange="$('#form1').submit()" id="idsucu" name="fksucursal">
                    <option value="" {{($fksucursal=='' or $fksucursal==0)?'selected':''}}>Seleccionar Sucursal</option>
                    @foreach($allsucursales as $sucu)
                        <option value="{{$sucu->id}}" {{($sucu->id == $fksucursal)?'selected':''}}>
                            {{ $sucu->descrip }}
                        </option>
                    @endforeach
                </select>
            </div>
                <div class="col-md-3  ">
                    <label class="form-label">Busqueda</label>
                    <div class="input-group">
                        <input type="text" class="form-control"
                               placeholder="Busqueda: Proveedor, Numero compra..."
                               name="busqueda"  value="{{(isset($busqueda) and $busqueda !='')? $busqueda : ''}}" >
                    </div>
                </div>
                <div class="col-md-2  ">
                    <label class="form-label">Status</label>
                    <select class="form-select" data-choices  onchange="$('#form1').submit()"
                            id="status" name="status">
                        <option  {{( isset($status) and  $status == '1' )? 'selected':''}} value="1">Abiertas</option>
                        <option  {{( isset($status) and  $status == '0' )? 'selected':''}} value="0">Cerradas</option>
                        <option  {{( isset($status) and  $status == '2' )? 'selected':''}} value="2">Pendientes</option>
                        <option  {{( isset($status) and  $status == '' )? 'selected':''}} value="">Todas</option>

                    </select>

                </div>
                <div class="col-md-3 order-last">
                    <label class="form-label">Fechas</label>
                            <div class="input-group">
                                <input type="text" class="form-control" data-provider="flatpickr"
                                       data-range-date="true" data-date-format="d/m/Y"
                                       data-deafult-date="" name="fechasreport" readonly="readonly" value="{{(isset($fechasreport)? $fechasreport : '')}}"
                                >
                                <div class="input-group-text bg-primary border-primary text-white">
                                    <button type="submit" class="botoncal" >Consultar</button>
                                </div>
                            </div>

                </div>
                @csrf
                @method('POST')
        </div>
    </form>
    <input type="hidden" name="idcompraudate" id="idcompraudate" value="">
    <input type="hidden" name="statusval" id="statusval" value="">
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table width="100%" class="table table-bordered table-centered align-middle table-nowrap mb-0" style="color:#333;">
                    <thead>
                    <tr bgcolor="#fff">
                        <th class="tdline">PROVEEDOR</th>
                        <th class="tdlineff text-center">FECHA</th>
                        <th class="tdlineff text-center">DOCUMENTO</th>
                        <th class="tdlineff text-center">UNDS</th>
                        <th class="tdlineff text-center">CANT SERIALES</th>
                        <th class="tdlineff text-center">MONTO</th>
                        <th class="tdlineff text-center"  >STATUS</th>
                        <th class="tdline"> </th>
                        <th class="tdlineff text-center">VENDIDAS</th>
                        <th class="tdlineff text-center">DESCARGAS</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(isset($compras) && count($compras) > 0)
                        @foreach($compras as $index => $compra)
                            @php
                                $seriales = $cantidad = $costsuma = 0;
                                foreach ($compra->items as $item){
                                    $cantidad += $item->cantidad;
                                    $costsuma += $item->preciod * $item->cantidad;
                                }
                                foreach ($compra->seriales as $item){
                                    $seriales += 1;
                                }
                            @endphp
                            <tr style="background-color: {{ $loop->even ? '#eee' : '#fff' }}">
                                <td class="tdline">
                                    {{ $compra->descrip }}
                                    @if($compra->tipocom == 'Y')
                                        <span style="font-size:10px; color: red!important"> [DEVCOMPRA]</span>
                                    @endif
                                    <br>
                                    <span style="font-size: 10px;">
                                            {{ $compra->notas1 }} {{ $compra->notas2 }}
                                        </span>
                                </td>
                                <td class="tdline" align="center" >{{ $compra->createdformat }}</td>
                                <td class="tdline" align="center" style="{{ $compra->tipocom == 'Y' ? 'color: red !important' : '' }}">
                                    @if($compra->tipocom == 'U')
                                        <a href="/compra/{{ $compra->id }}" target="_blank">{{ $compra->numerod }}</a>
                                    @else
                                        {{ $compra->numerod }}
                                    @endif
                                </td>
                                <td class="tdline" align="center" >{{ $cantidad + 0 }}</td>
                                <td class="tdline" align="center"  style="{{ $compra->tipocom == 'Y' ? 'color: red !important' : '' }}">
                                    @if($compra->tipocom == 'U' && $seriales > 0)
                                        <a href="/compra/seriales/{{ $compra->id }}" target="_blank">Ver {{ $seriales }}</a>
                                    @endif
                                </td>
                                <td class="tdline text-right"  align="right" >{{ number_format($costsuma, 2, ',', '.') }}</td>
                                <td class="tdline"  align="center" >

                                        <div class="dropdown" id="contentstatus{{$compra->id}}" onclick="$('#idcompraudate').val({{ $compra->id }})">

                                            <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                @if($compra->status==0)
                                                    <i class="bi bi-lock text-success"></i> Cerrada
                                                @elseif($compra->status==1)
                                                    <i class="bi bi-eye text-primary"></i> Abierta
                                                @elseif($compra->status==2)
                                                    <span class="text-danger">Pendiente</span>
                                                @endif
                                            </button>

                                                <ul class="dropdown-menu">
                                                @if($compra->status != 0 and $seriales ==0 )
                                                    <li>
                                                            <button type="button" class="dropdown-item comprabtn" onclick="$('#statusval').val(0)" data-compraid="{{$compra->id}}">
                                                                <i class="bi bi-lock text-success"></i> Marcar como Cerrada
                                                            </button>
                                                    </li>
                                                @endif
                                                @if($compra->status == 0 and $seriales > 0 )
                                                        <li>
                                                            <button type="button" class="dropdown-item comprabtn"  onclick="$('#statusval').val(1)" data-compraid="{{$compra->id}}">
                                                                <i class="bi bi-lock text-success"></i> Abrir de nuevo
                                                            </button>
                                                        </li>
                                                @endif
                                            </ul>

                                        </div>

                                </td>
                                <td class="tdline">

                                </td>
                                <td class="tdline" style="color: #0072c5 !important;"  align="center" >
                                    {{ $compra->tipocom == 'U' ? $compra->ventas + 0 : '' }}
                                </td>
                                <td class="tdline" style="color: red !important;"  align="center" >
                                    {{ $compra->tipocom == 'U' ? $compra->descargas + 0 : '' }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="text-center tdline">No hay compras para mostrar</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>


@endsection
@section('scripts')

    <script src="{{ URL::asset('build/js/app.js') }}"></script>
    <script>
        $(document).ready(function() {

            $('.comprabtn').off('click').on('click', function() {
                var compraid  = $(this).data('compraid');
                var statusval = $('#statusval').val();
                $('#contentstatus'+compraid).html('<button class="btn btn-outline-primary btn-load"><span class="d-flex align-items-center"><span class="spinner-border flex-shrink-0" role="status"> <span class="visually-hidden"> Espere por favor...</span> </span> </span> </button>');

                $.ajax({
                    type: 'POST',
                    url: '/compra/cambiar-status',
                    data:{id: compraid, status: statusval},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        if(statusval==0)
                             $('#contentstatus'+compraid).html('<button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-lock text-success"></i> Cerrada</button>');
                        if(statusval==1)
                             $('#contentstatus'+compraid).html('<button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="bi bi-eye text-primary"></i> Abierta</button>');
                    }
                });
            });


            // Mantener el valor del input de fechas
            @if(isset($fechasreport) && $fechasreport)
                flatpickr("input[name='fechasreport']", {
                    mode: "range",
                    dateFormat: "d/m/Y",
                    defaultDate: "{{ $fechasreport }}"
                });
            @endif
        });
    </script>
@endsection
