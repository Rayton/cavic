<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Model;

class LoanCollectionFollowUp extends Model
{
    use MultiTenant;

    protected $table = 'loan_collection_follow_ups';

    protected $fillable = [
        'loan_repayment_id',
        'loan_id',
        'member_id',
        'outcome',
        'note',
        'next_action_date',
        'promised_payment_date',
        'tenant_id',
        'created_user_id',
    ];

    protected $casts = [
        'next_action_date' => 'date',
        'promised_payment_date' => 'date',
    ];

    const OUTCOME_REACHED = 1;
    const OUTCOME_UNREACHABLE = 2;
    const OUTCOME_PROMISED_TO_PAY = 3;
    const OUTCOME_ESCALATED = 4;
    const OUTCOME_REMINDER_SENT = 5;
    const OUTCOME_RESOLVED = 6;

    public function repayment()
    {
        return $this->belongsTo(LoanRepayment::class, 'loan_repayment_id')->withDefault();
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id')->withDefault();
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id')->withDefault();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_user_id')->withDefault(['name' => _lang('N/A')]);
    }

    public function getOutcomeTextAttribute()
    {
        return match ((int) $this->outcome) {
            self::OUTCOME_REACHED => _lang('Reached'),
            self::OUTCOME_UNREACHABLE => _lang('Unreachable'),
            self::OUTCOME_PROMISED_TO_PAY => _lang('Promised to Pay'),
            self::OUTCOME_ESCALATED => _lang('Escalated'),
            self::OUTCOME_REMINDER_SENT => _lang('Reminder Sent'),
            self::OUTCOME_RESOLVED => _lang('Resolved'),
            default => _lang('Logged'),
        };
    }

    public function getOutcomeThemeAttribute()
    {
        return match ((int) $this->outcome) {
            self::OUTCOME_REACHED => 'active',
            self::OUTCOME_UNREACHABLE => 'overdue',
            self::OUTCOME_PROMISED_TO_PAY => 'ready',
            self::OUTCOME_ESCALATED => 'critical',
            self::OUTCOME_REMINDER_SENT => 'upcoming',
            self::OUTCOME_RESOLVED => 'active',
            default => 'info',
        };
    }

    public function getCreatedAtAttribute($value)
    {
        $date_format = get_date_format();
        $time_format = get_time_format();

        return \Carbon\Carbon::parse($value)->format("$date_format $time_format");
    }
}
