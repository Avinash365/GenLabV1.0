<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;

class OnlyOfficeController extends Controller
{
    public function newDocument()
    {
        $documentServerUrl = env('ONLYOFFICE_SERVER_URL');
        $secret = env('ONLYOFFICE_JWT_SECRET');

        $documentKey = "Khirz6zTPdfd7";


        $config = [
            "document" => [
                "fileType" => "docx",
                "key" => $documentKey,
                "title" => "NewDocument.docx",
                "url" => url("storage/docs/{$documentKey}.docx"),  // Make sure this file exists or is accessible
            ],
            "documentType" => "word",
            "editorConfig" => [
                "callbackUrl" => route('onlyoffice.save'),
                "user" => [
                    "id" => 1,
                    "name" => "John Doe"
                ],
                "customization" => [
                    "forcesave" => true
                ]
            ],
        ];


        // JWT token must sign the entire config including document, editorConfig, documentType
        $jwtToken = JWT::encode($config, $secret, 'HS256');
       

        return view('Reportfrmt.editor', [
            'documentServerUrl' => $documentServerUrl,
            'config' => json_encode($config),
            'jwtToken' => $jwtToken,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->all();
        $status = $data['status'] ?? null;
        
        dd($status); 

        if ($status == 2 || $status == 3) {
            $fileUrl = $data['url'];
            $fileName = 'docs/' . $data['key'] . '.docx';

            if (!file_exists(storage_path('app/public/docs'))) {
                mkdir(storage_path('app/public/docs'), 0777, true);
            }

            $contents = file_get_contents($fileUrl);
            file_put_contents(storage_path('app/public/' . $fileName), $contents);
        }

        return response()->json(['error' => 0]);
    }
}
