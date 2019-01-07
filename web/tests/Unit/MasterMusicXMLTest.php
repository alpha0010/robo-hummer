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
		$m = Media::orderBy('id','desc')->first();

		// Generate the master musicxml file.
		$getMusicXML = $this->get( "/media/{$m->id}/master.musicxml" );
		$getMusicXML->assertStatus( 200, "Could not convert $fileName to master.musicxml" );
		// Re-upload the master musicxml file so we can compare some conversions.
		Artisan::call( "media:create", [ 'file' => $m->getAbsPath("master.musicxml") ] );
		$m2 = Media::orderBy('id','desc')->first();

		$midiOriginal  = $this->get( "/media/{$m->id}/harmony.midi" );
		$midiConverted = $this->get( "/media/{$m2->id}/harmony.midi" );

		$midiOriginal->assertStatus( 200 );
		$midiConverted->assertStatus( 200 );

		$this->assertEquals(
			file_get_contents( $m->getAbsPath( "harmony.midi" ) ),
			file_get_contents( $m2->getAbsPath( "harmony.midi" ) )
		);
	}
}
