<?php
// app/Models/CompraSerial.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraSerial extends Model
{
    protected $table = 'compra_seriales';

    protected $fillable = [
        'codsucu', 'tipocom', 'numerod', 'codprov', 'nrolinea',
        'nrolineac', 'nroserial', 'coditem', 'codubic', 'fk_sucursal'
    ];

    public function compra()
    {
        return $this->belongsTo(Sacomp::class, ['numerod', 'tipocom'], ['numerod', 'tipocom']);
    }

    public function itemCompra()
    {
        return $this->belongsTo(Saitemcom::class,
            ['tipocom', 'numerod', 'nrolinea', 'coditem'],
            ['tipocom', 'numerod', 'nrolinea', 'coditem']
        );
    }
}
