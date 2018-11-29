<?php

namespace App\Console\Commands;

use App\TrustedUUID;
use Illuminate\Console\Command;

class TrustUUID extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'robo:trust-uuid { uuid : The SSO UUID for the user we want to trust. }';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Add a UUID as a trusted user';

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
		$tu = new TrustedUUID( [ 'uuid' => $this->argument( 'uuid' ) ] );
		$tu->save();
	}
}
