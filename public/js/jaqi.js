$(document).ready(function(){

//CALENDARIO FERIADOS

  var Eventos_iniciales = {
       
    url: 'calendario_feriados',
    type: 'GET', // Send post data
    error: function() {
        alert('No se encontró ninguna fecha.');
    }
  };
  $('#calendario_feriados').fullCalendar({
      header: {
          left: 'prev,next today',
          center: 'title',
          right: 'month,listYear'
      },
      // allDay : false,
      aspectRatio: 1,
      weekends: false,
      editable: true,
      eventLimit: true, // allow "more" link when too many events
      selectable: true,
      selectHelper: true,
      eventTextColor: 'Black',

      eventRender: function(event, element) {
          element.bind('dblclick', function() {
              $('#btn_borrar_feriado').attr("disabled", false);
              $('#ModalEdit #id').val(event.id);
              $('#ModalEdit #title').val(event.title);
              $('#ModalEdit #color').val(event.color);
              $('#ModalEdit').modal('show');
              $('#desc_feriado').val(event.title);
          });
      },
      dayClick: function(date) {
        $('#ModalAdd #start').val(moment(date).format('YYYY-MM-DD'));
        $('#ModalAdd #end').val(moment(date).format('YYYY-MM-DD'));
        $('#ModalAdd').modal('show');
      },
      events: Eventos_iniciales,
      eventOverlap: false,
      // eventRender: false   
  });

  $('#btn_borrar_feriado').click(function(){
    $('#ModalEdit').modal('hide');
  });

  
  $('#btn-pdf').hide()
  $('#btn-cancelar').hide()
  //REPORTES
  $("#id_min").change(function(){
    cargaDirecciones();
    cargaUnidades();
  });

  $("#id_dir").change(function(){
    cargaUnidades();
  });

  $("#id_uni").change(function(){
    cargaRanking();
  });

  function cargaRanking(){
    $("#tabla_ranking tbody").html("");
      // $("#error").html("<div class='modal1'><div class='center1'> <center> <img src='img/gif-load.gif'> Buscando Informacion...</center></div></div>");
    var id_min = $("#id_min").val();
    var id_dir = $("#id_dir").val();
    var id_uni = $("#id_uni").val();

    $.getJSON("ranking_vacaciones",{id_min:id_min, id_dir:id_dir, id_uni:id_uni},function(objetosretorna){
      $("#error").html("");
      var TamanoArray = objetosretorna.length;
      $.each(objetosretorna, function(i,datos){

        var nuevaFila =
        "<tr>"
        +"<td>"+datos.unidad+"</td>"
        +"<td>"+datos.nombre+" "+datos.paterno+" "+datos.materno+"</td>"
        +"<td>"+datos.fechaingreso+"</td>"
        +"<td>"+datos.item+"</td>"
        +"<td>"+parseInt(datos.desde.substr(0,4))+" - "+parseInt(datos.hasta.substr(0,4))+"</td>"
        +"<td>"+datos.vigencia+"</td>"
        +"<td>"+(datos.dias+datos.total_saldo)+"</td>"
        +"<td>"+datos.dias+"</td>"
        +"<td>"+datos.total_saldo+"</td>"
        +"</tr>";

        $(nuevaFila).appendTo("#tabla_ranking tbody");
      });
      if(TamanoArray==0){
        var nuevaFila =
        "<tr><td colspan=6>No se encontraron parametros para su busqueda</td>"
        +"</tr>";
        $(nuevaFila).appendTo("#tabla_ranking tbody");
      }
    });
  };
  
  function cargaDirecciones(){
    $(".dir_json select").html("");
    var id_min = $("#id_min").val();

    // console.log($("#anio").val());
    $.getJSON("consultaDirecciones/"+id_min+"",{},function(objetosretorna){
      $("#error").html("");
      var TamanoArray = objetosretorna.length;
      $(".dir_json select").append('<option value="0"> --- SELECCIONE UNA DIRECCION --- </option>');
      $.each(objetosretorna, function(i,value){
        $(".dir_json select").append('<option value="'+value.id_dir+'">'+value.nombre_dir+'</option>');
      });
    });
  };

  function cargaUnidades(){
    $(".uni_json select").html("");
    var id_dir = $("#id_dir").val();

    // console.log($("#anio").val());
    $.getJSON("consultaUnidades/"+id_dir+"",{},function(objetosretorna){
      $("#error").html("");
      var TamanoArray = objetosretorna.length;
      $(".uni_json select").append('<option value="0"> --- SELECCIONE UNA UNIDAD --- </option>');
      $.each(objetosretorna, function(i,value){
        $(".uni_json select").append('<option value="'+value.id_unidad+'">'+value.nombre_unidad+'</option>');
      });
    });
  };


  //CALENDARIO
  $("#tablajson tbody").html("");
  $("#div_calendar").hide()
  
  $('#btn-calendar').click(function(){
    $("#div_calendar").show();
    $('#btn-pdf').show()
    $('#btn-cancelar').show()
    limpiar();
    var id_sol = $("#id_solicitud").val();
    calendario();
    
    $("#btn-calendar").hide();
  });
  
  $('#btn_guarda_fecha').click(function(){
    // $('#btn_guarda_fecha').attr("disabled", true);
  });

  $('#btn-pdf').click(function(){
      id_sol = $("#id_solicitud").val()
      $.ajax({
        type:'get',
        url:"agregar_solicitud",
        data:{'id_sol':id_sol},
        success: function(result){
          if (result == 'error') {
            alert("No se pudo realizar la petición");
          }
          else if(result == 'vacio'){
            alert('No seleccionó ninguna fecha..!');
          }
          else if(result == 'ok'){
          recargar();
          // alert($("#id_solicitud").val());
          }
          else
          {
            alert("Ocurrió un error, revise su conexión");
          }
        }
    });
  });

  $('#btn-cancelar').click(function(){
    var div_resul="div_notificacion_sol";
    var id_sol = $("#id_solicitud").val();
    $.ajax({
      type:'POST',
      url:"borrar_sol", // sending the request to the same page we're on right now
      data:{'id_sol':id_sol},
         success: function(result){
              if (result == 'ok') {
                refrescar();
              }
              else{
                $("#"+div_resul+"").html(result);
              }
          }
      }
  )

  });


    var meses = new Array ("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
    var f=new Date();
    // document.write(f.getDate() + " de " + meses[f.getMonth()] + " de " + f.getFullYear());
    $("#hoy").text(f.getDate() + " de " + meses[f.getMonth()] + " de " + f.getFullYear());

});

function refrescar(){
  timout=setTimeout(function(){
      location.reload();
  },3000,"JavaScript");//3 segundos
}
function recargar(){
  timout=setTimeout(function(){
      location.reload();
  },0,"JavaScript");//3 segundos
}

function refresh_calendar(){
  $('#btn_guarda_fecha').attr("disabled", false);
  var events = {
      url: 'calendar_datos',
      type: 'GET', // Send post data
      error: function() {
          alert('No se encontró ninguna fecha.');
      }
  };
//   var events;
//   $.ajax({
//   type:'get',
//   url:"calendar_datos",
//   success: function(result){
//       if (result == 'error') {
//       alert("No se pudo realizar la petición");
//       }
//       else if(result == 'ok'){
//           alert("ok petición");
//       }
//       else{
//           defaultEvents = result;
//       }
//   }
// });

  $('#calendar').fullCalendar('removeEventSource', events);
  $('#calendar').fullCalendar('addEventSource', events);
  $('#calendar').fullCalendar('refetchEvents');
}

function refresh_calendar_feriado(){
  $('#btn_guarda_fecha').attr("disabled", false);
  var events = {
      url: 'calendario_feriados',
      type: 'GET', // Send post data
      error: function() {
          alert('No se encontró ninguna fecha.');
      }
  };

  $('#calendario_feriados').fullCalendar('removeEventSource', events);
  $('#calendario_feriados').fullCalendar('addEventSource', events);
  $('#calendario_feriados').fullCalendar('refetchEvents');
}

function refresh_calendar_emergencias(id_sol){
  var events = {
      url: 'calendar_datos_emergencias/'+id_sol,
      type: 'GET', // Send post data
      error: function() {
          alert('No se encontró ninguna fecha.');
      }
  };

  $('#calendar_emergencias').fullCalendar('removeEventSource', events);
  $('#calendar_emergencias').fullCalendar('addEventSource', events);
  $('#calendar_emergencias').fullCalendar('refetchEvents');
}

function aceptar_suspension_rr_hh(id_suspension){
  var div_resul="div_notificacion_sol";
  $.ajax({
    type:'POST',
    url:"aceptar_suspension_rr_hh", // sending the request to the same page we're on right now
    data:{'id_suspension':id_suspension},
       success: function(result){
            if (result == 'ok') {
              location.reload()
              // refrescar();
              // $('#div-suspension').load(location.href);  
            }
            else{
              $(div_resul).html(result);
            }
        }
    })
}

function aceptar_suspension_unidad(id_suspension){
  var div_resul="div_notificacion_sol";
  $.ajax({
    type:'POST',
    url:"aceptar_suspension_unidad", // sending the request to the same page we're on right now
    data:{'id_suspension':id_suspension},
       success: function(result){
            if (result == 'ok') {
              location.reload()
              // refrescar();
              // $('#div-suspension').load(location.href);  
            }
            else{
              $(div_resul).html(result);
            }
        }
    })
}

function estado_calendario(arg){//get con Json
  $("#tablajson tbody").html("");
  $.getJSON("estado_calendario/"+arg+"",{},function(objetosretorna){
    // alert(objetosretorna);
    $("#error").html("");
    var TamanoArray = objetosretorna.length;
    var solicitados = 0;
    var saldo = 0;
    var indice = 0;
    var disponible = 0;
    if(TamanoArray > 0){
      disponible = parseFloat(objetosretorna[0].disponible);
    }
    
    $("#total_solicitud").text(solicitados);
    $("#total_saldo").text(saldo);
    $("#total_disponible").text(disponible);
    $.each(objetosretorna, function(i,items){
      
      solicitados = solicitados + parseFloat(items.usadas);
      // disponible = (parseFloat(items.disponible) - total);
      saldo = (parseFloat(disponible - solicitados));
      indice ++;
      var nuevaFila =
    "<tr>"
    +"<td>"+indice+"</td>"
    +"<td>"+formato(items.start)+"</td>"
    +"<td>"+items.title+"</td>"
    +"</tr>";
    
      $(nuevaFila).appendTo("#tablajson tbody");
    });
    
    $("#total_solicitud").text(solicitados);
    $("#total_saldo").text(saldo);
    if(TamanoArray==0){
      var nuevaFila =
      "<tr><td colspan=6>Seleccione un día</td>"
      +"</tr>";
      $(nuevaFila).appendTo("#tablajson tbody");
    }
  });
}

function formato(fecha){
  return fecha.replace(/^(\d{4})-(\d{2})-(\d{2})$/g,'$3/$2/$1');
}

function limpiar(){
  // var id = $id_sol;
 $('#btn-calendar').attr("disabled", true);
  $.ajax({
      type:'get',
      url:"crear_sol",
      // data:{'id_sol':id},
      success: function(result){
        if (result == 'error') {
          alert("No se pudo realizar la petición");
        }
        else{
          $("#span_solicitud").text('Formulario de Solicitud No. '+result);
          estado_calendario(result);
          $("#id_solicitud").val(result);
          $("#id_solicitud_edit").val(result);
          // alert($("#id_solicitud").val());
        }
      }
  });
}

function  sol_vacaciones(arg){
	var urlraiz=$("#url_raiz_proyecto").val();
	var miurl =urlraiz+"/form_sol_vacacion/"+arg+""; 
	$("#capa_modal").show();
	$("#capa_formularios").show();
	var screenTop = $(document).scrollTop();
	$("#capa_formularios").css('top', screenTop);
  $("#capa_formularios").html($("#cargador_empresa").html());

    $.ajax({
    url: miurl
    }).done( function(resul) 
    {
     $("#capa_formularios").html(resul);
    }).fail( function() 
   {
    $("#capa_formularios").html('<span>...Ha ocurrido un error, revise su conexión y vuelva a intentarlo...</span>');
   }) ;
}

function  verinfo_usuario(id, form){
  var urlraiz=$("#url_raiz_proyecto").val();
  if(form == 1){var miurl =urlraiz+"/form_editar_usuario/"+id+""; }
  if(form == 2){var miurl =urlraiz+"/form_baja_usuario/"+id+""; }

  if(form == 4){var miurl =urlraiz+"/form_editar_gestion/"+id+""; }
  if(form == 7){var miurl =urlraiz+"/form_sol_vacacion_unidad/"+id+""; }
  if(form == 8){var miurl =urlraiz+"/form_sol_vacacion_rr_hh/"+id+""; }
  if(form == 9){var miurl =urlraiz+"/form_sol_suspension_usuario/"+id+""; }
  if(form == 10){var miurl =urlraiz+"/form_sol_suspension_unidad/"+id+""; }
  if(form == 11){var miurl =urlraiz+"/form_sol_suspension_rr_hh/"+id+""; }
  if(form == 12){var miurl =urlraiz+"/form_anulacion_vacacion/"+id+""; }
  if(form == 13){var miurl =urlraiz+"/form_sol_emergencias_usuario/"+id+""; }
  
	
	$("#capa_modal").show();
	$("#capa_formularios").show();
	var screenTop = $(document).scrollTop();
	$("#capa_formularios").css('top', screenTop);
  $("#capa_formularios").html($("#cargador_empresa").html());

    $.ajax({
    url: miurl
    }).done( function(resul) 
    {
     $("#capa_formularios").html(resul);
   
    }).fail( function() 
   {
    $("#capa_formularios").html('<span>...Ha ocurrido un error, revise su conexión y vuelva a intentarlo...</span>');
   }) ;
}

$(document).on("click",".div_modal", function(e){
	$(this).hide();
	$("#capa_formularios").hide();
	$("#capa_formularios").html("");
})

$(document).on("click","#cerrar_modal", function(e){
  $("#capa_modal").hide();
  $("#capa_formularios").hide();
})

function cargar_formulario(arg){
   var urlraiz=$("#url_raiz_proyecto").val();
   $("#capa_modal").show();
   $("#capa_formularios").show();
   var screenTop = $(document).scrollTop();
   $("#capa_formularios").css('top', screenTop);
   $("#capa_formularios").html($("#cargador_empresa").html());
   if(arg==1){ var miurl=urlraiz+"/form_nuevo_usuario"; }
   if(arg==2){ var miurl=urlraiz+"/form_nuevo_rol"; }
   if(arg==3){ var miurl=urlraiz+"/form_nuevo_permiso"; }
   if(arg==4){ var miurl=urlraiz+"/form_nueva_gestion"; }

    $.ajax({
    url: miurl
    }).done( function(resul) 
    {
     $("#capa_formularios").html(resul);
   
    }).fail( function() 
   {
    $("#capa_formularios").html('<span>...Ha ocurrido un error, revise su conexión y vuelva a intentarlo...</span>');
   }) ;

}

$(document).on("submit",".formentrada",function(e){
  
  var id_sol = $("#id_solicitud").val();
  e.preventDefault();
  $('#btn_guarda_fecha').attr("disabled", true);
  $('#ModalAdd').modal('hide');
  $('#ModalEdit').modal('hide');

  var quien=$(this).attr("id");
  var formu=$(this);
  var varurl="";

  if(quien=="f_editar_solicitud"){  var varurl=$(this).attr("action");  var div_resul="div_notificacion_sol";}
  if(quien=="f_editar_gestion"){  var varurl=$(this).attr("action");  var div_resul="capa_formularios";}  
  if(quien=="f_crear_gestion"){  var varurl=$(this).attr("action");  var div_resul="capa_formularios";}  
  if(quien=="f_editar_tiempo"){  var varurl=$(this).attr("action");  var div_resul="div_notificacion_sol";}
  if(quien=="f_agregar_fechas"){  var varurl=$(this).attr("action");  var div_resul="div_notificacion_modal";  }
  if(quien=="f_verificar_solicitud"){  var varurl=$(this).attr("action");  var div_resul="capa_formularios";  }
  if(quien=="f_autorizar_solicitud"){  var varurl=$(this).attr("action");  var div_resul="capa_formularios";  }
  if(quien=="f_crear_solicitud"){  var varurl=$(this).attr("action");  var div_resul="div_notificacion_sol";  }
  if(quien=="f_crear_usuario"){  var varurl=$(this).attr("action");  var div_resul="capa_formularios";  }
  if(quien=="f_crear_permiso"){  var varurl=$(this).attr("action");  var div_resul="capa_formularios";  }
  if(quien=="f_editar_usuario"){  var varurl=$(this).attr("action");  var div_resul="notificacion_E2";  }
  if(quien=="f_editar_acceso"){  var varurl=$(this).attr("action");  var div_resul="notificacion_E3";  }
  if(quien=="f_borrar_usuario"){  var varurl=$(this).attr("action");  var div_resul="capa_formularios";  }
  if(quien=="f_asignar_permiso"){  var varurl=$(this).attr("action");  var div_resul="capa_formularios";  }
  if(quien=="f_agregar_feriado"){  var varurl=$(this).attr("action");  var div_resul="div_notificacion_sol";}
  if(quien=="f_editar_feriado"){  var varurl=$(this).attr("action");  var div_resul="div_notificacion_sol";  }
  
  // $("#"+div_resul+"").html( $("#cargador_empresa").html());
  
  $.ajax({
    // la URL para la petición
    url : varurl,
    data : formu.serialize(),
    type : 'POST',
    dataType : 'html',
  
    success : function(resul) {
      
      if(quien=="f_agregar_fechas"){
        if (resul == 'ok') {
                    // calendario();
          refresh_calendar();
          refresh_calendar_emergencias(id_sol);
          estado_calendario(id_sol)
          alertify.success('Fecha agregada');
          $('#btn_guarda_fecha').attr("disabled", false);
        }
        else if(resul == 'pasado'){
          alertify.warning('Está tratando de ingresar una fecha anterior!');
          $('#btn_guarda_fecha').attr("disabled", false);
        }
      }else if(quien=="f_editar_tiempo" && resul == 'ok'){
        $('#ModalEdit').modal('hide');
        refresh_calendar();
        refresh_calendar_emergencias(id_sol);
        estado_calendario(id_sol);
        $('#btn_edita_fecha').attr("disabled", false);
        }else if(quien=="f_editar_tiempo" && resul == 'diferente'){
          alertify.success('No puede editar otras solicitudes');
        }
        else if(quien=="f_editar_solicitud" && resul == 'ok'){
          
          refresh_calendar(id_sol);
          // estado_calendario(id_sol);
        }
        else if(quien=="f_agregar_feriado" && resul == 'ok'){
          refresh_calendar_feriado();
        }
        else if(quien=="f_editar_feriado" && resul == 'ok'){
          refresh_calendar_feriado();
        }
        else{
          // $('#capa_modal').modal('hide');
          
          $("#"+div_resul+"").html(resul);
        }
    },
    error : function(xhr, status) {
          $("#"+div_resul+"").html('ha ocurrido un error, revise su conexion e intentelo nuevamente');
    }
  });
})

$(document).on("submit",".form_crear_rol",function(e){
  e.preventDefault();
  var quien=$(this).attr("id");
  var formu=$(this);
  var varurl=$(this).attr("action"); 

   $("#div_notificacion_rol").html( $("#cargador_empresa").html());
   $(".form-group").removeClass("has-error");
   $(".help-block").text('');
  
  $.ajax({
    // la URL para la petición
    url : varurl,
    data : formu.serialize(),
    type : 'POST',
    dataType : "html",
  
    success : function(resul) {
      $("#capa_formularios").html(resul);
    },
    error : function(data) {
              var lb="";
              var errors = $.parseJSON(data.responseText);
               $.each(errors, function (key, value) {

                   $("#"+key+"_group").addClass( "has-error" );
                   $("#"+key+"_span").text(value);
               });
           $("#div_notificacion_rol").html('');
    }

  });
})

function asignar_rol(idusu){
   var idrol=$("#rol1").val();
   var urlraiz=$("#url_raiz_proyecto").val();
   $("#zona_etiquetas_roles").html($("#cargador_empresa").html());
   var miurl=urlraiz+"/asignar_rol/"+idusu+"/"+idrol+""; 

    $.ajax({
    url: miurl
    }).done( function(resul) 
    { 
      var etiquetas="";
      var roles=$.parseJSON(resul);
      $.each(roles,function(index, value) {
        etiquetas+= '<span class="label label-warning">'+value+'</span> ';
      })

     $("#zona_etiquetas_roles").html(etiquetas);
   
    }).fail( function() 
    {
    $("#zona_etiquetas_roles").html('<span style="color:red;">...Error: Aun no ha agregado roles o revise su conexion...</span>');
    }) ;

}

function quitar_rol(idusu){
   var idrol=$("#rol2").val();
   var urlraiz=$("#url_raiz_proyecto").val();
   $("#zona_etiquetas_roles").html($("#cargador_empresa").html());
   var miurl=urlraiz+"/quitar_rol/"+idusu+"/"+idrol+""; 

    $.ajax({
    url: miurl
    }).done( function(resul) 
    { 
      var etiquetas="";
      var roles=$.parseJSON(resul);
      $.each(roles,function(index, value) {
        etiquetas+= '<span class="label label-warning" style="margin-left:10px;" >'+value+'</span> ';
      })

     $("#zona_etiquetas_roles").html(etiquetas);
   
    }).fail( function() 
    {
    $("#zona_etiquetas_roles").html('<span style="color:red;">...Error: Aun no ha agregado roles  o revise su conexion...</span>');
    }) ;
}

function borrado_usuario(idusu){

   var urlraiz=$("#url_raiz_proyecto").val();
   $("#capa_modal").show();
   $("#capa_formularios").show();
   var screenTop = $(document).scrollTop();
   $("#capa_formularios").css('top', screenTop);
   $("#capa_formularios").html($("#cargador_empresa").html());
   var miurl=urlraiz+"/form_borrado_usuario/"+idusu+""; 
  
    $.ajax({
    url: miurl
    }).done( function(resul) 
    {
     $("#capa_formularios").html(resul);
   
    }).fail( function(resul) 
   {
    $("#capa_formularios").html(resul);
   }) ;
}


function borrar_permiso(idrol,idper){

     var urlraiz=$("#url_raiz_proyecto").val();
     var miurl=urlraiz+"/quitar_permiso/"+idrol+"/"+idper+""; 
     $("#filaP_"+idper+"").html($("#cargador_empresa").html() );
        $.ajax({
    url: miurl
    }).done( function(resul) 
    {
     $("#filaP_"+idper+"").hide();
   
    }).fail( function() 
   {
     alert("No se borro correctamente, intentalo nuevamente o revisa tu conexion");
   }) ;



}


function borrar_rol(idrol){

     var urlraiz=$("#url_raiz_proyecto").val();
     var miurl=urlraiz+"/borrar_rol/"+idrol+""; 
     $("#filaR_"+idrol+"").html($("#cargador_empresa").html() );
        $.ajax({
    url: miurl
    }).done( function(resul) 
    {
     $("#filaR_"+idrol+"").hide();
   
    }).fail( function() 
   {
     alert("No se borro correctamente, intentalo nuevamente o revisa tu conexion");
   }) ;



}
