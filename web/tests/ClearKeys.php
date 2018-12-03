<?php

namespace Tests;

use Illuminate\Support\Facades\Storage;

trait ClearKeys
{
	public function clearKeys()
	{
		Storage::delete( "testing-sso-public.key" );
		Storage::delete( "testing-sso-private.key" );
	}
}
