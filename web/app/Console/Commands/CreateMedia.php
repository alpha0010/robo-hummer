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
		// TODO: Save file to temporary location.
		$file = file_get_contents( $this->argument( 'file' ) );

		// TODO: Determine file type.
		$filename = "melody.musicxml";

		$media = new Media( [
			"originalFile" => $filename,
			"textID" => $this->option( 'textID' ),
			"tuneID" => $this->option( 'tuneID' ),
		] );
		$media->save();

		$directory = Media::getDir() . "/$media->id";
		Storage::makeDirectory( $directory );
		// TODO: Move file from temporary location.
		Storage::put( $filename, $file, $directory );

		$this->line( "You can view this media at <info>" . url( "/" ) . "/media/$media->id/$filename" );
	}
}
