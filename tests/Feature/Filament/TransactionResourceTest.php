<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\User;

it('admin can access Transaction resource index page', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->get(TransactionResource::getUrl('index'));

    $response->assertOk();
});
