<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Model;

class LoanApproverSetting extends Model
{
    use MultiTenant;

    protected $table = 'loan_approver_settings';

    protected $fillable = [
        'approval_level',
        'approval_level_name',
        'approver_member_id',
        'status',
        'tenant_id',
    ];

    // Approval level constants
    const LEVEL_TRUSTEE_1 = 1;
    const LEVEL_TRUSTEE_2 = 2;
    const LEVEL_SECRETARY = 3;
    const LEVEL_CHAIRMAN = 4;

    public function approver()
    {
        return $this->belongsTo(Member::class, 'approver_member_id');
    }

    public static function getApprovalLevels()
    {
        return [
            self::LEVEL_TRUSTEE_1 => _lang('Trustee 1'),
            self::LEVEL_TRUSTEE_2 => _lang('Trustee 2'),
            self::LEVEL_SECRETARY => _lang('Secretary'),
            self::LEVEL_CHAIRMAN => _lang('Chairman'),
        ];
    }

    public function getApprovalLevelTextAttribute()
    {
        $levels = self::getApprovalLevels();
        return $levels[$this->approval_level] ?? $this->approval_level_name;
    }
}
