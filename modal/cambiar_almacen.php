			<!-- Modal -->
			<div class="modal fade bs-example-modal-lg" id="cambiar_almacen" tabindex="-1" role="dialog"
			    aria-labelledby="myModalLabel" aria-hidden="true">
			    <div class="modal-dialog  " role="document">
			        <div class="modal-content">
			            <div class="modal-header">
			                <h4 class="modal-title" id="myModalLabel">Cambiar de almacén</h4>
			            </div>
			            <div class="modal-body">
			                <form class="form-horizontal" method="post" id="formActualizarAlmacen" name="">
			                   <label for="mod_ubicacion" class="col-sm-6 control-label">Tu almacen actual es el: <?php echo $no_almacen ?></label>    
                            <div id="resultados_ajax3"></div>

			                    <div class="form-group">
			                        <label for="mod_ubicacion" class="col-sm-6 control-label">Cambiar a almacen:</label>
			                        <div class="col-sm-8">
			                    <!-- <input type="text" lass="form-control" id="id_ubicacion" name="mod_ubicacion" value="" required> -->
                                 <?php $listas=$pdo->query("SELECT * FROM almacenes");
                                  
                                        ?>
                                 <select name="id_almacen" id="" > 
                          <?php         
                        foreach ($listas as $lista) {
                        echo '<option value="' . htmlspecialchars($lista["id_almacen"]) . '">' . htmlspecialchars($lista["id_almacen"].'-'.htmlspecialchars($lista["nombre_almacen"])) . '</option>';
                            }
                            ?>  </select>
			                        </div>
			                        <input type="hidden" id="id_mod" name="mod_id" value="">
                                    <input type="hidden" id="" name="id_usuario" value="<?php echo $usuario;?>">
                                    
			                    </div>
			            </div>
			        <div class="modal-footer">
			            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" id="editar_ubicacion">Actualizar Almacén</button>

			        </div>
                    </form>
			    </div>
			</div>
			</div>