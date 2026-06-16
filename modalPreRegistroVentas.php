<!-- Modal de Pre-registro de servicio (Departamento de Ventas) -->
<!-- Se carga vía AJAX (.load) en #contenedorModalVentas al hacer click en un espacio vacío del calendario -->
<div class="modal fade" id="modalPreRegistroVentas" tabindex="-1" aria-labelledby="modalPreRegistroVentasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalPreRegistroVentasLabel">
                    <i class="fas fa-store"></i> Pre-registro de Servicio (Ventas)
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-2" role="alert" style="font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Este pre-registro quedar&aacute; pendiente hasta que el Jefe de Laboratorio lo apruebe y complete los datos.
                </div>
                <form id="formPreRegistroVentas" name="formPreRegistroVentas">
                    <div class="row">
                        <div class="col-sm-6 mb-2">
                            <label for="regCliente">Cliente</label>
                            <input type="text" class="form-control form-control-sm" oninput="convertirTexto(this)" id="regCliente" name="regCliente" placeholder="Cliente">
                        </div>
                        <div class="col-sm-6 mb-2">
                            <label for="regCiudad">Ciudad</label>
                            <select id="regCiudad" name="regCiudad" class="form-select" style="width:100%;">
                                <option value="">Selecciona...</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 mb-2">
                            <label for="regArea">&Aacute;rea</label>
                            <select name="regArea" id="regArea" class="form-select">
                                <option value="">Selecciona...</option>
                                <option value="ALTA EXACITUD">Servicios Alta Exactitud</option>
                                <option value="CALIBRACIONES">Servicios Calibraciones</option>
                                <option value="DIMENSIONAL">Servicios Dimensional</option>
                                <option value="SFG">Servicios SFG</option>
                                <option value="MITUTOYO">Servicios Mitutoyo</option>
                                <option value="DUREZA">Servicios Dureza</option>
                                <option value="MANTENIMIENTO">Servicios Mantenimiento</option>
                                <option value="ELECTRICA">Servicios El&eacute;ctrica</option>
                                <option value="TEMPERATURA">Servicios Temperatura</option>
                                <option value="PRESION">Servicios Presi&oacute;n</option>
                                <option value="APLICACIONES">Servicios APP Aplicaciones</option>
                                <option value="MT">Servicios MT</option>
                                <option value="MTS">Servicios MTS</option>
                                <option value="ZEISS">Servicios Zeiss</option>
                                <option value="MASA">Servicios Masa</option>
                                <option value="FUERZA">Servicios Fuerza</option>
                                <option value="PAR TORSIONAL">Servicios Par Torsional</option>
                            </select>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label for="regOT">OV / OT <small class="text-muted">(opcional)</small></label>
                            <input type="text" class="form-control form-control-sm" id="regOT" name="regOT" oninput="convertirTexto(this)" placeholder="Ej. MESS-OV-1234-2025">
                        </div>
                        <div class="col-sm-4 mb-2">
                            <label for="regFecha">Fecha planeada</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="regFecha" name="regFecha">
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
                <button type="button" class="btn btn-success" id="btnPreRegistroVentas" onclick="registrarPreRegistroVentas()">Registrar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
