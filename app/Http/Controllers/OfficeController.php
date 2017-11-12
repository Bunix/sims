<?php
namespace App\Http\Controllers;

use App;
use Carbon;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Input;
class OfficeController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if(Request::ajax())
		{
			return json_encode([
				'data' => App\Office::all()
			]);
		}
		return view('maintenance.office.index')
					->with('title','Office');
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return view('maintenance.office.create')
					->with('title','Office');
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

		$name = $this->sanitizeString(Input::get('name'));
		$code = $this->sanitizeString(Input::get('code'));
		$head = $this->sanitizeString(Input::get('head'));
		$description = $this->sanitizeString(Input::get('description'));

		$validator = Validator::make([
			'Name' => $name,
			'Code' => $code
		],App\Office::$rules);

		if($validator->fails())
		{
			return redirect('maintenance/office/create')
				->withInput()
				->withErrors($validator);
		}

		$office = new App\Office;
		$office->code = $code;
		$office->name = $name;
		$office->description = $description;
		$office->head = $head;
		$office->save();

		\Alert::success('Office added')->flash();
		return redirect('maintenance/office');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{

	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$office = App\Office::find($id);
		return view("maintenance.office.edit")
				->with('office',$office)
				->with('title','Office');
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$name = $this->sanitizeString(Input::get('name'));
		$code = $this->sanitizeString(Input::get('code'));
		$head = $this->sanitizeString(Input::get('head'));
		$description = $this->sanitizeString(Input::get('description'));

		$validator = Validator::make([
			'Name' => $name,
			'Code' => $code
		],App\Office::$rules);

		if($validator->fails())
		{
			return redirect("maintenance/office/$id/edit")
				->withInput()
				->withErrors($validator);
		}

		$office = App\Office::find($id);
		$office->code = $code;
		$office->name = $name;
		$office->description = $description;
		$office->head = $head;
		$office->save();

		\Alert::success('Office Information Updated')->flash();
		return redirect('maintenance/office');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if(Request::ajax())
		{
			$office = App\Office::find($id);
			$office->delete();
			return json_encode('success');
		}

		try
		{
			$office = App\Office::find($id);
			$office->delete();
			\Alert::success('Office Removed')->flash();
		} catch (Exception $e) {
			\Alert::error('Problem Encountered While Processing Your Data')->flash();
		}
		return redirect('maintenance/office');
	}

	public function getAllCodes()
	{
		if(Request::ajax())
		{
			return json_encode(Office::pluck('deptcode')->toArray());
		}
	}

	public function getOfficeCode()
	{
		if(Request::ajax())
		{
			$code = $this->sanitizeString(Input::get('term'));
			return json_encode(Office::where('deptcode','like','%'.$code.'%')->pluck('deptcode')->toArray());
		}
	}


}
