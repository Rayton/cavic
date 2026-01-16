<?php

namespace App\Models;

use App\Traits\MultiTenant;
use Illuminate\Database\Eloquent\Model;

class Leader extends Model
{
    use MultiTenant;

    protected $table = 'leaders';

    protected $fillable = [
        'member_id',
        'position',
        'status',
        'tenant_id',
    ];

    // Position constants
    const POSITION_SECRETARY = 'secretary';
    const POSITION_CHAIRMAN = 'chairman';

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public static function getPositions()
    {
        return [
            self::POSITION_SECRETARY => _lang('Secretary'),
            self::POSITION_CHAIRMAN => _lang('Chairman'),
        ];
    }

    public function getPositionTextAttribute()
    {
        $positions = self::getPositions();
        return $positions[$this->position] ?? $this->position;
    }

    public static function getSecretary($tenantId = null)
    {
        $tenantId = $tenantId ?? request()->tenant->id ?? null;
        return self::where('position', self::POSITION_SECRETARY)
            ->where('status', 1)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    public static function getChairman($tenantId = null)
    {
        $tenantId = $tenantId ?? request()->tenant->id ?? null;
        return self::where('position', self::POSITION_CHAIRMAN)
            ->where('status', 1)
            ->where('tenant_id', $tenantId)
            ->first();
    }
}
