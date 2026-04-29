<?php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait MultiTenant {

    public static function bootMultiTenant() {
        $table = (new self())->getTable();
        static $columnCache = [];

        $hasColumn = function ($table, $column) use (&$columnCache) {
            $key = $table . '.' . $column;
            if (! array_key_exists($key, $columnCache)) {
                $columnCache[$key] = Schema::hasColumn($table, $column);
            }

            return $columnCache[$key];
        };

        if (auth()->check()) {
            $user = auth()->user();

            static::saving(function ($model) use ($user, $hasColumn) {
                if ($hasColumn($model->getTable(), 'tenant_id') && $user->user_type != 'superadmin') {
                    $model->tenant_id = $user->tenant_id;
                }

                if ($hasColumn($model->getTable(), 'created_user_id')) {
                    if (! $model->exists) {
                        $model->created_user_id = $user->id;
                    }
                }
                if ($hasColumn($model->getTable(), 'updated_user_id')) {
                    if ($model->exists) {
                        $model->updated_user_id = $user->id;
                    }
                }
            });

            static::updating(function ($model) use ($user, $hasColumn) {
                if ($hasColumn($model->getTable(), 'tenant_id') && $user->user_type != 'superadmin') {
                    $model->tenant_id = $user->tenant_id;
                }
                if ($hasColumn($model->getTable(), 'updated_user_id')) {
                    $model->updated_user_id = $user->id;
                }
            });

            static::addGlobalScope('tenant', function (Builder $builder) use ($table, $user, $hasColumn) {
                if ($hasColumn($table, 'tenant_id') && $user->user_type != 'superadmin') {
                    return $builder->where($table . '.tenant_id', $user->tenant_id);
                }
            });

        }

    }

}
