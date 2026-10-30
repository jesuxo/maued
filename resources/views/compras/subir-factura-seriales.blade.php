{{-- resources/views/compras/subir-factura-seriales.blade.php --}}
@extends('layouts.master')

@section('title')
    Extraer seriales
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <style>
        /* Estilos adicionales para mejorar la experiencia */
        #serialesTextarea {
            transition: all 0.3s;
            min-height: 150px;
        }
        #serialesTextarea:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        #btnCopiar {
            transition: all 0.3s;
        }
        #btnCopiar:hover {
            transform: scale(1.02);
        }
        .file-info {
            margin-top: 8px;
        }
        .file-info .badge {
            margin-right: 4px;
        }
        #resultados {
            display: none;
        }
        #progressContainer {
            display: none;
            margin-top: 15px;
        }
        .card-border-primary {
            border: 1px solid #0d6efd;
        }
        .card-border-success {
            border: 1px solid #198754;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">📄 Extraer Seriales desde Factura</h4>
                    </div>

                    <div class="card-body">
                        <!-- Alertas -->
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Formulario de subida -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-border-primary">
                                    <div class="card-body">
                                        <h5 class="card-title">📤 Subir Factura</h5>
                                        <form id="formFactura" enctype="multipart/form-data">
                                            @csrf

                                            <div class="form-group mb-3">
                                                <label for="archivo" class="form-label fw-bold">Selecciona la factura</label>
                                                <input type="file"
                                                       name="archivo"
                                                       id="archivo"
                                                       class="form-control"
                                                       accept=".pdf,.jpg,.jpeg,.png"
                                                       required>
                                                <small class="form-text text-muted">
                                                    📌 Formatos soportados: PDF, JPG, JPEG, PNG (máx 10MB)
                                                </small>
                                                <div id="fileInfo" class="file-info"></div>
                                            </div>

                                            <button type="submit" class="btn btn-primary w-100" id="btnProcesar">
                                                <span id="btnTexto">🔍 Extraer Seriales</span>
                                                <span id="btnSpinner" style="display:none;">
                                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                                    Procesando...
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-border-success">
                                    <div class="card-body">
                                        <h5 class="card-title">📋 Resultados</h5>
                                        <div id="resultados">
                                            <div class="alert alert-success" id="mensajeExito"></div>

                                            <div class="mb-3">
                                                <label class="fw-bold">Seriales encontrados (<span id="totalSeriales">0</span>)</label>
                                                <div class="input-group">
                                                    <textarea id="serialesTextarea"
                                                              class="form-control"
                                                              rows="6"
                                                              readonly
                                                              style="font-family: monospace; font-size: 14px; background: #f8f9fa;"></textarea>
                                                    <button class="btn btn-success" onclick="copiarTextarea()" id="btnCopiar">
                                                        📋 Copiar
                                                    </button>
                                                </div>
                                                <small class="text-muted">Los seriales están separados por <code>|</code> para facilitar el pegado</small>
                                            </div>

                                            <div class="alert alert-info">
                                                <strong>💡 Uso:</strong> Copia los seriales y pégalos en el sistema donde los necesites.
                                                <br>Formato: <code>SER001|SER002|SER003|SER004</code>
                                            </div>

                                            <button class="btn btn-secondary" onclick="window.location.reload()">
                                                🔄 Nueva Lectura
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de progreso -->
                        <div id="progressContainer">
                            <div class="progress">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                     role="progressbar" style="width: 0%">0%</div>
                            </div>
                            <p class="text-center text-muted mt-2" id="progressText">Procesando documento...</p>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h5>📖 Instrucciones</h5>
                        <ul class="mb-0">
                            <li>Sube una factura en formato <strong>PDF</strong> o <strong>imagen</strong></li>
                            <li>El sistema extraerá automáticamente los <strong>seriales</strong> usando OCR</li>
                            <li>Los seriales aparecerán separados por <code>|</code> para copiarlos fácilmente</li>
                            <li><strong>Nota:</strong> Los seriales solo se extraen, <strong>NO se guardan</strong> automáticamente</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        document.getElementById('formFactura').addEventListener('submit', function(e) {
            e.preventDefault();

            const btnTexto = document.getElementById('btnTexto');
            const btnSpinner = document.getElementById('btnSpinner');
            const btnProcesar = document.getElementById('btnProcesar');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            const resultados = document.getElementById('resultados');

            // Resetear resultados
            resultados.style.display = 'none';

            // Mostrar progreso
            btnTexto.style.display = 'none';
            btnSpinner.style.display = 'inline-block';
            btnProcesar.disabled = true;
            progressContainer.style.display = 'block';
            progressBar.style.width = '10%';
            progressBar.textContent = '10%';
            progressText.textContent = 'Subiendo archivo...';

            const formData = new FormData(this);

            // Simular progreso
            let progress = 10;
            const interval = setInterval(() => {
                progress += 10;
                if (progress >= 80) {
                    clearInterval(interval);
                    progress = 80;
                    progressText.textContent = 'Analizando documento...';
                }
                progressBar.style.width = progress + '%';
                progressBar.textContent = progress + '%';
            }, 800);

            // Enviar request AJAX
            fetch('{{ route("compras.extraer-seriales") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            })
                .then(response => response.json())
                .then(data => {
                    clearInterval(interval);
                    progressBar.style.width = '100%';
                    progressBar.textContent = '100%';
                    progressText.textContent = '✅ Procesado';

                    btnTexto.style.display = 'inline';
                    btnSpinner.style.display = 'none';
                    btnProcesar.disabled = false;

                    if (data.success) {
                        mostrarResultados(data);
                    } else {
                        mostrarError(data.message);
                    }

                    setTimeout(() => {
                        progressContainer.style.display = 'none';
                    }, 1500);
                })
                .catch(error => {
                    clearInterval(interval);
                    btnTexto.style.display = 'inline';
                    btnSpinner.style.display = 'none';
                    btnProcesar.disabled = false;
                    progressContainer.style.display = 'none';
                    mostrarError('Error al procesar: ' + error.message);
                });
        });

        function mostrarResultados(data) {
            const resultados = document.getElementById('resultados');
            const textarea = document.getElementById('serialesTextarea');
            const totalSpan = document.getElementById('totalSeriales');
            const mensajeExito = document.getElementById('mensajeExito');

            // Mostrar mensaje de éxito
            mensajeExito.textContent = '✅ ' + data.message + ' (' + data.total + ' seriales)';
            mensajeExito.style.display = 'block';

            // Mostrar seriales en textarea
            textarea.value = data.seriales_string;
            totalSpan.textContent = data.total;

            // Mostrar resultados
            resultados.style.display = 'block';

            // Hacer scroll al resultado
            resultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function mostrarError(mensaje) {
            const resultados = document.getElementById('resultados');
            const mensajeExito = document.getElementById('mensajeExito');

            mensajeExito.className = 'alert alert-danger';
            mensajeExito.textContent = '❌ ' + mensaje;
            mensajeExito.style.display = 'block';

            resultados.style.display = 'block';
        }

        function copiarTextarea() {
            const textarea = document.getElementById('serialesTextarea');

            if (textarea.value === '') {
                alert('No hay seriales para copiar');
                return;
            }

            // Método moderno usando navigator.clipboard
            if (navigator.clipboard) {
                navigator.clipboard.writeText(textarea.value)
                    .then(() => {
                        mostrarNotificacion('✅ Seriales copiados al portapapeles');
                    })
                    .catch(() => {
                        // Fallback: método tradicional
                        copiarTradicional(textarea);
                    });
            } else {
                // Fallback: método tradicional
                copiarTradicional(textarea);
            }
        }

        function copiarTradicional(textarea) {
            textarea.select();
            textarea.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');
                mostrarNotificacion('✅ Seriales copiados al portapapeles');
            } catch (err) {
                alert('❌ No se pudo copiar. Selecciona el texto manualmente.');
            }
        }

        function mostrarNotificacion(mensaje) {
            // Crear notificación flotante
            const toast = document.createElement('div');
            toast.className = 'alert alert-success position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            toast.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            document.body.appendChild(toast);

            // Auto-cerrar después de 3 segundos
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 3000);
        }

        // Mostrar información del archivo seleccionado
        document.getElementById('archivo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const info = document.getElementById('fileInfo');
            if (file) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                const extension = file.name.split('.').pop().toUpperCase();
                info.innerHTML = `
                    <span class="badge bg-primary">📄 ${file.name}</span>
                    <span class="badge bg-secondary">${sizeMB} MB</span>
                    <span class="badge bg-info">${extension}</span>
                `;
            } else {
                info.innerHTML = '';
            }
        });

        // Atajo de teclado: Ctrl+C en el textarea ya funciona por defecto
    </script>
@endsection
