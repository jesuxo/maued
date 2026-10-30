{{-- resources/views/facturas/modal-detalle.blade.php --}}
<style>
    .modal-factura-header {
        border-bottom: 1px solid #132659;
        padding: 1rem;
    }
    .modal-factura-body {
        padding: 1.5rem;
    }
    .modal-factura-table th {
        background-color: #f8f9fa;
    }
    .modal-factura-info {
        color: #132659;
    }
    .modal-factura-info p {
        margin-bottom: 0.5rem;
    }
</style>

<div class="modal-factura-header">
    <div class="row">
        <div class="col-lg-12">
            <div class="d-sm-flex justify-content-between">
                <div class="flex-grow-1">
                    <strong>Cliente:</strong> {{$documento->descrip}} - {{(isset($documento->id3)?$documento->id3 : '')}} - {{(isset($documento->telef)?$documento->telef : '')}} - {{(isset($documento->direc1)?$documento->direc1 : '')}} {{(isset($documento->direc2)?$documento->direc2: '')}}
                </div>
                @php

                @endphp
                <div class="flex-shrink-0 mt-sm-0 mt-3" style="text-align: left">
                    <h6><span class="text-muted fw-normal">Contado: &nbsp;</span> $<span>{{number_format($documento->contado,2,',','.')}}</span></h6>
                    <h6><span class="text-muted fw-normal">Crédito: &nbsp;</span> $<span>{{number_format($documento->credito,2,',','.')}}</span></h6>
                </div>
                <div class="flex-shrink-0 mt-sm-0 mt-3" style="text-align: right">
                    <h6><span class="text-muted fw-normal">Sucursal: &nbsp;</span> <span>{{$documento->sucursal->descrip ?? 'N/A'}}</span></h6>
                    <h6><span class="text-muted fw-normal">Estación: &nbsp;</span> <span>{{$documento->codesta}}</span></h6>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal-factura-body">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <p class="text-muted mb-2 text-uppercase fw-semibold fs-14">DOCUMENTO NRO</p>
            <h5 class="fs-15 mb-0">{{$numerod}}</h5>
        </div>
        <div class="col-md-4">
            <p class="text-muted mb-2 text-uppercase fw-semibold fs-14">FECHA</p>
            <h5 class="fs-15 mb-0">
                <span>{{$documento->fecha}}</span>
                <small class="text-muted">{{$documento->hora}}</small>
            </h5>
        </div>
        <div class="col-md-4 text-end">
            <p class="text-muted mb-2 text-uppercase fw-semibold fs-14">Estatus</p>
            @php
                $pagado = 1;
                if($documento->credito){
                    if($documento->pagado < $documento->credito){
                        $pagado = 0;
                    }
                }
            @endphp
            @if($pagado)
                <span class="badge badge-soft-success" id="payment-status">Pagado</span>
            @else
                <span class="badge badge-soft-danger" id="payment-status">Pendiente</span>
            @endif
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-borderless text-center table-nowrap align-middle mb-0">
            <thead>
            <tr class="table-active">
                <th width="1%">#</th>
                <th width="54%" class="text-start">Detalle Producto/Servicio</th>
                <th width="15%" class="text-end">Precio</th>
                <th width="15%" class="text-center">Cantidad</th>
                <th width="15%" class="text-end">Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($documento->items as $index => $item)
                @if($item->fk_sucursal == $documento->fk_sucursal)
                    <tr @if(($index%2)!=0) bgcolor="#f5f8fb" @endif>
                        <th scope="row">{{$index+1}}</th>
                        <td class="text-start">
                            <span class="fw-medium">{{ $item->Descrip1 }}</span>
                            <p class="text-muted mb-0">
                                {{(isset($item->producto))? $item->producto->instancia->descrip : ''}}
                                {{(isset($item->producto) and $item->producto->refere !='')? $item->producto->refere : ''}}
                                {{(isset($item->producto) and $item->producto->marca  !='')? $item->producto->marca  : ''}}
                            </p>
                        </td>
                        <td class="text-end">${{number_format($item->costod,2,',','.')}}</td>
                        <td class="text-center">{{$item->Cantidad+0}}</td>
                        <td class="text-end">${{number_format($item->costod*$item->Cantidad,2,',','.')}}</td>
                    </tr>
                @endif
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <table width="100%" border="0">
            <tr>
                <td width="50%" valign="top">
                    <h6 class="text-uppercase fw-semibold mb-3 modal-factura-info">INFORMACION ADICIONAL</h6>
                </td>
            </tr>
            <tr>
                <td width="50%" valign="top">
                    <p class="mb-1 modal-factura-info"><b>NOTAS1:</b> {{$documento->notas1}} </p>
                    <p class="mb-1 modal-factura-info"><b>NOTAS2:</b> {{$documento->notas2}} </p>
                    <p class="mb-1 modal-factura-info"><b>NOTAS3:</b> {{$documento->notas3}} </p>
                </td>
            </tr>
            <tr>
                <td>
                    @if($documento->cancele != 0)
                        <p class="mb-1 modal-factura-info"><b>EfectivoBs:</b> {{number_format($documento->cancele,2,',','.')}}</p>
                    @endif
                    @if($documento->vuelto_cancele != 0)
                        <p class="mb-1 modal-factura-info"><b>Vueltos Bs:</b> {{number_format($documento->vuelto_cancele,2,',','.')}}</p>
                    @endif
                    @if($documento->dolares != 0)
                        <p class="mb-1 modal-factura-info"><b>EfectivoUSD:</b> {{number_format($documento->dolares,2,',','.')}}</p>
                    @endif
                    @if($documento->vuelto_dolares != 0)
                        <p class="mb-1 modal-factura-info"><b>Vueltos USD:</b> {{number_format($documento->vuelto_dolares,2,',','.')}}</p>
                    @endif
                    @if($documento->pesos != 0)
                        <p class="mb-1 modal-factura-info"><b>Efectivo PESOS:</b> {{number_format($documento->pesos,2,',','.')}}</p>
                    @endif
                    @if($documento->vuelto_pesos != 0)
                        <p class="mb-1 modal-factura-info"><b>Vueltos PESOS:</b> {{number_format($documento->vuelto_pesos,2,',','.')}}</p>
                    @endif
                    @if($documento->cancelt != 0)
                        <p class="mb-1 modal-factura-info"><b>Instrumento pago Bs:</b> {{number_format($documento->cancelt,2,',','.')}}</p>
                    @endif
                    @if($documento->transf != 0)
                        <p class="mb-1 modal-factura-info"><b>Instrumento pago USD:</b> {{number_format($documento->transf,2,',','.')}}</p>
                    @endif
                    @if($documento->cancelausd != 0)
                        <p class="mb-1 modal-factura-info"><b>ANTICIPO APLICADO USD:</b> {{number_format($documento->cancelausd,2,',','.')}}</p>
                    @endif

                    @if(isset($instpago) and count($instpago)>0)
                        @foreach($instpago as $index => $data)
                            <div class="mt-2">
                                -------------------------------------------- <br>
                                @if(isset($data->dolares) and $data->dolares > 0)
                                    Transferencia Dolares: [Codpago:{{$data->CodPago}}] {{$data->Descrip}} &nbsp; &nbsp; $ <b>{{number_format($data->dolares,2,',','.')}}</b>
                                @else
                                    @if(isset($data->pesos) and $data->pesos > 0)
                                        Transferencia Pesos: [Codpago:{{$data->CodPago}}] {{$data->Descrip}} &nbsp; &nbsp; COP <b>{{number_format($data->pesos,2,',','.')}}</b>
                                    @else
                                        @if(isset($data->Monto) and $data->Monto > 0)
                                            Transferencia Bs: [Codpago:{{$data->CodPago}}] {{$data->Descrip}} &nbsp; &nbsp; Bs <b>{{number_format($data->Monto,2,',','.')}}</b>
                                        @endif
                                    @endif
                                @endif
                                <br>
                            </div>
                        @endforeach
                    @endif

                    @if(isset($documento->seriales) && $documento->seriales->count() > 0)
                        <div class="mt-3">
                            <strong>Seriales:</strong><br>
                            @foreach($documento->seriales as $serial)
                                [{{$serial->coditem}}] {{$serial->nroserial}} {{$serial->compraprov}}<br>
                            @endforeach
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
