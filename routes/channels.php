<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('jadwal.{jadwalId}', fn () => true);
