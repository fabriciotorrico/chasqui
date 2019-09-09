<head>
  <meta charset="utf-8">
  <link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body>
  <p class="titulo">.</p>
  <!-- ENCABEZADO -->
  <table style="width:100%; height:4%;">
    <tr>
      <th width="20%"><img src="{{asset('/img/logochacana.jpg')}}" style="width:180px; height:50px;"/></th>
      <th width="60%">
        <p class="titulo">COMUNICACIÓN INTERNA</p>
        <p class="titulo">{{ $datos["cite"]}}</p>
      </th>
      <th width="20%"><img src="{{asset('/img/chasqui_diseño_color.png')}}" style="width:180px;height:50px;"/></th>
    </tr>
  </table>
  <br>

  <!-- DERIVACION POSICION 1 -->
  <table style="width:100%; height:45%; max-height: 100px;" border=0>
    <tr>
      <td>
        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0;" border=1>
          <tr>
            <th class="texto"><b>Información de la derivación</b></th>
          </tr>
          <tr>
            <td>
              <p class="texto"><b>Remitente: </b> {{ $datos["remitente"]}}</p>
              <p class="texto"><b>Destinatario: </b> {{ $datos["destinatario"]}}</p>
              @if($destinatarios_copia != [])
              <p class="texto"><b>Con copia: </b>
                @foreach($destinatarios_copia as $destinatario_copia)
                  <br> * {{ $destinatario_copia}}
                @endforeach
              </p>
              @endif
            </td>
          </tr>
        </table>

        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0;" border=1>
          <tr>
            <td>
              <p class="texto"><b>Instrucción: </b> {{ $datos["instruccion"]}}</p>
              <p class="texto" align="justify"><b>Referencia: </b> {{ $datos["referencia"]}}</p>
              <p class="texto" align="justify"><b>Contenido: </b> {{ $datos["contenido"]}}</p>
            </td>
          </tr>
        </table>

        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0; margin-bottom: 0" border=1>
          <tr>
            <th colspan="2" class="texto"><b>Remitente</b></th>
            <th colspan="2" class="texto"><b>Recepción</b></th>
            <th class="texto"><b>Observaciones</b></th>
          </tr>
          <tr style="height: 115px;">
            <th colspan="2" width="33%"></th>
            <th colspan="2" width="33%"></th>
            <td rowspan="2" width="34%" style="vertical-align: top;">
              <p class="texto"><b>Número de páginas agregadas: </b> {{ $datos["nro_paginas_agregadas"]}}</p>
              @if($datos["prioridad"] == "Alta")
              <p class="texto"><b>Plazo: </b> {{ $datos["plazo"]}} días a partir del {{ $datos["fecha_creacion"]}}</p>
              @endif
            </td>
          </tr>
          <tr>
            <td align="center" class="texto"><i class="fa fa-calendar"></i> {{ $datos["fecha_creacion"]}}</td>
            <td align="center" class="texto"><i class="fa fa-clock-o"></i> {{ $datos["hora_creacion"]}}</td>
            <td><i class="texto fa fa-calendar"></i></td>
            <td><i class="sangria fa fa-clock-o"></i></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <p class="titulo"> — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — —  </p>
  <!-- DERIVACION POSICION 2 -->
  <table style="width:100%; height:45%; max-height: 100px;" border=0>
    <tr>
      <td>
        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0;" border=1>
          <tr>
            <th class="texto"><b>Información de la derivación</b></th>
          </tr>
          <tr>
            <td>
              <p class="texto"><b>Remitente: </b> {{ $datos["remitente"]}}</p>
              <p class="texto"><b>Destinatario: </b> {{ $datos["destinatario"]}}</p>
              @if($destinatarios_copia != [])
              <p class="texto"><b>Con copia: </b>
                @foreach($destinatarios_copia as $destinatario_copia)
                  <br> * {{ $destinatario_copia}}
                @endforeach
              </p>
              @endif
            </td>
          </tr>
        </table>

        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0;" border=1>
          <tr>
            <td>
              <p class="texto"><b>Instrucción: </b> {{ $datos["instruccion"]}}</p>
              <p class="texto" align="justify"><b>Referencia: </b> {{ $datos["referencia"]}}</p>
              <p class="texto" align="justify"><b>Contenido: </b> {{ $datos["contenido"]}}</p>
            </td>
          </tr>
        </table>

        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0; margin-bottom: 0" border=1>
          <tr>
            <th colspan="2" class="texto"><b>Remitente</b></th>
            <th colspan="2" class="texto"><b>Recepción</b></th>
            <th class="texto"><b>Observaciones</b></th>
          </tr>
          <tr style="height: 115px;">
            <th colspan="2" width="33%"></th>
            <th colspan="2" width="33%"></th>
            <td rowspan="2" width="34%" style="vertical-align: top;">
              <p class="texto"><b>Número de páginas agregadas: </b> {{ $datos["nro_paginas_agregadas"]}}</p>
              @if($datos["prioridad"] == "Alta")
              <p class="texto"><b>Plazo: </b> {{ $datos["plazo"]}} días a partir del {{ $datos["fecha_creacion"]}}</p>
              @endif
            </td>
          </tr>
          <tr>
            <td align="center" class="texto"><i class="fa fa-calendar"></i> {{ $datos["fecha_creacion"]}}</td>
            <td align="center" class="texto"><i class="fa fa-clock-o"></i> {{ $datos["hora_creacion"]}}</td>
            <td><i class="texto fa fa-calendar"></i></td>
            <td><i class="sangria fa fa-clock-o"></i></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>

<style>
  .titulo {font: bolt 100% cursive;
           margin: 1px;}

  .sangria {margin-left: 3%;
            margin-right: 3%;
            margin-top: 3px;}

  .texto {font: <?php echo $datos["tamaño_texto"];?>% cursive;
           margin: 1px;
           margin-left: 3%;
           margin-right: 3%;
           margin-top: 3px;}

</style>
