<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PaymentGateway extends Model
{
    use HasFactory;

    /**
     * @return MorphMany
     */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'gatewayable');
    }

    /**
     * @param Builder $builder Builder.
     *
     * @return Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('is_default', 1);
    }
}
