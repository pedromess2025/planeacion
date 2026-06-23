<div class="modal fade" id="modalPreRegistroVentas" tabindex="-1" aria-labelledby="modalPreRegistroVentasLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalPreRegistroVentasLabel">
                    <i class="fas fa-store"></i> Pre-registro de Servicio
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2 mb-2" role="alert" style="font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Este pre-registro quedar&aacute; pendiente hasta que el Jefe de Laboratorio lo apruebe y complete los datos.
                </div>
                <div class="alert alert-light py-2 mb-3 border" style="font-size: 13px;">
                    <i class="fas fa-user"></i> <b id="infoIngNombre"></b> &nbsp;|&nbsp;
                    <i class="fas fa-building"></i> <b id="infoArea"></b>
                </div>
                <form id="formPreRegistroVentas" name="formPreRegistroVentas">
                    <input type="hidden" id="regId" name="regId" value="">
                    <input type="hidden" id="regArea" name="regArea" value="">
                    <input type="hidden" id="regFechaBase" value="">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label for="regCliente">Cliente</label>
                            <input type="text" class="form-control form-control-sm" oninput="convertirTexto(this)" id="regCliente" name="regCliente" placeholder="Cliente">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="regCiudad">Ciudad</label>
                            <select id="regCiudad" name="regCiudad" class="form-select form-select-sm" style="width:100%;">
                                <option value="">Selecciona...</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label for="regOV">OV</label>
                            <input type="text" class="form-control form-control-sm" id="regOV" name="regOV" placeholder="OV (opcional)">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="regFecha">Fecha</label>
                            <input type="text" class="form-control form-control-sm" id="regFecha" name="regFecha" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 mb-2">
                            <label for="regHora">Hora</label>
                            <input type="time" class="form-control form-control-sm" id="regHora" name="regHora" value="08:00">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 mb-2">
                            <label for="regComentarios">Comentario <small class="text-muted">(opcional)</small></label>
                            <textarea name="regComentarios" id="regComentarios" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="btnPreRegistroVentas">Registrar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
