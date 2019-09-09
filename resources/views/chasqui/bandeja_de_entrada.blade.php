@extends('chasqui.bandeja')

@section('contenido-bandejas')
<div class="box-header with-border">
  <h3 class="box-title">Bandeja de entrada</h3>

  <!--div class="box-tools pull-right">
    <div class="has-feedback">
      <input type="text" class="form-control input-sm" placeholder="Search Mail">
      <span class="glyphicon glyphicon-search form-control-feedback"></span>
    </div>
  </div-->
</div>
<div class="box-body no-padding">
  <div class="mailbox-controls">
    <!-- Check all button -->
    <!--button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i>
    </button-->

    <a href="{{ url('bandeja') }}" class="btn btn-default btn-sm fa fa-refresh"></a>
    <div class="btn-group">
      <!--button type="button" class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
      <button type="button" class="btn btn-default btn-sm"><i class="fa fa-reply"></i></button-->
    </div>

    <!--div class="pull-right">
      1-50/200
      <div class="btn-group">
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-chevron-left"></i></button>
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-chevron-right"></i></button>
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-eye"> Ver </i></button>
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-share"> Responder </i></button>
      </div>
    </div-->
  </div>
  <div class="table-responsive mailbox-messages">
    <table class="table table-hover table-striped">
      <tr>
        <th width="23%" style="text-align: center;">Remitente</th>
        <th>Cite - Referencia</th>
        <th width="18%">Recibido</th>
        <th width="5%" colspan="2" style="text-align: center;">Acción</th>
      </tr>
      <tbody>
        <?php $pos=0;?>
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
                @if ($derivacion->prioridad == "Alta")
                  <i class="fa fa-hourglass-half"></i>&nbsp;&nbsp;&nbsp;&nbsp;
                  @if ($array_plazo[$pos] > 0)
                    <span class="label pull-left bg-orange">
                      {{ "".$array_plazo[$pos] }}&nbsp;&nbsp;
                    </span>
                  @else
                    <span class="label pull-left bg-red">
                      {{ "".$array_plazo[$pos] }}&nbsp;&nbsp;
                    </span>
                  @endif
                @endif
                <?php $pos=$pos+1;?>
                @if ($derivacion->fecha_recibido != "0000-00-00 00:00:00")
                  {{ " ".$derivacion->fecha_recibido }}
                @endif

              </td>
              <td>
                <form class="form" action="{{ url('leer_correspondencia') }}" method="post">
                  <input type="hidden" name="id_derivacion" value="{{ $derivacion->id_derivacion }}">
                  <input type="hidden" name="acciones_habilitadas" value="1">
                  <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></button>
                </form>
                <!--a href="leer_correspondencia" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></a-->
              </td>
              <!--td><button type="button" class="btn btn-default btn-sm"><i class="fa fa-share"></i></button></td-->
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
    <!--button type="button" class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i>
    </button>
    <div class="btn-group">
      <button type="button" class="btn btn-default btn-sm"><i class="fa fa-trash-o"></i></button>
      <button type="button" class="btn btn-default btn-sm"><i class="fa fa-reply"></i></button>
      <button type="button" class="btn btn-default btn-sm"><i class="fa fa-share"></i></button>
    </div>
    <button type="button" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i></button-->
    <div class="pull-right">
      1-50/200
      <div class="btn-group">
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-chevron-left"></i></button>
        <button type="button" class="btn btn-default btn-sm"><i class="fa fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
</div>
@endsection
