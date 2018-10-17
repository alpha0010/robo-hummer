<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( 'media', function ( Blueprint $table ) {
			$table->increments( 'id' );
			$table->string( 'originalFile', 64 )
				->comment( "The filename of the first media file uploaded (not generated)." );
			$table->integer( 'tuneID' )->unsigned()->nullable()
				->comment( "The hymnary identifier for this tune: hymnary.hymnTune.tuneID." );
			$table->integer( 'textID' )->unsigned()->nullable()
				->comment( "The hymnary identifier for this text: hymnary.hymnText.textID. "
					. "Useful if a media file is uploaded before the instance tune exists." );
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists( 'media' );
	}
}
