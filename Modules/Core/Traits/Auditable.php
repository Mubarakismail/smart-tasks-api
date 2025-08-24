<?php

namespace Modules\Core\Traits;

use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\Audit;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->getAttributes());
        });
        static::updated(function ($model) {
            $model->writeAudit('updated', $model->getOriginal(), $model->getChanges());
        });
        static::deleted(function ($model) {
            $model->writeAudit('deleted', $model->getOriginal(), null);
        });
        static::restored(function ($model) {
            $model->writeAudit('restored', null, $model->getAttributes());
        });
    }


    public function writeAudit(string $event, ?array $old = null, ?array $new = null): void
    {
        Audit::create([
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'user_id' => optional(Auth::user())->id,
            'event' => $event,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }
}
