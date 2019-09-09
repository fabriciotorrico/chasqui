@extends('chasqui.bandeja')

@section('contenido-bandejas')
<div class="box-header with-border">
  <h3 class="box-title">Buscar comunicación interna</h3>
</div>
<div class="box-body no-padding">
  <div class="table-responsive mailbox-messages">
    <section class="sidebar">
      <!-- Sidebar Menu -->
      <form class="" action="{{ url('seguimiento') }}" method="post">
        <p style="text-align:center; "></p>
        <div class="input-group input-group-sm">
          <input type="text" class="form-control" name="buscar" placeholder="Introduzca el cite (completo o solo el número), la referencia o el contenido de la comunicación interna que desea buscar.">
          <span class="input-group-btn">
            <button type="submit" class="btn btn-info btn-flat">Buscar</button>
          </span>
        </div>
      </form>
      <br>
      @foreach($cites as $cite)
        <?php
          $cite_codificado = base64_encode($cite->cite);
          $ruta_vuelta_codificado = base64_encode("seguimiento");
        ?>
        <p><a href="{{ url('imprimir_comunicacion_interna_todo/'.$cite_codificado.'/'.$ruta_vuelta_codificado) }}" style="background-color: white; color:black;">&nbsp;&nbsp;&nbsp;&nbsp; <i class="fa fa-file-o"></i> {{ $cite->cite }}</a></p>
      @endforeach

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
