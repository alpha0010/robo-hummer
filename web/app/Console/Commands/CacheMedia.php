<?php

namespace App\Console\Commands;

use App\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CacheMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cache '
        . '{--type= : Specific type of media to clear cache for}'
        . '{--recache= : "all", "errors", or "successes" Remove certain files before caching them.}'
    ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache media.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mediaQuery = new Media();
        $type = $this->option('type');
        if (! $type) {
            $this->error("--type parameter must be given");
            return 1;
        }

        $media = Media::where('originalFile', '!=', $type)
            ->canGenerate($type)
            ->get();

        $count = 0;
        $deleted = 0;
        $errorsDeleted = 0;

        $bar = $this->output->createProgressBar(count($media));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');
        $bar->start();

        $recache = $this->option('recache');

        foreach ($media as $entry) {
            if ($recache) {
                $path = $entry->getPath($type);
                $visibility = Storage::getVisibility($path);
                // Cached errors are files saved with 'private' visibility.
                if ($visibility == 'private' && in_array($recache, ['all', 'errors'])) {
                    $errorsDeleted += Storage::delete($path);
                } elseif ($visibility == 'public' && in_array($recache, ['all', 'successes'])) {
                    $deleted += Storage::delete($path);
                }
            }

            $count += $entry->cache($type);
            $bar->advance();
        }

        $bar->finish();
        $this->line("");

        $this->message($deleted, "properly generated file", ["was", "were"], "deleted");
        $this->message($errorsDeleted, "error", ["was", "were"], "deleted");
        $this->message($count, "properly generated file", ["is", "are"], "cached");
        $this->message(count($media) - $count, "error", ["is", "are"], "cached");
    }

    /**
     * @brief Create and output a message like "1 file is cached", or "2 files were deleted".
     * @param int $count The plural number describing $thing.
     * @param string $thing The subject of the sentence.
     * @param array $predVerb An array containing verbs of the form [0 => 'singular verb', 1 => 'plural verb'].
     * @param string $predNom The predicate nominative comes after the Predicate Verb.
     */
    private function message(int $count, string $subject, array $predVerb, string $predNom)
    {
        $subjectS = str_plural($subject, $count);
        $verb = $count != 1 ? $predVerb[1] : $predVerb[0];

        $this->line("$count $subjectS $verb $predNom.");
    }
}
