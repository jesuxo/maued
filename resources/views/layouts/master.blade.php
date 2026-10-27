<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-layout="vertical" data-topbar="light" data-sidebar="light"
      data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-body-image="none">

<head>
    <meta charset="utf-8">
    <title>@yield('title') |   SISDATO - Sistema dado para todos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="SISDATO - Sistema dado para todos" name="description">
    <meta content="wwwww" name="author">
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">

    <script
        src="https://code.jquery.com/jquery-3.7.0.min.js"
        integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g="
        crossorigin="anonymous"></script>

    <!-- head css -->
    @include('layouts.head-css')
    <style>
        input[type=number] {
            -moz-appearance: textfield;
        }
        .screenshot-notification {
            position: fixed !important;
            top: 80px !important;
            right: 20px !important;
            z-index: 99999 !important;
            animation: slideInRight 0.3s ease;
        }
    </style>
</head>

<body>

<!-- Begin page -->
<div id="layout-wrapper">
    <!-- top tagbar -->
    @include('layouts.top-tagbar')
    <!-- topbar -->
    @include('layouts.topbar')
    @include('layouts.sidebar')

    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>

        @include('layouts.footer')
    </div>

</div>
@include('layouts.customizer')
@include('layouts.vendor-scripts')

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
{{--<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>--}}
<script>

    function capture(selector) {
        const element = document.querySelector(selector);
        if (!element) {
            alert('No hay datos para capturar');
            return;
        }

        mostrarNotificacion('Generando captura...', 'info');

        html2canvas(element, {
            scale: 2.5,
            backgroundColor: '#ffffff',
            logging: false,
            useCORS: true
        }).then(canvas => {
            // Método universal para Safari y otros navegadores
            canvas.toBlob(function(blob) {
                // Detectar si es Safari
                const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

                if (!isSafari && navigator.clipboard && navigator.clipboard.write) {
                    // Para Chrome, Edge, Firefox modernos
                    try {
                        const clipboardItem = new ClipboardItem({ [blob.type]: blob });
                        navigator.clipboard.write([clipboardItem]).then(() => {
                            mostrarNotificacion('✅ Captura copiada al portapapeles! Puedes pegarla con Ctrl+V', 'success');
                        }).catch(() => {
                            // Fallback a descarga
                            descargarImagen(canvas);
                            mostrarNotificacion('⚠️ Se descargó la imagen (clipboard no disponible)', 'warning');
                        });
                    } catch (e) {
                        // Fallback para Safari
                        descargarImagen(canvas);
                        mostrarNotificacion('✅ Captura descargada (Safari)', 'success');
                    }
                } else {
                    // Para Safari y navegadores sin soporte
                    descargarImagen(canvas);
                    mostrarNotificacion('✅ Captura descargada', 'success');
                }
            }, 'image/png');
        }).catch(error => {
            console.error('Error:', error);
            alert('Error al capturar la pantalla');
        });
    }

    function descargarImagen(canvas, nombre = 'captura') {
        const link = document.createElement('a');
        const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
        link.download = `${nombre}_${timestamp}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }

    function mostrarNotificacion(mensaje, tipo = 'success') {
        $('.screenshot-notification').remove();

        let bgColor   = tipo === 'success' ? '#0072c5' : (tipo === 'warning' ? '#ffc107' : '#0072c5');
        let icono     = tipo === 'success' ? 'check-circle-fill' : (tipo === 'warning' ? 'exclamation-triangle-fill' : 'info-circle-fill');
        let textColor = tipo === 'warning' ? '#000' : '#fff';

        let notification = $(`
                <div class="screenshot-notification alert" style="background-color: ${bgColor}; color: ${textColor};">
                    <i class="bi bi-${icono} me-2"></i>
                    ${mensaje}
                    <button type="button" class="btn-close btn-close-${tipo === 'warning' ? 'black' : 'white'}" data-bs-dismiss="alert"></button>
                </div>
            `);
        $('body').append(notification);
        setTimeout(() => notification.fadeOut(300, () => notification.remove()), 4000);
    }

    $('input[type=text]').attr("autocomplete", "off");

    /* <button type="button" class="btn btn-sm btn-light me-2"   onclick="   capture('#motospagasporrecibir')">
                        <i class="bi bi-camera"></i> Capturar
                    </button>*/

    function exportarExcel(table) {
        var table = document.getElementById(table);
        var html = table.outerHTML;
        var url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
        var link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', 'archivo_excel.xls');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>
</body>

</html>
