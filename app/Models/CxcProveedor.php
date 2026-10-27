<?php
// app/Models/CxcProveedor.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CxcProveedor extends Model
{
    protected $table = 'cxc_proveedores';

    protected $fillable = [
        'concepto', 'monto', 'signo', 'tasabs', 'monedapago', 'conversion',
        'archived', 'numerod', 'tipo', 'fecha', 'codprov', 'tipocomp',
        'fk_estructura', 'abonado', 'compras', 'gastos', 'anticipos',
        'contabilidad', 'NROUNICOCXP', 'fechav', 'codoper', 'fk_sucursal'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'abonado' => 'decimal:2',
        'fecha' => 'date',
        'fechav' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Saprov::class, 'codprov', 'codprov');
    }

    public function compra()
    {
        return $this->belongsTo(Sacomp::class, 'numerod', 'numerod');
    }
}
