<?php

namespace App\Imports;

use App\Models\BankTransaction;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BankStatementImport implements ToModel, WithHeadingRow
{
    /**
     * Clean amount fields (NA, empty, commas)
     */
    private function cleanAmount($value)
    {
        if (
            $value === null ||
            trim($value) === '' ||
            strtoupper(trim($value)) === 'NA'
        ) {
            return null; // change to 0 if required
        }

        return (float) str_replace(',', '', $value);
    }

    /**
     * Universal date parser
     * Handles:
     * - Excel numeric dates
     * - 20-Dec-2025
     * - 20/12/2025
     * - 20-Dec-\n2025
     * - 2025-12-20
     */
    private function parseDate($value)
    {
        if (!$value) {
            return null;
        }

        //  Excel numeric date
        if (is_numeric($value)) {
            return Carbon::instance(
                ExcelDate::excelToDateTimeObject($value)
            );
        }

        // Normalize text (remove newlines & extra spaces)
        $value = preg_replace('/\s+/', ' ', trim($value));

        // Try known bank formats
        $formats = [
            'd-M-Y',   // 20-Dec-2025
            'd/m/Y',   // 20/12/2025
            'd-m-Y',   // 20-12-2025
            'Y-m-d',   // 2025-12-20
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (\Exception $e) {
                // try next format
            }
        }

        // Final fallback
        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            Log::warning('Invalid date skipped in bank import', [
                'value' => $value
            ]);
            return null;
        }
    }

    public function model(array $row)
    {
        $date   = $this->parseDate($row['transaction_date'] ?? null);
        $chqRef = $row['cheque_no_refno'] ?? null;

        // Duplicate check
        $tranId = $row['tran_id'] ?? null;

        if ($tranId) {
            $exists = BankTransaction::where('tran_id', $tranId)->exists();
            if ($exists) {
                return null;
            }
        }

        return new BankTransaction([
            'date' => $date,

            'tran_id' => $row['tran_id'] ?? null,

            'transaction_remarks' => $row['transaction_remarks'] ?? null,

            'chq_ref_no' => $chqRef,

            'value_date' => $this->parseDate($row['value_date'] ?? null),

            'withdrawal' => $this->cleanAmount($row['withdrawl_dr'] ?? null),

            'deposit' => $this->cleanAmount($row['deposit_cr'] ?? null),

            'closing_balance' => $this->cleanAmount($row['balance'] ?? null),

            'note' => null,
            'marketing_person' => null,
        ]);
    }
}