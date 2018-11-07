<?php

namespace App\Http\Controllers;

use App\Media;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class MediaController extends Controller
{
	public function get( string $number, string $type )
	{
		$filepath = Media::getDir() . "/$number/$type";
		if ( Storage::exists( $filepath ) )
		{
			return Storage::response( $filepath );
		}

		$media = Media::find( $number );
		if ( $media )
		{
			if ( $type == 'incipit' )
			{
				$shell_path = "../storage/app/"
					. Media::getDir()
					. "/$media->id/$media->originalFile";
				// Validate that json was returned, and return the object (outputs as json).
				return json_decode( shell_exec( "../../tools/incipit.py $shell_path" ) );
			}
		}
		// Otherwise, the file wasn't found.
		abort( 404 );
	}
}
