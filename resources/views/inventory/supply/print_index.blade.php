@extends('layouts.report')
@section('title',"Supplies Masterlist")
@section('content')
  <style>
      th , tbody{
        text-align: center;
      }

      #content{
        font-family: "Times New Roman";
      }

      @media print {
          tr.page-break  { display: block; page-break-after: always; }
      }   

  </style>
  <div id="content" class="col-sm-12">
    <table class="table table-striped table-bordered" id="inventoryTable" width="100%" cellspacing="0">
        <thead>
          <th class="col-sm-1">Stock No.</th>
          <th class="col-sm-1">Details</th>
          <th class="col-sm-1">Unit</th>
        </thead>
        <tbody>
        @foreach($supplies as $supply)
        <tr>
          <td>{{ $supply->stocknumber }}</td>
          <td>
            <span style="font-size:
            @if(strlen($supply->details) > 80) 9px 
              @elseif(strlen($supply->details) > 40) 11px 
              @else 12px 
            @endif">
              {{ $supply->details }}
            </span>
          </td>
          <td>{{ $supply->unit->name }}</td>
        </tr>
        @endforeach
        <tr>
          <td colspan=3 class="col-sm-12"><p class="text-center">  ******************* Nothing Follows ******************* </p></td>
        </tr>
        </tbody>
    </table>
  </div>
@endsection