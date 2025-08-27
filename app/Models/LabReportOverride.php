<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabReportOverride extends Model
{
    protected $table = 'lab_report_overrides';
    protected $fillable = [
        'format',
        'reference_no',
        'start_date',
        'completion_date',
        'letter_ref',
        'results',
        'conformity',
        'created_by',
        'updated_by',
    ];
}
