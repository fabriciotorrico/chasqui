@extends('layouts.app')

@section('htmlheader_title')
	Home
@endsection


@section('main-content')
	<div class="container spark-screen">
		<div class="row">
			{{-- <div class="col-md-10 col-md-offset-1">
				<div class="panel panel-default">
					<div class="panel-heading">Bienvenid@</div>

					<div class="panel-body">
						{{ trans('adminlte_lang::message.logged') }}
					</div>\
				</div>
			</div> --}}

			<div style="text-align:center">
				<h3><b>Bienvenido al Sistema de Correspondencia</b></h3>
				<img src="{{asset('img/chasqui_diseño_negro.png')}}" style="width:500px;height:450px;" class="centered"/>
				<!--img src="{{asset('img/minculturas-logo.png')}}" style="width:460px;height:100px;" class="centered"/-->
			</div>
		</div>
	</div>
@endsection
