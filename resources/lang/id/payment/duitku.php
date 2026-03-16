<?php

return [
    'messages' => [
        'redirect_to_payment' => 'Silakan lengkapi pembayaran di halaman berikutnya.',
        'transfer_to_va' => 'Silakan transfer ke nomor Virtual Account: :number',
        'scan_qris' => 'Silakan scan kode QRIS untuk menyelesaikan pembayaran.',
        'open_app_payment' => 'Silakan buka aplikasi pembayaran untuk melanjutkan.',
        'payment_pending' => 'Pembayaran sedang diproses. Silakan tunggu konfirmasi.',
        'payment_success' => 'Pembayaran berhasil! Terima kasih telah berbelanja.',
        'payment_failed' => 'Pembayaran gagal. Silakan coba lagi atau hubungi customer service.',
        'payment_cancelled' => 'Pembayaran dibatalkan oleh pengguna.',
    ],
    'errors' => [
        'api_request_failed' => 'Gagal menghubungi server pembayaran (Kode: :code). :message',
        'unknown_error' => 'Terjadi kesalahan yang tidak diketahui.',
        'invalid_response' => 'Respon dari server pembayaran tidak valid.',
        'connection_error' => 'Gagal terhubung ke server pembayaran. Silakan coba beberapa saat lagi.',
        'minimum_amount' => 'Minimal pembayaran adalah Rp 10.000.',
        'order_id_too_long' => 'ID Order tidak boleh lebih dari 50 karakter.',
        'invalid_signature' => 'Verifikasi keamanan gagal. Permintaan tidak valid.',
        'merchant_mismatch' => 'Kode merchant tidak cocok. Permintaan ditolak.',
        'callback_processing_failed' => 'Gagal memproses notifikasi pembayaran.',
        'transaction_not_found' => 'Transaksi tidak ditemukan.',
        'amount_mismatch' => 'Jumlah pembayaran tidak sesuai dengan order.',
    ],
    'labels' => [
        'payment_method' => 'Metode Pembayaran',
        'amount' => 'Jumlah Pembayaran',
        'order_id' => 'ID Order',
        'reference' => 'Referensi Transaksi',
        'status' => 'Status',
        'paid_at' => 'Dibayar Pada',
        'va_number' => 'Nomor Virtual Account',
        'expiry' => 'Kadaluarsa Dalam',
        'minutes' => 'menit',
    ],
];
