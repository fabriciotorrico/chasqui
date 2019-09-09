<?php echo "hay" ?>
@extends('layouts.app')

@section('htmlheader_title')
	Home
@endsection

@section('main-content')
<section class="content-header">
	@if(isset($mensaje_exito))
		<div class="alert" style="background-color:#B3E7CB;">
	    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	    <h4><i class="icon fa fa-check"></i> Acción realizada con éxito</h4>
			{{ $mensaje_exito }}
	  </div>
	@endif
  <h1>
    Bandeja <!--small>13 new messages</small-->
  </h1>
</section>
@endsection
