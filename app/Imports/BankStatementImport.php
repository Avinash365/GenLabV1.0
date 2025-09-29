<?php
namespace App\Imports;

use App\Models\BankTransaction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class BankStatementImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
{
    $date = isset($row['transaction_date']) ? Carbon::createFromFormat('d-m-Y', $row['date']) : null;
    $chqRef = $row['chq_ref_number'] ?? null;

    $exists = BankTransaction::where('date', $date)
                ->where('chq_ref_no', $chqRef)
                ->exists();

    if ($exists) {
        // Skip this row silently
        return null;
    }

    return new BankTransaction([
        'date'                     => $date,
        'tran_id'                  => $row['tran_id'] ?? null, 
        'transaction_remarks'      => $row['narration'] ?? null, 
        
        'chq_ref_no'     => $chqRef,
        'value_date'     => isset($row['value_date']) ? Carbon::createFromFormat('d-m-Y', $row['value_date']) : null,
        'withdrawal'     => isset($row['withdrawal_amt']) ? str_replace(',', '', $row['withdrawal_amt']) : null,
        'deposit'        => isset($row['deposit_amt']) ? str_replace(',', '', $row['deposit_amt']) : null,
        'closing_balance'=> isset($row['closing_balance']) ? str_replace(',', '', $row['closing_balance']) : null,
        'note'           => $row['note'] ?? null, 
        'marketing_person' => $row['marketing_person'] ?? null, 
    ]);
}

}
