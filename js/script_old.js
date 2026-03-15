
let tipo = document.getElementById('tipo');
let operacion = document.querySelector('input[name="operacion"]:checked');
let input = document.getElementById('codigo');
let codigos = [];

document.querySelectorAll('input[name="operacion"]').forEach(el => {
  el.addEventListener('change', () => {
    operacion = document.querySelector('input[name="operacion"]:checked');
  });
});

input.addEventListener('keypress', function (e) {
  if (e.key === 'Enter') {
    const codigo = input.value.trim();
    input.value = "";

    if (tipo.value === "largo") {
      final_codigo=codigo.substring(0, 16);
      const cad = codigo.substring(18, 24);
      const lote = codigo.substring(26);
      console.log('codigo '+codigo+' lote '+lote+' caducidad '+ cad+' final_codigo '+final_codigo);
      enviar(codigo.substring(0, 16), lote, cad, '', operacion.value);
    } else {
      codigos.push(codigo);
      if (codigos.length === 2) {
        let codigo01 = codigos.find(c => c.startsWith("01"));
        let codigo17 = codigos.find(c => c.startsWith("17"));
        if (codigo01 && codigo17) {
          const cad = codigo17.substring(2, 8);
          const lote = codigo17.substring(10);
          enviar(codigo01.substring(2, 16), lote, cad, '', operacion.value);
        } else {
          document.getElementById("mensaje").innerText = "Error: códigos incorrectos.";
        }
        codigos = [];
      } else {
        document.getElementById("mensaje").classList.add("alert-warning");
        document.getElementById("mensaje").innerText = "Escanea el segundo código ";
        
        
      }
    }
  }
});

function enviar(codigo, lote, cad, referencia = '', operacion = 'sumar',final_codigo) {
  const cadYMD = "20" + cad.substring(0, 2) + "-" + cad.substring(2, 4) + "-" + cad.substring(4, 6);

  const data = `codigo=${codigo}&lote=${lote}&caducidad=${cadYMD}&referencia=${referencia}&operacion=${operacion}&final_codigo=${final_codigo}`;

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
          enviar(codigo, lote, cad, ref, operacion,final_codigo);
          document.getElementById("mensaje").classList.remove("alert-warning");
        } else {
          document.getElementById("mensaje").innerText = "Referencia no proporcionada.";
        }
      } else {
        document.getElementById("mensaje").classList.add("alert-success");
        document.getElementById("mensaje").innerText = data;
        cargarTabla();
      }
    });
}

function cargarTabla() {
  fetch("obtener_registros.php")
    .then(res => res.json())
    .then(data => {
      let tbody = document.querySelector("#tabla tbody");
      tbody.innerHTML = "";
      data.forEach(row => {
        tbody.innerHTML += `
          <tr>
            <td>${row.codigo}</td>
            <td>${row.lote}</td>
            <td>${row.caducidad}</td>
            <td>${row.referencia}</td>
            <td>${row.cantidad}</td>
            <td><a href="" onclick='eliminar(${row.id})'><i class="bi bi-trash"></i></a></td>
          </tr>
        `;
      });
    });
}
function eliminar(id) {
  if (confirm("¿Estás seguro de que deseas eliminar este registro?")) {
    fetch(`eliminar.php?id=${id}`, {
      method: "DELETE"
    })
      .then(res => res.text())
      .then(data => {
        document.getElementById("mensaje").innerText = data;
        cargarTabla();
      });
  }
}

window.onload = cargarTabla;
