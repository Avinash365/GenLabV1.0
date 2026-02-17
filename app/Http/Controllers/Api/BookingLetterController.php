<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;
use App\Models\NewBooking;
use Illuminate\Support\Facades\File;

class BookingLetterController extends Controller
{
    // public function viewLetter($id)
    // {
    //     $booking = NewBooking::findOrFail($id);

    //     if (!$booking->upload_letter_path) {
    //         return response()->json([
    //             'message' => 'Letter not found'
    //         ], 404);
    //     }
    //     // Remove localhost base URL
    //     //$path = str_replace('http://127.0.0.1:8000/', '', $booking->upload_letter_path);
    //     $path = str_replace(url('/'), '', $booking->upload_letter_path);

    //     // if (!file_exists(public_path($path))) {
    //     //     return response()->json([
    //     //         'message' => 'File does not exist',
    //     //         'checked_path' => public_path($path)
    //     //     ], 404);
    //     // }

    //     return response()->file(public_path($path));
    // }

    public function viewLetter($id)
    {
        $booking = NewBooking::findOrFail($id);

        if (!$booking->upload_letter_path) {
            return response()->json([
                'message' => 'Letter not found'
            ], 404);
        }


        // Extract the relative file path from the full URL
        $path = parse_url($booking->upload_letter_path, PHP_URL_PATH);
        
        return response()->file(public_path($path));
    }



    // public function showLetters()
    // {
    //     $basePath = public_path('storage/letters');

    //     if (!File::exists($basePath)) {
    //         abort(404, 'Letters folder not found');
    //     }

    //     $tree = $this->buildTree($basePath);

    //     return view('letters.explorer', compact('tree'));
    // }

    // private function buildTree($path)
    // {
    //     $result = [
    //         'name' => basename($path),
    //         'type' => 'folder',
    //         'children' => []
    //     ];

    //     foreach (File::directories($path) as $folder) {
    //         $result['children'][] = $this->buildTree($folder);
    //     }

    //     foreach (File::files($path) as $file) {
    //         $result['children'][] = [
    //             'name' => $file->getFilename(),
    //             'type' => 'file',
    //             'url' => asset('storage/letters/' .
    //                 str_replace(public_path('storage/letters') . '/', '', $file->getPathname()))
    //         ];
    //     }

    //     return $result;
    // }

    public function showLetters($path = null)
    {
        $basePath = public_path('storage/letters');

        // Prevent directory traversal
        if ($path && str_contains($path, '..')) {
            abort(400, 'Invalid path');
        }

        $currentPath = $path
            ? $basePath . '/' . $path
            : $basePath;

        if (!File::exists($currentPath)) {
            abort(404, 'Folder not found');
        }

        $tree = $this->buildTree($currentPath, $path ?? '');

        return view('letters.explorer', compact('tree', 'path'));
    }

    // private function buildTree($path, $relativePath = '')
    // {
    //     $result = [
    //         'name' => basename($path),
    //         'type' => 'folder',
    //         'children' => []
    //     ];
    //     foreach (File::directories($path) as $folder) {
    //         $result['children'][] = $this->buildTree(
    //             $folder,
    //             $relativePath . '/' . basename($folder)
    //         );
    //     }
    //     foreach (File::files($path) as $file) {

    //         $fileRelativePath = trim(
    //             $relativePath . '/' . $file->getFilename(),
    //             '/'
    //         );
    //         $result['children'][] = [
    //             'name' => $file->getFilename(),
    //             'type' => 'file',
    //             'url' => asset('storage/letters/' . $fileRelativePath)
    //         ];
    //     }
    //     return $result;
    // }
    private function buildTree($path, $relativePath = '')
{
    $result = [
        'name' => basename($path),
        'type' => 'folder',
        'children' => []
    ];

    foreach (File::directories($path) as $folder) {
        $folderName = basename($folder);

        $result['children'][] = [
            'name' => $folderName,
            'type' => 'folder',
            'url'  => url('letters-explorer/' . trim($relativePath . '/' . $folderName, '/'))
        ];
    }

    foreach (File::files($path) as $file) {

        $fileRelativePath = trim(
            $relativePath . '/' . $file->getFilename(),
            '/'
        );

        $result['children'][] = [
            'name' => $file->getFilename(),
            'type' => 'file',
            'url'  => asset('storage/letters/' . $fileRelativePath)
        ];
    }

    return $result;
}

}
