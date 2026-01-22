<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReceptionContact;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;

class ReceptionController extends Controller
{
    public function index()
    {
        $contacts = ReceptionContact::orderBy('name')->paginate(25);
        return view('superadmin.reception.index', compact('contacts'));
    }

    public function exportPdf()
    {
        $contacts = ReceptionContact::orderBy('name')->get();
        $pdf = Pdf::loadView('superadmin.reception.pdf', compact('contacts'));
        return $pdf->download('reception_contacts.pdf');
    }

    public function exportExcel()
    {
        $contacts = ReceptionContact::orderBy('name')->get();
        $callback = function() use ($contacts) {
            $FH = fopen('php://output', 'w');
            fputcsv($FH, ['Name','Role','Phone','Alternate Phone','Address','Notes']);
            foreach ($contacts as $c) {
                fputcsv($FH, [
                    $c->name,
                    $c->role,
                    $c->phone,
                    $c->alternate_phone,
                    strip_tags($c->address),
                    strip_tags($c->notes),
                ]);
            }
            fclose($FH);
        };
        $filename = 'reception_contacts.csv';
        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function create()
    {
        return view('superadmin.reception.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'alternate_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $data['created_by'] = Auth::id();
        ReceptionContact::create($data);
        return redirect()->route('superadmin.reception.index')->with('success','Contact created.');
    }

    public function show(ReceptionContact $reception)
    {
        if (request()->ajax()) {
            return view('superadmin.reception._modal', ['contact' => $reception]);
        }
        return view('superadmin.reception.show', ['contact' => $reception]);
    }

    public function edit(ReceptionContact $reception)
    {
        if (request()->ajax()) {
            return view('superadmin.reception._edit_modal', ['contact' => $reception]);
        }
        return view('superadmin.reception.edit', ['contact' => $reception]);
    }

    public function update(Request $request, ReceptionContact $reception)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:255',
            'phone' => 'required|string|max:50',
            'alternate_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $reception->update($data);
        return redirect()->route('superadmin.reception.index')->with('success','Contact updated.');
    }

    public function destroy(ReceptionContact $reception)
    {
        $reception->delete();
        return redirect()->route('superadmin.reception.index')->with('success','Contact deleted.');
    }
}
