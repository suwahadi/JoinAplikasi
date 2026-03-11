<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\User;

it('admin can access Category resource index page', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->get(CategoryResource::getUrl('index'));

    $response->assertOk();
});
