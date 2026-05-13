<?php

use App\Models\CommissionMonthReport;
use App\Models\User;
use App\Models\UserType;

test('guests are redirected to login from admin commissions', function () {
    $this->get(route('admin.commissions.index'))
        ->assertRedirect(route('login', absolute: false));
});

test('non-admin users cannot access admin commissions', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.commissions.index'))
        ->assertForbidden();
});

test('non-admin users cannot submit commission distribution', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post(route('admin.commissions.store'), [
            'gross_total' => 1000,
            'report_month' => '2026-01',
        ])
        ->assertForbidden();
});

test('non-admin users cannot update department salaries', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post(route('admin.commissions.update-salaries'), [
            'salary_budget' => [],
        ])
        ->assertForbidden();
});

test('admin can view commissions page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.commissions.index'))
        ->assertOk()
        ->assertSee(__('Commission distribution'))
        ->assertSee(__('By user type'))
        ->assertSee(__('Overall totals'))
        ->assertSee(__('Save department salaries'));
});

test('admin can update department salary budget for a single recipient', function () {
    $type = UserType::factory()->create(['percentage' => 10.0]);
    $admin = User::factory()->create(['is_admin' => true]);
    $recipient = User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
        'salary' => 1000,
        'commissions' => 100,
        'remaining_to_pay' => 900,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.commissions.update-salaries'), [
            'salary_budget' => [
                (string) $type->id => 5000,
            ],
        ])
        ->assertRedirect(route('admin.commissions.index', absolute: false));

    $recipient->refresh();
    expect((float) $recipient->salary)->toBe(5000.0);
    expect((float) $recipient->commissions)->toBe(100.0);
    expect((float) $recipient->remaining_to_pay)->toBe(4900.0);
});

test('admin can set shared department budget across multiple recipients proportionally', function () {
    $type = UserType::factory()->create(['percentage' => 5.0]);
    $admin = User::factory()->create(['is_admin' => true]);
    $a = User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
        'salary' => 30,
        'commissions' => 0,
        'remaining_to_pay' => 0,
    ]);
    User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
        'salary' => 70,
        'commissions' => 0,
        'remaining_to_pay' => 0,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.commissions.update-salaries'), [
            'salary_budget' => [
                (string) $type->id => 1000,
            ],
        ])
        ->assertSessionHasNoErrors();

    $a->refresh();
    $users = User::query()->commissionRecipients()->where('user_type_id', $type->id)->orderBy('id')->get();
    expect((float) $users->sum('salary'))->toBe(1000.0);
    expect((float) $users[0]->salary)->toBe(300.0);
    expect((float) $users[1]->salary)->toBe(700.0);
});

test('admin can submit monthly profit and persist recipient commissions', function () {
    $type = UserType::factory()->create(['percentage' => 10.0]);
    $admin = User::factory()->create(['is_admin' => true]);
    $recipient = User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
        'salary' => 1000,
        'commissions' => 0,
        'remaining_to_pay' => 0,
    ]);

    $profit = 5000.0;

    $this->actingAs($admin)
        ->post(route('admin.commissions.store'), [
            'gross_total' => $profit,
            'report_month' => '2026-03',
        ])
        ->assertRedirect(route('admin.commissions.index', absolute: false));

    $recipient->refresh();
    expect((float) $recipient->commissions)->toBe(5000.0);
    expect((float) $recipient->remaining_to_pay)->toBe(0.0);

    expect(CommissionMonthReport::query()->whereDate('report_month', '2026-03-01')->exists())->toBeTrue();
});

test('sum of commissions across recipients equals entered profit', function () {
    $t1 = UserType::factory()->create(['percentage' => 1.0]);
    $t2 = UserType::factory()->create(['percentage' => 4.0]);
    $t3 = UserType::factory()->create(['percentage' => 5.0]);
    $admin = User::factory()->create(['is_admin' => true]);
    User::factory()->create(['is_admin' => false, 'user_type_id' => $t1->id, 'salary' => 0]);
    User::factory()->create(['is_admin' => false, 'user_type_id' => $t2->id, 'salary' => 0]);
    User::factory()->create(['is_admin' => false, 'user_type_id' => $t3->id, 'salary' => 0]);

    $profit = 123.45;

    $this->actingAs($admin)
        ->post(route('admin.commissions.store'), [
            'gross_total' => $profit,
            'report_month' => '2026-07',
        ])
        ->assertSessionHasNoErrors();

    $sum = (float) User::query()->commissionRecipients()->sum('commissions');
    expect($sum)->toBe($profit);
});

test('saving the same report month twice updates the row without unique errors', function () {
    $type = UserType::factory()->create(['percentage' => 10.0]);
    $admin = User::factory()->create(['is_admin' => true]);
    User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
        'salary' => 1000,
    ]);

    $payload = [
        'gross_total' => 1000,
        'report_month' => '2026-06',
    ];

    $this->actingAs($admin)
        ->post(route('admin.commissions.store'), $payload)
        ->assertSessionHasNoErrors();

    $this->actingAs($admin)
        ->post(route('admin.commissions.store'), array_merge($payload, ['gross_total' => 2000]))
        ->assertSessionHasNoErrors();

    expect(CommissionMonthReport::query()->whereRaw("strftime('%Y-%m', report_month) = ?", ['2026-06'])->count())->toBe(1);

    $report = CommissionMonthReport::query()->whereRaw("strftime('%Y-%m', report_month) = ?", ['2026-06'])->first();
    expect((float) $report->gross_total)->toBe(2000.0);
});

test('remaining to pay on users is never negative', function () {
    $type = UserType::factory()->create(['percentage' => 7.5]);
    $admin = User::factory()->create(['is_admin' => true]);
    $recipient = User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
        'salary' => 100,
        'commissions' => 0,
        'remaining_to_pay' => 0,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.commissions.store'), [
            'gross_total' => 10_000,
            'report_month' => '2026-04',
        ])
        ->assertRedirect(route('admin.commissions.index', absolute: false));

    $recipient->refresh();
    expect((float) $recipient->remaining_to_pay)->toBe(0.0);
});
