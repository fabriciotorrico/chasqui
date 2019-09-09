<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

        <!-- Sidebar user panel (optional) -->
        @if (! Auth::guest())
          <div class="user-panel">
              <div class="pull-left image">
                @foreach ($personas as $persona)
                  @if ( $persona->id_persona == Auth::user()->id_persona)
                    <img src="{{asset('/fotos_servidores/'.$persona->nombre_foto)}}" class="img-circle" alt="User Image" />
                  @endif
                @endforeach
              </div>
              <div class="pull-left info">
                @foreach ($personas as $persona)
                  @if ( $persona->id_persona == Auth::user()->id_persona)
                    {{--Verificamos si tiene mas de un nombre, viendo si existe espacio en la cadena--}}
                    @if (strpos($persona->nombres, " "))
                      <p>{{ strstr($persona->nombres, ' ', true)." ".$persona->paterno." ".substr($persona->materno, 0, 1) }}</p>
                    @else
                      <p>{{ $persona->nombres." ".$persona->paterno." ".substr($persona->materno, 0, 1) }}</p>
                    @endif
                    <p><i class="fa fa-caret-right text-yellow"></i> {{ $persona->sigla_del_area }}</p>
                  @endif
                @endforeach
              </div>
          </div>
        @endif

        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">

          <li class="header">MENU</li>

          <li class="treeview">
              <a href="{{ url('bandeja') }}"><i class='fa fa-envelope-o'></i> <span>CORRESPONDENCIA</span></a>
              <!--ul class="treeview-menu">
                <li><a href="javascript:void(0);" onclick="mostrar_formulario(1);">Nueva</a></li>
                <li><a href="{{ url('bandeja') }}">Recibida</a></li>
                <li><a href="{{ url('reporte_usuarios') }}">Enviada</a></li>
                <li><a href="{{ url('reporte_usuarios') }}">Archivada</a></li>
                <li><a href="javascript:void(0);" onclick="cargar_formulario(1);">Agregar Usuario</a></li>
              </ul-->
          </li>

          <!--li class="treeview">
              <a href="#"><i class='fa fa-search'></i> <span>SEGUIMIENTO</span><i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li><a href="{{ url('listado_usuarios') }}">Gestión Actual</a></li>
                <li><a href="{{ url('reporte_usuarios') }}">Gestiones Pasadas</a></li>
              </ul>
          </li-->

          <!--li class="treeview">
              <a href="#"><i class='fa fa-calendar'></i> <span>HISTÓRICO</span><i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li><a href="#">Gestión 2018</a></li>
                <li><a href="#">Gestiòn 2017</a></li>
              </ul>
          </li-->

          <!--li class="treeview">
              <a href="#"><i class='fa fa fa-folder-open'></i> <span>DOCUMENTOS</span><i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu">
                <li><a href="{{ url('listado_usuarios') }}">Nuevo</a></li>
                <li><a href="{{ url('reporte_usuarios') }}">Realizados</a></li>
              </ul>
          </li-->

          <li class="treeview">
              <a href="{{ url('manual') }}"><i class='fa fa-file-text'></i> <span>MANUAL</span></a>
          </li>

          <li class="treeview">
            <a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"> <i class='fa fa-power-off'></i> <span>SALIR</span> </a>
          </li>


        </ul><!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
</aside>
