<section  id="content" style="background-color: #002640;">

    <div class="" >
        <div class="container"> 
                    
            <div class="row">
              <div class="col-sm-6 col-sm-offset-3 myform-cont" >
                
                     <div class="myform-top">
                        <div class="myform-top-left">
                           <img  src="{{ url('img/minculturas_logo.png') }}" class="img-responsive logo" />
                          <h3 class="text-muted">Registro de Usuarios.</h3>
                            <p class="text-muted">Por favor ingrese sus datos personales:</p>
                        </div>
                        <div class="myform-top-right">
                          <i class="fa fa-user"></i>
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
                      
                            <form   action="{{ url('crear_usuario') }}"  method="post" id="f_crear_usuario" class="formentrada" >
                      <input type="hidden" name="_token" value="{{ csrf_token() }}">
                      
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted ">Nombres</label>
                                <input type="text" name="nombre" placeholder="Nombre Completo" class="form-control" value="{{ old('nombre') }}" >
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="form-group">
                                    <label class="text-muted ">Apellido Paterno</label>
                                <input type="text" name="paterno" placeholder="Apellido Paterno" class="form-control" value="{{ old('paterno') }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                    <label class="text-muted ">Apellido Materno</label>
                                <input type="text" name="materno" placeholder="Apellido Materno" class="form-control" value="{{ old('materno') }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                    <label class="text-muted ">Cedula de Identidad</label>
                                <input type="text" class="form-control" id="ci" name="ci" placeholder="No. Carnet"  value="{{ old('ci') }}" >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                    <label class="text-muted ">Email</label>
                                <input type="email" name="email" placeholder="Correo Electronico" class="form-control"  
                                value="{{ old('email') }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted ">Telefono</label>
                                <input type="number" name="telefono" placeholder="Telefono" class="form-control"  value="{{ old('telefono') }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted ">Domicilio</label>
                                <input type="text" name="domicilio" placeholder="Domicilio" class="form-control"  value="{{ old('domicilio') }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted ">Fecha de Ingreso</label>
                                <input type="date" name="fechaingreso" placeholder="Fecha de Ingreso" class="form-control" value="{{ old('fechaingreso') }}" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted ">Area</label>
                                <select class="form-control" name="area" id="id_uni">
                                        <option value="0" selected> --- SELECCIONE UNA UNIDAD --- </option>
                                    @foreach ($unidades as $unidad)
                                        {{-- <option value="{{$unidad->id}}" {{ old('area', $unidad->id) == $unidad->id ? 'selected' : '' }}>{{$unidad->nombre}}</option> --}}
                                        <option value="{{$unidad->id}}">{{$unidad->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group cargo_json">
                                <label class="text-muted ">Cargo</label>
                                <select class="form-control" name="cargo">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted ">Tipo de Usuario</label>
                                <select class="form-control" name="tipoUsuario" >
                                    <option value="4" {{ old('tipoUsuario') == 4 ? 'selected' : '' }}>Funcionario</option >
                                    <option value="3" {{ old('tipoUsuario') == 3 ? 'selected' : '' }}>Jefe Unidad</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-muted ">Password</label>
                                <input type="password" name="password" placeholder="Password" class="form-control" >
                            </div>
                        </div>
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label class="text-muted ">Confirme Password</label>
                                    <input type="password" name="password_confirmation" placeholder="Repite Password" class="form-control" >
                            </div>
                        </div>
{{-- 
                        <div class="form-group">
                         {!! Recaptcha::render() !!}
                        </div> --}}

                        <button type="submit" class="mybtn">Registrar</button>
                      </form>
                    
                    </div>
              </div>
            </div>
            {{-- <div class="row">
                <div class="col-sm-12 mysocial-login">
                    <h3>...Visitanos en nuestra Pagina</h3>
                    <h1><strong>minculturas.gob.bo</strong>.net</h1>
                </div>
            </div> --}}
        </div>
      </div>
 
</section>

<script>
    $(document).ready(function() {
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




    });
</script>