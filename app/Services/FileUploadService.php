<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileUploadService
{
    public function upload(UploadedFile $file, string $folder): string
    {
        // Path inside public/uploads/{folder}
        $destinationPath = public_path('uploads/' . $folder);

        // Create the folder if it doesn't exist
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Generate a unique filename
        $filename = Carbon::now()->format('Ymd_His') . '_' . Str::random(20) . '.' . $file->getClientOriginalExtension();

        // Move the file to public/uploads/{folder}
        $file->move($destinationPath, $filename);

        // Return the public URL
        return url('uploads/' . $folder . '/' . $filename);
    }
}
