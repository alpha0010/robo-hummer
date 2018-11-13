<?php

namespace App\Console\Commands;

use App\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteMedia extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'media:delete '
		. '{media : id of the media entry to delete, or "untracked" to clear untracked files} '
		. '{--dry-run : Print out the names instead of deleting files } '
	;

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Delete a media entry and its corresponding files.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$mediaArg = $this->argument( 'media' );
		if ( $mediaArg == 'untracked' )
		{
			$ids = Media::pluck( 'id' )->toArray();
			$directories = Storage::directories( Media::getDir() );
			foreach ( $directories as $dir )
			{
				$dirName = substr( $dir, strlen( Media::getDir() . "/" ) );
				if ( is_numeric( $dirName ) && ! in_array( (int) $dirName, $ids ) )
				{
					$this->delete( $dir, NULL );
				}
			}
		}
		else
		{
			$media = Media::find( $mediaArg );
			$this->delete( Media::getDir() . "/$mediaArg", $media );
		}
	}

	private function delete( $dir, $media )
	{
		$verb = "Would delete";
		if ( ! $this->option( 'dry-run' ) )
		{
			Storage::deleteDirectory( $dir );
			if ( $media )
			{
				$media->delete();
			}
			$verb = "Deleted";
		}
		if ( $this->option( 'dry-run' ) || $this->option( 'verbose' ) )
		{
			$this->line( "$verb files inside <info>$dir</info>" );
			if ( $media )
			{
				$this->line( "$verb media entry <info>$media->id</info>" );
			}
		}
	}
}

