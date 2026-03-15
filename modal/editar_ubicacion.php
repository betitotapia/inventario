			<!-- Modal -->
			<div class="modal fade bs-example-modal-lg" id="editar_ubicacion" tabindex="-1" role="dialog"
			    aria-labelledby="myModalLabel" aria-hidden="true">
			    <div class="modal-dialog  " role="document">
			        <div class="modal-content">
			            <div class="modal-header">
			                <h4 class="modal-title" id="myModalLabel">Editar Ubicacion</h4>
			            </div>
			            <div class="modal-body">
			                <form class="form-horizontal" method="post" id="editar_ubicacion_producto" name="actualizar_ubicacion">
			                    <div id="resultados_ajax3"></div>

			                    <div class="form-group">
			                        <label for="mod_ubicacion" class="col-sm-3 control-label">Ubicación</label>
			                        <div class="col-sm-8">
			                    <input type="text" class="form-control" id="id_ubicacion" name="mod_ubicacion" value="" required>
			                        </div>
			                        <input type="hidden" id="id_mod" name="mod_id" value="">
			                    </div>
			            </div>
			        <div class="modal-footer">
			            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="editar_ubicacion">Actualizar Ubicación</button>

			        </div>
                    </form>
			    </div>
			</div>
			</div>