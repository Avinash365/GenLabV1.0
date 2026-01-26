<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_items';

    protected $fillable = [
        'new_booking_id',
        'sample_description',
        'sample_code',
        'sample_quality',
        'lab_expected_date',
        'amount',
        'particulars', 
        'lab_analysis_code',
        'job_order_no',
        'storage_no',
        'storage_description',
        'received_by_id',
        'received_by_name',
        'received_at',
        'issue_date',
        'job_order_date',
        'status',
        'account_received_at',
        'account_received_by_id',
        'account_received_by_name',
        'dispatched_at',
        'dispatched_by_id',
        'dispatched_by_name',
        'dispatch_by',
        'dispatch_person_name',
        'dispatch_assignment_no',
        'dispatch_comment',
        'sample_details'
    ];


    protected $casts = [
        'job_order_date' => 'date',
        'lab_expected_date' => 'date',
        'amount' => 'decimal:2',
        'received_at' => 'datetime',
        'issue_date' => 'date',
    ];

    /**
     * Relationship: BookingItem belongs to a NewBooking
     */
    public function booking()
    {
        return $this->belongsTo(NewBooking::class, 'new_booking_id');
    }

    /**
     * Relationship: BookingItem belongs to an Analyst (User) via lab_analysis_code -> users.user_code
     */
    public function analyst()
    {
        return $this->belongsTo(User::class, 'lab_analysis_code', 'user_code');
    }

    /**
     * Relationship: BookingItem received by (User)
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_id');
    } 

    

    public function reports()
    {
        return $this->belongsToMany(
            ReportEditorFile::class,
            'booking_item_report',   // pivot table name
            'booking_item_id',       // foreign key on pivot for this model
            'report_editor_file_id'  // foreign key on pivot for related model
        )->withPivot('id','booking_id', 'pdf_path', 'generated_report_path', 'issue_to_date')->withTimestamps();
    }  

    
    public function reports_28days()
    {
        return $this->belongsToMany(
            ReportEditorFile::class,
            'booking_item_report_28day',   // pivot table name
            'booking_item_id',       // foreign key on pivot for this model
            'report_editor_file_id'  // foreign key on pivot for related model
        )->withPivot('id','booking_id', 'pdf_path', 'generated_report_path', 'issue_to_date')->withTimestamps();
    } 

    public function marketingEnquiries()
    {
        return $this->hasMany(MarketingEnquiry::class, 'booking_item_id');
    }

    public function latestMarketingEnquiry()
    {
        return $this->hasOne(MarketingEnquiry::class, 'booking_item_id')->latestOfMany();
    }

}
