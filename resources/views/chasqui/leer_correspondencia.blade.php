@extends('chasqui.bandeja')

@section('contenido-bandejas')


@foreach ($derivaciones as $derivacion)
  <div class="box-header with-border text-center">
    <h3 class="box-title">{{ $derivacion->cite}}
      @if ($derivacion->tipo == "Copia")
        {{ "(".$derivacion->tipo.")" }}
      @endif
    </h3>
  </div>

  <div class="box-body no-padding">
    <div class="mailbox-read-info">
      <h4> <b>Remitente: </b>{{ $derivacion->nombres." ".$derivacion->paterno." ".$derivacion->materno." - ".$derivacion->nombre_del_cargo." - ".$derivacion->sigla }}</h4>
      <h4><b>Instrucción: </b>{{ $derivacion->instruccion }}
          <span class="mailbox-read-time pull-right">Enviado: {{ $derivacion->fecha_creacion}}</span>
      </h4>
    </div>
  </div>

  <div class="box-body no-padding">
    <div class="mailbox-read-info">
      <h4> <b>Referencia: </b>{{ $derivacion->referencia }}</h4>
      <h4><b>Contenido: </b>{{ $derivacion->contenido }}</h4>
    </div>
  </div>

  <!-- /.box-body DOCUMENTOS ADJUNTOS-->
  <!--div class="box-footer">
    <ul class="mailbox-attachments clearfix">
      <li>
        <span class="mailbox-attachment-icon"><i class="fa fa-file-pdf-o"></i></span>

        <div class="mailbox-attachment-info">
          <a href="#" class="mailbox-attachment-name"><i class="fa fa-paperclip"></i> Sep2014-report.pdf</a>
              <span class="mailbox-attachment-size">
                1,245 KB
                <a href="#" class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
              </span>
        </div>
      </li>
      <li>
        <span class="mailbox-attachment-icon"><i class="fa fa-file-word-o"></i></span>

        <div class="mailbox-attachment-info">
          <a href="#" class="mailbox-attachment-name"><i class="fa fa-paperclip"></i> App Description.docx</a>
              <span class="mailbox-attachment-size">
                1,245 KB
                <a href="#" class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
              </span>
        </div>
      </li>
      <li>
        <span class="mailbox-attachment-icon has-img"><img src="../../dist/img/photo1.png" alt="Attachment"></span>

        <div class="mailbox-attachment-info">
          <a href="#" class="mailbox-attachment-name"><i class="fa fa-camera"></i> photo1.png</a>
              <span class="mailbox-attachment-size">
                2.67 MB
                <a href="#" class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
              </span>
        </div>
      </li>
      <li>
        <span class="mailbox-attachment-icon has-img"><img src="../../dist/img/photo2.png" alt="Attachment"></span>

        <div class="mailbox-attachment-info">
          <a href="#" class="mailbox-attachment-name"><i class="fa fa-camera"></i> photo2.png</a>
              <span class="mailbox-attachment-size">
                1.9 MB
                <a href="#" class="btn btn-default btn-xs pull-right"><i class="fa fa-cloud-download"></i></a>
              </span>
        </div>
      </li>
    </ul>
  </div-->
  <!-- /.box-footer -->
  <div class="box-footer">

    <!--a href="{{ url('imprimir_correspondencia/'.$derivacion->id_derivacion) }}" class="btn btn-default"> <i class="fa fa-print"></i> Imprimir </a-->
    @if ($acciones_habilitadas == 1)
      @if ($derivacion->tipo == "Copia")
        <!--a href="{{ url('guardar_copia_correspondencia/'.$derivacion->id_derivacion) }}" class="btn btn-default"> <i class="fa fa-download"></i> Guardar copia </a-->
        <form class="form" action="{{ url('guardar_copia_correspondencia') }}" method="post">
          <input type="hidden" name="id_derivacion" value="{{ $derivacion->id_derivacion }}">
          <button type="submit" class="btn btn-default"><i class="fa fa-download"></i> Guardar copia </button>
          <a href="javascript:void(0);" onclick="mostrar_formulario_derivar({{ $derivacion->id_derivacion }});" class="btn btn-default"> <i class="fa fa-share"></i> Derivar</a>
          <a href="javascript:void(0);" onclick="mostrar_formulario_juntar_y_derivar({{ $derivacion->id_derivacion }});" class="btn btn-default"> <i class="fa fa-code-fork"></i> Juntar y Derivar <i class="fa fa-share"></i></a>
        </form>
      @else
        <a href="javascript:void(0);" onclick="mostrar_formulario_archivar({{ $derivacion->id_derivacion }});" class="btn btn-default"> <i class="fa fa-download"></i> Archivar</a>
        <a href="javascript:void(0);" onclick="mostrar_formulario_derivar({{ $derivacion->id_derivacion }});" class="btn btn-default"> <i class="fa fa-share"></i> Derivar</a>
        <a href="javascript:void(0);" onclick="mostrar_formulario_juntar_y_derivar({{ $derivacion->id_derivacion }});" class="btn btn-default"> <i class="fa fa-code-fork"></i> Juntar y Derivar <i class="fa fa-share"></i></a>
      <!--button type="button" class="btn btn-default"><i class="fa fa-share"></i> Derivar </button>

      <form class="form" action="{{ url('leer_correspondencia') }}" method="post">
        <input type="hidden" name="cite" value="{{ $derivacion->cite }}">
        <button type="submit" class="btn btn-default btn-sm"><i class="fa fa-eye"></i></button>
      </form-->
      @endif
    @elseif ($acciones_habilitadas == 2)
      <?php
        //Obteenemos el $cite_codificado
        $cite_codificado = base64_encode($derivacion->cite);
      ?>
      <a href="{{ url('reimprimir_hri/'.$cite_codificado) }}" class="btn btn-default"> <i class="fa fa-print"></i> Reimprimir </a>
    @endif
  </div>
@endforeach
@endsection
