<?php

namespace App\Console\Commands;

use App\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearCachedMedia extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'media:clear-cache '
		. '{media? : id of the media file to clear cache for} '
		. '{--force : Clear untracked files} '
		. '{--type= : Specific type of media to clear cache for}'
	;

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear cached media.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$media = Media::all();
		if ( $this->argument( 'media' ) )
		{
			$media = [ Media::findOrFail( $this->argument( 'media' ) ) ];
		}
		foreach ( $media as $entry )
		{
			$files = Storage::allFiles( Media::getDir() . "/" . $entry->id );
			if ( in_array( Media::getDir() . "/$entry->id/$entry->originalFile", $files ) )
			{
				foreach ( $files as $file )
				{
					if ( $file != Media::getDir() . "/$entry->id/$entry->originalFile" )
					{
						$typePath = Media::getDir() . "/$entry->id/" . $this->option( 'type' );
						if ( ! $this->option( 'type' ) || $typePath == $file )
						{
							Storage::delete( $file );
							if ( $this->option( 'verbose' ) )
							{
								$this->line( "Deleted <info>$file</file>" );
							}
						}
					}
				}
			}
		}
		if ( ! $this->argument( 'media' ) )
		{
			if ( $this->option( 'force' ) )
			{
				// TODO: Delete files that aren't tracked
			}
		}
	}
}
