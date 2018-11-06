<?php

namespace Tests;

use Illuminate\Support\Facades\Storage;

trait ClearMedia
{
	public function clearMedia()
	{
		// We could use Media::getDir(), but it's safer to avoid potentially removing live media.
		Storage::deleteDirectory( "testmedia" );
	}
}
