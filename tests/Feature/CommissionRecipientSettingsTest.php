<?php

use App\Models\User;
use App\Models\UserType;

test('commission settings page is forbidden for admins', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/commission-settings')
        ->assertForbidden();
});

test('commission settings page is displayed for commission recipients', function () {
    $type = UserType::factory()->create(['percentage' => 2.5]);
    $user = User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
        'salary' => 1000,
    ]);

    $this->actingAs($user)
        ->get('/commission-settings')
        ->assertOk();
});

test('commission recipient can update salary but not role profit weight', function () {
    $type = UserType::factory()->create(['percentage' => 1.0]);
    $user = User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
        'salary' => 1000,
        'commissions' => 250,
        'remaining_to_pay' => 750,
    ]);

    $this->actingAs($user)
        ->patch('/commission-settings', [
            'salary' => 5000,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect('/commission-settings');

    $user->refresh();
    expect((float) $user->salary)->toBe(5000.0);
    expect((float) $user->remaining_to_pay)->toBe(4750.0);

    $type->refresh();
    expect((float) $type->percentage)->toBe(1.0);
});

test('commission settings page shows role profit weight for recipients', function () {
    $type = UserType::factory()->create(['name' => 'Sales Dept', 'percentage' => 2.5]);
    $user = User::factory()->create([
        'is_admin' => false,
        'user_type_id' => $type->id,
    ]);

    $this->actingAs($user)
        ->get('/commission-settings')
        ->assertOk()
        ->assertSee('Sales Dept')
        ->assertSee('2.500');
});

test('guests are redirected from commission settings', function () {
    $this->get('/commission-settings')
        ->assertRedirect(route('login'));
});
