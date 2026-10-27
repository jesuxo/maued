<style>
    /* ========================================== */
    /* ESTILOS MODERNOS PARA EXISTENCIAS POR DEPÓSITO - DENTRO DEL MODAL */
    /* ========================================== */
    .card-existencias-modal {
        border-radius: 0;
        overflow: hidden;
        background: transparent;
        height: auto;
        display: block;
    }

    .table-existencias-modal {
        font-size: 0.78rem;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-existencias-modal thead th {
        background: #0072c5 !important;
        color: #fff !important;
        padding: 8px 6px;
        font-weight: 600;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border: none;
        white-space: nowrap;
    }

    .table-existencias-modal thead th:first-child {
        border-radius: 0;
        padding-left: 12px;
    }

    .table-existencias-modal thead th:last-child {
        border-radius: 0;
        padding-right: 12px;
    }

    .table-existencias-modal tbody td {
        padding: 5px 6px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2f5;
        font-size: 0.75rem;
        transition: background 0.15s ease;
    }

    .table-existencias-modal tbody td:first-child {
        padding-left: 12px;
    }

    .table-existencias-modal tbody td:last-child {
        padding-right: 12px;
    }

    .table-existencias-modal tbody tr {
        transition: background 0.15s ease;
    }

    .table-existencias-modal tbody tr:hover {
        background: #f0f7ff !important;
    }

    .table-existencias-modal tbody tr:last-child td {
        border-bottom: none;
    }

    /* Fila de totales */
    .table-existencias-modal .fila-total {
        background: #0072c5 !important;
        color: #fff !important;
        font-weight: 700;
    }

    .table-existencias-modal .fila-total td {
        background: #0072c5 !important;
        padding: 8px 6px;
        font-size: 0.8rem;
        border-top: 2px solid #0056a7;
        color: #fff !important;
    }

    /* Badges de cantidad */
    .badge-cantidad-modal {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        min-width: 28px;
        text-align: center;
        transition: all 0.2s ease;
    }

    .badge-cantidad-modal.positivo {
        background: #d4edda;
        color: #155724;
    }

    .badge-cantidad-modal.alto {
        background: #cce5ff;
        color: #004085;
        font-weight: 700;
    }

    .badge-cantidad-modal.muy-alto {
        background: #fff3cd;
        color: #856404;
        font-weight: 700;
        animation: pulse-badge-modal 2s infinite;
    }

    @keyframes pulse-badge-modal {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .badge-cantidad-modal.cero {
        background: #f8f9fa;
        color: #adb5bd;
    }

    .codigo-prod-modal {
        font-weight: 700;
        color: #0072c5;
        font-size: 0.7rem;
        font-family: 'Courier New', monospace;
    }

    .nombre-prod-modal {
        font-weight: 500;
        color: #2c3e50;
        font-size: 0.75rem;
    }

    .descrip2-prod-modal {
        font-size: 0.65rem;
        color: #6c757d;
    }

    .total-unds-modal {
        font-weight: 700;
        color: #0072c5;
        text-align: center;
        font-size: 0.8rem;
    }

    .total-unds-footer-modal {
        font-weight: 700;
        color: #ffd700;
        text-align: center;
        font-size: 0.85rem;
    }

    .celda-vacia-modal {
        color: #dee2e6;
        font-size: 0.65rem;
    }

    /* Contenedor de scroll: SIN overflow ni altura fija */
    .scroll-existencias-modal {
        flex: none;
        overflow: visible;
        min-height: auto;
    }

    .scroll-existencias-modal::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }

    .scroll-existencias-modal::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 8px;
    }

    .scroll-existencias-modal::-webkit-scrollbar-thumb {
        background: #0072c5;
        border-radius: 8px;
    }

    .scroll-existencias-modal::-webkit-scrollbar-thumb:hover {
        background: #0056a7;
    }

    /* ========================================== */
    /* FILTRO DE DEPÓSITOS CON REORDENAMIENTO */
    /* ========================================== */
    .deposito-filter-modal {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 8px 12px;
        margin: 0 0 10px 0;
        border: 1px solid #e9ecef;
        flex-shrink: 0;
    }

    .deposito-filter-modal .filter-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
        display: block;
    }

    .deposito-filter-modal .depositos-container {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        align-items: center;
        margin-bottom: 4px;
    }

    .deposito-filter-modal .deposito-item {
        display: inline-flex;
        align-items: center;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 16px;
        padding: 2px 8px;
        transition: all 0.2s ease;
        cursor: grab;
        font-size: 0.65rem;
        user-select: none;
    }

    .deposito-filter-modal .deposito-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .deposito-filter-modal .deposito-item:active {
        cursor: grabbing;
    }

    .deposito-filter-modal .deposito-item.dragging {
        opacity: 0.5;
        transform: scale(0.95);
    }

    .deposito-filter-modal .deposito-item .drag-handle {
        cursor: grab;
        color: #adb5bd;
        margin-right: 4px;
        font-size: 0.6rem;
    }

    .deposito-filter-modal .deposito-item .drag-handle:active {
        cursor: grabbing;
    }

    .deposito-filter-modal .deposito-item input[type="checkbox"] {
        margin-right: 4px;
        accent-color: #0072c5;
        width: 12px;
        height: 12px;
        cursor: pointer;
    }

    .deposito-filter-modal .deposito-item.checked {
        background: #cce5ff;
        border-color: #0072c5;
    }

    .deposito-filter-modal .deposito-item .deposito-nombre {
        font-size: 0.65rem;
        color: #2c3e50;
    }

    .deposito-filter-modal .deposito-item .orden-numero {
        font-size: 0.5rem;
        color: #6c757d;
        margin-left: 4px;
        background: #e9ecef;
        padding: 0 4px;
        border-radius: 8px;
    }

    .deposito-filter-modal .filter-actions {
        display: flex;
        gap: 3px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .deposito-filter-modal .btn-aplicar-modal {
        background: #0072c5;
        color: #fff;
        border: none;
        padding: 3px 14px;
        border-radius: 16px;
        font-size: 0.65rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .deposito-filter-modal .btn-aplicar-modal:hover {
        background: #0056a7;
    }

    .deposito-filter-modal .btn-seleccionar-todos-modal {
        color: #fff;
        border: none;
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 0.6rem;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-right: 3px;
    }

    .deposito-filter-modal .btn-seleccionar-todos-modal.verde {
        background: #28a745;
    }

    .deposito-filter-modal .btn-seleccionar-todos-modal.verde:hover {
        background: #218838;
    }

    .deposito-filter-modal .btn-seleccionar-todos-modal.rojo {
        background: #dc3545;
    }

    .deposito-filter-modal .btn-seleccionar-todos-modal.rojo:hover {
        background: #c82333;
    }

    .deposito-filter-modal .btn-resetear-orden {
        background: #6c757d;
        color: #fff;
        border: none;
        padding: 3px 10px;
        border-radius: 16px;
        font-size: 0.6rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .deposito-filter-modal .btn-resetear-orden:hover {
        background: #5a6268;
    }

    .deposito-filter-modal .badge-depositos-modal {
        background: #0072c5;
        color: #fff;
        padding: 1px 8px;
        border-radius: 10px;
        font-size: 0.6rem;
        margin-left: 4px;
    }

    .deposito-filter-modal .orden-indicador {
        font-size: 0.55rem;
        color: #6c757d;
        margin-left: 6px;
    }

    /* Drag and Drop styles */
    .deposito-item.drag-over {
        border-color: #0072c5;
        background: #e6f3ff;
        transform: scale(1.05);
        box-shadow: 0 2px 12px rgba(0,114,197,0.2);
    }

    .deposito-item.dragging {
        opacity: 0.3;
        transform: scale(0.9);
    }

    .deposito-item .drag-handle {
        cursor: grab;
        color: #adb5bd;
        margin-right: 4px;
        font-size: 0.6rem;
        transition: color 0.2s ease;
    }

    .deposito-item:hover .drag-handle {
        color: #0072c5;
    }

    /* Barra de información */
    .info-bar-modal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 6px;
        font-size: 0.65rem;
        color: #6c757d;
        border-top: 1px solid #e9ecef;
        margin-top: 6px;
        padding-top: 6px;
        flex-shrink: 0;
    }

    /* Contenedor de la tabla: SIN flex ni altura fija */
    .table-wrapper-modal {
        flex: none;
        display: block;
        min-height: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .table-existencias-modal {
            font-size: 0.65rem;
        }

        .table-existencias-modal thead th,
        .table-existencias-modal tbody td {
            padding: 3px 3px;
        }

        .badge-cantidad-modal {
            padding: 1px 6px;
            font-size: 0.6rem;
            min-width: 20px;
        }

        .deposito-filter-modal {
            padding: 6px 8px;
        }

        .deposito-filter-modal .deposito-item {
            font-size: 0.55rem;
            padding: 1px 6px;
        }

        .deposito-filter-modal .deposito-item .deposito-nombre {
            font-size: 0.55rem;
        }

        .deposito-filter-modal .btn-aplicar-modal,
        .deposito-filter-modal .btn-seleccionar-todos-modal,
        .deposito-filter-modal .btn-resetear-orden {
            font-size: 0.55rem;
            padding: 2px 8px;
        }
    }

    /* Modo impresión */
    @media print {
        .deposito-filter-modal {
            display: none !important;
        }

        .table-existencias-modal thead th {
            background: #0072c5 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .table-existencias-modal .fila-total {
            background: #0072c5 !important;
            color: #fff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .scroll-existencias-modal {
            max-height: none !important;
            overflow: visible !important;
        }
    }
</style>

<div class="card-existencias-modal" id="cardExistenciasModal">
    <!-- ========================================== -->
    <!-- FILTRO DE DEPÓSITOS CON REORDENAMIENTO -->
    <!-- ========================================== -->
    @if(count($deposito) > 0)
        <div class="deposito-filter-modal">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div style="flex: 1;">
                <span class="filter-label">
                    <i class="bi bi-arrow-up-down"></i> Ordenar y Filtrar Depósitos:
                    <span class="badge-depositos-modal" id="contadorSeleccionadosModal">
                        {{ count($depositoValues) }} seleccionados
                    </span>
                    <span class="orden-indicador">
                        <i class="bi bi-grip-vertical"></i> Arrastra para reordenar
                    </span>
                </span>
                    <div class="depositos-container" id="depositosContainer">
                        @foreach($deposito as $key => $nombre)
                            @php
                                $checked = in_array($key, $depositosSeleccionados ?? array_keys($deposito));
                                $posicion = array_search($key, array_keys($deposito)) + 1;
                            @endphp
                            <div class="deposito-item {{ $checked ? 'checked' : '' }}"
                                 data-key="{{ $key }}"
                                 data-posicion="{{ $posicion }}"
                                 draggable="true">
                            <span class="drag-handle">
                                <i class="bi bi-grip-vertical"></i>
                            </span>
                                <input type="checkbox"
                                       class="deposito-checkbox-modal"
                                       value="{{ $key }}"
                                    {{ $checked ? 'checked' : '' }}>
                                <span class="deposito-nombre">{{ Str::limit($nombre, 14) }}</span>
                                <span class="orden-numero">{{ $posicion }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="filter-actions mt-1 mt-md-0">
                    <button class="btn-seleccionar-todos-modal verde" onclick="seleccionarTodosModal()">
                        <i class="bi bi-check-all"></i> Todos
                    </button>
                    <button class="btn-seleccionar-todos-modal rojo" onclick="deseleccionarTodosModal()">
                        <i class="bi bi-x"></i> Ninguno
                    </button>
                    <button class="btn-resetear-orden" onclick="resetearOrdenModal()" title="Restaurar orden original">
                        <i class="bi bi-arrow-counterclockwise"></i> Orden
                    </button>
                    <button class="btn-aplicar-modal" onclick="aplicarFiltroModal()">
                        <i class="bi bi-check2"></i> Aplicar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- TABLA CON ALTURA DINÁMICA -->
    <!-- ========================================== -->
    <div class="table-wrapper-modal">
        <div class="scroll-existencias-modal" id="scrollExistenciasModal">
            <table class="table-existencias-modal table table-borderless table-centered align-middle table-nowrap mb-0"
                   id="tablaExistenciasModal">
                <thead>
                <tr>
                    <th width="5%" class="text-center">CÓD</th>
                    <th width="22%">PRODUCTO</th>
                    <th width="8%" class="text-center">REF</th>
                    @foreach($depositoValues as $indexDep => $descripdepo)
                        <th width="12%" class="text-center">
                                <span class="deposito-tooltip" title="{{ $descripdepo }}">
                                    {{ Str::limit($descripdepo, 10) }}
                                </span>
                        </th>
                    @endforeach
                    <th width="8%" class="text-center">UNDS</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $tantos = 0;
                    $totalcost = 0;
                    $existdepstt = 0;
                    $arraycantdep = array_fill(0, count($depositoValues), 0);
                @endphp

                @forelse($productos as $index => $producto)
                    @php
                        $tantos++;
                        $bgcolor = ($tantos % 2 == 0) ? '#ffffff' : '#f8f9fa';
                        $existdeps = 0;
                        $tieneStock = false;
                    @endphp

                    <tr bgcolor="{{ $bgcolor }}" style="color:#2c3e50;">
                        <td align="left" class="codigo-prod-modal">{{ $index }}</td>
                        <td align="left" class="nombre-prod-modal">{{ $producto['descrip'] ?? '' }}</td>
                        <td align="left" class="descrip2-prod-modal">
                            {{ $producto['descrip2'] ?? '-' }}
                        </td>

                        @foreach($depositoKeys as $depIndex => $depKey)
                            @php
                                $cantidad = $existenciasFiltradas[$index][$depKey] ?? 0;
                                if($cantidad > 0) {
                                    $arraycantdep[$depIndex] += $cantidad;
                                    $existdeps += $cantidad;
                                    $existdepstt += $cantidad;
                                    $totalcost += $cantidad * ($producto['preciodpro'] ?? 0);
                                    $tieneStock = true;
                                }
                            @endphp
                            <td align="center">
                                @if($cantidad > 0)
                                    @php
                                        $claseBadge = 'positivo';
                                        if($cantidad >= 50) $claseBadge = 'muy-alto';
                                        elseif($cantidad >= 20) $claseBadge = 'alto';
                                    @endphp
                                    <span class="badge-cantidad-modal {{ $claseBadge }}">
                                            {{ number_format($cantidad, 0, ',', '.') }}
                                        </span>
                                @else
                                    <span class="celda-vacia-modal">-</span>
                                @endif
                            </td>
                        @endforeach

                        <td align="center" class="total-unds-modal">
                            {{ number_format($existdeps, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($depositoValues) + 3 }}" align="center" style="padding: 30px 0;">
                            <i class="bi bi-inbox" style="font-size: 36px; color: #ccc;"></i>
                            <h6 style="color: #6c757d; margin-top: 8px;">No hay productos con existencias</h6>
                            <p style="color: #999; font-size: 0.8rem;">No se encontraron productos en esta categoría</p>
                        </td>
                    </tr>
                @endforelse

                @if(count($productos) > 0)
                    <!-- FILA DE TOTALES -->
                    <tr class="fila-total" bgcolor="#0072c5">
                        <td colspan="3" align="right">
                            <strong>TOTALES</strong>
                        </td>
                        @foreach($arraycantdep as $totalDep)
                            <td align="center" class="total-unds-footer-modal">
                                @if($totalDep > 0)
                                    {{ number_format($totalDep, 0, ',', '.') }}
                                @else
                                    <span style="color: rgba(255,255,255,0.4);">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td align="center" class="total-unds-footer-modal">
                            <strong>{{ $existdepstt+0 }}</strong>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Barra de información -->
    <div class="info-bar-modal">
        <span>
            <i class="bi bi-info-circle me-1"></i>
            {{ count($productos) }} productos ·
            <span id="footerDepositosModal">{{ count($depositoValues) }}</span> depósitos
        </span>
        <span>
            <i class="bi bi-clock me-1"></i>
            {{ now()->format('H:i') }}
        </span>
    </div>
</div>

<script>
    // ==========================================
    // AJUSTAR ALTURA DEL SCROLL DINÁMICAMENTE
    // (Neutralizada: la tabla crece según su contenido)
    // ==========================================

    function ajustarAlturaScroll() {
        var scrollContainer = document.getElementById('scrollExistenciasModal');
        if (scrollContainer) {
            scrollContainer.style.maxHeight = '';
            scrollContainer.style.height = '';
        }
    }

    // ==========================================
    // DRAG AND DROP PARA REORDENAR DEPÓSITOS
    // ==========================================

    var draggedItem = null;
    var dragOverItem = null;

    function inicializarDragDrop() {
        var items = document.querySelectorAll('.deposito-item');

        items.forEach(function(item) {
            item.removeEventListener('dragstart', handleDragStart);
            item.removeEventListener('dragend', handleDragEnd);
            item.removeEventListener('dragover', handleDragOver);
            item.removeEventListener('dragenter', handleDragEnter);
            item.removeEventListener('dragleave', handleDragLeave);
            item.removeEventListener('drop', handleDrop);

            item.addEventListener('dragstart', handleDragStart);
            item.addEventListener('dragend', handleDragEnd);
            item.addEventListener('dragover', handleDragOver);
            item.addEventListener('dragenter', handleDragEnter);
            item.addEventListener('dragleave', handleDragLeave);
            item.addEventListener('drop', handleDrop);
        });
    }

    function handleDragStart(e) {
        draggedItem = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }

    function handleDragEnd(e) {
        this.classList.remove('dragging');
        document.querySelectorAll('.deposito-item').forEach(function(item) {
            item.classList.remove('drag-over');
        });
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        return false;
    }

    function handleDragEnter(e) {
        e.preventDefault();
        if (this !== draggedItem) {
            this.classList.add('drag-over');
        }
    }

    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }

    function handleDrop(e) {
        e.preventDefault();
        e.stopPropagation();

        this.classList.remove('drag-over');

        if (draggedItem !== this) {
            var container = document.getElementById('depositosContainer');
            var items = container.querySelectorAll('.deposito-item');

            var draggedIndex = Array.from(items).indexOf(draggedItem);
            var targetIndex = Array.from(items).indexOf(this);

            if (draggedIndex < targetIndex) {
                this.parentNode.insertBefore(draggedItem, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedItem, this);
            }

            actualizarNumerosOrden();
        }

        draggedItem = null;
        return false;
    }

    function actualizarNumerosOrden() {
        var items = document.querySelectorAll('.deposito-item');
        items.forEach(function(item, index) {
            var numSpan = item.querySelector('.orden-numero');
            if (numSpan) {
                numSpan.textContent = index + 1;
            }
            item.dataset.posicion = index + 1;
        });
    }

    function getOrdenDepositos() {
        var items = document.querySelectorAll('.deposito-item');
        var orden = [];
        items.forEach(function(item) {
            var key = item.dataset.key;
            if (key) {
                orden.push(key);
            }
        });
        return orden;
    }

    function resetearOrdenModal() {
        var container = document.getElementById('depositosContainer');
        var items = container.querySelectorAll('.deposito-item');
        var itemsArray = Array.from(items);

        itemsArray.sort(function(a, b) {
            return a.dataset.key.localeCompare(b.dataset.key);
        });

        itemsArray.forEach(function(item) {
            container.appendChild(item);
        });

        actualizarNumerosOrden();
        aplicarFiltroModal();
    }

    // ==========================================
    // FILTRO DE DEPÓSITOS
    // ==========================================

    var modalCodalte = '{{ $codalte ?? '' }}';
    var modalBusqueda = '';

    function getModalBusqueda() {
        var input = document.getElementById('busquedaentredepositos');
        return input ? input.value : '';
    }

    function actualizarContadorModal() {
        var checkboxes = document.querySelectorAll('.deposito-checkbox-modal:checked');
        var total = document.querySelectorAll('.deposito-checkbox-modal').length;
        var contador = document.getElementById('contadorSeleccionadosModal');
        if (contador) {
            contador.textContent = checkboxes.length + ' seleccionados';
        }

        var footerDep = document.getElementById('footerDepositosModal');
        if (footerDep) footerDep.textContent = checkboxes.length;

        document.querySelectorAll('.deposito-item').forEach(function(item) {
            var checkbox = item.querySelector('.deposito-checkbox-modal');
            if (checkbox && checkbox.checked) {
                item.classList.add('checked');
            } else {
                item.classList.remove('checked');
            }
        });
    }

    function seleccionarTodosModal() {
        document.querySelectorAll('.deposito-checkbox-modal').forEach(function(cb) {
            cb.checked = true;
        });
        actualizarContadorModal();
    }

    function deseleccionarTodosModal() {
        document.querySelectorAll('.deposito-checkbox-modal').forEach(function(cb) {
            cb.checked = false;
        });
        actualizarContadorModal();
    }

    function aplicarFiltroModal() {
        var checkboxes = document.querySelectorAll('.deposito-checkbox-modal:checked');
        var depositos = [];
        checkboxes.forEach(function(cb) {
            depositos.push(cb.value);
        });

        if (depositos.length === 0) {
            alert('Debe seleccionar al menos un depósito para visualizar.');
            return;
        }

        var busqueda = getModalBusqueda();
        modalBusqueda = busqueda;
        var orden = getOrdenDepositos();

        var content = document.getElementById('contentviewprodcodalte');
        if (content) {
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Actualizando datos...</p>
                </div>
            `;
        }

        $.ajax({
            type: 'post',
            data: {
                codalte: modalCodalte,
                busqueda: busqueda,
                depositos: JSON.stringify(depositos),
                orden_depositos: JSON.stringify(orden)
            },
            url: '/saprod/viewprodinstsanciascodalte',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (content) {
                    content.innerHTML = response;
                    setTimeout(function() {
                        inicializarFiltroModal();
                    }, 100);
                }
            },
            error: function() {
                if (content) {
                    content.innerHTML = `
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error al actualizar los datos. Por favor, intente nuevamente.
                        </div>
                    `;
                }
            }
        });
    }

    function recargarModalConBusqueda(busqueda) {
        modalBusqueda = busqueda;

        var checkboxes = document.querySelectorAll('.deposito-checkbox-modal:checked');
        var depositos = [];
        checkboxes.forEach(function(cb) {
            depositos.push(cb.value);
        });

        if (depositos.length === 0) {
            var allCheckboxes = document.querySelectorAll('.deposito-checkbox-modal');
            allCheckboxes.forEach(function(cb) {
                depositos.push(cb.value);
            });
        }

        var orden = getOrdenDepositos();

        var content = document.getElementById('contentviewprodcodalte');
        if (content) {
            content.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 text-muted">Buscando: ${busqueda}</p>
                </div>
            `;
        }

        $.ajax({
            type: 'post',
            data: {
                codalte: modalCodalte,
                busqueda: busqueda,
                depositos: JSON.stringify(depositos),
                orden_depositos: JSON.stringify(orden)
            },
            url: '/saprod/viewprodinstsanciascodalte',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (content) {
                    content.innerHTML = response;
                    setTimeout(function() {
                        inicializarFiltroModal();
                    }, 100);
                }
            },
            error: function() {
                if (content) {
                    content.innerHTML = `
                        <div class="alert alert-danger text-center">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Error al buscar. Por favor, intente nuevamente.
                        </div>
                    `;
                }
            }
        });
    }

    function inicializarFiltroModal() {
        document.querySelectorAll('.deposito-checkbox-modal').forEach(function(cb) {
            cb.removeEventListener('change', actualizarContadorModal);
            cb.addEventListener('change', actualizarContadorModal);
        });

        inicializarDragDrop();
        actualizarContadorModal();

        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        var rows = document.querySelectorAll('.table-existencias-modal tbody tr:not(.fila-total)');
        rows.forEach(function(row) {
            var celdas = row.querySelectorAll('td');
            var tieneStock = false;
            for (var i = 3; i < celdas.length - 1; i++) {
                var texto = celdas[i].textContent.trim();
                if (texto !== '-' && texto !== '') {
                    tieneStock = true;
                    break;
                }
            }
            if (tieneStock) {
                row.style.borderLeft = '3px solid #28a745';
            }
        });
    }

    // ==========================================
    // EVENTOS
    // ==========================================

    document.addEventListener('DOMContentLoaded', function() {
        inicializarFiltroModal();
    });

    $(document).ready(function() {
        $('#viewprodcodaltemodal').on('shown.bs.modal', function() {
            setTimeout(function() {
                inicializarFiltroModal();
            }, 300);
        });

        // Ya no se reajusta altura al redimensionar (no hay altura fija)
    });
</script>
