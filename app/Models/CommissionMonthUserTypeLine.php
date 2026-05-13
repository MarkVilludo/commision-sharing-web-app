<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionMonthUserTypeLine extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'commission_month_report_id',
        'user_type_id',
        'user_type_name',
        'percentage',
        'recipient_count',
        'total_salary',
        'total_commission',
        'total_remaining',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:3',
            'total_salary' => 'decimal:2',
            'total_commission' => 'decimal:2',
            'total_remaining' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<CommissionMonthReport, $this>
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(CommissionMonthReport::class, 'commission_month_report_id');
    }

    /**
     * @return BelongsTo<UserType, $this>
     */
    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }
}
