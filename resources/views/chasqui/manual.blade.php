@extends('layouts.app')

@section('htmlheader_title')
	Home
@endsection

@section('main-content')
<section class="content-header">
  <h1 style="text-align: center"> Manual de Usuario </h1>
</section>

<section class="content">
    <div class="col-md-12">
      <div class="box box-primary">
				<embed src="{{asset('/documentos/manual.pdf')}}" type="application/pdf" width="100%" height="750px" />
      </div>
    </div>
  </div>
</section>
@endsection
