@extends('layouts.report')
@section('title',"Purchase Order $purchaseorder->number")
@section('content')
  <div id="content" class="col-sm-12">
    <h3 class="text-center">
    @if( $purchaseorder->supplier->name == config('app.main_agency') )
    Agency Procurement Request
    @else
    Purchase Order
    @endif
    </h3>
    <table class="table table-striped table-bordered" id="inventoryTable" width="100%" cellspacing="0">
      <thead>
        <tr rowspan="2">
            <th class="text-left" colspan="4">Code:  <span style="font-weight:normal">{{ $purchaseorder->number }}</span> </th>
            <th class="text-left" colspan="4">Fund Cluster:  
              <span style="font-weight:normal">
                <!-- {{ implode(", ", App\PurchaseOrderFundCluster::findByPurchaseOrderNumber([$purchaseorder->number])->pluck('fundcluster_id')->toArray()) }} -->
                <!-- {{ count($purchaseorder->fundclusters) > 0 ? implode( $purchaseorder->fundclusters->pluck('code')->toArray(), ",") : "None" }} -->
              </span> 
            </th>
        </tr>
        <tr rowspan="2">
            <th class="text-left" colspan="4">Supplier:  <span style="font-weight:normal">{{ $purchaseorder->supplier->name }}</span> </th>
            <th class="text-left" colspan="4">Date:  <span style="font-weight:normal">{{ Carbon\Carbon::parse($purchaseorder->date_received)->toFormattedDateString() }}</span> </th>
        </tr>
        <tr rowspan="2">
            <th class="text-left" colspan="4">Details:  <span style="font-weight:normal">{{ $purchaseorder->details }}</span> </th>
            <th class="text-left" colspan="4"></th>
        </tr>
        <tr>
          <th>ID</th>
          <th>Supply Item</th>
          <th>Ordered Quantity</th>
          <th>Received Quantity</th>
          <th>Remaining Quantity</th>
          <th>Unit Price</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
      @if(count($purchaseordersupply) > 0)
        @foreach($purchaseordersupply as $supply)
        <tr>
          <td>{{ $supply->id }}</td>
          <td>{{ $supply->stocknumber }}</td>
          <td>{{ $supply->orderedquantity }}</td>
          <td>{{ $supply->receivedquantity }}</td>
          <td>{{ $supply->remainingquantity }}</td>
          <td>{{ number_format($supply->unitcost, 2) }}</td>
          <td>{{ number_format($supply->receivedquantity * $supply->unitcost, 2) }}</td>
        </tr>
        @endforeach
      @else
      <tr>
        <td colspan=7 class="col-sm-12"><p class="text-center">  No record </p></td>
      </tr>
      @endif
      </tbody>
    </table>
  </div>
@include('vendor.print_footer')
@endsection
