<?php

use App\Models\CommissionMonthReport;
use App\Models\User;

test('guests cannot view monthly commission reports', function () {
    $this->get(route('admin.commission-months.index'))
        ->assertRedirect(route('login', absolute: false));
});

test('non-admin users cannot access monthly commission reports', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.commission-months.index'))
        ->assertForbidden();
});

test('admin can list monthly commission reports', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.commission-months.index'))
        ->assertOk()
        ->assertSee(__('Monthly commission reports'));
});

test('admin can view a monthly report detail', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $report = CommissionMonthReport::query()->create([
        'report_month' => '2026-02-01',
        'gross_total' => 1000,
        'total_commission' => 100,
        'total_remaining' => 50,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.commission-months.show', $report))
        ->assertOk()
        ->assertSee(__('By user type'));
});
