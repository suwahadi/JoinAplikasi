<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Credential;
use App\Models\ProductItem;
use Illuminate\Database\Seeder;

class CredentialSeeder extends Seeder
{
    public function run(): void
    {
        $items = ProductItem::query()->get();

        foreach ($items as $item) {
            $exists = Credential::query()->where('product_item_id', $item->id)->exists();
            if ($exists) {
                continue;
            }

            Credential::query()->create([
                'product_item_id' => $item->id,
                'username' => 'user_'.$item->id,
                'password' => 'password_'.$item->id,
                'instructions_markdown' => "Gunakan kredensial ini untuk masuk ke layanan terkait.\n- Username dan password bersifat rahasia\n- Jangan dibagikan ke pihak lain",
            ]);
        }
    }
}
