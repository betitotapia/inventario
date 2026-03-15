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


      // Si ya tenemos el código 01, procesamos completo
      if (codigo01) {
        
          if (codigo.length >= 8) { // 17 + 6 dígitos fecha
            
        const char9 = codigo.charAt(8);
        const char10 = codigo.charAt(9);
        const combo = char9 + char10;
        if (combo === '10') {
              const fechaCaducidad = codigo.substring(2, 8);
              const lote = codigo.substring(10); // CORRECCIÓN: Cambiado de 10 a 8
              
              if (/^\d{6}$/.test(fechaCaducidad)) {
                  const caducidadFormateada = `20${fechaCaducidad.substring(0, 2)}-${fechaCaducidad.substring(2, 4)}-${fechaCaducidad.substring(4, 6)}`;
                  enviarDatos(codigo01, lote, caducidadFormateada, '', operacion);
                  resetearGS1();
              } else {
                  mostrarMensaje('Fecha inválida en código 17 (debe ser AAMMDD)', 'danger');
              }
          }else if (combo === '11') {
            console.log('combo 11');
              const fechaCaducidad = codigo.substring(2, 8);
              const lote = codigo.substring(17); // CORRECCIÓN: Cambiado de 10 a 8

              if (/^\d{6}$/.test(fechaCaducidad)) {
                  const caducidadFormateada = `20${fechaCaducidad.substring(0, 2)}-${fechaCaducidad.substring(2, 4)}-${fechaCaducidad.substring(4, 6)}`;
                  enviarDatos(codigo01, lote, caducidadFormateada, '', operacion);
                  resetearGS1();
              } else {
                  mostrarMensaje('Fecha inválida en código 17 (debe ser AAMMDD)', 'danger');
              }
          }
      }else{
            mostrarMensaje('Código 17 incompleto', 'danger');
          } 
          
      // Si no tenemos el código 01, lo guardamos y pedimos el 
  } else {
          codigo17 = codigo;
          mostrarMensaje('Primero escanea el código de producto (01...)', 'warning');
      }

}
  
    function resetearGS1() {
      codigo01 = null;
      codigo17 = null;
    }
  
    // ========== FUNCIONES PARA OTROS CÓDIGOS ========== //
  
    function procesarCodigoLargo(codigo, operacion) {

      try {

        const char17 = codigo.charAt(16);
        const char18 = codigo.charAt(17);
        const combo = char17 + char18;

        if (combo=== '17') {
                const codigoProducto = codigo.substring(0, 16);
                const cad = codigo.substring(18, 24);
                const lote = codigo.substring(26);
                const cadYMD = `20${cad.substring(0, 2)}-${cad.substring(2, 4)}-${cad.substring(4, 6)}`;
                enviarDatos(codigoProducto, lote, cadYMD, '', operacion);
        } else {
                const codigoProducto = codigo.substring(0, 16);
                const fabricacion= codigo.substring(18, 24);
                const cad = codigo.substring(26, 32);
                const lote = codigo.substring(34);
                const cadYMD = `20${cad.substring(0, 2)}-${cad.substring(2, 4)}-${cad.substring(4, 6)}`;
                console.log(codigoProducto, lote, cadYMD, '', operacion);
                enviarDatos(codigoProducto, lote, cadYMD, '', operacion);
        }
      } catch (e) {
        mostrarMensaje('Error en formato de código largo', 'danger');
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
               
              </tr>
            `;
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
  
          document.querySelectorAll(".eliminar").forEach(btn => {
            btn.addEventListener("click", function() {
              if (confirm("¿Eliminar este producto?")) {
                fetch(`eliminar_producto.php?id=${this.dataset.id}`, {
                  method: "DELETE"
                }).then(() => cargarTabla());
              }
            });
          });
        });
    }
  
    // Cargar tabla al inicio
    cargarTabla();
  };