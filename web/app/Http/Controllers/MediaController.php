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
			list( $name, $extension ) = explode( ".", $type );
			if ( in_array( $type, [ 'incipit.json', 'harmony.musicxml', 'harmony.midi' ] ) )
			{
				$shell_path = $media->getAbsPath( $media->originalFile );
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
			else if ( in_array( $extension, [ 'ogg', 'mp3', 'wav' ] ) )
			{
				// TODO: Don't re-render the premaster if it already exists
				$process = new Process( [
					"fluidsynth",
					"-F", $media->getAbsPath( "$name.premaster.wav" ),
					"/usr/share/sounds/sf2/TimGM6mb.sf2",
					// TODO: Generate midi file if it doesn't exist.
					$media->getAbsPath( "$name.midi" )
				] );
				$process->run();
				// fluidsynth doesn't return 1 when unsuccessful (such as invalid soundfont)
				// TODO: Find a way to verify that fluidsynth created audio that you can hear.
				if ( ! $process->isSuccessful() )
				{
					throw new ProcessFailedException($process);
				}
				if ( ! Storage::exists( $media->getPath( "$name.premaster.wav" ) ) )
				{
					return "Unable to generate audio from midi.";
				}
				$process = new Process( [
					'ffmpeg',
					'-i', $media->getAbsPath( "$name.premaster.wav" ),
					'-filter_complex',
					"compand=attacks=0.3 0.3:decays=0.8 0.8:points=-80/-900|-45/-25|-10/-10",
					// ffmpeg automatically converts to the file format that is requested.
					$media->getAbsPath( $type )
				] );
				$process->run();
				if ( ! $process->isSuccessful() )
				{
					throw new ProcessFailedException($process);
				}
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
		$media = new Media( [
			"originalFile" => $filename,
			"textID" => $request->textID ?? NULL,
			"tuneID" => $request->tuneID ?? NULL,
		] );
		$media->save();
		$request->file( 'file' )->storeAs( Media::getDir() . "/$media->id", $filename );
		$media->updateFileType();
		return $media;
	}
}
