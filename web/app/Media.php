<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
