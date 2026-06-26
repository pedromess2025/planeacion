<?php include 'config_entregas.php'; ?>
<div class="modal fade" id="modalEnlace" tabindex="-1" aria-labelledby="modalEnlaceLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEnlaceLabel">
                    <i class="fas fa-truck"></i> <span id="modalEnlaceTitulo">Nuevo enlace</span>
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formEnlace" name="formEnlace">
                    <input type="hidden" id="enlaceId" name="enlaceId" value="">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label for="enlaceOrigen">Origen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="enlaceOrigen" name="enlaceOrigen" placeholder="Origen">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="enlaceDestino">Destino <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="enlaceDestino" name="enlaceDestino" placeholder="Destino">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 mb-2">
                            <label for="enlaceContenido">Contenido <span class="text-danger">*</span></label>
                            <textarea class="form-control form-control-sm" id="enlaceContenido" name="enlaceContenido" rows="2" placeholder="Qué se envía"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label for="enlaceResponsable">Responsable</label>
                            <select class="form-select form-select-sm" id="enlaceResponsable" name="enlaceResponsable">
                                <option value="">Selecciona...</option>
                            </select>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="enlaceFechaEnvio">Fecha de envío</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="enlaceFechaEnvio" name="enlaceFechaEnvio">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label for="enlaceFechaEntrega">Entrega estimada</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="enlaceFechaEntrega" name="enlaceFechaEntrega">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label for="enlaceEstatus">Estatus</label>
                            <select id="enlaceEstatus" name="enlaceEstatus" class="form-select form-select-sm">
                                <?php foreach (ESTATUS_ENLACES as $e): ?>
                                    <option value="<?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="enlaceComentario">Comentario
                                <small class="text-muted" id="enlaceComentarioHint" style="display:none;">(obligatorio para este estatus)</small>
                            </label>
                            <textarea class="form-control form-control-sm" id="enlaceComentario" name="enlaceComentario" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnGuardarEnlace"><i class="fas fa-save"></i> Guardar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?php
// Estatus que exigen comentario, expuestos a JS para validar en cliente (fuente: config_entregas.php).
echo '<script>window.ESTATUS_REQUIERE_COMENTARIO = ' . json_encode(ESTATUS_REQUIERE_COMENTARIO) . ';</script>';
?>
