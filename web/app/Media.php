<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		"originalFile", "textID", "tuneID",
	];

	public static function getDir()
	{
		if ( App::environment( "testing" ) )
		{
			return "testmedia";
		}
		return "media";
	}
}
