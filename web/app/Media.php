<?php

namespace App;

use App;
use App\Http\Controllers\MediaController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Media extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "originalFile", "textID", "tuneID",
    ];

    /**
     * @brief Eloquent Dynamic Scope to constrain media
     *  that is able to generate a certain type of file.
     */
    public function scopeCanGenerate($query, $type)
    {
        $requires = [
            'incipit.json' => ['harmony.musicxml'],
            '6.tuples.json' => ['harmony.musicxml', 'harmony.midi'],
            '8.tuples.json' => ['harmony.musicxml', 'harmony.midi'],
        ];
        if (! isset($requires[$type])) {
            $requires[$type] = [];
        }
        return $query->whereIn('originalFile', $requires[$type]);
    }

    /**
     * @brief Create a file if it does not exist.
     * @return true if there is a file, false if there was an error, or it is not found.
     */
    public function cache($type)
    {
        $mc = new MediaController();
        try {
            $mc->get($this->id, $type);
            return true;
        } catch (NotFoundHttpException $e) {
            return false;
        }
    }

    /**
     * @brief Attempt to detect the filetype, rename the file if successful.
     * @return true if the filetype was changed, false otherwise.
     */
    public function updateFiletype()
    {
        // Determine the type of the file.
        $type = mime_content_type($this->getAbsPath($this->originalFile));
        $newName = false;
        if ($type == "audio/midi") {
            $newName = "harmony.midi";
        } elseif ($type == "application/xml") {
            $newName = "harmony.musicxml";
        }
        if ($newName) {
            // Move the file, and update the database.
            Storage::move($this->getPath($this->originalFile), $this->getPath($newName));
            $this->originalFile = $newName;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * @brief Get the file directory for this media entry.
     */
    public function getPath($filename = "", $absolute = false)
    {
        $abs = "";
        if ($absolute) {
            $abs = "/var/www/web/storage/app/";
        }
        return $abs . Media::getDir() . "/" . $this->id . "/" . $filename;
    }
    public function getAbsPath($filename = "")
    {
        return $this->getPath($filename, true);
    }

    public static function getDir()
    {
        if (App::environment("testing")) {
            return "testmedia";
        }
        return "media";
    }
}
