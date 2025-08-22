<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use App\Models\Department; 
use App\Models\Permission; 

class DepartmentController extends Controller
{
    /**
     * Display a listing of the departments.
     */

    public function __construct()
    {
        $this->authorizeResource(Department::class, 'department');
    }

    public function index()
    {
        $departments = Department::latest()->paginate(15);
        return view('superadmin.department.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        $departments = Department::latest()->paginate(15);
        return view('superadmin.department.index', compact('departments'));
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
       
        $input = $request->all();

        if (isset($input['codes']) && is_string($input['codes'])) {
            $input['codes'] = array_map('trim', explode(',', $input['codes']));
        }

        // Validation
        $validator = Validator::make($input, [
            'name' => 'required|string|max:100|unique:departments,name',
            'codes' => 'required|array|min:1',
            'codes.*' => 'required|string|alpha|min:3|max:4',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Department::create([
                'name' => $input['name'],
                'codes' => $input['codes'],
                'description' => $input['description'] ?? null,
                'is_active' => isset($input['is_active']) ? (bool)$input['is_active'] : true,
            ]);

            

            return redirect()->back()->with('success', 'Department created successfully.');

        } catch (\Exception $e) {
            Log::error("Department creation failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create department.')->withInput();
        }
    }



    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        return view('superadmin.department.show', compact('department'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        return view('superadmin.department.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        $input = $request->all();
        // dd($input); 
        // exit; 

        // Convert codes to array if it's a string
        if (isset($input['codes']) && is_string($input['codes'])) {
            $input['codes'] = array_map('trim', explode(',', $input['codes']));
        }

        // Validate the updated $input, not the original request data
        $validator = Validator::make($input, [
            'name'  => 'required|string|max:100|unique:departments,name,' . $department->id,
            'codes' => ['required', 'array', 'min:1'],
            'codes.*' => ['string', 'alpha', 'min:3', 'max:4'],
            'description' => 'nullable|string|max:1000',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $department->update([
                'name'        => $input['name'],
                'codes'       => $input['codes'],
                'description' => $input['description'] ?? null,
                'is_active'   => isset($input['is_active']) ? (bool)$input['is_active'] : true,
            ]); 

            return redirect()->back()->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            Log::error("Department update failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update department.')->withInput();
        }
    }


    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        try {
            $department->delete();
            return redirect()->back()
                             ->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Department deletion failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete department.');
        }
    }

    /**
     * Optional: Restore a soft-deleted department
     */
    public function restore($id)
    {
        try {
            $department = Department::withTrashed()->findOrFail($id);
            $department->restore();
            return redirect()->back()
                             ->with('success', 'Department restored successfully.');
        } catch (\Exception $e) {
            Log::error("Department restore failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to restore department.');
        }
    }
}
