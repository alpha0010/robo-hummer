<?php

namespace App\Console\Commands;

use App\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearCachedMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:clear-cache '
        . '{media? : id of the media entry to clear cache for} '
        . '{--type= : Specific type of media to clear cache for}'
    ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cached media.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mediaQuery = new Media();
        $constraints = [];
        $type = $this->option('type');
        if ($type) {
            $mediaQuery = $mediaQuery->where('originalFile', '!=', $type);
            $constraints[] = "The original file is not '$type'";
        }
        $media = Media::all();
        $mediaArg = $this->argument('media');
        if ($this->argument('media')) {
            $mediaQuery = $mediaQuery->where('id', $mediaArg);
            $constraints[] = "The media ID is '$mediaArg'";
        }

        $media = $mediaQuery->get();
        if ($media->count() === 0) {
            if ($constraints) {
                $this->error("Could not find media entry with constraints: ");
                $this->error(implode(' and ', $constraints));
            } else {
                $this->error("Could not find any media entries.");
            }
            $this->line("Consider using <info>media:delete untracked</info> "
                . "to delete untracked files.");
            return 1;
        }


        foreach ($media as $entry) {
            $files = Storage::allFiles(Media::getDir() . "/" . $entry->id);
            // Only delete cached files if the original file is still there.
            $originalFile = Media::getDir() . "/$entry->id/$entry->originalFile";
            if (in_array($originalFile, $files)) {
                foreach ($files as $file) {
                    // Don't delete the original file.
                    if ($file != $originalFile) {
                        $typePath = Media::getDir() . "/$entry->id/" . $this->option('type');
                        // If the --type option is used, delete only the matching file,
                        // otherwise, delete all other cached files.
                        if (! $this->option('type') || $typePath == $file) {
                            Storage::delete($file);
                            if ($this->option('verbose')) {
                                $this->line("Deleted <info>$file</info>");
                            }
                        }
                    }
                }
            }
        }
    }
}
