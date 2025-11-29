// FrameWork Ver. 1.0.
// Rutinas JavaScript Generales del 30 Noviembre 2023
// <script src="../web_framework/web_script/rutinas_javascript.js"></script>
    console.log("Archivo JavaScript cargado correctamente");

    const regex = /^[VEJGP]-\d{6,8}$/;

    // Función para completar con ceros a la izquierda
    function formatCedula(cedula) {
        const match = cedula.match(regex);
        if (match) {
            const parts = match[0].split('-');
            const number = parts[1].padStart(8, '0');
            return `${parts[0]}-${number}`;
        }
        return null;
    }

    // Ejemplo de uso
    // const cedulaEjemplo = "V-12345";
    // const cedulaFormateada = formatCedula(cedulaEjemplo);
    //console.log(cedulaFormateada); // Salida: V-00012345

    // Valida el contenido y si el primer carácter no es válido lo borra
    function validarYConvertirPrimerCaracter(event) {
        const input = event.target.value;
        const primerCaracter = input.charAt(0).toUpperCase();
        const segundoCaracter = input.charAt(1);
        const restoCadena = input.slice(2);
        const letrasPermitidas = ['V', 'E', 'J', 'G', 'P'];
    
        if (primerCaracter && letrasPermitidas.includes(primerCaracter)) {
            if (input.length >= 2) {
                if (segundoCaracter === '-') {
                    if (/^\d*$/.test(restoCadena)) {
                        if (restoCadena.length === 0 || (restoCadena.length <= 8)) {
                            event.target.value = primerCaracter + '-' + restoCadena;
                        } else {
                            alert("El resto de la cadena debe contener entre 6 y 8 dígitos.");
                            event.target.value = primerCaracter + '-';
                        }
                    } else {
                        alert("El resto de la cadena debe contener solo dígitos.");
                        event.target.value = primerCaracter + '-';
                    }
                } else if (segundoCaracter !== undefined) {
                    alert("El segundo carácter debe ser un guión.");
                    event.target.value = primerCaracter + '-';
                }
            } else {
                event.target.value = primerCaracter;
            }
        } else {
            alert("El primer carácter debe ser una de las siguientes letras: V, E, J, G o P.");
            event.target.value = '';
        }
    }
                                    
    // Valida el contenido y si el primer carácter no es válido lo borra
    function validarCedula(event) {
        const input = event.target.value;
        const primerCaracter = input.charAt(0).toUpperCase();
        const segundoCaracter = input.charAt(1);
        const restoCadena = input.slice(2);
        const letrasPermitidas = ['V', 'E', 'P'];
    
        if (primerCaracter && letrasPermitidas.includes(primerCaracter)) {
            if (input.length >= 2) {
                if (segundoCaracter === '-') {
                    if (/^\d*$/.test(restoCadena)) {
                        if (restoCadena.length === 0 || (restoCadena.length <= 8)) {
                            event.target.value = primerCaracter + '-' + restoCadena;
                        } else {
                            alert("El resto de la cadena debe contener entre 6 y 8 dígitos.");
                            event.target.value = primerCaracter + '-';
                        }
                    } else {
                        alert("El resto de la cadena debe contener solo dígitos.");
                        event.target.value = primerCaracter + '-';
                    }
                } else if (segundoCaracter !== undefined) {
                    alert("El segundo carácter debe ser un guión.");
                    event.target.value = primerCaracter + '-';
                }
            } else {
                event.target.value = primerCaracter;
            }
        } else {
            alert("El primer carácter debe ser una de las siguientes letras: V, E o P.");
            event.target.value = '';
        }
    }

    // Convertimos todo el RIF a mayúsculas para asegurar una comparación consistente
    // Expresión regular para validar el formato del RIF en mayúsculas
    // Función para validar el RIF en tiempo real
    function validarRIF(event) {
        //const rifInput = document.getElementById('rifInput');
        const rifInput = event.target.value;
        const rif = rifInput.value.toUpperCase(); // Convertir a mayúsculas automáticamente
        const regex = /^[VEJPG]{1}-\d([0-9]{6,8})$/; // Patrón para RIF completo

        // Validar el RIF completo
        if (regex.test(rif)) {
            document.getElementById('resultado').textContent = "RIF válido";
            document.getElementById('resultado').style.color = "green";
        } else {
            document.getElementById('resultado').textContent = "RIF inválido";
            document.getElementById('resultado').style.color = "red";
        }
    }

    // Función para validar el RIF al presionar un botón o enviar el formulario
    function validarRIFFormulario() {
        const rifInput = document.getElementById('rifInput');
        const rif = rifInput.value.toUpperCase(); // Asegurarse de que el RIF esté en mayúsculas
        const regex = /^[VEJPG]-\d{6,8}$/;

        if (regex.test(rif)) {
            return true; // El formulario es válido
        } else {
            alert("RIF inválido. Por favor, revisa el formato.");
            return false; // Detener el envío del formulario
        }
    }
  
  // Validar los Select de los Formularios
  // <form onsubmit="return validarSelects()">
  // <option value="">Seleccione...</option>
  function validarSelects() {
      var selects = document.getElementsByTagName('select');
      var i;
      for (i = 0; i < selects.length; i++) {
          if (selects[i].value === "") {
              alert("Sin " + (i + 1) + " Seleccionar...");
              return false;
          }
      }
      return true;
  };
  
  // Elimina los Espacios en Blanco Continuos, al Principio y al Final También.
  // onchange="valideKeyRecortar(this.id);"
  function valideKeyRecortar(id) {
      var inpt = document.getElementById(id);
      inpt.value = inpt.value.replace(/\s+/g, ' ').trim();
  };
  
  // Convierte las Letras a Mayúsculas Mientras se Escribe
  // onkeyup="valideKeyMayusculas(this.id);"
  function valideKeyMayusculas(id) {
      var inpt = document.getElementById(id);
      inpt.value = inpt.value.toUpperCase();
  };
  
  // Convierte las Letras a Minúsculas Mientras se Escribe
  // onkeyup="valideKeyMinusculas(this.id);"
  function valideKeyMinusculas(id) {
      var inpt = document.getElementById(id);
      inpt.value = inpt.value.toLowerCase();
  };
  
  // Solo Números del Rango de 0 a 7
  // onblur="return validarRango0a7(this);" maxlength="1"
  function validarRango0a7(elementoDias) {
      var numeroDias = parseInt(elementoDias.value, 8);
      // Solo Números
      if (isNaN(numeroDias)) {
          alert('Solo Números del Rango de 0 a 7');
          elementoDias.focus();
          elementoDias.select();
      }
  
      // El Rango de 0 a 7
      if (numeroDias < 0 || numeroDias > 7) {
          elementoDias.focus();
          elementoDias.select();
          return false;
      }
      return true;
  };
  
  // Solo Números del Rango de 0 a 24
  // onblur="return validarRango0a24(this);" maxlength="2"
  function validarRango0a24(elementoHoras) {
      var numeroHoras = parseInt(elementoHoras.value, 25);
      // Solo Números
      if (isNaN(numeroHoras)) {
          alert('Solo Números del Rango de 0 a 24');
          elementoHoras.focus();
          elementoHoras.select();
      }
  
      // El Rango de 0 a 24
      if (numeroHoras < 0 || numeroHoras > 24) {
          elementoHoras.focus();
          elementoHoras.select();
          return false;
      }
      return true;
  };
  
  // Solo Retroceso, Espacio, Letras en Mayúsculas y Minúsculas
  // onkeypress="return valideKeyLetras(event)"
  function valideKeyLetras(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 32) { // Espacio
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras Minúsculas
          return true;
      } else {
          return false;
      }
  };
  
  // Para los Input de Campo de Persona o Empresa
  // onkeypress="return valideKeyCampo(event)"
  function valideKeyCampo(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 32) { // Espacio
          return true;
      } else if (code == 45) { // Guion
          return true;
      } else if (code == 46) { // Punto
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras Minúsculas
          return true;
      } else {
          return false;
      }
  };
  
  // Para Todos Campos Texto.
  // onkeypress="return valideKeyText(event)"
  function valideKeyText(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 32) { // Espacio
          return true;
      } else if (code >= 35 && code <= 38) { // Signo Numeral, Signo Dinero, Signo Porcentaje, Ampersand
          return true;
      } else if (code >= 40 && code <= 47) { // Abre Paréntesis, Cierra Paréntesis, Asterisco, Signo Más, Coma, Signo Menos, Punto, Barra Inclinada
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code == 58) { // Dos Puntos
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras Minúsculas
          return true;
      } else {
          return false;
      }
  };
  
  // Para los Input de Nombre de Persona o Empresa
  // onkeypress="return valideKeyNombre(event)"
  function valideKeyNombre(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 32) { // Espacio
          return true;
      } else if (code == 38) { // Ampersand &
          return true;
      } else if (code == 44) { // Coma
          return true;
      } else if (code == 45) { // Guion
          return true;
      } else if (code == 46) { // Punto
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras Minúsculas
          return true;
      } else {
          return false;
      }
  };
  
  // Para los Input de los Data List.
  // onkeypress="return valideDataList(event);"
  function valideDataList(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 32) { // Espacio
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras Minúsculas
          return true;
      } else {
          return false;
      }
  };
  
  //Solo Retroceso, y Números del 0 al 9
  // onkeypress="return valideKeyFecha(event)"
  function valideKeyFecha(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else {
          return false;
      }
  };
  
  // Solo Retroceso, Guion, Números del 0 al 9 y Letras.
  // onkeypress="return valideKeyPasaporte(event)"
  function valideKeyPasaporte(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras Minúsculas
          return true;
      } else {
          return false;
      }
  };
  
  // Solo Retroceso, Espaciador, Números del 0 al 9 y Letras (Minúsculas/Mayúsculas).
  // onkeypress="return valideKeyBuscar(event)"
  function valideKeyBuscar(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 32) { // Espaciador
          return true;
      } else if (code == 45) { // Guion
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras en Minúscula
          return true;
      } else {
          return false;
      }
  };
  
  //Solo Retroceso, Guion y Números del 0 al 9
  // onkeypress="return valideKeyNumCuenta(event)"
  function valideKeyNumCuenta(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 45) { // Guion
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else {
          return false;
      }
  };
  
  //Solo Retroceso, y Números del 0 al 9
  // onkeypress="return valideKeyNum(event)"
  function valideKeyNum(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else {
          return false;
      }
  };
  
  //Solo Retroceso, Guion, Punto y Números del 0 al 9
  // onkeypress="return valideKeyNumCod(event)"
  function valideKeyNumCod(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 45) { // Guion
          return true;
      } else if (code == 46) { // Punto
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras Minúsculas
          return true;
      } else {
          return false;
      }
  };
  
  // Solo Retroceso, Punto y Números del 0 al 9.
  // onkeypress="return valideKeyNumDot(event)"
  function valideKeyNumDot(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 46) { // Punto
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else {
          return false;
      }
  };
  
  // Solo Retroceso, Signo Negativo, Punto y Números del 0 al 9.
  // onkeypress="return valideKeyCoorDeci(event)"
  function valideKeyCoorDeci(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 45) { // Guion
          return true;
      } else if (code == 46) { // Punto
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else {
          return false;
      }
  };
  
  // Validar Numero de Teléfono.
  // onkeypress="return valideKeyNumTel(event)"
  function valideKeyNumTel(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 43) { // Signo de Sumar
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else {
          return false;
      }
  };
  
  // Validar Código Postal.
  // onkeypress="return valideKeyNumPostal(event)"
  function valideKeyNumPostal(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 45) { // Guion
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras en Minúscula
          return true;
      } else {
          return false;
      }
  };
  
  // Validar Email.
  // onkeypress="return valideKeyEmail(event)"
  function valideKeyEmail(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 45) { // Guion
          return true;
      } else if (code == 46) { // Punto
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code == 64) { // Arroba
          return true;
      } else if (code == 95) { // Guion Bajo
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras en Minúscula
          return true;
      } else {
          return false;
      }
  };
  
  // Validar Clave del Usuario.
  // onkeypress="return valideKeyClave(event)"
  function valideKeyClave(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 65 && code <= 90) { // Letras Mayúsculas
          return true;
      } else if (code >= 97 && code <= 122) { // Letras en Minúscula
          return true;
      } else {
          return false;
      }
  };
  
  // Validar Dirección Web.
  // onkeypress="return valideKeyWeb(event)"
  function valideKeyWeb(evt) {
      var code = (evt.which) ? evt.which : evt.keyCode;
      if (code == 8) { // Retroceso
          return true;
      } else if (code == 45) {  // Guion
          return true;
      } else if (code == 46) { // Punto
          return true;
      } else if (code >= 48 && code <= 57) { // Números
          return true;
      } else if (code >= 97 && code <= 122) { // Letras en Minúscula
          return true;
      } else {
          return false;
      }
  };
  
  /** INICIA: formatNumberES(n, d) --------------------------------------- */
  
  // formatNumberES(99);           // "99"
  // formatNumberES(99, 3);        // "99,000"
  // formatNumberES(1000);         // "1.000"
  // formatNumberES(10000000);     // "10.000.000"
  // formatNumberES(1000, 2);      // "1.000,00"
  // formatNumberES(1000, 5);      // "1.000,00000"
  // formatNumberES(1000.11);      // "1.000"
  // formatNumberES(1000.11, 0);   // "1.000"
  // formatNumberES(1000.11, 1);   // "1.000,1"
  // formatNumberES(1000.11, 2);   // "1.000,11"
  // formatNumberES(1000.11, 3);   // "1.000,110"
  // formatNumberES("1000.11", 2); // "1.000,11"
  // formatNumberES("1000.11", 0); // "1.000"
  
  /** INICIO: formatNumberES( num, dec ) ---------------------------------------
   * Función para devolver un numero formateado con separadores de miles y decimales 
   * en formato español
   * @param {int|float|string} n  - numero valido en formato entero, float o string
   * @param {int} d               - numero de decimales
   */
  const formatNumberES = (n, d = 0) => {
      n = new Intl.NumberFormat("es-ES").format(parseFloat(n).toFixed(d))
      if (d > 0) {
          // Obtiene la cantidad de decimales que tiene el numero
          const decimals = n.indexOf(",") > -1 ? n.length - 1 - n.indexOf(",") : 0;
  
          // Anade los ceros necesarios al numero
          n = (decimals == 0) ? n + "," + "0".repeat(d) : n + "0".repeat(d - decimals);
      }
      return n;
  };
  
  // oninput="formatoNumerico(this)"
  function formatoNumerico(input) {
      // Elimina caracteres que no son dígitos, decimales o signo negativo
      input.value = input.value.replace(/[^0-9.\-]/g, "");
  
      // Divide la cadena en parte entera y parte decimal
      let partes = input.value.split(".");
      let parteEntera = partes[0];
      let parteDecimal = partes[1];
  
      // Agrega el separador de miles a la parte entera
      parteEntera = parteEntera.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  
      // Limita la parte decimal a dos decimales
      if (parteDecimal !== undefined) {
          parteDecimal = parteDecimal.slice(0, 2);
      }
  
      // Vuelve a formatear el valor del input
      if (parteDecimal !== undefined) {
          input.value = parteEntera + "." + parteDecimal;
      } else {
          input.value = parteEntera;
      }
  }
  /** FINALIZA: formatNumberES( num, dec ) --------------------------------------- */
  
  /** INICIO Paginador --------------------------------------- */
  function slideAtras(filaAnterior, filaActual) {
      document.querySelector('#linea_' + filaAnterior).removeAttribute("style");
      document.querySelector('#linea_' + filaActual).setAttribute('style', 'display: none;');
  };
  
  function slideAdelante(filaSiguiente, filaActual) {
      document.querySelector('#linea_' + filaActual).setAttribute('style', 'display: none;');
      document.querySelector('#linea_' + filaSiguiente).removeAttribute("style");
  };
  /** FINAL Paginador --------------------------------------- */
  
  /** INICIO Funciones del Div ------------------------------- */
  
  function refrescar_listado() {
      event.preventDefault();
      // $('#div_contenido_container').load(' #div_contenido_container2');
      window.location.reload();
  };
  
  function buscar_div() {
  
      event.preventDefault();
  
      let elemento_buscar = $('#registro_buscar').val();
  
      // Obtener la URL actual
      var url = new URL(window.location.href);
  
      // Obtener los parámetros de búsqueda existentes
      var searchParams = new URLSearchParams(url.search);
  
      // Agregar un nuevo parámetro de búsqueda
      searchParams.set('registro_buscar', elemento_buscar);
      searchParams.delete('pagina');
  
      // Actualizar la URL con los nuevos parámetros de búsqueda
      url.search = searchParams.toString();
  
      // Reemplazar la URL actual sin recargar la página
      window.history.replaceState(null, '', url.toString());
  
      // $('#div_contenido_container').load(' #div_contenido_container2');
      window.location.reload();
  };
  
  function maxRegistrosPagina_div(maxRegistrosPagina) {
  
      // Obtener la URL actual
      var url = new URL(window.location.href);
  
      // Obtener los parámetros de búsqueda existentes
      var searchParams = new URLSearchParams(url.search);
  
      // Agregar un nuevo parámetro de búsqueda
      searchParams.set('maxRegistrosPagina', maxRegistrosPagina);
      searchParams.delete('pagina');
  
      // Actualizar la URL con los nuevos parámetros de búsqueda
      url.search = searchParams.toString();
  
      // Reemplazar la URL actual sin recargar la página
      window.history.replaceState(null, '', url.toString());
  
      // $('#div_contenido_container').load(' #div_contenido_container2');
      window.location.reload();
  };
  
  // DataTable - Ordenar Columna
  function ordenar_div(orden, direccion) {
  
      // Obtener la URL actual
      var url = new URL(window.location.href);
  
      // Obtener los parámetros de búsqueda existentes
      var searchParams = new URLSearchParams(url.search);
  
      // Agregar un nuevo parámetro de búsqueda
      searchParams.set('orden', orden);
      searchParams.delete('pagina');
  
      // Actualizar la URL con los nuevos parámetros de búsqueda
      url.search = searchParams.toString();
  
      // Reemplazar la URL actual sin recargar la página
      window.history.replaceState(null, '', url.toString());
  
      // Obtener la URL actual
      var url = new URL(window.location.href);
  
      // Obtener los parámetros de búsqueda existentes
      var searchParams = new URLSearchParams(url.search);
  
      // Agregar un nuevo parámetro de búsqueda
      searchParams.set('direccion', direccion);
  
      // Actualizar la URL con los nuevos parámetros de búsqueda
      url.search = searchParams.toString();
  
      // Reemplazar la URL actual sin recargar la página
      window.history.replaceState(null, '', url.toString());
  
      // $('#table_container').load(' #table');
      // $('#paginador_container').load(' #paginador')
      window.location.reload();
  };
  
  function cambio_pagina(pagina_actual) {
  
      event.preventDefault();
  
      // Obtener la URL actual
      var url = new URL(window.location.href);
  
      // Obtener los parámetros de búsqueda existentes
      var searchParams = new URLSearchParams(url.search);
  
      // Agregar un nuevo parámetro de búsqueda
      searchParams.set('pagina', pagina_actual);
  
      // Actualizar la URL con los nuevos parámetros de búsqueda
      url.search = searchParams.toString();
  
      // Reemplazar la URL actual sin recargar la página
      window.history.replaceState(null, '', url.toString());
  
      // $('#div_contenido_container').load(' #div_contenido_container2');
      window.location.reload();
  };
  /** Fin Funciones del Div ---------------------------------- */
  
  /** INICIO - Funciones Pre-Visualizar Imagen --------------- */
  function previewImage(event, querySelector) {
  
      //Recuperamos el input que desencadeno la acción
      const input = event.target;
  
      //Recuperamos la etiqueta img donde cargaremos la imagen
      $imgPreview = document.querySelector(querySelector);
  
      // Verificamos si existe una imagen seleccionada
      if (!input.files.length) return
  
      //Recuperamos el archivo subido
      file = input.files[0];
  
      //Creamos la url
      objectURL = URL.createObjectURL(file);
  
      //Modificamos el atributo src de la etiqueta img
      if (input.files[0].type != "application/pdf") {
          $imgPreview.src = objectURL;
      } else {
          $imgPreview.src = "../web_logo/logo_pdf_view.png"
      }
  };
  /** FINAL - Funciones Pre-Visualizar Imagen --------------- */