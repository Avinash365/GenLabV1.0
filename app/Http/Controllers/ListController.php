<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\NewBooking;

class ListController extends Controller
{
    
    public function view(){
        return view('Reportfrmt.index');
    }    
    // Clients
    public function clients(Request $request)
    { 


        $q = $request->q ?? '';

        $clients = Client::where('name', 'like', "%$q%")
            ->get(['id', 'name'])
            ->map(fn($item) => [
                'id' => $item->id,
                'text' => $item->name
            ]);

        return response()->json($clients);
    }

    // Invoice Numbers
    public function invoices(Request $request)
    {
        $q = $request->q ?? '';

        $invoices = Invoice::select('invoice_no')
            ->distinct()
            ->where('invoice_no', 'like', "%$q%")
            ->get()
            ->map(fn($item) => [
                'id' => $item->invoice_no,
                'text' => $item->invoice_no
            ]);

        return response()->json($invoices);
    }

    // Ref Numbers
    public function refNos(Request $request)
    {
        $q = $request->q ?? '';

        $refnos = NewBooking::select('reference_no')
            ->distinct()
            ->where('reference_no', 'like', "%$q%")
            ->get()
            ->map(fn($item) => [
                'id' => $item->reference_no,
                'text' => $item->reference_no
            ]);

        return response()->json($refnos);
    }
}
