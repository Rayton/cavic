<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use MultiTenant;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bank_accounts';

    public function currency() {
		return $this->belongsTo('App\Models\Currency', 'currency_id')->withDefault();
	}

    public function getOpeningDateAttribute($value) {
        $date_format = get_date_format();
        return \Carbon\Carbon::parse($value)->format("$date_format");
    }
}