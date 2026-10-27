<div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
        <thead class="table-light">
        <tr>
            <th>Sucursal</th>
            <th class="text-end">Monto USD</th>
            <th class="text-center">Estado</th>
            <th class="text-center">Fecha</th>
        </tr>
        </thead>
        <tbody>
        @foreach($pagos as $pago)
            <tr>
                <td>
                    <i class="bi bi-shop me-1"></i>
                    {{ $pago->sucursalcli->descrip ?? 'N/A' }}
                </td>
                <td class="text-end text-primary fw-bold">
                    ${{ number_format($pago->montodolares, 2, ',', '.') }}
                </td>
                <td class="text-center">
                        <span class="badge {{ $pago->descargar == 1 ? 'bg-warning' : 'bg-success' }}">
                            {{ $pago->descargar == 1 ? 'Pendiente' : 'Descargado' }}
                        </span>
                </td>
                <td class="text-center small">
                    {{ \Carbon\Carbon::parse($pago->created_at)->format('d/m/Y H:i:s') }}
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot class="table-secondary">
        <tr>
            <td class="fw-bold">TOTAL</td>
            <td class="text-end fw-bold text-primary">
                ${{ number_format($total, 2, ',', '.') }}
            </td>
            <td colspan="2" class="text-center small text-muted">
                {{ $pagos->count() }} sucursal(es)
            </td>
        </tr>
        </tfoot>
    </table>
</div>
