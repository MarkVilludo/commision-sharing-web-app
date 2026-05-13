<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserType extends Model
{
    /** @use HasFactory<\Database\Factories\UserTypeFactory> */
    use HasFactory;

    /**
     * `percentage` = relative weight for splitting monthly profit among recipients (see {@see ProfitPool}).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'percentage',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:3',
        ];
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Non-admin users (commission distribution recipients) for this type.
     *
     * @return HasMany<User, $this>
     */
    public function recipientUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('is_admin', false);
    }
}
