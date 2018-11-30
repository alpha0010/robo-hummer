<?php

namespace App\Console\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetSSOKey extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'robo:set-sso-key '
	                     . '{key? : The RSA public key that SSO uses to sign identity proof JWTs}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set the public key used to verify JWTs sent by SSO.';

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
		$publicKey = '';
		if ( $this->argument( 'key' ) )
		{
			$publicKey = $this->argument( 'key' );
		}
		else
		{
			$publicKey = $this->ask( "SSO public key:" );
		}

		$path = 'sso-public.key';
		if ( App::environment( "testing" ) )
		{
			$path = 'testing-sso-public.key';
		}
		Storage::put( $path, $publicKey );

		$this->line( "SSO public key stored in <info>$path</info>" );
	}
}
