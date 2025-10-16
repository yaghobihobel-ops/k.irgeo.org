<?php

namespace App\Models;

use App\Traits\ApiQuery;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Operator extends Model
{
    use GlobalStatus, ApiQuery;

    protected $casts = [
        'fx'                               => 'object',
        'logo_urls'                        => 'array',
        'fixed_amounts'                    => 'array',
        'fixed_amounts_descriptions'       => 'object',
        'local_fixed_amounts'              => 'array',
        'local_fixed_amounts_descriptions' => 'object',
        'suggested_amounts'                => 'array',
        'suggested_amounts_map'            => 'object',
        'fees'                             => 'object',
        'geographical_recharge_plans'      => 'array',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
