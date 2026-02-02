<?php

namespace App\Models;

use App\Traits\Member;
use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model {

    use MultiTenant, Member;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deposit_requests';

    protected $fillable = [
        'member_id', 'method_id', 'credit_account_id', 'amount', 'converted_amount', 'charge',
        'description', 'requirements', 'attachment', 'user_transaction_id', 'user_reference',
        'deposit_request_group_id', 'status', 'transaction_id',
    ];

    public function method() {
        return $this->belongsTo('App\Models\DepositMethod', 'method_id')->withDefault();
    }

    public function member() {
        return $this->belongsTo('App\Models\Member', 'member_id')->withDefault();
    }

    public function account() {
        return $this->belongsTo('App\Models\SavingsAccount', 'credit_account_id')->withDefault();
    }

    public function getRequirementsAttribute($value) {
        return json_decode($value);
    }

    /** All deposit requests in the same submission (same group), including this one. */
    public function getGroupRequestsAttribute() {
        if (! $this->deposit_request_group_id) {
            return collect([]);
        }
        return self::with(['account.savings_type', 'account.savings_type.currency'])
            ->where('deposit_request_group_id', $this->deposit_request_group_id)
            ->orderBy('id')
            ->get();
    }
}