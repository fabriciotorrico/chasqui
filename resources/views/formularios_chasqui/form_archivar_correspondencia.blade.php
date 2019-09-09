<section  id="contenido_principal" style="background-color: #002640;">
    <div class="" >
        <div class="container">
            <div class="row">
              <div class="col-sm-9 col-sm-offset-1 myform-cont" >
                 <div class="myform-top">
                    <div class="myform-top-left">
                       <img  src="{{ url('img/chasqui_diseño_blanco.png') }}" class="img-responsive logo" />
                       <h3 class="text-muted">Archivar Correspondencia</h3>
                       <p class="text-muted">Esta acción finalizará la correspondencia, se archivará y no podrá ser derivada. <p>
                    </div>
                    <div class="myform-top-right">
                      <i class="fa fa-edit"></i>
                    </div>
                  </div>

                   <div class="myform-bottom">

                      <form class="form" action="{{ url('archivar_correspondencia') }}" method="post">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted ">Responsable de correspondencia archivada:</label>
                                <input type="text" name="persona_origen" class="form-control" value="{{ $persona_origen." - ".$cargo_origen." - ".$sigla_origen }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="text-muted ">Carpeta:</label>
                                <select class="form-control" name="id_carpeta"required>
                                    <option value="" selected> --- Seleccione una carpeta donde archivar la correspondencia --- </option>
                                    @foreach ($carpetas as $carpeta)
                                      <option value="{{$carpeta->id_carpeta}}">{{ $carpeta->nombre }}</option>
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
                                <label class="text-muted ">Proveido:</label>
                                <input type="text" name="proveido" placeholder="Introduzca le motivo por el cual se archiva la correspondencia" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <br>
                            </div>
                        </div>
                        <input type="hidden" name="id_area" class="form-control" value="{{ $id_area_origen }}">
                        <input type="hidden" name="id_cargo" class="form-control" value="{{ $id_cargo_origen }}">
                        <input type="hidden" name="id_persona" class="form-control" value="{{ $id_persona_origen }}">
                        <input type="hidden" name="id_derivacion" class="form-control" value="{{ $id_derivacion }}">
                        <button type="submit" class="bg-blue mybtn">  <i class="fa fa-download"></i> Archivar Correspondencia</button>
                    </form>
                  </div>
              </div>
            </div>
        </div>
      </div>
</section>
