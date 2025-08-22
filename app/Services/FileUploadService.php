<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FileUploadService
{
    /**
     * Upload file to project root /uploads/{folder} directory.
     *
     * @param UploadedFile $file
     * @param string $folder  Sub-folder inside /uploads (e.g. "documents", "images")
     * @return string File path relative to root
     */
    public function upload(UploadedFile $file, string $folder): string
    {
        // Generate unique filename with datetime + random string
        $filename = Carbon::now()->format('Ymd_His') . '_' . Str::random(20) . '.' . $file->getClientOriginalExtension();

        // Define root-level uploads/{folder} directory
        $destination = base_path("uploads/{$folder}");

        // Ensure directory exists
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        // Move file to root uploads/{folder} folder
        $file->move($destination, $filename);

        // Return relative path (to store in DB, and usable in Blade)
        return "uploads/{$folder}/" . $filename;
    }
}
