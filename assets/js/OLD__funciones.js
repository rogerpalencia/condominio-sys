function tips(mensaje){
    const Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
      }
    })
    Toast.fire({
      icon: 'success',
      title: mensaje
    })
}

function mensaje(mensaje){
    Swal.fire({
      icon: 'info',
      title: mensaje,
      showConfirmButton: true
    })    
}

function alerta(mensaje){
    Swal.fire({
      icon: 'warning',
      title: mensaje,
      showConfirmButton: true
    })    
}

function confirmar(mensaje,obs,link){
    Swal.fire({
      title: mensaje,
      text: obs,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Aceptar'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location=link ;
      }
    })
}

function validateEmail(email) 
{
  var re= /^[\w-]+(\.[\w-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)*?\.[a-z]{2,6}|(\d{1,3}\.){3}\d{1,3})(:\d{4})?$/ ;
   return re.test(email); 
}

function validateText(stringf) 
{
  var re= /^[A-Za-z ]+$/;
  return re.test(stringf); 
}

function validateMisc(stringf) 
{
  var re= /^[\w-_. ]*$/;
  return re.test(stringf); 
}

function validaLogin(username, userpass){
  var bool= true;
  if(!(username)){mensaje("Indique Su Usuario");bool=false;return;}
  if(!(userpass)){mensaje("Indique Su Clave");bool=false;return;}
  return bool;
}

function validaNewUser(usuario,clave,confirm_clave,tipo_rif,rif,celular){
    var bool= true;
    if(!(usuario)){mensaje("Indique su Email");bool=false;return;}
    if(!(clave)){mensaje("Indique su Clave");bool=false;return;}
    if(clave.length <= 5){mensaje("Su Clave debe tener al menos 6 Caracteres");bool=false;return;}
    if(clave!=confirm_clave){mensaje("Error de confirmacion en su clave");bool=false;return;}
    if(!(tipo_rif)){mensaje("Indique Tipo de RIF");bool=false;return;}
    if(!(rif)){mensaje("Indique su RIF de Empresa");bool=false;return;}
    if(!(celular)){mensaje("Indique su Nro. Celular");bool=false;return;}
    return bool;
}

function validarecupera(userpass,username,token){
    var bool= true;
    if(!(userpass)){mensaje("Indique su Clave");bool=false;return;}
    if((userpass)!==$(confuser_pass)){ mensaje("Error de confirmacion en su clave");bool=false;return;}
    if(userpass.length < 5){mensaje("Su Clave debe tener al menos 6 Caracteres");bool=false;return;}
    if(!(token)) { mensaje("Error No Valido");bool=false;return;}
    if(!(username)){mensaje("Error No Valido");bool=false;return;}
    return bool;
}

function validaRecuperaClave(useremail){
    if(!(useremail)){mensaje("Indique Su Email");bool=false;return;}
    return bool;
}

function validaClaves(user_pass,confuser_pass){
    var bool= true;
    if(!(user_pass)){mensaje("Indique su Clave");bool=false;return;}
    if(user_pass!==confuser_pass){mensaje("Error de confirmacion en su clave");bool=false;return;}
    if(user_pass.length < 5){mensaje("Su Clave debe tener al menos 6 Caracteres");bool=false;return;}
    return bool;
}


function validadeclaracionaduana(monto){ 
    return true ;
}

function validadeclaracionestimada(monto){
    return true ;s
}

function dologin(){
    var parserJsn=$("#parserJsn").val();
    var username= $("#username").val() ; 
    var userpass= $("#userpass").val() ; 
    var reg =validaLogin(username, userpass) ;
    if (reg){
        Swal.fire({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        });
        $.ajax({
            type: "post",
            datatype:'json',
            url: "./auth-login_mod.php",
            data: {username:username, userpass:userpass},
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    document.location.href = "index.php" ;
                } else {
                    alerta(data.respuesta);
                }
            },
            error: function(data) {
                alerta(data.respuesta)
            }
        })
    }  
}


function signup(){
    var parserJsn=$("#parserJsn").val();
    var username= $("#username").val() ; 
    var userpass= $("#userpass").val() ; 
    var tipo_rif= $('input[name="tipo_rif"]:checked').val();
    var rif = $("#rif").val() ;
    var celular= $("#celular").val() ;
    var confirm_pass= $("#confirm_pass").val() ; 
    var reg =validaNewUser(username,userpass,confirm_pass,tipo_rif,rif,celular) ;
    if (reg){
        Swal.fire({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        });
        $.ajax({
            type: "post",
            datatype:'json',
            url: "./auth-register_mod.php",
            data: {username:username,userpass:userpass,celular:celular,tipo_rif:tipo_rif,rif:rif},
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    mensaje(data.respuesta);
                } else {
                    alerta(data.respuesta);
                }
            },
            error: function(data) {
                alerta(data.respuesta)
            }
        })
    }  
}


function dorecovery(){
    var parserJsn=$("#parserJsn").val();
    var username= $("#username").val() ; 
    var reg =validaRecuperaClave(username) ;
    if (reg){
        Swal.fire ({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        })
        $.ajax({
            type: "post",
            datatype:'json',
            url: "./auth-recoverpw_mod.php",
            data: {username:username},
            success: function(data) {
                console.log(data) ;
                 if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    mensaje(data.respuesta) ;
                } else {
                    alerta(data.respuesta);
                }
            },
            error: function(data) {
                alerta(data.respuesta)
            }
        })
    }  
}

function gcontribuyente(){ 
    var parserJsn=$("#parserJsn").val();
    var tipo_doc= $('input[name="tipo_doc"]:checked').val();
    var genero= $('input[name="genero"]:checked').val();
    var fecha_nacimiento= $("#fecha_nacimiento").val();
    var cedula= $("#cedula").val();
    var pasaporte= $("#pasaporte").val();
    var rif= $("#rif").val();
    
    var apellido1= $("#apellido1").val();
    var apellido2= $("#apellido2").val();
    var nombre1 = $("#nombre1").val();
    var nombre2 = $("#nombre2").val();
    var id_estado_civil= $("#id_estado_civil").val();

    var domicilio_habitacion = $("#domicilio_habitacion").val();
    var cod_telefono= $("#cod_telefono").val();
    var telefono= $("#telefono").val();
    var cod_celular= $("#cod_celular").val();
    var celular= $("#celular").val();

    var id_estado= $("#id_estado").val();
    var id_ciudad= $("#id_ciudad").val();
    var id_municipio= $("#id_municipio").val();
    var id_parroquia= $("#id_parroquia").val();
    var id_sector= $("#id_sector").val();

    //var reg= validaEnlace_Mun(cedula,rif,fecha_nacimiento,genero,estado_civil,apellidos,nombres,email,id_estado,id_ciudad,id_municipio,id_parroquia,id_sector) ;
    var reg=true ;

    if (reg)
    {
        Swal.fire
        ({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        })
        $.ajax({
            url: "contribuyentes_mod.php",
            type: "POST",
            data: {tipo_doc:tipo_doc,
                    fecha_nacimiento:fecha_nacimiento,
                    genero:genero,
                    pasaporte:pasaporte,
                    cedula:cedula,
                    rif:rif,
                    id_estado_civil:id_estado_civil,
                    apellido1:apellido1,
                    apellido2:apellido2,
                    nombre1:nombre1,
                    nombre2:nombre2,
                    domicilio_habitacion:domicilio_habitacion,
                    cod_telefono:cod_telefono,
                    telefono:telefono,
                    cod_celular:cod_celular,
                    celular:celular,
                    id_estado:id_estado,
                    id_ciudad:id_ciudad,
                    id_municipio:id_municipio,
                    id_parroquia:id_parroquia,
                    id_sector:id_sector
            },
            datatype:'json',
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    //$('#id').val(data.id) ;
                    tips(data.respuesta) ;
                } else {
                    alerta(data.respuesta);
                }
            },
            error: function(data) {
                alerta(data.respuesta)
            }
        })
    }
}

function gdeclaracionaduana(){
    var bool= true;
    var parserJsn          = $("#parserJsn").val(); 
    var id_declaracion     = $("#id_declaracion").val();
    var numero_dua         = $("#numero_dua").val();
    var monto_dua          = $("#monto_dua").val();
    var consignatario      = $("#consignatario").val();
    var tipo_operacion     = $('input[name="tipo_operacion"]:checked').val();
    var vapor              = $("#vapor").val();
    var fecha_dua          = $("#fecha_dua").val();
    var origen             = $("#origen").val();
    var ubicacion          = $("#ubicacion").val();
    var llegada            = $("#llegada").val();
    var documento          = $("#documento").val();
    var tipo_mercancia     = $("#tipo_mercancia").val();
    var bel                = $("#bel").val();
    var rif                = $("#rif").val();
    const validado = true ;
    if (validado)
    {
        Swal.fire
        ({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        })
        $.ajax({
            url: "aduana_mod.php",
            type: "POST",
            data: {
                id_declaracion:id_declaracion,
                numero_dua:numero_dua,       
                monto_dua:monto_dua,        
                consignatario:consignatario,     
                tipo_operacion:tipo_operacion,    
                vapor:vapor,              
                fecha_dua:fecha_dua,          
                origen:origen,             
                llegada:llegada,            
                documento:documento,
                ubicacion:ubicacion,          
                tipo_mercancia:tipo_mercancia,        
                bel:bel,
                rif:rif},
            datatype:'json',
            success: function(data){
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1){
                    bool=true;
                } else {
                    bool=false;
                }
            },
            error: function(data) {
                bool=false;
            }
        })
        return bool ;
    }
}


function pagodeclaracionaduana(){
    var bool= true;
    var parserJsn        = $("#parserJsn").val(); 
    var numero_dua      = $("#numero_dua").val();
    var id_banco         = $("#id_banco").val();
    var id_banco_cuenta  = $("#id_banco_cuenta").val();
    var monto            = $("#monto").val();
    var fecha_pago     = $("#fecha_pago").val();
    var referencia     = $("#referencia").val();
    const validado = true ;
    if (validado)
    {
        Swal.fire
        ({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        })
        $.ajax({
            url: "aduana_pa_mod.php",
            type: "POST",
            data: {numero_dua:numero_dua,
                id_banco:id_banco,
                id_banco_cuenta:id_banco_cuenta,
                monto:monto,
                fecha_pago:fecha_pago,
                referencia:referencia},
            datatype:'json',
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    bool=true;
                } else {
                    bool=false;
                }
            },
            error: function(data) {
                bool=false;
            }
        })
        return bool ;
    }
}


function gdeclaracionestimada(){
    var bool= true;
    var parserJsn      = $("#parserJsn").val(); 
    var id_declaracion = $("#id_declaracion").val();
    var tipo_planilla  = $("#tipo_planilla").val();
    var anno= $("#anno").val();    
    var total_declaracion= $("#total_declaracion").val();
    //var reg= validadeclaracionestimada(cedula,rif) ;
    const validado = true ;
    if (validado)
    {
        Swal.fire
        ({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        })
        $.ajax({
            url: "estimada_mod.php",
            type: "POST",
            data: {id_declaracion:id_declaracion,
                tipo_planilla:tipo_planilla,
                anno:anno,
                total_declaracion:total_declaracion},
            datatype:'json',
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    bool=true;
                } else {
                    bool=false;
                }
            },
            error: function(data) {
                bool=false;
            }
        })
        return bool ;
    }
}


function gempresa(){
    var bool= true;
    var parserJsn     = $("#parserJsn").val(); 
    var id_empresa    = $("#id_empresa").val(); 
    var razon_social  = $("#razon_social").val(); 
    var domicilio_fiscal= $("#domicilio_fiscal").val(); 
    var rif           = $("#rif").val(); 
    var id_parroquia  = $("#id_parroquia").val(); 
    var id_sector     = $("#id_sector").val(); 
    var id_ramo       = $("#id_ramo").val(); 
    var cantidad_empleados= $("#cantidad_empleados").val(); 
    var tlf           = $("#tlf").val(); 
    var correo        = $("#correo").val(); 
    var pagina_web    = $("#pagina_web").val(); 
    var fecha_inicio  = $("#fecha_inicio").val(); 
    var fecha_registro  = $("#fecha_registro").val(); 
    var observaciones = $("#observaciones").val(); 
    //var reg= validadeclaracionestimada(cedula,rif) ;
    const validado = true ;
    if (validado)
    {  
        Swal.fire
        ({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        })
        $.ajax({
            url: "empresas_mod.php",
            type: "POST",
            data: {id_empresa:id_empresa,
                    razon_social:razon_social,
                    rif:rif,
                    domicilio_fiscal:domicilio_fiscal,
                    id_parroquia:id_parroquia,
                    id_sector:id_sector,
                    id_ramo:id_ramo,
                    cantidad_empleados:cantidad_empleados,
                    tlf:tlf,
                    correo:correo,
                    pagina_web:pagina_web,
                    fecha_inicio:fecha_inicio,
                    fecha_registro:fecha_registro,
                    observaciones:observaciones},
            datatype:'json',
            success: function(data) {
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    bool=true;
                } else {
                    bool=false;
                }
            },
            error: function(data) {
                bool=false;
            }
        })
        return bool ;
    }
}


function ginmueble(){
    var bool= true;
    var parserJsn     = $("#parserJsn").val(); 
    var id_inmueble    = $("#id_inmueble").val(); 
    var casa_edificio     = $("#casa_edificio").val(); 
    var direccion         = $("#direccion").val(); 
    var piso              = $("#piso").val(); 
    var local             = $("#local").val(); 
    var caracter_juridico = $("#caracter_juridico").val(); 
    var area_constru      = $("#area_constru").val(); 
    var area_nconstru     = $("#area_nconstru").val(); 
    var area_total        = $("#area_total").val(); 
    var lindero_n         = $("#lindero_n").val(); 
    var lindero_s         = $("#lindero_s").val(); 
    var lindero_e         = $("#lindero_e").val(); 
    var lindero_o         = $("#lindero_o").val(); 
    var cv_nfolio         = $("#cv_nfolio").val(); 
    var cv_tomo           = $("#cv_tomo").val(); 
    var cv_ndocu          = $("#cv_ndocu").val(); 
    var cv_ncatastral     = $("#cv_ncatastral").val(); 
    var cv_adicional      = $("#cv_adicional").val(); 
    var id_uso_inmueble   = $("#id_uso_inmueble").val(); 
    var id_parroquia      = $("#id_parroquia").val(); 
    var id_sector         = $("#id_sector").val(); 
    var id_tipo_inmueble  = $("#id_tipo_inmueble").val(); 
    var id_tipo_terreno   = $("#id_tipo_terreno").val(); 
    var fecha_registro    = $("#fecha_registro").val(); 
    //var reg= validadeclaracionestimada(cedula,rif) ;
    const validado = true ;
    if (validado)
    {
        Swal.fire
        ({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        })
        $.ajax({
            url: "inmuebles_mod.php",
            type: "POST",
            data: {id_inmueble:id_inmueble,
            casa_edificio:casa_edificio,
            direccion:direccion,
            piso:piso,
            local:local,
            caracter_juridico:caracter_juridico,
            area_constru:area_constru,
            area_nconstru:area_nconstru,
            area_total:area_total,
            lindero_n:lindero_n,
            lindero_s:lindero_s,
            lindero_e:lindero_e,
            lindero_o:lindero_o,
            cv_nfolio:cv_nfolio,
            cv_tomo:cv_tomo,
            cv_ncatastral:cv_ncatastral,
            cv_ndocu:cv_ndocu,
            cv_adicional:cv_adicional,
            fecha_registro:fecha_registro,
            id_uso_inmueble:id_uso_inmueble,
            id_parroquia:id_parroquia,
            id_sector:id_sector,
            id_tipo_inmueble:id_tipo_inmueble,
            id_tipo_terreno:id_tipo_terreno},
            datatype:'json',
            success: function(data) {
                console.log(data) ;
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    bool=true;
                } else {
                    bool=false;
                }
            },
            error: function(data) {
                bool=false;
            }
        })
        return bool ;
    }
}


function gvehiculo(){
    var bool= true;
    var parserJsn      =$("#parserJsn").val(); 
    var id_vehiculo    =$("#id_vehiculo").val(); 
    var matricula      =$("#matricula").val(); 
    var id_tipo_vehiculo=$("#id_tipo_vehiculo").val(); 
    var id_marca       =$("#id_marca").val(); 
    var id_modelo      =$("#id_modelo").val(); 
    var anio          =$("#anio").val(); 
    var id_color       =$("#id_color").val(); 
    var numero_puestos =$("#numero_puestos").val(); 
    var capacidad      =$("#capacidad").val(); 
    var valor          =$("#valor").val(); 
    var fecha_registro  =$("#fecha_registro").val(); 

    //var reg= validadeclaracionestimada(cedula,rif) ;
    const validado = true ;
    if (validado)
    {
        Swal.fire
        ({
            background: 'transparent',
            html: '<img src="./assets/images/loading.svg">',
            allowOutsideClick: false,
            showConfirmButton: false,
        })
        $.ajax({
            url: "vehiculos_mod.php",
            type: "POST",
                data: {id_vehiculo:id_vehiculo,
                id_tipo_vehiculo:id_tipo_vehiculo,
                matricula:matricula,
                id_marca:id_marca,
                id_modelo:id_modelo,
                anio:anio,
                id_color:id_color,
                numero_puestos:numero_puestos,
                capacidad:capacidad,
                valor:valor,
                fecha_registro:fecha_registro},
            datatype:'json',
            success: function(data) {
                console.log(data) ;
                if (parserJsn==1)
                    data= JSON.parse(data);
                if (data.estatus==1) {
                    bool=true;
                } else {
                    bool=false;
                }
            },
            error: function(data) {
                bool=false;
            }
        })
        return bool ;
    }
}

$(document).ready(function(){
    $("#id_estado").on('change', function () {
        var id_estado = $("#id_estado option:selected").val() ;
        $.post("./localidades.php", {id_estado:id_estado,proc:1}, function(data){
            console.log(data) 
            $("#id_ciudad").html(data);
        });         
   });
});

$(document).ready(function(){
    $("#id_ciudad").on('change', function () {
        var id_estado = $("#id_estado option:selected").val() ;
        var id_ciudad = $("#id_ciudad option:selected").val() ;
        $.post("./localidades.php", {id_estado:id_estado,id_ciudad:id_ciudad,proc:2}, function(data){
            console.log(data) 
            $("#id_municipio").html(data);
        });         
   });
});
$(document).ready(function(){
    $("#id_municipio").on('change', function (){
        var id_estado = $("#estado option:selected").val() ;
        var id_ciudad = $("#ciudad option:selected").val() ;
        var id_municipio = $("#id_municipio option:selected").val() ;
        $.post("./localidades.php", {id_estado:id_estado,id_ciudad:id_ciudad,id_municipio:id_municipio,proc:3}, function(data){
            console.log(data) 
            $("#id_parroquia").html(data);
        });         
   });
});

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

