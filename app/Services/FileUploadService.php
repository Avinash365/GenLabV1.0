<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    // public function upload(UploadedFile $file, string $folder): string
    // {
    //     // Path inside public/uploads/{folder}
    //     $destinationPath = public_path('uploads/' . $folder);

    //     // Create the folder if it doesn't exist
    //     if (!file_exists($destinationPath)) {
    //         mkdir($destinationPath, 0755, true);
    //     }

    //     // Generate a unique filename
    //     $filename = Carbon::now()->format('Ymd_His') . '_' . Str::random(20) . '.' . $file->getClientOriginalExtension();

    //     // Move the file to public/uploads/{folder}
    //     $file->move($destinationPath, $filename);

    //     // Return the public URL
    //     return url('uploads/' . $folder . '/' . $filename);
    // }

    // public function upload($file, string $folder, string  $fileName = null): ?string
    // {
    //     try {

    //         if($fileName){
    //             $path = Storage::disk('s3')->putFileAs($folder, $file, $fileName);
    //         }else{
    //              $path = Storage::disk('s3')->putFile($folder, $file);
    //         }

    //         if (!$path) {
    //             throw new \Exception('Upload returned false');
    //         }
    //         return $path;
    //     } catch (\Throwable $e) {
    //         dd($e->getMessage()); //  THIS WILL SHOW REAL ERROR
    //     }
    // }

    /* why file is not upload to s3? */
    public function upload($file, string $folder, string $fileName = null): ?string
    {
        try {

            // CASE 1: If it's string content (HTML, JSON, etc.)
            if (is_string($file)) {
                $fileName = $fileName ?? uniqid() . '.html';
                $path = $folder . '/' . $fileName;

                $uploaded = Storage::disk('s3')->put($path, $file);

                if (!$uploaded) {
                    throw new \Exception('S3 upload failed for HTML');
                }
                return $path;
            } 
          
            // CASE 2: If it's actual uploaded file
            if ($fileName) {
                $path = Storage::disk('s3')->putFileAs($folder, $file, $fileName);
            } else {
                $path = Storage::disk('s3')->putFile($folder, $file);
            }

            if (!$path) {
                throw new \Exception('Upload returned false');
            }

            return $path;

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    } 

    public function delete(string $path): bool{
        try{

            if(empty($path)){
                throw new \Exception('File path is empty');
            } 

            if(!Storage::disk('s3')->exists($path)){
                throw new \Exception('File does not exist at path: ' . $path);
            }  

            $deleted = Storage::disk('s3')->delete($path);  

            if(!$deleted){
                throw new \Exception('Failed to delete file at path: ' . $path);
            } 
            return $deleted;
            
        } catch (\Throwable $e) {
            dd($e->getMessage());   
        }
    }
    
}
