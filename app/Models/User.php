<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'salary',
        'commissions',
        'remaining_to_pay',
        'user_type_id',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'salary' => 'decimal:2',
            'commissions' => 'decimal:2',
            'remaining_to_pay' => 'decimal:2',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Users who receive commission when an admin enters a gross total.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeCommissionRecipients(Builder $query): Builder
    {
        return $query->where('is_admin', false);
    }

    /**
     * @return BelongsTo<UserType, $this>
     */
    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }
}
