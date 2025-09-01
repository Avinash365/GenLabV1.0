<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileUploadService
{
   
    public function upload(UploadedFile $file, string $folder): string
    {

        $filename = Carbon::now()->format('Ymd_His') . '_' . Str::random(20) . '.' . $file->getClientOriginalExtension();
        $destination = base_path("uploads/{$folder}");

        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $file->move($destination, $filename);

        return "uploads/{$folder}/" . $filename;

    }
}
