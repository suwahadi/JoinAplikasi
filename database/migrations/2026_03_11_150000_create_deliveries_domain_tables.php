<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['group_id', 'status']); // unik per status untuk mencegah multiple ACTIVE/PENDING bersamaan
        });

        Schema::create('credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_item_id')->constrained('product_items')->cascadeOnDelete();
            $table->string('username');
            $table->text('password_encrypted');
            $table->text('instructions_markdown')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
            $table->foreignId('group_member_id')->constrained('group_members')->cascadeOnDelete();
            $table->foreignId('credential_id')->nullable()->constrained('credentials')->nullOnDelete();
            $table->boolean('visible')->default(false);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->unique(['delivery_id', 'group_member_id']);
        });

        Schema::create('delivery_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->nullable()->constrained('deliveries')->nullOnDelete();
            $table->foreignId('delivery_item_id')->nullable()->constrained('delivery_items')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_audits');
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('credentials');
        Schema::dropIfExists('deliveries');
    }
};
