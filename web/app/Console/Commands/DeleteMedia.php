<?php

namespace App\Console\Commands;

use App\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:delete '
        . '{media : id of the media entry to delete, or "untracked" to clear untracked files} '
        . '{--dry-run : Print out the names instead of deleting files } '
    ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a media entry and its corresponding files.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $mediaArg = $this->argument('media');
        $counts = [ 'files' => 0, 'dirs' => 0, 'entries' => 0 ];
        if ($mediaArg == 'untracked') {
            $ids = Media::pluck('id')->toArray();
            $directories = Storage::directories(Media::getDir());
            foreach ($directories as $dir) {
                $dirName = substr($dir, strlen(Media::getDir() . "/"));
                if (is_numeric($dirName) && ! in_array((int) $dirName, $ids)) {
                    $this->delete($dir, null, $counts);
                }
            }
        } else {
            $media = Media::find($mediaArg);
            $this->delete(Media::getDir() . "/$mediaArg", $media, $counts);
        }
        $verb = $this->option('dry-run') ? "Would delete" : "Deleted";
        $fileS = str_plural('file', $counts['files']);
        $dirS = str_plural('directory', $counts['dirs']);
        $entrieS = str_plural('media entry', $counts['entries']);
        $this->line("$verb <info>$counts[files]</info> $fileS.");
        $this->line("$verb <info>$counts[dirs]</info> $dirS.");
        $this->line("$verb <info>$counts[entries]</info> $entrieS.");
    }

    /**
     * @brief If --dry-run is not specified, deletes files from a directory,
     *  prints out what it did
     * @param string $dir The storage directory from which to delete
     * @param Media $media A media entry to delete or NULL if it doesn't exist.
     * @param[in,out] array $counts An array of counts of directories and files that were deleted.
     */
    private function delete($dir, $media, &$counts)
    {
        $files = count(Storage::allFiles($dir));
        $dirs = Storage::exists($dir) ? 1 : 0;
        $entries = $media ? 1 : 0;

        if (! $this->option('dry-run')) {
            Storage::deleteDirectory($dir);
            if ($media) {
                $media->delete();
            }
        }
        if ($this->option('dry-run') || $this->option('verbose')) {
            $verb = $this->option('dry-run') ? "Would delete" : "Deleted";
            if ($dirs) {
                $fileS = str_plural('file', $files);
                $this->line("$verb <info>$files</info> $fileS inside <info>$dir</info>");
            }
            if ($entries) {
                $this->line("$verb media entry <info>$media->id</info>");
            }
        }

        $counts['files'] += $files;
        $counts['dirs'] += $dirs;
        $counts['entries'] += $entries;
    }
}
