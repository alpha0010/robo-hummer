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
		$mediaArg = $this->argument( 'media' );
		if ( $this->argument( 'media' ) )
		{
			$media = [ Media::find( $mediaArg ) ];
			if ( ! $media[ 0 ] )
			{
				$this->error( "Could not find media entry '$mediaArg'." );
				$this->line( "Consider using <info>media:clear-cache --force</info> "
					. "to clear caches, including untracked media." );
				return 1;
			}
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
								$this->line( "Deleted <info>$file</info>" );
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
				$ids = Media::pluck( 'id' )->toArray();
				// TODO: Delete files that aren't tracked
				$directories = Storage::directories( Media::getDir() );
				foreach ( $directories as $dir )
				{
					$dirName = substr( $dir, strlen( Media::getDir() . "/" ) );
					if ( is_numeric( $dirName ) && ! in_array( (int) $dirName, $ids ) )
					{
						Storage::deleteDirectory( $dir );
						if ( $this->option( 'verbose' ) )
						{
							$this->line( "Deleted files inside<info>$dir</info>" );
						}
					}
				}
			}
		}
	}
}
