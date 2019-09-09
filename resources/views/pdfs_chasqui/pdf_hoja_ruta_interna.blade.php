<head>
  <meta charset="utf-8">
  <link href="{{ asset('/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css" />
</head>
<body>
  <p class="titulo"></p>
  <!-- ENCABEZADO -->
  <table style="width:100%; height:4%;">
    <tr>
      <th width="20%"><img src="{{asset('/img/logochacana.jpg')}}" style="width:180px; height:50px;"/></th>
      <th width="60%">
        <p class="titulo">HOJA DE RUTA INTERNA</p>
        <p class="titulo">{{ $datos["cite"]}}</p>
      </th>
      <th width="20%"><img src="{{asset('/img/chasqui_diseño_color.png')}}" style="width:180px;height:50px;"/></th>
    </tr>
  </table>
  <br>

  <!-- DERIVACION INICIAL -->
  <!--table style="width:100%; height:44%; max-height: 100px; border-collapse: collapse; margin: 0 0 1em 0" border=1-->
  <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0;" border="1">
    <tr>
      <td colspan="1" class="color-titulo"><p class="texto color-titulo"><b>Remitente: </b> </p></td>
      <td colspan="7"><p class="texto">{{ $datos["remitente"]}}</p></td>
    </tr>

    <tr>
      <td  class="color-titulo" colspan="1"><b><p class="texto"><b>Destinatario: </b></p></td>
      <td colspan="7"><p class="texto">{{ $datos["destinatario"]}}</p></td>
    </tr>

    @if($destinatarios_copia != [])
      <tr>
        <td class="color-titulo" colspan="1"><b><p class="texto"><b>Con copia: </b></p></td>
        <td colspan="7">
            <?php
            //Controlamos que no muestre mas de tres usuarios como copias
              $suma_con_copia=1;
            ?>
            @foreach($destinatarios_copia as $destinatario_copia)
              @if($suma_con_copia<=3)
                <p class="texto"> * {{ $destinatario_copia}}</p>
              @endif
              <?php $suma_con_copia=$suma_con_copia+1;?>
            @endforeach
            @if($suma_con_copia>4)
              <p class="texto"> * ... (Ver más en sistema)</p>
            @endif
        </td>
      </tr>
    @endif

    <tr>
      <td class="color-titulo" colspan="1"><b><p class="texto"><b>Referencia: </b></p></td>
      <td colspan="7"><p class="texto">{{ $datos["referencia"]}}</p></td>
    </tr>

    <tr>
      <td class="color-titulo" colspan="1"><b><p class="texto"><b>Instrucción: </b></p></td>
      <td colspan="7">
        <p class="texto">{{ $datos["instruccion"]}}
        @if($datos["id_instruccion"] == 999999)
          {{ ": ".$datos["instruccion_otro"]}}
        @endif
        </p>
      </td>
    </tr>

    <tr>
      <td class="color-titulo" width="20%"><b><p class="texto"><b>Páginas Agregadas: </b></p></td>
      <td align="center" width="5%"><p class="texto">{{ $datos["nro_paginas_agregadas"]}}</p></td>

      <td class="color-titulo" width="10%"><b><p class="texto"><b>Plazo: </b></p></td>
      <td align="center"><p class="texto">
        @if($datos["prioridad"] == "Alta")
          {{ $datos["plazo"]}} días a partir de fecha {{ $datos["fecha_creacion"]}}
        @else
          ---
        @endif
        </p>
      </td>

      <td align="center" class="texto"><i class="fa fa-calendar"></i> {{ $datos["fecha_creacion"]}}</td>
      <td align="center" class="texto"><i class="fa fa-clock-o"></i> {{ $datos["hora_creacion"]}}</td>

      <td class="color-titulo" width="10%"><b><p class="texto"><b>Derivación: </b></p></td>
      <td align="center" width="2%"><p class="texto">1</p></td>
    </tr>
  </table>

  <!-- CUADRO PARA OTRAS DERIVACIONES -->
  <?php for($i = 2; $i <= 4; $i++) {?>
    <table style="width:100%; border-collapse: collapse; margin: 0 0 0 0; border:1px solid black; border-bottom:0">
      <tr>
        <td class="color-titulo borde-marcado" width="20%"><p class="texto"><b>Remitente: </b> </p></td>
        <td class="borde-marcado"></td>
      </tr>

      <tr>
        <td class="color-titulo borde-marcado"><b><p class="texto"><b>Destinatario: </b></p></td>
        <td class="borde-marcado"></td>
      </tr>


      <tr>
        <td class="color-titulo borde-marcado"><b><p class="texto"><b>Con copia: </b></p></td>
        <td class="borde-marcado"></td>
      </tr>

      <tr>
        <td class="color-titulo borde-marcado"><b><p class="texto"><b>Referencia: </b></p></td>
        <td class="borde-marcado"></td>
      </tr>
    </table>
    <table style="width:100%; border-collapse: collapse; margin: 0 0 1em 0" border=1>
      <tr>
        <th class="color-titulo" colspan="2" width="34%"><b><p class="texto"><b>Instrucción </b></p></th>

        <td class="color-titulo" width="15%"><b><p class="texto"><b>Páginas Agregadas: </b></p></td>
        <td width="5%"></td>

        <td class="color-titulo" width="10%"><b><p class="texto"><b>Plazo: </b></p></td>
        <td width="20%"></td>

        <td class="color-titulo" width="12%"><b><p class="texto"><b>Derivación: </b></p></td>
        <td align="center" class="texto" width="3%"><p class="texto">{{$i}}</p></td>
      </tr>

      <tr>
        <td><p class="texto-instrucciones">{{$instrucciones[0]}}</p></td>
        <td width="3%"></td>

        <td style="vertical-align: top; text-align: center" colspan="3" rowspan={{$suma_instrucciones}} width="33%"><p class="texto">Sello / Firma de Recepción</p></td>
        <td style="vertical-align: top; text-align: center" colspan="3" rowspan={{$suma_instrucciones}} width="33%"><p class="texto">Proveido (Otra Instrucción), Firma / Sello</p></td>
      </tr>

      <?php $flag=0?>
      @foreach($instrucciones as $instruccion)
        @if($flag==1)
          <tr>
            <td><p class="texto-instrucciones">{{$instruccion}}</p></td>
            <td></td>
          </tr>
        @endif
        <?php $flag=1?>
      @endforeach

      <tr>
        <td><p class="texto-instrucciones">Otro</p></td>
        <td></td>

        <td><p class="texto"><i class="fa fa-calendar"></i></p></td>
        <td colspan="2"><p class="texto"><i class="fa fa-clock-o"></i></p></td>

        <td><p class="texto"><i class="fa fa-calendar"></i></p></td>
        <td colspan="2"><p class="texto"><i class="fa fa-clock-o"></i></p></td>
      </tr>
    </table>
  <?php }?>
  <p align="center" style="font-size: 12px">Página 1</p>
</body>

<style>
  .titulo {font: bolt 100% cursive;
           margin: 1px;}

  .sangria {margin-left: 3%;
            margin-right: 3%;
            margin-top: 3px;}

  .texto {font: 13px cursive;
           margin: 1px;
           margin-left: 3%;
           margin-right: 3%;
           margin-top: 3px;}

   .texto-instrucciones {font-size:11px;}

   .color-titulo {background-color: #C2D2D4;}

   .borde-marcado {border:1px solid black; border-bottom:0}
</style>
