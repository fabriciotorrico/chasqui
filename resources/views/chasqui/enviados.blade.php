@extends('chasqui.bandeja')

@section('contenido-bandejas')
<div class="box-header with-border">
  <h3 class="box-title">Correspondencia enviada</h3>
</div>
<div class="box-body no-padding">
  <div class="mailbox-controls">
    <a href="{{ url('enviados') }}" class="btn btn-default btn-sm fa fa-refresh"></a>
  </div>
  <div class="table-responsive mailbox-messages">
    <table class="table table-hover table-striped">
      <tr>
        <th width="23%" style="text-align: center;">Destinatario</th>
        <th>Cite - Referencia</th>
        <th width="18%">Enviado</th>
        <th width="5%" colspan="2" style="text-align: center;">Acción</th>
      </tr>
      <tbody>
        @foreach ($derivaciones as $derivacion)
            <tr>
              <td class="mailbox-name">
                @if (strpos($derivacion->nombres, " "))
                  {{ " ".strstr($derivacion->nombres, ' ', true)." ".$derivacion->paterno." ".substr($derivacion->materno, 0, 1)." - ".$derivacion->sigla }}</p>
                @else
                  {{ " ".$derivacion->nombres." ".$derivacion->paterno." ".substr($derivacion->materno, 0, 1)." - ".$derivacion->sigla }}</p>
                @endif
              </td>
              <td class="mailbox-subject"><b>{{ $derivacion->cite }}</b> - {{ $derivacion->referencia }}</td>
              <td class="mailbox-date">
                {{ $derivacion->fecha_creacion }}
              </td>
              <td>
                <form class="form" action="{{ url('leer_correspondencia') }}" method="post">
                  <input type="hidden" name="id_derivacion" value="{{ $derivacion->id_derivacion }}">
                  <input type="hidden" name="acciones_habilitadas" value="2">
                  <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></button>
                </form>
              </td>
            </tr>
        @endforeach
      </tbody>
    </table>
    <!-- /.table -->
  </div>
  <!-- /.mail-box-messages -->
</div>
<!-- /.box-body -->
<div class="box-footer no-padding">
  <div class="mailbox-controls">
    <div class="pull-right">
      <div class="btn-group">
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-chevron-left"></i></button>
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
</div>
@endsection
