<?php

use App\Http\Controllers\TonerController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteProveedorController;
use App\Http\Controllers\SaprovController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\SaprodController;
use App\Http\Controllers\SadepoController;
use App\Http\Controllers\ChatConversationController;
use App\Http\Controllers\CwtokenController;
use App\Http\Controllers\SasucursalController;
use App\Http\Controllers\CwtransferenciasController;
use App\Http\Controllers\Seriales\VerificacionSerialController;
use App\Http\Controllers\SacompController;
use App\Http\Controllers\IAController;
use App\Http\Controllers\Seriales\SerialController;
use App\Http\Controllers\SavendController;
use App\Http\Controllers\SaprodImagenController;
use App\Http\Controllers\SafactController;
use App\Http\Controllers\SainstaController;
use App\Http\Controllers\SatarjController;
use App\Http\Controllers\PagoProveedorController;
use App\Http\Controllers\ComercialDashboardController;
use App\Http\Controllers\SaclieController;
use App\Http\Controllers\TransferenciaController;
use App\Http\Controllers\SaacxcController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImagenController;
use App\Http\Controllers\UserSucursalController;
use App\Http\Controllers\TesoroController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/* Auth */

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('index/{locale}', [HomeController::class, 'lang']);

\Illuminate\Support\Facades\Auth::routes();

Route::post('register', 'Auth\RegisterController@register')->name('register');

\Illuminate\Support\Facades\Auth::routes(['verify' => true]);

Route::group(['prefix' => 'error'], function(){
    Route::get('404', function () { return view('error.404'); });
    Route::get('500', function () { return view('error.500'); });
});

// Rutas para el dashboard del comercial
Route::middleware(['auth', 'redirect.to.comercial'])->group(function () {
    Route::get('/comercial/{comercialId}/dashboard', [ComercialDashboardController::class, 'index'])
        ->name('comercial.dashboard');

    Route::get('/sin-asignacion', function () {
        return view('errors.sin-asignacion');
    })->name('sin.asignacion');
});

use App\Http\Controllers\PedidoController;
use App\Http\Controllers\TramitarCompraController;

Route::middleware(['auth'])->group(function () {

    Route::match(['GET', 'POST'], '/cashea', [HomeController::class, 'reporteFacturasCashea'])->name('reporte.cashea');
    Route::match(['GET', 'POST'], '/krece', [HomeController::class, 'reporteFacturasKrece'])->name('reporte.krece');

    Route::get('/cambiarcomercial/{comercialId}', [ComercialDashboardController::class, 'cambiarComercial'])
        ->name('comercial.cambiar');

    Route::get('/comerciales/disponibles', [ComercialDashboardController::class, 'getComercialesDisponibles'])
        ->name('comerciales.disponibles');

    // Pedidos
    Route::resource('pedidos', PedidoController::class);

    // AJAX - Pedidos
    Route::post('pedidos/{pedido}/agregar-item', [PedidoController::class, 'agregarItem'])->name('pedidos.agregar-item');
    Route::post('pedidos/buscar-productos', [PedidoController::class, 'buscarProductos'])->name('pedidos.buscar-productos');
    Route::post('pedidos/cambiar-producto', [PedidoController::class, 'cambiarProducto'])->name('pedidos.cambiar-producto');
    Route::post('pedidos/actualizar-item', [PedidoController::class, 'actualizarItem'])->name('pedidos.actualizar-item');
    Route::delete('pedidos/eliminar-item', [PedidoController::class, 'eliminarItem'])->name('pedidos.eliminar-item');
    Route::post('pedidos/toggle-item-compra', [PedidoController::class, 'toggleItemCompra'])->name('pedidos.toggle-item-compra');
    Route::post('pedidos/update-variable', [PedidoController::class, 'updateVariable'])->name('pedidos.update-variable');
    Route::post('pedidos/{pedido}/colocar-costos', [PedidoController::class, 'colocarCostosPrecios'])->name('pedidos.colocar-costos');

    // Costos Adicionales
    Route::post('pedidos/{pedido}/agregar-costo-adicional', [PedidoController::class, 'agregarCostoAdicional'])->name('pedidos.agregar-costo-adicional');
    Route::post('pedidos/actualizar-costo-adicional', [PedidoController::class, 'actualizarCostoAdicional'])->name('pedidos.actualizar-costo-adicional');
    Route::delete('pedidos/eliminar-costo-adicional', [PedidoController::class, 'eliminarCostoAdicional'])->name('pedidos.eliminar-costo-adicional');

    // Seriales
    Route::get('pedidos/{pedido}/seriales/{item}', [PedidoController::class, 'seriales'])->name('pedidos.seriales');
    Route::post('pedidos/agregar-serial-temporal', [PedidoController::class, 'agregarSerialTemporal'])->name('pedidos.agregar-serial-temporal');
    Route::post('pedidos/guardar-seriales/{item}', [PedidoController::class, 'guardarSeriales'])->name('pedidos.guardar-seriales');
    Route::delete('pedidos/eliminar-serial-temporal', [PedidoController::class, 'eliminarSerialTemporal'])->name('pedidos.eliminar-serial-temporal');
    Route::post('pedidos/limpiar-seriales-temporales', [PedidoController::class, 'limpiarSerialesTemporales'])->name('pedidos.limpiar-seriales-temporales');
    Route::delete('pedidos/eliminar-serial/{serial}', [PedidoController::class, 'eliminarSerial'])->name('pedidos.eliminar-serial');

    Route::get('tramitar-compra/{pedido}', [TramitarCompraController::class, 'index'])->name('compras.tramitar');
    Route::post('tramitar-compra/{pedido}', [TramitarCompraController::class, 'procesar'])->name('compras.procesar');

    Route::prefix('usersucursal')->group(function () {
        Route::get('/', [UserSucursalController::class, 'index'])->name('usersucursal.index');
        Route::get('/usuarios', [UserSucursalController::class, 'getUsersConSucursales'])->name('usersucursal.usuarios');
        Route::get('/sucursales', [UserSucursalController::class, 'getAllSucursales'])->name('usersucursal.sucursales');
        Route::get('/sucursales-asignadas/{userId}', [UserSucursalController::class, 'getSucursalesAsignadasPorUsuario']);
        Route::get('/usuarios-por-sucursal/{sucursalId}', [UserSucursalController::class, 'getUsuariosPorSucursal']);
        Route::post('/asignar', [UserSucursalController::class, 'asignarSucursal'])->name('usersucursal.asignar');
        Route::post('/quitar', [UserSucursalController::class, 'quitarSucursal'])->name('usersucursal.quitar');
    });

    Route::get('/doc/{tipofac}/{numerod}/{fksucu}', [SafactController::class, 'documentoSafact'])->name('documento.view');
    Route::get('/doc/buscar', [SafactController::class, 'buscarFactura'])->name('documento.buscar');

    Route::prefix('pagos-proveedores')->name('pagos-proveedores.')->middleware(['auth'])->group(function () {
        Route::get('/', [PagoProveedorController::class, 'index'])->name('index');
        Route::get('/create', [PagoProveedorController::class, 'create'])->name('create');
        Route::get('/detalle-facturadas', [PagoProveedorController::class, 'detalleFacturadas'])->name('detallefacturadas');
        Route::post('/', [PagoProveedorController::class, 'store'])->name('store');
        Route::get('/{id}', [PagoProveedorController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PagoProveedorController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PagoProveedorController::class, 'update'])->name('update');
        Route::delete('/{id}', [PagoProveedorController::class, 'destroy'])->name('destroy');
        Route::get('/productos/{id}/precio', [PagoProveedorController::class, 'getPrecio'])->name('getPrecio');
        Route::get('/{id}/productos', [PagoProveedorController::class, 'getProductos'])->name('productos.get');
        Route::put('/{id}/productos', [PagoProveedorController::class, 'updateProductos'])->name('productos.update');
        Route::match(['GET', 'POST'], '/resumen-general', [PagoProveedorController::class, 'resumenGeneral'])->name('resumen-general');
        Route::get('/exportar-resumen', [PagoProveedorController::class, 'exportarResumen'])->name('exportar-resumen');
        Route::get('/{id}/productos/form', [PagoProveedorController::class, 'productosForm'])->name('productos.form');
        Route::post('/{id}/productos', [PagoProveedorController::class, 'agregarProducto'])->name('productos.agregar');
        Route::delete('/{id}/productos/{detalleId}', [PagoProveedorController::class, 'eliminarProducto'])->name('productos.eliminar');
        Route::get('/{id}/comprobantes', [PagoProveedorController::class, 'getComprobantes'])->name('comprobantes.index');
        Route::get('/{id}/comprobantes/form', [PagoProveedorController::class, 'comprobantesForm'])->name('comprobantes.form');
        Route::post('/{id}/comprobantes', [PagoProveedorController::class, 'agregarComprobante'])->name('comprobantes.agregar');
        Route::delete('/{id}/comprobantes/{comprobanteId}', [PagoProveedorController::class, 'eliminarComprobante'])->name('comprobantes.eliminar');
        Route::get('/{id}/despachos', [PagoProveedorController::class, 'getDespachos'])->name('despachos.index');
        Route::get('/{id}/despachos/form', [PagoProveedorController::class, 'despachosForm'])->name('despachos.form');
        Route::post('/{id}/despachos', [PagoProveedorController::class, 'registrarDespacho'])->name('despachos.registrar');
        Route::delete('/{id}/despachos/{despachoId}', [PagoProveedorController::class, 'eliminarDespacho'])->name('despachos.eliminar');
        Route::post('/{id}/asignar-aprobacion', [PagoProveedorController::class, 'asignarAprobacion'])->name('asignar-aprobacion');
        Route::post('/{id}/editar-aprobacion', [PagoProveedorController::class, 'editarAprobacion'])->name('editar-aprobacion');
    });

    Route::get('/motosporfecha', [PagoProveedorController::class, 'motosPorFecha'])->name('motosPorFecha');
    Route::get('/buscar-productos', [PagoProveedorController::class, 'buscarProductos'])->name('buscar.productos');

    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/conversations', [ChatConversationController::class, 'index'])->name('index');
        Route::post('/conversations/{id}', [ChatConversationController::class, 'show'])->name('show');
        Route::put('/conversations/{id}/status', [ChatConversationController::class, 'updateStatus'])->name('update-status');
        Route::delete('/conversations/{id}', [ChatConversationController::class, 'destroy'])->name('destroy');
        Route::get('/export', [ChatConversationController::class, 'export'])->name('export');
        Route::get('/stats', [ChatConversationController::class, 'stats'])->name('stats');
    });

    Route::resource('tokens',  CwtokenController::class);
    Route::prefix('tokens')->group(function () {
        Route::get('/', [CwtokenController::class, 'reportetokens'])->name('reportetokens');
        Route::post('/', [CwtokenController::class, 'reportetokens']);
        Route::post('/store', [CwtokenController::class, 'store'])->name('tokens.store');
        Route::post('/update', [CwtokenController::class, 'tokenupdate'])->name('token.update');
        Route::post('/generar-auto', [CwtokenController::class, 'generarTokenAuto']);
        Route::post('/new', [CwtokenController::class, 'newtoken']);
        Route::get('/export', [CwtokenController::class, 'export']);
        Route::delete('/{id}', [CwtokenController::class, 'destroy']);
        Route::post('/bulk-delete', [CwtokenController::class, 'bulkDelete']);
    });

    Route::controller(SasucursalController::class)->group(function () {
        Route::post('sascursal/bancos', 'bancos');
    });

    Route::get('/buscarproducto/{codprod}/{comercial}', [SaprodController::class, 'buscarproductoget'])->name('buscarproductoget');
    Route::post('/sascursal/bancos', [SasucursalController::class, 'getBancos'])->name('sucursal.bancos');

    Route::resource('transferencias', CwtransferenciasController::class);
    Route::controller( CwtransferenciasController::class)->group(function () {
        Route::match(['get','post'],'reporte/transferencias', 'reportetransferencias')->name('reportetransferencias');
        Route::post('transferencias/json/{busquedatransf}/{status}/{fechas}', 'json');
        Route::get('transferencias/status/{status}', 'filtrarstatus');
        Route::post('transferencias/pendienteAgain', 'pendienteAgain');
        Route::post('transferencias/verificar', 'verificar');
        Route::match(['get','post'],'transferencia/informacion', 'informacion');
        Route::post('/transferencias/verificar-tiempo-real', 'verificarTiempoReal')->name('transferencias.verificar.tiemporeal');
        Route::post('/transferencias/buscar-numeros-similares', 'buscarNumerosSimilares')->name('transferencias.buscar.numeros');
    });

    Route::get('transferencias/exportar/excel', [CwtransferenciasController::class, 'exportarExcel'])->name('transferencias.exportar.excel');
    Route::get('transferencias/exportar/estadisticas', [CwtransferenciasController::class, 'exportarEstadisticas'])->name('transferencias.exportar.estadisticas');
    Route::get('/transferencias/data', [CwtransferenciasController::class, 'getTransferenciasData'])->name('transferencias.data');
    Route::get('imagen/transferencia/{id}', [ImagenController::class, 'transferencia'])->name('imagen.transferencia');
    Route::get('transferencias/categorias/{q}', [CwtransferenciasController::class, 'getCategorias'])->name('transferencias.categorias');

    Route::get('/bancos/{id}/moneda', function($id) {
        $banco = \App\Models\Cwbancos::find($id);
        if ($banco->bs)      return response()->json(['moneda' => 'BS']);
        if ($banco->dolares) return response()->json(['moneda' => 'USD']);
        if ($banco->pesos)   return response()->json(['moneda' => 'COP']);
        return response()->json(['moneda' => 'BS']);
    });

    Route::get('/verpermisos/{id?}', [PermissionController::class, 'showForm'])->name('permissions.assign');
    Route::post('/verpermisos', [PermissionController::class, 'assign']);
    Route::post('/create/permissions', [PermissionController::class, 'create'])->name('permissions.create');
    Route::get('/revoke/{user}/{permiso}', [PermissionController::class, 'revokePermission'])->name('permissions.revoke');

    Route::match(['get','post'],'/resumenVentas', [HomeController::class, 'resumenVentas'])->name('resumenVentas');
    Route::match(['get','post'],'/tesoro', [TesoroController::class, 'index'])->name('tesoro');
    Route::get('/tesoro/historial', [TesoroController::class, 'historial'])->name('tesoro.historial');

    Route::match(['get','post'],'/reporte/compra', [SacompController::class, 'reportecompra'])->name('reportecompra');
    Route::post('/compras/documento-ajax', [SacompController::class, 'documentoAjax'])->name('compras.documento-ajax');

    Route::resource('compras', SacompController::class);
    Route::controller(SacompController::class)->group(function () {
        Route::get('compra/{id}', 'documentoSacomp');
        Route::get('compra/seriales/{id}', 'documentoSerialesSacomp');
    });

    Route::resource('iaknowledge', IAController::class);
    Route::get('iaknowledge-search', [IAController::class, 'search'])->name('iaknowledge.search');
    Route::get('/productos/plantilla', [SaprodController::class, 'descargarPlantilla'])->name('productos.plantilla');
    Route::post('/productos/importarcrear', [SaprodController::class, 'importarcrear'])->name('productos.importarcrear');
    Route::post('saprod/update', [SaprodController::class, 'updateSaprodData']);
    Route::get('saprod/export/{codalte}', [SaprodController::class, 'saprodexport']);
    Route::post('/productos/validar-codigo', [SaprodController::class, 'validarCodigo'])->name('productos.validar-codigo');

    Route::resource('vendedores', SavendController::class);
    Route::controller(SavendController::class)->group(function () {
        Route::get('savend/json', 'json')->name('vendedores.json');
    });

    Route::controller(SafactController::class)->group(function () {
        Route::get('doc/{tipofac}/{numerod}/{fksucu}', 'documentoSafact')->name('facturaver');
        Route::post('openDoc', 'documentoAjax');
    });

    Route::resource('instancias', SainstaController::class);
    Route::controller(SainstaController::class)->group(function () {
        Route::get('sainsta/json', 'json')->name('sainsta.json');
        Route::post('sainsta/check/lastprod/{codinst}', 'lastprod');
    });

    Route::resource('instpago', SatarjController::class);
    Route::controller(SatarjController::class)->group(function () {
        Route::get('satarj/json', 'json');
    });

    Route::resource('cliente', SaclieController::class);
    Route::controller(SaclieController::class)->group(function () {
        Route::match(['get','post'],'/clientes/{codclie?}/{tab?}', 'index')->name('buscarclientes');
        Route::post('updatecliente', 'updatecliente')->name('updatecliente');
        Route::post('buscarclienteajax', 'buscarclienteajax')->name('buscarclienteajax');
    });

    // routes/web.php - dentro del grupo auth
    Route::prefix('cxcweb')->name('cxcweb.')->group(function () {
        Route::get('/instrumentos', [SaacxcController::class, 'getInstrumentosPago'])->name('instrumentos');
        Route::post('/procesar-pago-web', [SaacxcController::class, 'procesarPagoWeb'])->name('procesar.pago.web');
    });

    Route::post('/cxc/detalle-grupo', [SaacxcController::class, 'detalleGrupoPago'])->name('cxc.detalle.grupo');

    Route::controller(SaacxcController::class)->group(function () {
        Route::match(['get','post'],'cxc/{id?}', 'saacxc')->name('saacxc');
        Route::post('/cxclist', 'cxclist');
        Route::post('/cxcabonarweb', 'cxcabonarweb');
        Route::post('/cxc/clientes-por-sucursal', 'clientesPorSucursal');
        Route::post('/cxcdescuento',  'aplicarDescuento')->name('cxcdescuento');
    });

    // ============================================================
    // RUTAS DE PROVEEDORES — ESPECÍFICAS PRIMERO, RESOURCE AL FINAL
    // ============================================================
    // (CRÍTICO: van antes de Route::resource para no ser capturadas por {proveedor})

    Route::get('proveedores/analisis-gerencial-global-cxp', [SaprovController::class, 'analisisGerencialGlobalCxp'])
        ->name('proveedores.analisis-gerencial-global-cxp');
    Route::post('proveedores/compras-historicas-producto', [SaprovController::class, 'comprasHistoricasProducto'])
        ->name('proveedores.compras-historicas-producto');
    Route::post('proveedores/compras-diarias-producto', [SaprovController::class, 'comprasDiariasProducto'])
        ->name('proveedores.compras-diarias-producto');
    Route::post('proveedores/ventas-diarias-producto', [SaprovController::class, 'ventasDiariasProducto'])
        ->name('proveedores.ventas-diarias-producto');
    Route::get('proveedores/cuentas-por-pagar/resumen-general', [SaprovController::class, 'getResumenGeneralCuentasPorPagar'])
        ->name('proveedores.cuentas-por-pagar.resumen-general');
    Route::post('proveedores/producto/quick-update', [SaprovController::class, 'quickUpdateProducto'])
        ->name('proveedores.producto.quick-update');
    Route::get('proveedores/debug/{codprov}/{codprod}', [SaprovController::class, 'debug'])
        ->name('proveedores.debug');
    Route::post('proveedores/buscarPredictivo', [SaprovController::class, 'buscarPredictivo'])
        ->name('proveedores.buscarPredictivo');
    Route::match(['get','post'], 'proveedores/{codprov}/productos-panel', [SaprovController::class, 'productosPanel'])
        ->name('proveedores.productos-panel');
    Route::get('proveedores/{codprov}/cxp-simple', [SaprovController::class, 'cxpProveedorSimple'])
        ->name('proveedores.cxp-simple');
    Route::post('proveedores/{codprov}/productos-panel', [SaprovController::class, 'productosPanel'])
        ->name('proveedores.productos-panel.post');
    Route::get('proveedores/{codprov}/cuentas-por-pagar', [SaprovController::class, 'getCuentasPorPagar'])
        ->name('proveedores.cuentas-por-pagar');
    Route::post('proveedoresupdate', [SaprovController::class, 'proveedoresupdate'])
        ->name('proveedoresupdate');
    Route::post('proveedores/marcar-pagado', [SaprovController::class, 'marcarPagado'])
        ->name('proveedores.marcar-pagado');
    Route::get('proveedores/pagos/pendientes', [SaprovController::class, 'pagosPendientes'])
        ->name('proveedores.pagos-pendientes');
    Route::get('proveedores-json', [SaprovController::class, 'json'])->name('proveedores.json');
    Route::get('saprov/json', [SaprovController::class, 'json']);

    // Resource al final: captura GET/POST/... /proveedores/{id}
    Route::resource('proveedores', SaprovController::class);

    // Index con parámetros opcionales
    Route::match(['get','post'], 'proveedores/{codprov?}/{tab?}', [SaprovController::class, 'index'])
        ->name('proveedores.index');

    // ============================================================

    Route::get('/productos/{codprod}/datos-edit', [SaprodController::class, 'getDatosEdit'])
        ->name('productos.datos-edit');

    Route::resource('productos', SaprodController::class);
    Route::controller(SaprodController::class)->group(function () {
        Route::post('saprod/listprodubiccompany', 'listprodubiccompany')->name('saprod.listprodubiccompany');
        Route::get('saprod/json', 'json');
        Route::match(['get','post'],'sugerir-transferencias', [SaprodController::class, 'sugerirTransferencias'])->name('sugerir-transferencias');
        Route::post('saprod/check/codprod/{codprod}', 'checkcodprod');
        Route::post('saprod/home/busqueda', 'busquedaHomeProd');
        Route::match(['get','post'],'existencias', 'existencias');
        Route::post('reporte/existen/php', 'existenciasphp');
        Route::match(['get','post'],'ventas/productos/sucursales', 'productossucursales');
        Route::match(['get','post'],'ventas/resultado', 'resultadosucursales');
        Route::post('saprod/viewprodinstsanciascodalte', 'viewprodinstsanciascodalte');
        Route::match(['get','post'],'/operaciones/{codprod?}', 'index');
        Route::get( '/existencia/celulares', 'existenciasCelulares');
        Route::post( '/existencia/celulares/modelos', 'existenciasCelularesModelos');
        Route::get( '/existencia/celulares/modelos/{inspadre}', 'existenciasCelularesModelos');
    });

    Route::prefix('productos-imagenes')->name('productos.imagenes.')->group(function () {
        Route::get('/{codprod}', [SaprodImagenController::class, 'getImagenes'])->name('get');
        Route::get('/{codprod}/principal', [SaprodImagenController::class, 'getImagenPrincipal'])->name('get-principal');
        Route::post('/upload', [SaprodImagenController::class, 'upload'])->name('upload');
        Route::post('/upload-multiple', [SaprodImagenController::class, 'uploadMultiple'])->name('upload-multiple');
        Route::delete('/{id}', [SaprodImagenController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-principal', [SaprodImagenController::class, 'setPrincipal'])->name('set-principal');
        Route::post('/{id}/set-thumbnail', [SaprodImagenController::class, 'setThumbnail'])->name('set-thumbnail');
        Route::post('/{id}/set-icono', [SaprodImagenController::class, 'setIcono'])->name('set-icono');
        Route::put('/{id}/tipo', [SaprodImagenController::class, 'updateTipo'])->name('update-tipo');
        Route::post('/update-order', [SaprodImagenController::class, 'updateOrder'])->name('update-order');
    });

    Route::resource('depositos', SadepoController::class);
    Route::controller(SadepoController::class)->group(function () {
        Route::get('sadepo/json', 'json')->name('depositos.json');
    });

    Route::get('/buscar-factura/{tipo}/{numero}', [SafactController::class, 'buscarFacturaPorNumero'])
        ->name('documento.buscar.ajax');

    Route::match(['get','post'],'/reporte/instpagobs',      [SatarjController::class, 'instpagobs'])->name('instpagobs');
    Route::match(['get','post'],'/reporte/instpagodolares', [SatarjController::class, 'instpagodolares'])->name('instpagodolares');

    Route::match(['get','post'],'/reporte/venta', [HomeController::class, 'reporteventa'])->name('reporteventa');
    Route::post('/reporte/venta/sucu', [HomeController::class, 'reporteventasucu'])->name('reporteventasucu');
    Route::get('/cambiarcomercial/{comercialid}', [HomeController::class, 'cambiarcomercial'])->name('cambiarcomercial');

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('proveedor', [ReporteProveedorController::class, 'index'])->name('proveedor.index');
        Route::post('proveedor/marcar-pagado/{id}', [ReporteProveedorController::class, 'marcarPagado'])->name('proveedor.marcar-pagado');
        Route::get('proveedor/resumen', [ReporteProveedorController::class, 'resumen'])->name('proveedor.resumen');
        Route::get('proveedor/exportar', [ReporteProveedorController::class, 'exportarExcel'])->name('proveedor.exportar');
    });

    Route::match(['get','post'],'/', [HomeController::class, 'index'])->name('index');
    Route::match(['get','post'],'/index', [HomeController::class, 'index'])->name('index');

    Route::prefix('seriales')->name('seriales.')->group(function () {
        Route::get('/historial', [SerialController::class, 'historial'])->name('historial');
        Route::post('/buscar-ajax', [SerialController::class, 'buscarSeriales'])->name('buscar.ajax');
        Route::post('/historial-ajax', [SerialController::class, 'getHistorialAjax'])->name('historial.ajax');
        Route::get('/historial-json/{codprod}/{serial}', [SerialController::class, 'historialJson'])->name('historial.json');
        Route::get('/buscar', [SerialController::class, 'buscar'])->name('buscar');
        Route::get('/estadisticas-compra/{compraId}', [SerialController::class, 'estadisticasCompra'])->name('estadisticas-compra');
        Route::get('/{id}/comentario', [VerificacionSerialController::class, 'getComentario'])->name('get-comentario');
        Route::post('/{id}/verificar', [VerificacionSerialController::class, 'verificar'])->name('verificar');
        Route::get('/estadisticas-compra/{compraId}', [VerificacionSerialController::class, 'estadisticasVerificacion'])->name('estadisticas-verificacion');
    });

    Route::get('/seriales/{id}/data', [VerificacionSerialController::class, 'getSerial']);
    Route::get('/facturas/detalle-vista/{tipofac}/{numerod}/{fksucu}', [App\Http\Controllers\Facturas\FacturaController::class, 'detalleVista']);
    Route::get('/facturas/detalle/{tipo}/{numero}/{sucursal}', [App\Http\Controllers\Facturas\FacturaController::class, 'detalle']);

    Route::prefix('transferencias')->group(function () {
        Route::get('/', [TransferenciaController::class, 'index'])->name('transferencias.index');
        Route::post('/buscar-producto', [TransferenciaController::class, 'buscarProducto'])->name('transferencias.buscar');
        Route::post('/agregar-item', [TransferenciaController::class, 'agregarItem'])->name('transferencias.agregar');
        Route::delete('/eliminar-item/{id}', [TransferenciaController::class, 'eliminarItem'])->name('transferencias.eliminar');
        Route::put('/actualizar-item/{id}', [TransferenciaController::class, 'actualizarItem'])->name('transferencias.actualizar');
        Route::post('/guardar', [TransferenciaController::class, 'guardarProceso'])->name('transferencias.guardar');
        Route::get('/historial', [TransferenciaController::class, 'historial'])->name('transferencias.historial');
        Route::get('/detalle/{id}', [TransferenciaController::class, 'detalle'])->name('transferencias.detalle');
        Route::post('/cancelar/{id}', [TransferenciaController::class, 'cancelar'])->name('transferencias.cancelar');
        Route::post('/cargar-sugerencia', [TransferenciaController::class, 'cargarSugerencia'])->name('transferencias.cargar-sugerencia');
        Route::post('/limpiar-sesion', [TransferenciaController::class, 'limpiarSesion'])->name('transferencias.limpiar');
    });

    Route::get('{any}', [TonerController::class, 'index']);
    Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
});

Route::post('/chat/initialize', [ChatbotController::class, 'initialize'])->name('chat.initialize');
Route::post('/chat/message', [ChatbotController::class, 'message'])->name('chatbot.message');
Route::post('/chat/end', [ChatbotController::class, 'endConversation'])->name('chat.end');

Route::controller(CwtransferenciasController::class)->group(function () {
    Route::get('transferencias/cambiarstatus/{Cwtransferencia}', 'cambiarstatus');
    Route::get('transferencias/validar/{Cwtransferencia}', 'validar')->name('transferencias.validar');
});
