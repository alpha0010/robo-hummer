<?php

namespace App\Console\Commands;

use App\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class IndexListMelody extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'melody:index-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build a list of files to use in the melody search.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $media = Media::where('shouldIndex', true)->get();

        $this->line("Counting files...");
        $tCount = count($media);
        $bar = $this->output->createProgressBar($tCount);
        $bar->setRedrawFrequency($tCount / 1000);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $bar->start();

        $filesToIndex = "";
        $count = 0;

        foreach ($media as $entry) {
            $path = $entry->getPath("6.tuples.json");
            if (Storage::exists($path) && Storage::getVisibility($path) == 'public') {
                $filesToIndex .= "/srv/robo-media-dart/$entry->id/6.tuples.json\n";
                $count++;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line("");
        Storage::put('files-to-index.txt', $filesToIndex);
        $this->line("$count of the $tCount files that should be indexed exist.");
    }
}
