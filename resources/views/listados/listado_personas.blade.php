@extends('layouts.app')

@section('htmlheader_title')
	Home
@endsection


@section('main-content')


<section  id="contenido_principal">

@include('menu.menu_directorio')



<div class="box box-primary">
		<div id='table_responsive' style='padding: 10 10 10 10; min-height: 700px;' >

		   <table class="table table-bordered" id="tabla-empresas">
           <thead>
              <th colspan='7'>

                
                <form  method="get" action="{{ url('buscar_persona') }}">
                   <div class="input-group input-group-sm pull-right">
                    <input type="text" class="form-control" id='dato_buscado' name='dato_buscado' >
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-info btn-flat" >buscar!</button>
                    </span>
                  </div>

                   <div class="input-group input-group-sm pull-right">
                   <select class='form-control' name='ciudad' >
                   <option disabled selected >...</option>
                   <option>cali</option>
                   <option>armenia</option>
                   </select>
                   </div>
                </form>
              </th>
           </thead>
		       <thead>
		        <th><a href='{{ url("listado_personas/id/$orden") }}'> Id <i class="fa fa-fw fa-chevron-down pull-right"></i></a>  </th>
		        <th><a href='{{ url("listado_personas/nom_persona/$orden") }}'> Persona <i class="fa fa-fw fa-chevron-down pull-right"></i></a> </th>
		        <th ><a href='{{ url("listado_personas/telefono/$orden") }}'>telefono <i class="fa fa-fw fa-chevron-down pull-right"></i></a></th>
		        <th><a href='{{ url("listado_personas/email/$orden") }}'>email <i class="fa fa-fw fa-chevron-down pull-right"></i></a></th>
		        <th><a href='{{ url("listado_personas/ciudad/$orden") }}'>ciudad <i class="fa fa-fw fa-chevron-down pull-right"></i></a></th>
		        <th><a href='{{ url("listado_personas/direccion/$orden") }}'>direccion <i class="fa fa-fw fa-chevron-down pull-right"></i></a></th>
		        <th>accion</th>    
		      </thead>
            <tbody>
                @foreach($listado as $fila)

                <tr>
                       <td>{{ $fila->id  }}</td>
                       <td>{{ $fila->nom_persona }}</td>
                       <td>{{ $fila->telefono  }}</td>
                       <td>{{ $fila->email }}</td>
                       <td>{{ $fila->ciudad }}</td>
                       <td>{{ $fila->direccion }}</td>
                       <td><a href='{{ url('form_editar_contacto/'.$fila->id)  }}' class='btn btn-xs btn-primary' >Editar</a></td>
                </tr>
                @endforeach

            </tbody>
		    </table>
        <!--    para paginacion normal sin filtros usar  $users->links()  -->
           

            {{ $listado->appends(Request::only(['dato_buscado','ciudad']) )->render() }}
		 </div>   
	
</div>  


</section>

@section('scripts')
@parent


@endsection


@endsection