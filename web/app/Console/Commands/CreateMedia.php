<?php

namespace App\Console\Commands;

use App\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CreateMedia extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'media:create '
		. '{file : URL of the file to upload} '
		. '{--textID= : Hymnary Text ID for this media file} '
		. '{--tuneID= : Hymnary Tune ID for this media file}'
	;

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create an authoritative media file.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$file = file_get_contents( $this->argument( 'file' ) );
		// Save the file as "original", so the route /media/#/original always gives the file.
		$filename = "original";

		// Save the entry in the database to get an ID,
		$media = new Media( [
			"originalFile" => $filename,
			"textID" => $this->option( 'textID' ),
			"tuneID" => $this->option( 'tuneID' ),
		] );
		$media->save();

		// then save the file in the directory for the media ID.
		$directory = Media::getDir() . "/$media->id";
		Storage::makeDirectory( $directory );
		$process = new Process( [ 'chown', 'www-data:www-data', "/var/www/web/storage/app/" . $directory ] );
		$process->run();
		if ( ! $process->isSuccessful() )
		{
			throw new ProcessFailedException($process);
		}

		Storage::put( $directory . "/" . $filename, $file );

		if ( ! $media->updateFileType() )
		{
			$this->error( "Unable to determine media type." );
		}
		$this->line( "You can view this media at ");
		$this->info( url( "/" ) . "/media/$media->id/$media->originalFile" );
	}
}
