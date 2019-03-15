<?php

namespace Tests\Feature;

use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ClearMedia;
use Tests\TestCase;

class TupleTest extends TestCase
{
    use RefreshDatabase;
    use ClearMedia;

    public function testCreate6TupleFromMidi()
    {
        $localPath = "../examplemedia/2/melody.midi";
        Artisan::call("media:create", [ 'file' => $localPath ]);

        // These are the 6-tuples that describe Amazing Grace
        // See /slides/final-report#note-context
        $tuples = [
            //-ma-    -zi-     -ng    (Tone difference from "A-", relative length)
            [5, 2.0,  9, 0.5,  5, 0.5],
            //-zi-    -ng       grace
            [4, 0.25, 0, 0.25, 4, 1.0],
            //-ng     grace    how
            [-4, 1.0, 0, 4.00, -2, 2.0],
        ];

        $tuples = array_map([self::class, 'transformTuple'], $tuples);

        $response = $this->get('/media/1/6.tuples.json');
        $response
            ->assertJson($tuples)
            ->assertOk();
    }

    /**
     * @brief Apply transformations to tuple.
     * These are documented in `searcher.computeFeatures`.
     */
    private static function transformTuple($tuple)
    {
        $logBase = 4;
        $multiplier = 7;
        foreach ($tuple as $key => &$value) {
            if ($key % 2 == 1) {
                $value = (log($value) / log($logBase)) * $multiplier;
            }
        }
        return $tuple;
    }
}
