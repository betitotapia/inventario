

window.onload = () => {
    // Variables de estado
    let operacion = 'sumar';
    let codigo01 = null;
    let codigo17 = null;
    const input = document.getElementById('codigo');
    const mensajeElement = document.getElementById('mensaje');
    const modoElement = document.getElementById('modo');
  
    // Configurar operación predeterminada
    document.querySelector('input[value="sumar"]').checked = true;
    document.getElementById('modo_operacion').textContent = 'Sumando';
    document.getElementById('operacion_modo').style.backgroundColor = 'green';
    
    // Manejar cambios entre sumar/restar

    document.querySelectorAll('input[name="operacion"]').forEach(el => {
        el.addEventListener('change', () => {
          operacion = document.querySelector('input[name="operacion"]:checked').value;
          switch (operacion) {
            case 'sumar':
              tipo_operacion = 'success';
              document.getElementById('modo_operacion').textContent = 'Estas Sumando';
              document.getElementById('operacion_modo').style.backgroundColor = 'green';
              break;
            case 'restar':
              tipo_operacion = 'danger';
              document.getElementById('modo_operacion').textContent = 'Estas Restando';
              document.getElementById('operacion_modo').style.backgroundColor = 'red';
              break;
            case 'ajuste':
              tipo_operacion = 'warning';
              document.getElementById('modo_operacion').textContent = 'Estas Ajustando';
              document.getElementById('operacion_modo').style.backgroundColor = '#ffc107';
              break;
          }
          mostrarMensaje(`Modo: ${operacion.toUpperCase()}`, tipo_operacion); 
        });
      });
      
    // Evento principal para escanear códigos
    input.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        const codigo = input.value.trim();
        input.value = '';
        limpiarMensaje();
  
        // 1. Código largo (≥30 caracteres)
        if (codigo.length >= 30 && codigo.startsWith("01")) {
          procesarCodigoLargo(codigo, operacion);
        }
        // 2. Código que comienza con 01 (GTIN)
        else if (codigo.startsWith("01")) {
          procesarCodigo01(codigo);
        }
        // 3. Código que comienza con 17 (Caducidad/Lote)
        else if (codigo.startsWith("17")) {
          procesarCodigo17(codigo, operacion);
        }
        //4. Código que comienza con 113 (Lote)

        else if (codigo.startsWith("113")) {
          procesarCodigoSumed(codigo, operacion);
        } 
        // 5. Código simple
        else {
          procesarCodigoSimple(codigo, operacion);
          
        }
      }
    });
  
    //=============FUNCION EXTRAER LOTES==================//

    function extraerLoteDesde(codigo, index10) {
  const posiblesAIs = [
    '00','01','02','10','11','12','13','14','15','16','17','20','21','22','23','30',
    '31','32','33','34','35','36','37','90','91','92','93','94','95','96','97','98','99'
  ];
  const inicioLote = index10 + 2;
  const data = codigo.substring(inicioLote);

  for (let i = 1; i < data.length - 1; i++) {
    const posibleAI = data.substring(i, i + 2);
    const hayMas = (i + 2) < data.length;

    if (posiblesAIs.includes(posibleAI) && hayMas) {
      return data.substring(0, i); // Corta antes del nuevo AI
    }
  }

  return data; // No hay otro AI válido → todo es lote
}

    // ========== FUNCIONES PARA CÓDIGOS GS1 ========== //
  
    function procesarCodigo01(codigo) {
      if (codigo.length >= 16) { // 01 + 14 dígitos GTIN
        codigo01 = codigo.substring(2, 16); // Extraemos solo el GTIN
        
        if (/^\d{14}$/.test(codigo01)) {
          mostrarMensaje('Ahora escanea el código de caducidad (17...)', 'info');
        } else {
          mostrarMensaje('GTIN inválido: debe tener 14 dígitos', 'danger');
          resetearGS1();
        }
      } else {
        mostrarMensaje('Código 01 incompleto (necesita 16 caracteres)', 'danger');
      }
    }
  function procesarCodigo17(codigo, operacion) {
  if (!codigo01) {
    codigo17 = codigo;
    mostrarMensaje('Primero escanea el código de producto (01...)', 'warning');
    return;
  }

  try {
    // 1. Fecha caducidad
    const fechaCadRaw = codigo.substring(2, 8);
    if (!/^\d{6}$/.test(fechaCadRaw)) {
      mostrarMensaje('Fecha inválida en código 17 (debe ser AAMMDD)', 'danger');
      return;
    }

    const cadYMD = `20${fechaCadRaw.substring(0, 2)}-${fechaCadRaw.substring(2, 4)}-${fechaCadRaw.substring(4, 6)}`;

    // 2. Buscar AI 10
    const index10 = codigo.indexOf("10", 8);
    if (index10 === -1) {
      mostrarMensaje('No se encontró el AI 10 (Lote)', 'danger');
      return;
    }

    // 3. Si el código es corto, tomar todo lo que sigue como lote
    let lote;
    const umbralCorte = 28;

    if (codigo.length <= umbralCorte) {
      // Código corto → tomar todo el lote sin cortar
      lote = codigo.substring(index10 + 2);
    } else {
      // Código largo → aplicar lógica de corte
      lote = extraerLoteDesde(codigo, index10);
    }

    if (!lote || lote.length < 1) {
      mostrarMensaje('Lote no válido', 'danger');
      return;
    }

    enviarDatos(codigo01, lote, cadYMD, '', operacion);
    resetearGS1();

  } catch (e) {
    console.error(e);
    mostrarMensaje('Error al procesar código 17', 'danger');
  }
}

  
    function resetearGS1() {
      codigo01 = null;
      codigo17 = null;
    }
  
    // ========== FUNCIONES PARA OTROS CÓDIGOS ========== //
   function procesarCodigoLargo(codigo, operacion) {
  try {
    const index01 = codigo.indexOf("01");
    const gtin = codigo.substring(index01 + 2, index01 + 16);

    if (!/^\d{14}$/.test(gtin)) {
      mostrarMensaje('GTIN inválido', 'danger');
      return;
    }

    const index17 = codigo.indexOf("17", index01 + 16);
    const fechaCadRaw = codigo.substring(index17 + 2, index17 + 8);

    if (!/^\d{6}$/.test(fechaCadRaw)) {
      mostrarMensaje('Fecha de caducidad inválida', 'danger');
      return;
    }

    const cadYMD = `20${fechaCadRaw.substring(0, 2)}-${fechaCadRaw.substring(2, 4)}-${fechaCadRaw.substring(4, 6)}`;

    const index10 = codigo.indexOf("10", index17 + 8);
    if (index10 === -1) {
      mostrarMensaje('No se encontró el AI 10 (Lote)', 'danger');
      return;
    }

    const lote = extraerLoteDesde(codigo, index10);

    if (!lote || lote.length < 1) {
      mostrarMensaje('Lote no válido', 'danger');
      return;
    }

    enviarDatos(gtin, lote, cadYMD, '', operacion);

  } catch (e) {
    console.error(e);
    mostrarMensaje('Error al procesar código largo', 'danger');
  }
}


    function procesarCodigoSumed(codigo, operacion) {
     try {
        const codigoProducto = codigo.substring(0, 19);
        const cad = codigo.substring(21,27);
        const lote = codigo.substring(29);
        const cadYMD = `20${cad.substring(0, 2)}-${cad.substring(2, 4)}-${cad.substring(4, 6)}`;
      console.log(codigoProducto, lote, cadYMD, '', operacion);
        enviarDatos(codigoProducto, lote, cadYMD, '', operacion);
      } catch (e) {
        mostrarMensaje('Error en formato de código sumed', 'danger');
      }
    }

    function procesarCodigoSimple(codigo, operacion) {
      const codigoLimpio = codigo.replace(/\D/g, '').substring(0, 16);
      
      if (!codigoLimpio) {
        mostrarMensaje('Código no válido', 'danger');
        return;
      }
  
      fetch(`verificar_codigo.php?codigo=${codigoLimpio}`)
          .then(res => res.text())
          .then(resp => {
              const lote = prompt("Introduce el lote:");
              if (!lote) {
                  mostrarMensaje('Operación cancelada: lote requerido', 'warning');
                  return;
              }

              const cad = prompt("Introduce la caducidad (yyyy-mm-dd):");
              if (!cad || !/^\d{4}-\d{2}-\d{2}$/.test(cad)) {
                  mostrarMensaje('Caducidad inválida', 'danger');
                  return;
              }

              enviarDatos(codigoLimpio, lote, cad, '', operacion);
          })

        }

  
    function procesarCodigoSimple(codigo, operacion) {
      const codigoLimpio = codigo.replace(/\D/g, '').substring(0, 16);
      
      if (!codigoLimpio) {
          mostrarMensaje('Código no válido', 'danger');
          return;
      }
  
      fetch(`verificar_codigo.php?codigo=${codigoLimpio}`)
          .then(res => res.text())
          .then(resp => {
              const lote = prompt("Introduce el lote:");
              if (!lote) {
                  mostrarMensaje('Operación cancelada: lote requerido', 'warning');
                  return;
              }
  
              const cad = prompt("Introduce la caducidad (yyyy-mm-dd):");
              if (!cad || !/^\d{4}-\d{2}-\d{2}$/.test(cad)) {
                  mostrarMensaje('Formato de fecha inválido (usar yyyy-mm-dd)', 'danger');
                  return;
              }
  
              if (resp === "existe") {
                  enviarDatos(codigoLimpio, lote, cad, '', operacion);
              } else {
                  let ref;
                  do {
                      ref = prompt("Introduce la referencia (requerida para productos nuevos):");
                      if (ref === null) {
                          mostrarMensaje('Operación cancelada: referencia requerida', 'warning');
                          return;
                      }
                  } while (!ref);
                  
                  enviarDatos(codigoLimpio, lote, cad, ref, operacion);
              }
          })
          .catch(err => {
              mostrarMensaje('Error al verificar código', 'danger');
          });
  }
  
    // ========== FUNCIONES AUXILIARES ========== //
    function enviarDatos(codigo, lote, caducidad, referencia, operacion) {
     // const cadYMD = "20" + caducidad.substring(0, 2) + "-" + caducidad.substring(2, 4) + "-" + caducidad.substring(4, 6);
      
      const data = new URLSearchParams();
      data.append('codigo', codigo);
      data.append('lote', lote);
      data.append('caducidad', caducidad);
      data.append('referencia', referencia);
      data.append('operacion', operacion);
  
      if (operacion === 'ajuste') {
        
          const cantidad = getCantidadAjuste();
          if (cantidad === null) {
              mostrarMensaje('Operación cancelada', 'warning');
              return;

          }
          data.append('cantidad', cantidad);
      }
  
      fetch("procesar.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: data
      })
      .then(res => res.text())
      .then(data => {
          if (data === "NECESITA_REFERENCIA") {
              const ref = prompt("Código nuevo. Introduce la referencia:");
              if (ref) {
                  enviarDatos(codigo, lote, caducidad, ref, operacion);
              } else {
                  mostrarMensaje('Referencia no proporcionada', 'warning');
              }
          } else {
              mostrarMensaje(data, 'success');
              cargarTabla();
          }
      })
      .catch(err => {
          mostrarMensaje('Error al procesar el código', 'danger');
      });
  }

    function mostrarMensaje(texto, tipo = 'info') {
      mensajeElement.textContent = texto;
      mensajeElement.className = `alert alert-${tipo}`;
      mensajeElement.style.display = 'block';
      setTimeout(() => {
        if (mensajeElement.textContent === texto) {
          mensajeElement.style.display = 'none';
        }
      }, 5000);
    }
  
    function limpiarMensaje() {
      mensajeElement.textContent = '';
      mensajeElement.className = 'alert';
    }


    function getCantidadAjuste() {
        let cantidad;
        do {
          cantidad = prompt("Ingrese la cantidad para ajustar (ej: +5 para sumar, -3 para restar):");
          
          if (cantidad === null) return null; // Usuario canceló
          
          if (!/^[+-]?\d+$/.test(cantidad)) {
            alert("Por favor ingrese un número entero válido con + o - (ej: +5, -2)");
            cantidad = undefined;
          }
        } while (cantidad === undefined);
        
        return parseInt(cantidad);
      }
  
    function cargarTabla() {
      console.log("Cargando tabla...");
      fetch("ajax/obtener_registros.php")
        .then(res => res.json())
        .then(data => {
          const tabla = document.querySelector("#tabla");
          
          if (window.dataTable) {
            window.dataTable.destroy();
            tabla.innerHTML = `
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Referencia</th>
                  <th>Lote</th>
                  <th>Caducidad</th>
                  <th>Cantidad</th>
                  <th>Almacen</th>
                  <th>Ubicacion</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            `;
          }
  
        const tbody = tabla.querySelector("tbody");
          data.forEach(row => {
            const fechaCaducidad = new Date(row.caducidad);
            const hoy = new Date();

            
            // Normalizamos ambas fechas para evitar problemas de horas
            fechaCaducidad.setHours(0, 0, 0, 0);
            hoy.setHours(0, 0, 0, 0);

         let bgColor;

      if (fechaCaducidad <= hoy) {
        bgColor = 'red'; // Ya caducó
      } else {
        // Calculamos diferencia en meses
        let anios = fechaCaducidad.getFullYear() - hoy.getFullYear();
        let meses = fechaCaducidad.getMonth() - hoy.getMonth();
        let mesesDiferencia = (anios * 12) + meses;

        if (mesesDiferencia <= 6) {
            bgColor = 'orange'; // Menos de 6 meses
        } else if (mesesDiferencia <= 12) {
            bgColor = 'yellow'; // Entre 6 y 12 meses
        } else {
            bgColor = 'green'; // Más de 1 año
        }
    
            tbody.innerHTML += `
              <tr>
                <td>${row.codigo}</td>
                <td>${row.referencia}</td>
                <td>${row.lote}</td>
                <td style='background-color:${bgColor}'>${row.caducidad}</td>
                <td>${row.cantidad}</td>
                <td>${row.almacen}</td>
                <td>${row.ubicacion}</td>
                <td>
                  <button class="btn btn-danger editar" data-id="${row.id}" >Editar Ubicación</button>
               </td>
              </tr>
            `; 
          }

          });

      
          window.dataTable = new DataTable(tabla, {
            perPage: 10,
            searchable: true,
            labels: {
              placeholder: "Buscar...",
              perPage: "{select} registros por página",
              noRows: "No se encontraron registros"
            }
          });
          // Agregar evento de clic a los botones de edición
          const editarButtons = document.querySelectorAll(".editar");
          editarButtons.forEach(button => {
            button.addEventListener("click", function() {
              const id = this.getAttribute("data-id");
              editarRegistro(id);
              ;
              })
            });
        
    });
              function editarRegistro(id) {
                console.log("ID del registro a editar:", id);
                fetch(`ajax/obtener_ubicacion.php?id=${id}`)
                .then(res => res.json())
                .then(registro => {
                    if (registro) {
                      console.log("Registro encontrado:", registro);
                      const { ubicacion } = registro; 

                      const nuevaUbicacion = prompt("Ingrese la ubicacion", ubicacion);
                      if (nuevaUbicacion!== null) {
                        enviarDatosUbicacion( nuevaUbicacion, id);
                      } 
              }
       });
      }
      function enviarDatosUbicacion(ubicacion, id) {
            const data = new URLSearchParams();
            data.append('ubicacion', ubicacion);
            data.append('id', id);
        console.log("Datos a enviar:", ubicacion)  ;
            fetch("ajax/actualizar_ubicacion.php", {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: data
            })
            .then(res => res.text())
            .then(data => {
              mostrarMensaje(data, 'success');
              console.log("Ubicación actualizada:", data);
              cargarTabla();
            })
            .catch(err => {
              mostrarMensaje('Error al actualizar ubicación', 'danger');
            });
          }
    
          

    }

  cargarTabla();
  mostrarMensaje('Escanea un código para comenzar', 'info');
  // ========== EVENTOS ========== //
  }

  document.getElementById('formActualizarAlmacen').addEventListener('submit', function(e) {
    e.preventDefault(); // Evita el envío tradicional

    const formData = new FormData(this);

    fetch('ajax/actualizar_almacen.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('resultados_ajax3').innerHTML = data;
        alert('✅ Almacén actualizado correctamente, ¡POR FAVOR ACTUALIZA LA PAGINA!');
        
let modalElement = document.getElementById('cambiar_almacen');
let modal = bootstrap.Modal.getInstance(modalElement);
modal.hide();

        cargarTabla();
        // Opcional: cerrar el modal, limpiar el formulario, etc.
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error al actualizar el almacén.')
        document.getElementById('resultados_ajax3').innerHTML = 'Error al actualizar.';
    });
});

function cargarTabla() {
      console.log("Cargando tabla...");
      fetch("ajax/obtener_registros.php")
        .then(res => res.json())
        .then(data => {
          const tabla = document.querySelector("#tabla");
          
          if (window.dataTable) {
            window.dataTable.destroy();
            tabla.innerHTML = `
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Referencia</th>
                  <th>Lote</th>
                  <th>Caducidad</th>
                  <th>Cantidad</th>
                  <th>Almacen</th>
                  <th>Ubicacion</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody></tbody>
            `;
          }
  
        const tbody = tabla.querySelector("tbody");
          data.forEach(row => {
            const fechaCaducidad = new Date(row.caducidad);
            const hoy = new Date();

            
            // Normalizamos ambas fechas para evitar problemas de horas
            fechaCaducidad.setHours(0, 0, 0, 0);
            hoy.setHours(0, 0, 0, 0);

         let bgColor;

      if (fechaCaducidad <= hoy) {
        bgColor = 'red'; // Ya caducó
      } else {
        // Calculamos diferencia en meses
        let anios = fechaCaducidad.getFullYear() - hoy.getFullYear();
        let meses = fechaCaducidad.getMonth() - hoy.getMonth();
        let mesesDiferencia = (anios * 12) + meses;

        if (mesesDiferencia <= 6) {
            bgColor = 'orange'; // Menos de 6 meses
        } else if (mesesDiferencia <= 12) {
            bgColor = 'yellow'; // Entre 6 y 12 meses
        } else {
            bgColor = 'green'; // Más de 1 año
        }
    
            tbody.innerHTML += `
              <tr>
                <td>${row.codigo}</td>
                <td>${row.referencia}</td>
                <td>${row.lote}</td>
                <td style='background-color:${bgColor}'>${row.caducidad}</td>
                <td>${row.cantidad}</td>
                <td>${row.almacen}</td>
                <td>${row.ubicacion}</td>
                <td>
                  <button class="btn btn-danger editar" data-id="${row.id}" >Editar Ubicación</button>
               </td>
              </tr>
            `; 
          }

          });

      
          window.dataTable = new DataTable(tabla, {
            perPage: 10,
            searchable: true,
            labels: {
              placeholder: "Buscar...",
              perPage: "{select} registros por página",
              noRows: "No se encontraron registros"
            }
          });
          // Agregar evento de clic a los botones de edición
          const editarButtons = document.querySelectorAll(".editar");
          editarButtons.forEach(button => {
            button.addEventListener("click", function() {
              const id = this.getAttribute("data-id");
              editarRegistro(id);
              ;
              })
            });
        
    });
              function editarRegistro(id) {
                console.log("ID del registro a editar:", id);
                fetch(`ajax/obtener_ubicacion.php?id=${id}`)
                .then(res => res.json())
                .then(registro => {
                    if (registro) {
                      console.log("Registro encontrado:", registro);
                      const { ubicacion } = registro; 

                      const nuevaUbicacion = prompt("Ingrese la ubicacion", ubicacion);
                      if (nuevaUbicacion!== null) {
                        enviarDatosUbicacion( nuevaUbicacion, id);
                      } 
              }
       });
      }
      function enviarDatosUbicacion(ubicacion, id) {
            const data = new URLSearchParams();
            data.append('ubicacion', ubicacion);
            data.append('id', id);
        console.log("Datos a enviar:", ubicacion)  ;
            fetch("ajax/actualizar_ubicacion.php", {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: data
            })
            .then(res => res.text())
            .then(data => {
              mostrarMensaje(data, 'success');
              console.log("Ubicación actualizada:", data);
              cargarTabla();
            })
            .catch(err => {
              mostrarMensaje('Error al actualizar ubicación', 'danger');
            });
          }
    
          

    }
