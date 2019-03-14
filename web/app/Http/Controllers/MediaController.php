<?php

namespace App\Http\Controllers;

use App;
use App\Media;
use App\TrustedUUID;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class MediaController extends Controller
{
    public function get(string $number, string $type)
    {
        // Allow negative numbers to get you to newer media entries.
        if ($number < 0) {
            // -1 gets you the newest media item, -2 gets you the second newest...
            $id = Media::orderBy('id', 'desc')->skip(($number * -1) - 1)->first()->id;
            return redirect("/media/$id/$type");
        }
        $filepath = Media::getDir() . "/$number/$type";
        if (Storage::exists($filepath)) {
            return $this->getFileResponse($filepath);
        }

        $media = Media::find($number);
        if ($media) {
            $nameParts = $this->getNameParts($type);

            $name = $nameParts['name'];
            $extension = $nameParts['extension'];
            if (in_array($type, [
                'harmony.midi',
                'harmony.musicxml',
                'incipit.json',
                'master.musicxml',
                'melody.musicxml',
                'partify.musicxml',
                ]) ||
                in_array($extension, [
                'dynamic.svg',
                'dynamic.svg.info.json',
                'ly',
                'pdf',
                'tuples.json',
                ])
            ) {
                $shell_path = $this->getSourcePath($media, $type);
                // An alternate error message to print in case there was no error output.
                $altError = "Process could not complete.";
                $process = new Process([
                    "sudo", "-u", "python",
                    "/var/www/tools/convert.py", $shell_path, $type,
                ]);
                try {
                    $process->run();
                } catch (ProcessTimedOutException $e) {
                    $altError = "Process took too much time.";
                }
                if (! $process->isSuccessful()) {
                    Storage::put($filepath, $process->getErrorOutput() ?: $altError);
                    // Private files denote an error, they will only be shown in development.
                    Storage::setVisibility($filepath, 'private');
                } else {
                    Storage::put($filepath, $process->getOutput());
                }
                return $this->getFileResponse($filepath);
            } elseif ($extension == 'premaster.wav') {
                $this->checkExists($number, "$name.midi");
                $process = new Process([
                    "fluidsynth",
                    "-F", $media->getAbsPath("$name.premaster.wav"),
                    "/usr/share/sounds/sf2/TimGM6mb.sf2",
                    $media->getAbsPath("$name.midi")
                ]);
                $process->run();
                // fluidsynth doesn't return 1 when unsuccessful (such as invalid soundfont)
                // TODO: Find a way to verify that fluidsynth created audio that you can hear.
                if (! $process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
                if (! Storage::exists($media->getPath("$name.premaster.wav"))) {
                    abort(500, "Unable to generate audio from midi.");
                }
                return true;
            } elseif (in_array($extension, [ 'ogg', 'mp3', 'wav' ])) {
                $this->checkExists($number, "$name.premaster.wav");
                // FFmpeg's recommended way to increase volume:
                // Detect the peak audio rate, then increase the volume.
                // https://trac.ffmpeg.org/wiki/AudioVolume
                $process = new Process([
                    "ffmpeg",
                    "-i", $media->getAbsPath("$name.premaster.wav"),
                    "-filter:a", "volumedetect",
                    "-f", "null", "/dev/null"
                ]);
                $process->run();
                $matches = [];
                preg_match("/max_volume: (-?[0-9.]+) dB/", $process->getErrorOutput(), $matches);
                // Fallback: If max_volume wasn't found, just do no boosting.
                $vol = 0;
                if (isset($matches[1]) && is_numeric($matches[1])) {
                    // If the maximum volume is -8dB, we want to boost up 8.
                    $vol = -1 * $matches[1];
                    // But subtract a little, to reduce risk of clipping.
                    $vol -= 1;
                }
                $process = new Process([
                    'ffmpeg',
                    '-i', $media->getAbsPath("$name.premaster.wav"),
                    '-filter:a', "volume={$vol}dB",
                    // ffmpeg automatically converts to the file format that is requested.
                    $media->getAbsPath($type)
                ]);
                $process->run();
                if (! $process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }
                return $this->getFileResponse($filepath);
            } elseif ($type == 'original') {
                return redirect("/media/$media->id/$media->originalFile");
            }
        }
        // Otherwise, the file wasn't found.
        abort(404);
    }

    /**
     * @brief return array with keys 'name' and 'extension'.
     * @param $type - the name of the type.
     */
    private function getNameParts($type)
    {
        $parts = explode(".", $type, 2);
        $name = $parts[0];
        $extension = $parts[1] ?? '';
        return [
            'name' => $name,
            'extension' => $extension,
        ];
    }

    /**
     * @brief Get the absolute path for a source file (if it exists)
     *  that should be used when converting to a given destination file.
     * @param Media $media
     * @param string $destinationType The file format that you want to convert to.
     */
    private function getSourcePath($media, $destinationType)
    {
        $destNameParts = $this->getNameParts($destinationType);
        $name = $destNameParts['name'];

        $destToSourceType = [
            'melody.musicxml' => 'master.musicxml',
            'partify.musicxml' => 'master.musicxml',
            "{$name}.dynamic.svg" => "{$name}.musicxml",
            "{$name}.dynamic.svg.info.json" => "{$name}.dynamic.svg",
            "{$name}.ly" => "{$name}.musicxml",
            "{$name}.pdf" => "{$name}.ly",
            "{$name}.tuples.json" => "melody.musicxml",
        ];
        // Default to using the original file.
        $sourceType = $media->originalFile;

        if (isset($destToSourceType[$destinationType])) {
            $sourceType = $destToSourceType[$destinationType];
        }
        if ($sourceType == $destinationType) {
            // You can't create a file from itself. It doesn't exist.
            // Abort to prevent infinite loop.
            abort(404);
        }
        $this->checkExists($media->id, $sourceType);
        return $media->getAbsPath($sourceType);
    }

    /**
     * @brief Get a file response.
     * @param Filename
     * @returns Either an object, if this is to be returned as json, or the file response.
     */
    private function getFileResponse($filepath)
    {
        if (Storage::getVisibility($filepath) == 'private') {
            if (config('app.debug')) {
                $parts = explode("/", $filepath);
                $type = array_pop($parts);
                $id = array_pop($parts);
                $date = date('r', Storage::getTimestamp($filepath));
                $message = "An error was caught generating this file on $date. "
                    . "Use `artisan media:clear-cache $id --type={$type}` and reload to retry.";
                abort(404, json_encode([$message, Storage::get($filepath)]));
            } else {
                abort(404);
            }
        }
        if (substr($filepath, -5) == '.json') {
            return json_decode(Storage::get($filepath), true);
        } elseif (substr($filepath, -4) == '.svg') {
            // It's required to send it as an image so that <img> tags will show it.
            return response(Storage::get($filepath), 200)
                ->header('Content-Type', 'image/svg+xml');
        }
        // Note: musicxml files should have their XML declaration,
        // so Storage will automatically send them with Content-Type application/xml.
        return Storage::response($filepath);
    }

    /**
     * @brief Creates a media file if it doesn't exist, otherwise, aborts execution.
     * @param $number The number of the media entry
     * @param $type The filename of the media that we want
     */
    private function checkExists($number, string $type)
    {
        $media = Media::find($number);
        if (! $media) {
            abort(500, "Could not find media entry $number");
        } elseif (Storage::exists($media->getPath($type))) {
            return;
        } else {
            // $this->get() will also abort if there was an error.
            $this->get($number, $type);
            return;
        }
        abort(404);
    }

    public function post(Request $request)
    {
        $this->verifyJWT($request->jwt);
        $filename = 'original';
        $media = new Media([
            "originalFile" => $filename,
            "textID" => $request->textID ?? null,
            "tuneID" => $request->tuneID ?? null,
        ]);
        $media->save();
        $request->file('file')->storeAs(Media::getDir() . "/$media->id", $filename);
        $media->updateFileType();
        return $media;
    }

    /**
     * @brief Verify, Validate, and check uuid in JWT.
     * @param string $jwt The JWT, signed by SSO, containing a UUID that we trust.
     */
    private function verifyJWT($jwt)
    {
        $path = 'app/sso-public.key';
        if (App::environment("testing")) {
            $path = 'app/testing-sso-public.key';
        }
        $keychain = new Keychain();
        if (! $keychain->getPublicKey('file://' . storage_path($path))) {
            abort(500, "Trusted key not set up properly.");
        }

        if ($jwt) {
            // TODO: Cleaner abort if parsing doesn't work.
            $token = ( new Parser())->parse((string) $jwt);
            $claims = $token->getClaims();
            $data = new ValidationData();

            if ($token->validate($data)) {
                if ($token->verify(
                    new Sha256(),
                    $keychain->getPublicKey('file://' . storage_path($path))
                ) ) {
                    if ($claims['action'] == 'prove_identity' &&
                        TrustedUUID::where([ 'uuid' => $claims['uuid'] ])->exists() ) {
                        return true;
                    }
                    // Forbidden -- user not trusted.
                    abort(403);
                }
            }
        }
        // Not authorized -- JWT missing or not trusted.
        abort(401);
    }
}
