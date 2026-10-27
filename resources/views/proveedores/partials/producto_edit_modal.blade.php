<!-- Modal de Edición Rápida de Producto -->
<div class="modal fade" id="productoEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-2"></i>
                    Editar Producto: <span id="modalProductoNombre"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productoEditForm" method="POST">
                @csrf
                <input type="hidden" name="id" id="editProductoId" value="">

                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Código:</strong> <span id="editProductoCodigo"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Descripción del Producto</label>
                                <input type="text" class="form-control" id="editDescrip" name="descrip"  maxlength="40"
                                       placeholder="Nombre del producto" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Referencia / Código de Barra</label>
                                <input type="text" class="form-control" id="editRefere" name="refere"  maxlength="40"
                                       placeholder="Ej: Código de barra">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label class="form-label">Marca</label>
                                <input type="text" class="form-control" id="editMarca" name="marca"  maxlength="20"
                                       placeholder="Ej: Polar">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold text-primary"><i class="bi bi-tags me-2"></i>Costos</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Costo Anterior</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control precio-input" id="editPreciodant"
                                           name="preciodant" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Costo Promedio</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control precio-input" id="editPreciodpro"
                                           name="preciodpro" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Costo Actual</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control precio-input" id="editPreciod"
                                           name="preciod" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold text-success"><i class="bi bi-cash-stack me-2"></i>Precios de Venta</h6>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Precio 1</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control precio-input" id="editCostod"
                                           name="costod" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Precio 2</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control precio-input" id="editCostod2"
                                           name="costod2" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Precio 3</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control precio-input" id="editCostod3"
                                           name="costod3" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Instancia</label>
                                <input type="text" class="form-control" id="editInstancia" disabled>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Existencia Total</label>
                                <input type="text" class="form-control" id="editExistencia" disabled>
                            </div>
                        </div>
                        <div class="col-md-3">

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarProducto">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
