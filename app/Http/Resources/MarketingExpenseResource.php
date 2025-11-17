<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MarketingExpenseResource extends JsonResource
{
    public function toArray($request)
    {
        $amount = (float) ($this->amount ?? 0);
        $approvedAmount = (float) ($this->approved_amount ?? 0);
        if ($this->status === 'approved') {
            $approvedAmount = $amount;
        }
        $dueAmount = max(0, $amount - $approvedAmount);

        return [
            'id' => $this->id,
            'marketing_person_code' => $this->marketing_person_code,
            'person_name' => $this->person_name,
            'section' => $this->section,
            'amount' => $amount,
            'approved_amount' => $approvedAmount,
            'due_amount' => $dueAmount,
            'status' => $this->status,
            'from_date' => optional($this->from_date)->toDateString(),
            'to_date' => optional($this->to_date)->toDateString(),
            'description' => $this->description,
            'approval_note' => $this->approval_note,
            'submitted_for_approval' => (bool) $this->submitted_for_approval,
            'approval_summary_path' => $this->approval_summary_path,
            'approval_summary_url' => $this->approval_summary_path ? $this->storageUrl($this->approval_summary_path) : null,
            'receipt_path' => $this->file_path,
            'receipt_url' => $this->file_path ? $this->storageUrl($this->file_path) : null,
            'receipt_paths' => $this->when(isset($this->receipt_paths), function () {
                return array_values((array) $this->receipt_paths);
            }),
            'receipt_urls' => $this->when(isset($this->receipt_paths), function () {
                return collect((array) $this->receipt_paths)
                    ->map(fn ($path) => $this->storageUrl($path))
                    ->filter()
                    ->values()
                    ->all();
            }),
            'personal_period_label' => $this->when(isset($this->personal_period_label), $this->personal_period_label),
            'aggregate_ids' => $this->when(isset($this->aggregate_ids), $this->aggregate_ids),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'approved_at' => optional($this->approved_at)->toIso8601String(),
            'marketing_person' => $this->whenLoaded('marketingPerson', function () {
                return [
                    'id' => $this->marketingPerson->id,
                    'name' => $this->marketingPerson->name,
                    'user_code' => $this->marketingPerson->user_code,
                ];
            }),
            'approver' => $this->whenLoaded('approver', function () {
                return [
                    'id' => $this->approver->id,
                    'name' => $this->approver->name,
                    'email' => $this->approver->email ?? null,
                ];
            }),
        ];
    }

    protected function storageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
