<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Model;

class LoanApproval extends Model
{
    use MultiTenant;

    protected $table = 'loan_approvals';

    protected $fillable = [
        'loan_id',
        'approval_level',
        'approval_level_name',
        'approver_member_id',
        'status',
        'remarks',
        'approved_at',
        'approved_by_user_id',
        'tenant_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Approval level constants
    const LEVEL_TRUSTEE_1 = 1;
    const LEVEL_TRUSTEE_2 = 2;
    const LEVEL_SECRETARY = 3;
    const LEVEL_CHAIRMAN = 4;

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function approver()
    {
        return $this->belongsTo(Member::class, 'approver_member_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return _lang('Pending');
            case self::STATUS_APPROVED:
                return _lang('Approved');
            case self::STATUS_REJECTED:
                return _lang('Rejected');
            default:
                return _lang('Unknown');
        }
    }

    public function getApprovalLevelTextAttribute()
    {
        return _lang($this->approval_level_name);
    }

    /**
     * Get formatted approved_at date
     */
    public function getFormattedApprovedAtAttribute()
    {
        if ($this->approved_at) {
            $date_format = get_date_format();
            $time_format = get_time_format();
            return \Carbon\Carbon::parse($this->approved_at)->format("$date_format $time_format");
        }
        return null;
    }
}
