<head>
  <meta charset="utf-8">
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
  <table style="width:100%; height:40%; max-height: 100px;" border=1>
    <tr>
      <td>
        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0;" border=1>
          <tr>
            <th class="titulo"><b>Datos de los serivodores públicos</b></th>
          </tr>
          <tr>
            <td>
              <p class="titulo sangria"><b>Remitente: </b> {{ $datos["remitente"]}}</p>
              <p class="titulo sangria"><b>Destinatario: </b> {{ $datos["destinatario"]}}</p>
              @if($destinatarios_copia != [])
              <p class="titulo sangria"><b>Con copia: </b>
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
            <th class="titulo"><b>Datos del documento</b></th>
          </tr>
          <tr>
            <td>
              <p class="titulo sangria"><b>Instrucción: </b> {{ $datos["instruccion"]}}</p>
              <p class="titulo sangria" align="justify"><b>Referencia: </b> {{ $datos["referencia"]}}</p>
              <p class="titulo sangria"><b>Contenido: </b> {{ $datos["contenido"]}}</p>
              <p class="titulo sangria"><b>Número de páginas agregadas: </b> {{ $datos["nro_paginas_agregadas"]}}</p>
              <p class="titulo sangria"><b>Prioridad: </b> {{ $datos["prioridad"]}}</p>
              @if($datos["prioridad"] == "Alta")
              <p class="titulo sangria"><b>Plazo: </b> {{ $datos["plazo"]}} días a partir del {{ $datos["fecha_creacion"]}}</p>
              @endif
            </td>
          </tr>
        </table>

        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0; margin-bottom: 0" border=1>
          <tr>
            <th colspan="2">Entrega</th>
            <th colspan="2">Recepción</th>
            <th>Observaciones</th>
          </tr>
          <tr style="height: 100px;">
            <th colspan="2" width="30%">-----------------------------------</th>
            <th colspan="2" width="30%"></th>
            <th rowspan="2" width="40%"></th>
          </tr>
          <tr>
            <td>Fecha:</td>
            <td>Hora:</td>
            <td>Fecha:</td>
            <td>Hora:</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <p class="titulo"> — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — — —  </p>
  <table style="width:100%; height:40%; max-height: 100px;" border=1>
    <tr>
      <td>
        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0;" border=1>
          <tr>
            <th class="titulo"><b>Datos de los serivodores públicos</b></th>
          </tr>
          <tr>
            <td>
              <p class="titulo sangria"><b>Remitente: </b> {{ $datos["remitente"]}}</p>
              <p class="titulo sangria"><b>Destinatario: </b> {{ $datos["destinatario"]}}</p>
              @if($destinatarios_copia != [])
              <p class="titulo sangria"><b>Con copia: </b>
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
            <th class="titulo"><b>Datos del documento</b></th>
          </tr>
          <tr>
            <td>
              <p class="titulo sangria"><b>Instrucción: </b> {{ $datos["instruccion"]}}</p>
              <p class="titulo sangria" align="justify"><b>Referencia: </b> {{ $datos["referencia"]}}</p>
              <p class="titulo sangria"><b>Contenido: </b> {{ $datos["contenido"]}}</p>
              <p class="titulo sangria"><b>Número de páginas agregadas: </b> {{ $datos["nro_paginas_agregadas"]}}</p>
              <p class="titulo sangria"><b>Prioridad: </b> {{ $datos["prioridad"]}}</p>
              @if($datos["prioridad"] == "Alta")
              <p class="titulo sangria"><b>Plazo: </b> {{ $datos["plazo"]}} días a partir del {{ $datos["fecha_creacion"]}}</p>
              @endif
            </td>
          </tr>
        </table>

        <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0; margin-bottom: 0" border=1>
          <tr>
            <th colspan="2">Entrega</th>
            <th colspan="2">Recepción</th>
            <th>Observaciones</th>
          </tr>
          <tr style="height: 100px;">
            <th colspan="2" width="30%">-----------------------------------</th>
            <th colspan="2" width="30%"></th>
            <th rowspan="2" width="40%"></th>
          </tr>
          <tr>
            <td>Fecha:</td>
            <td>Hora:</td>
            <td>Fecha:</td>
            <td>Hora:</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>

<style>
  .titulo {font: coursive 100% cursive;
           margin: 1px;}

  .sangria {margin-left: 3%;
            margin-right: 3%;
            margin-top: 3px;}

</style>
