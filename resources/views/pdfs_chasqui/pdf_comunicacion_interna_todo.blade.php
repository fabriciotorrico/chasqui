<head>
  <meta charset="utf-8">
  <link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body>
    <?php $posicion=0; ?>
    @foreach($derivaciones as $derivacion)
      <?php $posicion=$posicion+1; ?>
      @if( $posicion%2 != 0)
        <p class="titulo">.</p>
        <!-- ENCABEZADO -->
        <table style="width:100%; height:4px;">
          <tr>
            <th width="20%"><img src="{{asset('/img/logochacana.jpg')}}" style="width:180px; height:50px;"/></th>
            <th width="60%">
              <p class="titulo">COMUNICACIÓN INTERNA</p>
              <p class="titulo">{{ $datos["cite"] }}</p>
            </th>
            <th width="20%"><img src="{{asset('/img/chasqui_diseño_color.png')}}" style="width:180px;height:50px;"/></th>
          </tr>
        </table>
        <br>

        <!-- DERIVACION POSICION 1 (impar)-->
        <table style="width:100%; height:535px; max-height: 100px; border-collapse: collapse; margin: 0 0 1em 0" border=1>
          <tr style="height:20px;">
            <th class="texto" colspan="5"><b>Información de la derivación</b></th>
          </tr>
          <tr>
            <!--td colspan="5" style="vertical-align: top;"-->
            <td colspan="5">
              <p class="texto"><b>Remitente: </b> {{ $derivacion->nombres_origen." ".$derivacion->paterno_origen." ".$derivacion->materno_origen." - ".$derivacion->nombre_del_cargo_origen." - ".$derivacion->sigla_origen}}</p>
              <p class="texto"><b>Destinatario: </b> {{ $derivacion->nombres_destino." ".$derivacion->paterno_destino." ".$derivacion->materno_destino." - ".$derivacion->nombre_del_cargo_destino." - ".$derivacion->sigla_destino}}</p>
              <?php
                //Establecemos los parametros para identificar si tiene o no copias
                $mostrar_titulo_copia = 1;
                $tenia_copias = 0;
                $referencia_copia = $derivacion->referencia." - COPIA";
              ?>
              @foreach($derivaciones_copia as $derivacion_copia)
                @if($derivacion_copia->referencia == $referencia_copia)

                  @if($mostrar_titulo_copia == 1)
                    <p class="texto">
                    <b>Con copia: </b>
                    <?php
                      $mostrar_titulo_copia = 0;
                      $tenia_copias = 1;
                    ?>
                  @endif
                  <br> * {{ $derivacion_copia->nombres." ".$derivacion_copia->paterno." ".$derivacion_copia->materno." - ".$derivacion_copia->nombre_del_cargo." - ".$derivacion_copia->sigla }}
                @endif
              @endforeach
              @if($tenia_copias == 1)</p>@endif
              <p class="texto"><b>Instrucción: </b> {{ $derivacion->instruccion }}</p>
              <p class="texto" align="justify"><b>Referencia: </b> {{ $derivacion->referencia }}</p>
              <p class="texto" align="justify"><b>Contenido: </b> {{ $derivacion->contenido }}</p>
            </td>
          </tr>
          <tr style="height:20px;">
            <th colspan="2" class="texto"><b>Remitente</b></th>
            <th colspan="2" class="texto"><b>Recepción</b></th>
            <th class="texto"><b>Observaciones</b></th>
          </tr>
          <tr style="height: 145px;">
            <th colspan="2" width="33%"></th>
            <th colspan="2" rowspan="2" width="33%"></th>
            <td rowspan="2" width="34%" style="vertical-align: top;">
              <p class="texto"><b>Número de páginas agregadas: </b> {{ $derivacion->nro_paginas_agregadas }}</p>
              @if( $derivacion->prioridad == "Alta")
              <p class="texto"><b>Plazo: </b> {{ $derivacion->plazo }} días a partir del {{ $derivacion->fecha_creacion }}</p>
              @endif
            </td>
          </tr>
          <tr style="height:20px;">
            <td align="center" class="texto"><i class="fa fa-calendar"></i> {{ strstr($derivacion->fecha_creacion, ' ', true) }}</td>
            <td align="center" class="texto"><i class="fa fa-clock-o"></i> {{ substr($derivacion->fecha_creacion, -8) }}</td>
          </tr>
        </table>
      @else
        <!-- DERIVACION POSICION 2 (par)-->
        <br>
        <table style="width:100%; height:535px; max-height: 100px; border-collapse: collapse; margin: 0 0 1em 0" border=1>
          <tr style="height:20px;">
            <th class="texto" colspan="5"><b>Información de la derivación</b></th>
          </tr>
          <tr>
            <!--td colspan="5" style="vertical-align: top;"-->
            <td colspan="5">
              <p class="texto"><b>Remitente: </b> {{ $derivacion->nombres_origen." ".$derivacion->paterno_origen." ".$derivacion->materno_origen." - ".$derivacion->nombre_del_cargo_origen." - ".$derivacion->sigla_origen}}</p>
              <p class="texto"><b>Destinatario: </b> {{ $derivacion->nombres_destino." ".$derivacion->paterno_destino." ".$derivacion->materno_destino." - ".$derivacion->nombre_del_cargo_destino." - ".$derivacion->sigla_destino}}</p>
              <?php
                //Establecemos los parametros para identificar si tiene o no copias
                $mostrar_titulo_copia = 1;
                $tenia_copias = 0;
                $referencia_copia = $derivacion->referencia." - COPIA";
              ?>
              @foreach($derivaciones_copia as $derivacion_copia)
                @if($derivacion_copia->referencia == $referencia_copia)

                  @if($mostrar_titulo_copia == 1)
                    <p class="texto">
                    <b>Con copia: </b>
                    <?php
                      $mostrar_titulo_copia = 0;
                      $tenia_copias = 1;
                    ?>
                  @endif
                  <br> * {{ $derivacion_copia->nombres." ".$derivacion_copia->paterno." ".$derivacion_copia->materno." - ".$derivacion_copia->nombre_del_cargo." - ".$derivacion_copia->sigla }}
                @endif
              @endforeach
              @if($tenia_copias == 1)</p>@endif
              <p class="texto"><b>Instrucción: </b> {{ $derivacion->instruccion }}</p>
              <p class="texto" align="justify"><b>Referencia: </b> {{ $derivacion->referencia }}</p>
              <p class="texto" align="justify"><b>Contenido: </b> {{ $derivacion->contenido }}</p>
            </td>
          </tr>
          <tr style="height:20px;">
            <th colspan="2" class="texto"><b>Remitente</b></th>
            <th colspan="2" class="texto"><b>Recepción</b></th>
            <th class="texto"><b>Observaciones</b></th>
          </tr>
          <tr style="height: 145px;">
            <th colspan="2" width="33%"></th>
            <th colspan="2" rowspan="2" width="33%"></th>
            <td rowspan="2" width="34%" style="vertical-align: top;">
              <p class="texto"><b>Número de páginas agregadas: </b> {{ $derivacion->nro_paginas_agregadas }}</p>
              @if( $derivacion->prioridad == "Alta")
              <p class="texto"><b>Plazo: </b> {{ $derivacion->plazo }} días a partir del {{ $derivacion->fecha_creacion }}</p>
              @endif
            </td>
          </tr>
          <tr style="height:20px;">
            <td align="center" class="texto"><i class="fa fa-calendar"></i> {{ strstr($derivacion->fecha_creacion, ' ', true) }}</td>
            <td align="center" class="texto"><i class="fa fa-clock-o"></i> {{ substr($derivacion->fecha_creacion, -8) }}</td>
          </tr>
        </table>
      @endif
    @endforeach

    <?//Imprimimos derivaciones?>
    @foreach($archivados as $archivado)
      <?php $posicion=$posicion+1; ?>
      @if( $posicion%2 != 0)
        <p class="titulo">.</p>
        <!-- ENCABEZADO -->
        <table style="width:100%; height:4px;">
          <tr>
            <th width="20%"><img src="{{asset('/img/logochacana.jpg')}}" style="width:180px; height:50px;"/></th>
            <th width="60%">
              <p class="titulo">COMUNICACIÓN INTERNA</p>
              <p class="titulo">{{ $datos["cite"] }}</p>
            </th>
            <th width="20%"><img src="{{asset('/img/chasqui_diseño_color.png')}}" style="width:180px;height:50px;"/></th>
          </tr>
        </table>
        <br>

        <!-- ARCHIVADO POSICION 1 (impar)-->
        <table style="width:100%; height:250px; border-collapse: collapse; margin: 0 0 1em 0" border=1>
          <tr style="height:20px;">
            <th class="texto" colspan="5"><b>Documento archivado</b></th>
          </tr>
          <tr>
            <!--td colspan="5" style="vertical-align: top;"-->
            <td colspan="2">
              <p class="texto"><b>Servidor que archivó el documento: </b> {{ $archivado->nombres." ".$archivado->paterno." ".$archivado->materno." - ".$archivado->nombre_del_cargo." - ".$archivado->sigla}}</p>
              <p class="texto"><b>Carpeta en la que se archivó el documento: </b> {{ $archivado->nombre_de_carpeta }}</p>
              <p class="texto" align="justify"><b>Proveido: </b> {{ $archivado->proveido }}</p>
            </td>
          </tr>
          <tr style="height:20px;">
            <td align="center" class="texto"><i class="fa fa-calendar"></i> {{ strstr($archivado->timestamp, ' ', true) }}</td>
            <td align="center" class="texto"><i class="fa fa-clock-o"></i> {{ substr($archivado->timestamp, -8) }}</td>
          </tr>
        </table>
      @else
        <!-- ARCHIVADO POSICION 2 (par)-->
        <br>
        <table style="width:100%; height:250px; border-collapse: collapse; margin: 0 0 1em 0" border=1>
          <tr style="height:20px;">
            <th class="texto" colspan="5"><b>Documento archivado</b></th>
          </tr>
          <tr>
            <!--td colspan="5" style="vertical-align: top;"-->
            <td colspan="2">
              <p class="texto"><b>Servidor que archivó el documento: </b> {{ $archivado->nombres." ".$archivado->paterno." ".$archivado->materno." - ".$archivado->nombre_del_cargo." - ".$archivado->sigla}}</p>
              <p class="texto"><b>Carpeta en la que se archivó el documento: </b> {{ $archivado->nombre_de_carpeta }}</p>
              <p class="texto" align="justify"><b>Proveido: </b> {{ $archivado->proveido }}</p>
            </td>
          </tr>
          <tr style="height:20px;">
            <td align="center" class="texto"><i class="fa fa-calendar"></i> {{ strstr($archivado->timestamp, ' ', true) }}</td>
            <td align="center" class="texto"><i class="fa fa-clock-o"></i> {{ substr($archivado->timestamp, -8) }}</td>
          </tr>
        </table>
      @endif
    @endforeach
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
