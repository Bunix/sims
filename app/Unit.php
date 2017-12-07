<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
class Unit extends Model{

	protected $table = 'units';

	public $timestamps = false;

	protected $fillable = ['name','description'];
	protected $primaryKey = 'id';
	public static $rules = array(
		'Name' => 'required|unique:units,name',
		'Description' => '',
		'Abbreviation' => 'required|unique:units,abbreviation'
	);

	public static $updateRules = array(
		'Name' => 'required',
		'Description' => '',
		'Abbreviation' => 'required'
	);

}
