@extends('backpack::layout')


@section('header')
	<section class="content-header">
		<legend><h3 class="text-muted">Categories</h3></legend>
      <ol class="breadcrumb">
          <li>
              <a href="{{ url('maintenance/category') }}">category</a>
          </li>
          <li class="active">Create</li>
      </ol>
	</section>
@endsection

@section('content')
<!-- Default box -->
  <div class="box">
    <div class="box-body">
        {{ Form::open(array('class' => 'form-horizontal','method'=>'post','route'=>'category.store','id'=>'categoryForm')) }}
            @if (count($errors) > 0)
                <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <ul style='margin-left: 10px;'>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        <div class="col-md-offset-3 col-md-6" style="padding:10px;">
          <div class="form-group">
            <div class="col-md-12">
              {{ Form::label('name','Name') }}
              {{ Form::text('name',Input::old('name'),[
                'class'=>'form-control',
                'placeholder'=>'Category Name'
              ]) }}
            </div>
          </div>
          <div class="form-group">
            <div class="col-md-12">
              {{ Form::label('code','UACS Code') }}
              {{ Form::text('code',Input::old('code'),[
                'class'=>'form-control',
                'placeholder'=>'UACS Code'
              ]) }}
            </div>
          </div>
          <div class="pull-right">
            <div class="btn-group">
              <button id="submit" class="btn btn-md btn-primary" type="submit">
                <span class="hidden-xs">Submit</span>
              </button>
            </div>
              <div class="btn-group">
                <button id="cancel" class="btn btn-md btn-default" type="button" onClick="window.location.href='{{ url("maintenance/category") }}'" >
                  <span class="hidden-xs">Cancel</span>
                </button>
              </div>
          </div>
      </div> <!-- centered  -->
      {{ Form::close() }}

    </div><!-- /.box-body -->
  </div><!-- /.box -->

@endsection