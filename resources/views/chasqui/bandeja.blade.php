@extends('layouts.app')

@section('htmlheader_title')
	Home
@endsection

@section('main-content')
<section class="content-header">
	@if(isset($mensaje_exito))
		<div class="alert" style="background-color:#B3E7CB;">
	    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	    <h4><i class="icon fa fa-check"></i> Acción realizada con éxito</h4>
			{{ $mensaje_exito }}
	  </div>
	@endif
  <h1>
    Bandeja <!--small>13 new messages</small-->
  </h1>
</section>

<section class="content">
  <div class="row">
    <div class="col-md-3">
      <a href="javascript:void(0);" onclick="mostrar_formulario(1);" class="btn btn-primary btn-block margin-bottom"> Nueva Hoja de Ruta Interna</a>

      <div class="box box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Folders</h3>
          <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="box-body no-padding">
          <ul class="nav nav-pills nav-stacked">
            <li @if($folder === 'bandeja_de_entrada') {{ 'class=active'}} @endif><a href="{{ url('bandeja') }}"><i class="fa fa-inbox"></i> Bandeja de entrada
              <span class="label label-primary pull-right">{{ $bandeja_nuevos_cantidad }}</span></a></li>
            <li @if($folder === 'copias_recibidas') {{ 'class=active'}} @endif><a href="{{ url('copias_recibidas') }}"><i class="fa fa-clone"></i> Copias recibidas </a></li>
            <li @if($folder === 'enviados') {{ 'class=active'}} @endif><a href="{{ url('enviados') }}"><i class="fa fa-external-link"></i> Enviados </a></li>
            <li @if($folder === 'archivados') {{ 'class=active'}} @endif><a href="{{ url('archivados') }}"><i class="fa fa-file-zip-o"></i> Archivados </a></li>
            <li @if($folder === 'seguimiento') {{ 'class=active'}} @endif><a href="{{ url('seguimiento_buscar') }}"><i class="fa fa-search"></i> Seguimiento </a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- /.col -->
    <div class="col-md-9">
      <div class="box box-primary">

				@yield('contenido-bandejas')

      </div>
      <!-- /. box -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.row -->
</section>
<!-- /.content -->

<!--section  id="contenido_principal">
  <div class="box box-primary box-white">
    <div class="box-body box-white">
    </div>
  </div>
</section-->
@endsection
