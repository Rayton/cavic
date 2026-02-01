<?php
namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use MultiTenant;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loans';

    protected static function booted()
    {
        static::addGlobalScope('borrower_id', function (Builder $builder) {
            if (auth()->check() && auth()->user()->user_type == 'user') {
                if (auth()->user()->all_branch_access == 1) {
                    if (session('branch_id') != '') {
                        $branch_id = session('branch_id') == 'default' ? null : session('branch_id');
                        return $builder->where('branch_id', $branch_id);
                    }
                } else {
                    return $builder->whereHas('borrower', function (Builder $query) {
                        $query->where('branch_id', auth()->user()->branch_id);
                    });
                }
            } else {
                if (session('branch_id') != '') {
                    $branch_id = session('branch_id') == 'default' ? null : session('branch_id');
                    return $builder->whereHas('borrower', function (Builder $query) use ($branch_id) {
                        $query->where('branch_id', $branch_id);
                    });
                }
            }
        });
    }

    public function borrower()
    {
        return $this->belongsTo('App\Models\Member', 'borrower_id')->withDefault();
    }

    public function currency()
    {
        return $this->belongsTo('App\Models\Currency', 'currency_id')->withDefault();
    }

    public function loan_product()
    {
        return $this->belongsTo('App\Models\LoanProduct', 'loan_product_id')->withDefault();
    }

    public function disburseTransaction()
    {
        return $this->hasOne('App\Models\Transaction', 'loan_id')
            ->where('type', 'Loan');
    }

    public function approved_by()
    {
        return $this->belongsTo('App\Models\User', 'approved_user_id')->withDefault();
    }

    public function created_by()
    {
        return $this->belongsTo('App\Models\User', 'created_user_id')->withDefault();
    }

    public function collaterals()
    {
        return $this->hasMany('App\Models\LoanCollateral', 'loan_id');
    }

    public function guarantors()
    {
        return $this->hasMany('App\Models\Guarantor', 'loan_id');
    }

    public function trustee1()
    {
        return $this->belongsTo('App\Models\Member', 'trustee1_member_id')->withDefault();
    }

    public function trustee2()
    {
        return $this->belongsTo('App\Models\Member', 'trustee2_member_id')->withDefault();
    }

    public function secretary()
    {
        return $this->belongsTo('App\Models\Leader', 'secretary_leader_id')->withDefault();
    }

    public function chairman()
    {
        return $this->belongsTo('App\Models\Leader', 'chairman_leader_id')->withDefault();
    }

    public function repayments()
    {
        return $this->hasMany('App\Models\LoanRepayment', 'loan_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\LoanPayment', 'loan_id');
    }

    /**
     * Transactions of type Loan_Repayment for this loan (actual amounts paid).
     */
    public function repaymentTransactions()
    {
        return $this->hasMany('App\Models\Transaction', 'loan_id')->where('type', 'Loan_Repayment');
    }

    public function next_payment()
    {
        return $this->hasOne('App\Models\LoanRepayment', 'loan_id')
            ->where('status', 0)
            ->orderBy('id', 'asc')
            ->withDefault();
    }

    public function approvals()
    {
        return $this->hasMany('App\Models\LoanApproval', 'loan_id')->orderBy('approval_level', 'asc');
    }

    public function current_approval()
    {
        return $this->hasOne('App\Models\LoanApproval', 'loan_id')
            ->where('status', LoanApproval::STATUS_PENDING)
            ->orderBy('approval_level', 'asc')
            ->withDefault();
    }

    public function isFullyApproved()
    {
        $allApprovals = $this->approvals;
        if ($allApprovals->count() < 4) {
            return false;
        }
        return $allApprovals->every(function ($approval) {
            return $approval->status == LoanApproval::STATUS_APPROVED;
        });
    }

    public function getApprovalProgressAttribute()
    {
        $approvals = $this->approvals;
        $approved = $approvals->where('status', LoanApproval::STATUS_APPROVED)->count();
        return [
            'current' => $approved,
            'total' => 4,
            'percentage' => ($approved / 4) * 100
        ];
    }

    public function getFirstPaymentDateAttribute($value)
    {
        $date_format = get_date_format();
        return \Carbon\Carbon::parse($value)->format("$date_format");
    }

    public function getReleaseDateAttribute($value)
    {
        if ($value != null) {
            $date_format = get_date_format();
            return \Carbon\Carbon::parse($value)->format("$date_format");
        }
    }

    public function getApprovedDateAttribute($value)
    {
        if ($value != null) {
            $date_format = get_date_format();
            return \Carbon\Carbon::parse($value)->format("$date_format");
        }
    }

    public function getCreatedAtAttribute($value)
    {
        $date_format = get_date_format();
        $time_format = get_time_format();
        return \Carbon\Carbon::parse($value)->format("$date_format $time_format");
    }

    public function getUpdatedAtAttribute($value)
    {
        $date_format = get_date_format();
        $time_format = get_time_format();
        return \Carbon\Carbon::parse($value)->format("$date_format $time_format");
    }

}
