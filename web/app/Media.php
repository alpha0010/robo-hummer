<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

	/**
	 * @brief Attempt to detect the filetype, rename the file if successful.
	 * @return true if the filetype was changed, false otherwise.
	 */
	public function updateFiletype()
	{
		$filename = $this->originalFile;
		$directory = Media::getDir() . "/$this->id";
		// Determine the type of the file.
		$type = mime_content_type( "/var/www/web/storage/app/$directory/$filename" );
		$newName = FALSE;
		if ( $type == "audio/midi" )
		{
			$newName = "harmony.midi";
		}
		else if ( $type == "application/xml" )
		{
			$newName = "harmony.musicxml";
		}
		if ( $newName )
		{
			// Move the file, and update the database.
			Storage::move( $directory . "/" . $filename, $directory . "/" . $newName );
			$this->originalFile = $newName;
			$this->save();
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @brief Get the file directory for this media entry.
	 */
	public function getPath()
	{
		return Media::getDir() . "/" . $this->id . "/";
	}

	public static function getDir()
	{
		if ( App::environment( "testing" ) )
		{
			return "testmedia";
		}
		return "media";
	}
}
