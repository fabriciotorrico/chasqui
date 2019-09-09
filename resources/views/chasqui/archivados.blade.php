@extends('chasqui.bandeja')

@section('contenido-bandejas')
<div class="box-header with-border">
  <h3 class="box-title">Correspondencia enviada</h3>
</div>
<div class="box-body no-padding">
  <div class="mailbox-controls">
    <a href="{{ url('archivados') }}" class="btn btn-default btn-sm fa fa-refresh"></a>
  </div>
  <div class="table-responsive mailbox-messages">
    <section class="sidebar">
      <!-- Sidebar Menu -->
      <ul class="sidebar-menu">
        @foreach ($carpetas as $carpeta)
          <li class="treeview">
            <!--a href="#" style="background-color: #F4F4F4; color:black;"><i class='fa fa-folder-open-o'></i> <span>Carpeta</span><i class="fa fa-angle-left pull-right"></i></a-->
            <a href="#" style="background-color: white; color:black;"><i class='fa fa-folder-open-o'></i> <span>{{ $carpeta->nombre}}</span><i class="fa fa-angle-left pull-right"></i></a>
            <ul class="treeview-menu" style="background-color: white; color:black;">
              @foreach ($archivados as $archivado)
                @if($archivado->id_carpeta == $carpeta->id_carpeta)
                  <!--li><a href="{{ url('listado_usuarios') }}" style="background-color: white; color:black;">&nbsp;&nbsp;&nbsp;&nbsp; <i class="fa fa-file-o"></i>{{ $archivado->cite." - ".$archivado->proveido }}</a></li-->
                  <?php
                    //Codificamos el cite
                    $cite_codificado = base64_encode($archivado->cite);
                    $ruta_vuelta_codificado = base64_encode("archivados");
                  ?>
                  <li><a href="{{ url('imprimir_comunicacion_interna_todo/'.$cite_codificado.'/'.$ruta_vuelta_codificado) }}" style="background-color: white; color:black;">&nbsp;&nbsp;&nbsp;&nbsp; <i class="fa fa-file-o"></i>{{ $archivado->cite." - ".$archivado->proveido }}</a></li>
                @endif
              @endforeach
            </ul>
          </li>
        @endforeach
      </ul>
      <!-- /.sidebar-menu -->
    </section>
  </div>
  <!-- /.mail-box-messages -->
</div>
<!-- /.box-body -->
<div class="box-footer no-padding">
  <div class="mailbox-controls">
  </div>
</div>
@endsection
