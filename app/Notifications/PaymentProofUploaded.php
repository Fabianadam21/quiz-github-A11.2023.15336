<?php

namespace App\Notifications;

use App\Models\Periksa;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentProofUploaded extends Notification
{
    use Queueable;

    public function __construct(private readonly Periksa $periksa) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $pasien = $this->periksa->daftarPoli->pasien;

        return [
            'message' => $pasien->nama.' mengunggah bukti pembayaran.',
            'url' => route('dokter.antrian.index'),
            'periksa_id' => $this->periksa->id,
        ];
    }
}
