<?php

namespace Tests\Feature;

use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ClearMedia;
use Tests\TestCase;

class IncipitTest extends TestCase
{
    use RefreshDatabase;
    use ClearMedia;

    public function testCreateIncipitFromMidi()
    {
        $localPath = "../examplemedia/2/melody.midi";
        Artisan::call("media:create", [ 'file' => $localPath ]);

        $incipit = [[
            'key' => "G major",
            'incipit' => "51313-21655-13132",
        ]];
        $response = $this->get('/media/1/incipit.json');
        $response
            ->assertJson($incipit)
            ->assertOk();
    }
}
