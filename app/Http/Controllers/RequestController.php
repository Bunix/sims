<?php

namespace App\Http\Controllers;

use App;
use Auth;
use DB;
use Carbon;
use Session;
use PDF;
use Validator;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if($request->ajax())
        {

          $ret_val = App\Request::all();

          if(Auth::user()->access == 3)
          {
            if(Auth::user()->position == 'head')
            {
              $ret_val = App\Request::findByOffice( Auth::user()->office )->get();
            }

            $ret_val = App\Request::me()->get();
          }

          return json_encode([
              'data' => $ret_val
          ]);
        }

        return view('request.index')
                ->with('title','Request');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
      $code = $this->generate($request);

      return view('request.create')
              ->with('code',$code)
              ->with('title','Request');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $stocknumbers = $request->get("stocknumber");
      $quantity = $request->get("quantity");
      $quantity_issued = null;
      $array = [];
      $issued_by = Auth::user()->username;
      $office = Auth::user()->office;
      $status = null;

      foreach(array_flatten($stocknumbers) as $stocknumber)
      {
        $validator = Validator::make([
            'Stock Number' => $stocknumber,
            'Quantity' => $quantity["$stocknumber"]
        ],App\Request::$issueRules);

        if($validator->fails())
        {
            return redirect("request/create")
                    ->with('total',count($stocknumbers))
                    ->with('stocknumber',$stocknumbers)
                    ->with('quantity',$quantity)
                    ->withInput()
                    ->withErrors($validator);
        }

        if(Auth::user()->access == 1)
        {
          if( App\Supply::findByStockNumber($stocknumber)->balance <= $quantity["$stocknumber"])
          {
              return redirect("request/create")
                      ->with('total',count($stocknumbers))
                      ->with('stocknumber',$stocknumbers)
                      ->with('quantity',$quantity)
                      ->withInput()
                      ->withErrors(["No more items to release for supply with stock number of $stocknumber"]);
          }

          $status = 'approved';
          $quantity_issued = $quantity[$stocknumber];
        }

        array_push($array,[
            'quantity_requested' => $quantity["$stocknumber"],
            'stocknumber' => $stocknumber,
            'quantity_issued' => $quantity_issued
        ]);
      }

      DB::beginTransaction();

      $request = App\Request::create([
        'requestor' => Auth::user()->username,
        'issued_by' => $issued_by,
        'office' => $office,
        'remarks' => null,
        'status' => $status
      ]);

      $request->supply()->sync($array);

      DB::commit();

      \Alert::success('Request Sent')->flash();
      return redirect('request');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        $id = $this->sanitizeString($id);

        if($request->ajax())
        {
          return json_encode([
            'data' => App\RequestSupply::with('supply')->where('request_id','=',$id)->get()
          ]);
        }

        $request = App\Request::find($id);
        return view('request.show')
              ->with('request',$request)
              ->with('title','Request');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $request = App\Request::find($id);
        $supplyrequest = App\RequestSupply::where('request_id','=',$id)->get();

        return view('request.edit')
                ->with('request',$request)
                ->with('supplyrequest',$supplyrequest)
                ->with('title',$request->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if($request->ajax())
        {
            $id = $this->sanitizeString($id);
            $status = $this->sanitizeString($request->get('status'));
            $remarks = $this->sanitizeString($request->get('reason'));

            $request = App\Request::find($id);
            $request->status = $status;
            $request->approved_at = Carbon\Carbon::now();
            $request->remarks = $remarks;
            $request->save();

            return json_encode('success');
        }

        $quantity = $request->get('quantity');
        $comment = $request->get('comment');
        $stocknumber = $request->get('stocknumber');

        DB::beginTransaction();

        $request = App\Request::find($id);

        foreach($stocknumber as $stocknumber)
        {
          $request->supply()->updateExistingPivot($stocknumber,[
            'quantity_issued' => $quantity[$stocknumber],
            'comments' => $comment[$stocknumber]
          ]);
        }

        $request->issued_by = Auth::user()->username;
        $request->status = 'approved';
        $request->approved_at = Carbon\Carbon::now();
        $request->save();

        DB::commit();

        \Alert::success('Request Approved')->flash();
        return redirect('request');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function releaseView($id)
    {
        $request = App\Request::find($id);
        $supplyrequest = App\RequestSupply::where('request_id','=',$id)->get();

        return view('request.release')
                ->with('request',$request)
                ->with('supplyrequest',$supplyrequest)
                ->with('title',$request->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {

      $daystoconsume = $request->get('daystoconsume');
      $quantity = $request->get('quantity');
      $stocknumber = $request->get('stocknumber');
      $date = Carbon\Carbon::now();

      DB::beginTransaction();

      $requests = App\Request::find($id);
      $requests->status = 'released';
      $requests->released_at = $date;
      $requests->save();

      $reference = $requests->id;
      $office = $requests->office;

      foreach($stocknumber as $stocknumber)
      {
        $supplyrequest = App\RequestSupply::where('request_id','=',$id)
                            ->where('stocknumber','=',$stocknumber)
                            ->first();
        $supplyrequest->save();
        
        $daystoconsume = $this->sanitizeString($daystoconsume[$stocknumber]);
        $quantity = $this->sanitizeString($quantity[$stocknumber]);

        $transaction = new App\StockCard;
        $transaction->date = $date;
        $transaction->stocknumber = $stocknumber;
        $transaction->reference = $reference;
        $transaction->organization = $office;
        $transaction->issued  = $quantity;
        $transaction->daystoconsume = $daystoconsume;
        $transaction->user_id = Auth::user()->id;
        $transaction->issue();
      }

      DB::commit();



      \Alert::success('Items Released')->flash();
      return redirect('request');

    }

    public function print($id)
    {
      $id = $this->sanitizeString($id);
      $supplyrequests = App\RequestSupply::with('supply')->where('request_id','=',$id)->get();
      $request = App\Request::find($id);

      $data = ['request' => $request, 'supplyrequests' => $supplyrequests ];

      $filename = "Request-".Carbon\Carbon::now()->format('mdYHm')."-$request->code".".pdf";
      $view = "request.print_show";

      return $this->printPreview($view,$data,$filename);

      // return view($view);
    }

    public function generate(Request $request)
    {

      $requests = App\Request::orderBy('created_at','desc')->first();
      $id = 1;
      $now = Carbon\Carbon::now();
      $const = 'RIS-' . $now->format('y') . '-' . $now->format('m');

      if(count($requests) > 0)
      {
        $id = $requests->id + 1;
      }

      if($request->ajax())
      {
        return json_encode( $const . '-' . $id ); 
      }

      return $const . '-' . $id;

    }
}
