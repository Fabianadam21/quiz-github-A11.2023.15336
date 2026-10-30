<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentVerified extends Notification
{
    use Queueable;

    public function __construct(private readonly Periksa $periksa) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Pembayaran pemeriksaan Anda telah diverifikasi dan dinyatakan lunas.',
            'url' => route('pasien.pembayaran.index'),
            'periksa_id' => $this->periksa->id,
        ];
    }
}
