<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionMonthReport extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'report_month',
        'gross_total',
        'total_commission',
        'total_remaining',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'report_month' => 'date',
            'gross_total' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'total_remaining' => 'decimal:2',
        ];
    }

    /**
     * @return HasMany<CommissionMonthUserTypeLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(CommissionMonthUserTypeLine::class)->orderBy('sort_order');
    }
}
