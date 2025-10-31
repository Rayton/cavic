<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use MultiTenant;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'currency';

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function savings_products(){
        return $this->hasMany(SavingsProduct::class, 'currency_id');
    }

    public function bank_accounts(){
        return $this->hasMany(BankAccount::class, 'currency_id');
    }
}