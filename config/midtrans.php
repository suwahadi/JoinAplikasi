<?php

declare(strict_types=1);

return [
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'is_production' => (bool) env('MIDTRANS_IS_PRODUCTION', false),
    'base_url' => env('MIDTRANS_BASE_URL', 'https://api.sandbox.midtrans.com'),
    'notification_route' => env('MIDTRANS_NOTIFICATION_ROUTE', '/webhooks/midtrans'),
];
