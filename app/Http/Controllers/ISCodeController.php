<?php

namespace App\Http\Controllers;

use App\Models\ISCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\FileUploadService;


class ISCodeController extends Controller
{
   
    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
        $this->authorizeResource(ISCode::class, 'iSCode');   
    }
    
    public function index(Request $request)
    {
        $search = $request->input('search');

        $iscodes = ISCode::with('creator')
        ->when($search, function($query, $search){
            $query->where('Is_code','like',"%$search%");
        })
        ->latest()->paginate(10);
        return view('superadmin.iscodes.index', compact('iscodes'));
    }

    // Show form to create new IS Code
    public function create()
    {
        return view('superadmin.iscodes.create'); // keep consistent view folder
    }

    // Store new IS Code
    public function store(Request $request)
    {

        // dd($request->all()); 
        // exit; 

        $request->validate([
            'Is_code'     => 'required|string|unique:IS_CODE,Is_code|max:100',
            'Description' => 'nullable|string|max:255',
            'upload_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:2048',
        ]);

        try {
            $data = $request->only('Is_code', 'Description');
            $data['created_by'] = Auth::id();

            
            if ($request->hasFile('upload_file')) {
                $data['upload_file'] = $this->fileUploadService->upload(
                    $request->file('upload_file'),
                    'ISCODE'
                );
            }


            ISCode::create($data);

            return redirect()->back()->with('success', 'IS Code created successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create IS Code: ' . $e->getMessage());
        }
    }

    // Show form to edit IS Code
    public function edit(ISCode $iscode)
    {
        return view('superadmin.iscodes.edit', compact('iscode'));
    }

    // Update IS Code
    public function update(Request $request, ISCode $iscode)
    {
        $request->validate([
            'Is_code'     => 'required|string|max:100|unique:IS_CODE,Is_code,' . $iscode->id,
            'Description' => 'nullable|string|max:255',
            'upload_file' => 'nullable|file|mimes:pdf,doc,docx,xlsx,jpg,png|max:2048',
        ]);

        try {
            $data = $request->only('Is_code', 'Description');

             if ($request->hasFile('upload_file')) {
                $data['upload_file'] = $this->fileUploadService->upload(
                    $request->file('upload_file'),
                    'ISCODE'
                );
            } 

            $iscode->update($data);

            return redirect()->back()->with('success', 'IS Code updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update IS Code: ' . $e->getMessage());
        }
    }

    // Delete IS Code
    public function destroy(ISCode $iscode)
    {
        try {
            if ($iscode->upload_file) {
                Storage::disk('public')->delete($iscode->upload_file);
            }

            $iscode->delete();

            return redirect()->back()->with('success', 'IS Code deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete IS Code: ' . $e->getMessage());
        }
    }
}
