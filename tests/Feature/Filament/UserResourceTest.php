<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;

it('admin can access User resource index page', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->get(UserResource::getUrl('index'));

    $response->assertOk();
});
