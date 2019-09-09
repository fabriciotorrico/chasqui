<section  id="contenido_principal" style="background-color: #002640;">
    <div class="" >
        <div class="container">
            <div class="row">
              <div class="col-sm-9 col-sm-offset-1 myform-cont" >
                 <div class="myform-top">
                    <div class="myform-top-left">
                       <img  src="{{ url('img/chasqui_diseño_blanco.png') }}" class="img-responsive logo" />
                       <h3 class="text-muted">Nueva Correspondencia</h3>
                    </div>
                    <div class="myform-top-right">
                      <i class="fa fa-edit"></i>
                    </div>
                  </div>

                  <div class="col-md-12" >
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>UPPS!</strong> Error al Registrar<br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>

                    @endif
                   </div  >

                   <div class="myform-bottom">

                      {{-- <form  action="{{ url(' nueva_correspondencia ') }}"  method="post" class="formentrada" > --}}
                      <form method="post" id="f_nueva_correspondencia" class="formentrada_usuario">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted ">De:</label>
                                <input type="text" name="persona_origen" class="form-control" value="{{ $persona_origen." - ".$cargo_origen." - ".$sigla_origen }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted ">Para:</label>
                                <select class="form-control" name="id_usuario_destino" id="id_usuario_destino" required>
                                    <option value="" selected> --- Seleccione el destinatario --- </option>
                                    @foreach ($destinatarios as $destinatario)
                                      <option value="{{$destinatario->id_usuario}}">{{$destinatario->nombres." ".$destinatario->paterno." ".$destinatario->materno." - ".$destinatario->descripcion_del_cargo." - ".$destinatario->sigla_del_area}}</option>
                                    @endforeach
                                    @foreach ($destinatarios_especiales as $destinatario_especial)
                                      <option value="{{$destinatario_especial->id_usuario}}">{{$destinatario_especial->nombres." ".$destinatario_especial->paterno." ".$destinatario_especial->materno." - ".$destinatario_especial->descripcion_del_cargo." - ".$destinatario_especial->sigla_del_area}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="text-muted ">Cite:</label>
                                <input type="text" name="cite" class="form-control" value="{{ $cite }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="text-muted ">Fecha:</label>
                                <input type="date" name="fecha_creacion" class="form-control" value="{{ $fecha }}" readonly/>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted ">Instrucción:</label>
                                <select class="form-control" name="id_instruccion" id="id_instruccion" required>
                                    <option value="" selected> --- Seleccione la instrucción --- </option>
                                    @foreach ($instrucciones as $instruccion)
                                      <option value="{{$instruccion->id_instruccion}}">{{$instruccion->descripcion}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted ">Referencia:</label>
                                <input type="text" name="referencia" id="referencia" placeholder="Introduzca la referencia de la Comunicación Interna" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label class="text-muted ">Contenido (cite / descripción):</label>
                                <input type="text" name="contenido" placeholder="Introduzca el cite y descripción de los documentos que adjunta" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="text-muted ">Páginas Agregadas:</label>
                                <input type="number" min="0" step="1" name="nro_paginas_agregadas" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="form-group">
                                <label class="text-muted ">Prioridad:</label>
                                <select class="form-control" name="prioridad" id="prioridad" onchange="mostrar_ocultar_plazo()"required>
                                    <option value="Media" selected> Media </option>
                                    <option value="Alta"> Alta </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="text-muted" id="label_plazo">Plazo (días):</label>
                                <input type="number" min="0" step="1" name="plazo" id="plazo" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted ">Enviar Copias:</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="switch">
                                  <input type="checkbox" name="switch_copia" id="switch_copia" onclick="mostrar_ocultar_copia()">
                                  <span class="slider round"></span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted" id="label_select_copia">Destinatarios como copia:</label>
                                <select class="form-control select2" name="id_usuario_copia[]" id="select_copia" multiple="multiple" style="width: 100%;" >
                                    <option value="" selected> --- Seleccione el destinatario al cual enviarle una copia del documento (Pulse Ctrl para seleccionar varios) --- </option>
                                    @foreach ($destinatarios as $destinatario)
                                      <option value="{{$destinatario->id_usuario}}">{{$destinatario->nombres." ".$destinatario->paterno." ".$destinatario->materno." - ".$destinatario->descripcion_del_cargo." - ".$destinatario->sigla_del_area}}</option>
                                    @endforeach
                                    @foreach ($destinatarios_especiales as $destinatario_especial)
                                      <option value="{{$destinatario_especial->id_usuario}}">{{$destinatario_especial->nombres." ".$destinatario_especial->paterno." ".$destinatario_especial->materno." - ".$destinatario_especial->descripcion_del_cargo." - ".$destinatario_especial->sigla_del_area}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <br>
                            </div>
                        </div>
                        <button type="submit" class="bg-blue mybtn" id="finalizar">  <i class="fa fa-share-square-o"></i>Enviar Correspondencia</button>
                    </form>
                  </div>
              </div>
            </div>
        </div>
      </div>
</section>

<script>

    $( document ).ready(function() {
      mostrar_ocultar_plazo();
      mostrar_ocultar_copia();
      //Initialize Select2 Elements
      //$('.select2').select2()
    });

    function mostrar_ocultar_plazo(){
      if (document.getElementById('prioridad').value === "Alta") {
          $("#plazo").prop("disabled", false);
          document.getElementById('label_plazo').style.display = "block";
          document.getElementById('plazo').style.display = "block";
      } else {
          $("#plazo").prop("disabled", true);
          document.getElementById('label_plazo').style.display = "none";
          document.getElementById('plazo').style.display = "none";
      }
    }

    function mostrar_ocultar_copia() {
      if(document.getElementById('switch_copia').checked) {
        document.getElementById('select_copia').style.display = "block";
        document.getElementById('label_select_copia').style.display = "block";
      }
      else {
        document.getElementById('select_copia').style.display = "none";
        document.getElementById('label_select_copia').style.display = "none";
      }
    }

	  $('#f_nueva_correspondencia').on('submit', function (event) {
 	          	event.preventDefault();

              var form = $("#f_nueva_correspondencia");
              var div_resul="capa_formularios";
              $.ajax({
                  type: "POST",
                  url: "nueva_correspondencia",
                  data: $("#f_nueva_correspondencia").serialize(),
                  //data:{'id_usuario_destino':id_usuario_destino},
                  success: function(resul)
                  {
                      // $('#resp').html(data);
                      if (resul == 'ok') {
                          $("#"+div_resul+"").html('<div class="box box-primary col-xs-8">                          <div class="aprobado" style="margin-top:90px; text-align: center">                                    <span class="label label-success" style="font-size:15px;"> Operación Exitosa <i class="fa fa-check"></i></span><br/><br/>                                       <label style="color:#177F6B; font-size:15px;">                                            Correspondencia enviada correctamente                                       </label>                             </div>                            <div class="margin" style="margin-top:50px; text-align:center;margin-bottom: 50px;">                                <div class="btn-group">                                <a href="javascript:void(0);" onclick="mostrar_formulario(1);" class="btn bg-green"  style="font-size:15px;"  value=" "  > Nueva Correspondencia </a>                                </div>                                <div class="btn-group" style="margin-left:50px; " >                                <a href="{{ url("home") }}" class="btn bg-blue"  style="font-size:15px;"  value=" "  > Imprimir </a>                                </div> <div class="btn-group" style="margin-left:50px; " >                                <a href="{{ url("home") }}" class="btn bg-green"  style="font-size:15px;"  value=" "  > Volver al Inicio </a>                                </div>                            </div>  </div>                        ');
                      }
                      else{
                          $("#"+div_resul+"").html(resul);
                      }

                  },
                  error : function(xhr, status) {
                      $("#"+div_resul+"").html('Ha ocurrido un error al enviar la correspondencia, revise su conexion e intentelo nuevamente');
                  }
              });
	  });










    /*$('#finalizar').click(function(){
      if(f_nueva_correspondencia.referencia.value=="")
       {
         alert("Debes ingresar algo en el campo Nombre.");
         return false;
       }
         alert("Esta bien.");
      return true;

      var form = $("#f_nueva_correspondencia");
      var div_resul="capa_formularios";
      $.ajax({
          type: "POST",
          url: "nueva_correspondencia",
          data: $("#f_nueva_correspondencia").serialize(),
          //data:{'id_usuario_destino':id_usuario_destino},
          success: function(resul)
          {
              // $('#resp').html(data);
              if (resul == 'ok') {
                  $("#"+div_resul+"").html('<div class="box box-primary col-xs-12">                          <div class="aprobado" style="margin-top:70px; text-align: center">                                    <span class="label label-success">Usuario Agregado<i class="fa fa-check"></i></span><br/>                                        <label style="color:#177F6B">                                            Usuario agregado Correctamente                                        </label>                             </div>                            <div class="margin" style="margin-top:50px; text-align:center;margin-bottom: 50px;">                                <div class="btn-group">                                <a href="#"  onclick="cargar_formulario(10);" class="btn btn-success"    value=" "  > Crear Usuario</a>                                </div>                                <div class="btn-group" style="margin-left:50px; " >                                <a href="{{ url("listado_usuarios") }}" class="btn btn-info"    value=" "  > Listado Usuarios </a>                                </div>                            </div>  </div>                        ');
              }
              else{
                  $("#"+div_resul+"").html(resul);
              }

          },
          error : function(xhr, status) {
              $("#"+div_resul+"").html('ha ocurrido un error al agregar el usuario, revise su conexion e intentelo nuevamente');
          }
      });
  });*/

    /*
    //Para que sea automatico (sin llamar a funciones en los elementos)
    $( function() {
      $("#prioridad").change( function() {
          if ($(this).val() === "Alta") {
              $("#plazo").prop("disabled", false);
              document.getElementById('label_plazo').style.display = "block";
              document.getElementById('plazo').style.display = "block";
          } else {
              $("#plazo").prop("disabled", true);
              document.getElementById('label_plazo').style.display = "none";
              document.getElementById('plazo').style.display = "none";
          }
      });
    });*/

    /*$(document).ready(function() {
        $("#id_uni").change(function(){
            cargaCargos();

        });

        function cargaCargos(){

            $(".cargo_json select").html("");
            var id_uni = $("#id_uni").val();

            // console.log($("#anio").val());
            $.getJSON("consultaCargos/"+id_uni+"",{},function(objetosretorna){
                $("#error").html("");
                var TamanoArray = objetosretorna.length;
                $(".cargo_json select").append('<option value="0"> --- SELECCIONE EL CARGO --- </option>');
                $.each(objetosretorna, function(i,value){
                    $(".cargo_json select").append('<option value="'+value.idcargo+'">'+value.nombrecargo+' - '+value.num_item+'</option>');
                });
            });
        };
    });*/

</script>


<style>
  .switch {
    position: relative;
    display: inline-block;
    width: 55px;
    height: 25px;
  }

  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
  }

  input:checked + .slider {
    background-color: #2196F3;
  }

  input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
  }

  input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
  }

  /* Rounded sliders */
  .slider.round {
    border-radius: 34px;
  }

  .slider.round:before {
    border-radius: 50%;
  }
</style>
