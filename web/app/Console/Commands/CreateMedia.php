<?php

namespace App\Console\Commands;

use App\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
		$filename = "undetermined";

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
		Storage::put( $directory . "/" . $filename, $file );

		// Determine the type of the file.
		$type = mime_content_type( "storage/app/$directory/$filename" );
		$newName = "still_undetermined";
		if ( $type == "audio/midi" )
		{
			$newName = "harmony.midi";
		}
		else if ( $type == "application/xml" )
		{
			$newName = "harmony.musicxml";
		}

		// Move the file, and update the database.
		Storage::move( $directory . "/" . $filename, $directory . "/" . $newName );
		$media->originalFile = $newName;
		$media->save();

		$this->line( "You can view this media at ");
		$this->info( url( "/" ) . "/media/$media->id/$media->originalFile" );
	}
}
