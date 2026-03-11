<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Filament\Resources\Groups\GroupResource;
use App\Models\User;

it('admin can access Group resource index page', function (): void {
    $user = User::factory()->create([
        'role' => UserRole::ADMIN,
    ]);

    $this->actingAs($user);

    $response = $this->get(GroupResource::getUrl('index'));

    $response->assertOk();
});
