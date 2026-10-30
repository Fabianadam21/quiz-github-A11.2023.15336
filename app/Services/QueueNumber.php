<?php

namespace App\Services;

use App\Models\DaftarPoli;

class QueueNumber
{
    public function currentlyCalled(int $jadwalId): ?int
    {
        return DaftarPoli::query()
            ->where('id_jadwal', $jadwalId)
            ->whereDoesntHave('periksas')
            ->orderBy('no_antrian')
            ->value('no_antrian');
    }
}
