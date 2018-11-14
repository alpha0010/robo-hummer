<?php

namespace App\Http\Controllers;

use App\Media;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MediaController extends Controller
{
	public function get( string $number, string $type )
	{
		$filepath = Media::getDir() . "/$number/$type";
		if ( Storage::exists( $filepath ) )
		{
			return $this->getFileResponse( $filepath );
		}

		$media = Media::find( $number );
		if ( $media )
		{
			if ( in_array( $type, [ 'incipit.json', 'harmony.musicxml' ] ) )
			{
				$shell_path = "/var/www/web/storage/app/"
					. Media::getDir()
					. "/$media->id/$media->originalFile";
				$process = new Process( [
					"sudo", "-u", "python",
					"/var/www/tools/convert.py", $shell_path, $type,
				] );
				$process->run();
				if ( ! $process->isSuccessful() )
				{
					throw new ProcessFailedException($process);
				}
				Storage::put( $filepath, $process->getOutput() );
				return $this->getFileResponse( $filepath );
			}
			else if ( $type == 'original' )
			{
				return redirect( "/media/$media->id/$media->originalFile" );
			}
		}
		// Otherwise, the file wasn't found.
		abort( 404 );
	}

	/**
	 * @brief Get a file response.
	 * @param Filename
	 * @returns Either an object, if this is to be returned as json, or the file response.
	 */
	private function getFileResponse( $filepath )
	{
		if ( substr( $filepath, -5 ) == '.json' )
		{
			return json_decode( Storage::get( $filepath ) );
		}
		return Storage::response( $filepath );
	}

	public function post( Request $request )
	{
		$filename = 'original';
		// TODO: Accept textID and tuneID from the post request
		$media = new Media( [
			"originalFile" => $filename,
		] );
		$media->save();
		$request->file( 'file' )->storeAs( Media::getDir() . "/$media->id", $filename );
		$media->updateFileType();
		return $media;
	}
}
