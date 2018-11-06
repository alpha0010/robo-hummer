<?php

namespace App\Http\Controllers;

use App\Media;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

class MediaController extends Controller
{
	public function get( string $number, string $type )
	{
		return Storage::download( Media::getDir() . "/$number/$type" );
	}
}
