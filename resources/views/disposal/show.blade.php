@extends('backpack::layout')

@section('header')
	<section class="content-header">
		<legend><h3 class="text-muted">{{ $disposal->code }}</h3></legend>
		<ul class="breadcrumb">
			<li><a href="{{ url('disposal') }}">Disposal</a></li>
			<li class="active"> {{ $disposal->code }} </li>
		</ul>
	</section>
@endsection

@section('content')
<!-- Default box -->
  <div class="box">
    <div class="box-body">
		<div class="panel panel-body table-responsive">
	        <a href="{{ url("disposal/$disposal->id/edit") }}" class="btn btn-default btn-sm">
	    		<i class="fa fa-pencil" aria-hidden="true"></i> Edit
	    	</a>
	        <hr />
			<table class="table table-hover table-striped table-bordered table-condensed" id="disposalTable" cellspacing="0" width="100%"	>
				<thead>
		            <tr rowspan="2">
		                <th class="text-left" colspan="3">Disposal Slip:  <span style="font-weight:normal">{{ $disposal->code }}</span> </th>
		                <th class="text-left" colspan="3">Created By:  <span style="font-weight:normal">{{ $disposal->created_by }}</span> </th>
		            </tr>
		            <tr rowspan="2">
		                <th class="text-left" colspan="3">Remarks:  <span style="font-weight:normal">{{ $disposal->remarks }}</span> </th>
		                <th class="text-left" colspan="3">Status:  <span style="font-weight:normal">{{ ($disposal->status == '') ? ucfirst(config('app.default_status')) : $disposal->status }}</span> </th>
		            </tr>
		            <tr>
						<th>Stock Number</th>
						<th>Details</th>
						<th>Quantity</th>
						<th>Unit Cost</th>
						<th>Amount</th>
						<th>Notes</th>
					</tr>
				</thead>
			</table>
		</div>

    </div><!-- /.box-body -->
  </div><!-- /.box -->

@endsection

@section('after_scripts')

<script>
	$(document).ready(function() {

    var table = $('#disposalTable').DataTable({
			language: {
					searchPlaceholder: "Search..."
			},
			"dom": "<'row'<'col-sm-3'l><'col-sm-6'<'toolbar'>><'col-sm-3'f>>" +
							"<'row'<'col-sm-12'tr>>" +
							"<'row'<'col-sm-5'i><'col-sm-7'p>>",
			"processing": true,
			ajax: "{{ url("disposal/$disposal->id") }}",
			columns: [
					{ data: "stocknumber" },
					{ data: "details" },
					{ data: "pivot.quantity" },
					{ data: "pivot.unitcost" },
					{ data: function(callback){
						return parseFloat(callback.pivot.quantity * callback.pivot.unitcost).toFixed(2).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")
					} },
			],
    });

    $('div.toolbar').html(`
         <a href="{{ url("disposal/$disposal->id/print") }}" target="_blank" id="print" class="print btn btn-sm btn-default ladda-button" data-style="zoom-in">
          <span class="glyphicon glyphicon-print" aria-hidden="true"></span>
          <span id="nav-text"> Print</span>
        </a>
        @if($disposal->status == 'approved')
        <a id="release" href="{{ url("disposal/$disposal->id/release") }}" class="btn btn-sm btn-danger ladda-button" data-style="zoom-in">
          <span class="ladda-label"><i class="glyphicon glyphicon-share-alt"></i> Release</span>
        </a>
        @endif
        <a id="comment" href="{{ url("disposal/$disposal->id/comments") }}" class="btn btn-sm btn-primary ladda-button" data-style="zoom-in">
          <span class="ladda-label"><i class="fa fa-comment" aria-hidden="true"></i> Commentary</span>
        </a>
    `)

	} );
</script>
@endsection