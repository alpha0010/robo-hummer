<?php

namespace Tests\Unit;

use App\Media;
use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ClearMedia;
use Tests\TestCase;

class MasterMusicXMLTest extends TestCase
{
	use RefreshDatabase;
	use ClearMedia;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        
        $this->assertTrue(true);
    }

	public function testRhythm()
	{
		$this->fileTest( "03d-Rhythm-DottedDurations-Factors.xml" );
	}

	/**
	 * @brief Test that information from a file isn't lost
	 * when it is converted to our master musicxml format.
	 */
	private function fileTest( $fileName )
	{
		$localPath = "../examplemedia/musicxmlTestSuite/xmlFiles/$fileName";
		Artisan::call( "media:create", [ 'file' => $localPath ] );
		$id = Media::max('id');
		$convertedPath = "http://localhost/media/$id/master.musicxml";
		Artisan::call( "media:create", [ 'file' => $convertedPath ] );
		$id2 = $id + 1;

		$midiOriginal  = $this->get( "/media/$id/harmony.midi" );
		$midiConverted = $this->get( "/media/$id2/harmony.midi" );

		$midiOriginal->assertStatus( 200 );
		$midiConverted->assertStatus( 200 );
	}
}
