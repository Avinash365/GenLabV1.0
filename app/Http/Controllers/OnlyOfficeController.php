<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnlyOfficeController extends Controller
{
    /**
     * Load a new ONLYOFFICE document editor
     */
    public function newDocument()
    {
        $documentServerUrl = env('ONLYOFFICE_SERVER_URL', 'http://localhost:8080');

        // Unique key for this document session
        $documentKey = uniqid('doc_', true);

        // Editor configuration: all permissions enabled
        $config = [
            "document" => [
                "fileType" => "docx",
                "key" => $documentKey,
                "title" => "NewDocument.docx",
                "url" => url("storage/docs/sample.docx"), // make sure this file exists
            ],
            "documentType" => "word",
            "editorConfig" => [
                "callbackUrl" => route('onlyoffice.save'),
                "user" => [
                    "id" => 1,
                    "name" => "John Doe"
                ],
                "permissions" => [
                    "edit" => true,
                    "download" => true,
                    "print" => true,
                    "comment" => true,
                    "fillForms" => true,
                    "review" => true
                ],
                "customization" => [
                    "forcesave" => true,
                    "hideRightMenu" => false,
                    "toolbarNoTabs" => false
                ]
            ],
        ];

        return view('Reportfrmt.editor', [
            'documentServerUrl' => $documentServerUrl,
            'config' => json_encode($config),
        ]);
    }

    /**
     * Save document callback from ONLYOFFICE
     */
    public function save(Request $request)
    {
        $data = $request->all();
        $status = $data['status'] ?? null;

        // Only save if document is ready to save
        if ($status == 2 || $status == 3) {
            $fileUrl = $data['url'];
            $fileName = 'docs/' . $data['key'] . '.docx';

            // Ensure directory exists
            if (!file_exists(storage_path('app/public/docs'))) {
                mkdir(storage_path('app/public/docs'), 0777, true);
            }

            // Download and save the file
            $contents = file_get_contents($fileUrl);
            file_put_contents(storage_path('app/public/' . $fileName), $contents);
        }

        return response()->json(['error' => 0]);
    }
}
