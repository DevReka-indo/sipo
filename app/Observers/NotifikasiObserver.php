<?php

namespace App\Observers;

use App\Models\Notifikasi;
use App\Jobs\SendExpoNotificationJob;

class NotifikasiObserver
{
    /**
     * Handle the Notifikasi "created" event.
     */
    public function created(Notifikasi $notifikasi): void
    {
        SendExpoNotificationJob::dispatch($notifikasi->id_notifikasi);
    }

    /**
     * Handle the Notifikasi "updated" event.
     */
    public function updated(Notifikasi $notifikasi): void
    {
        //
    }

    /**
     * Handle the Notifikasi "deleted" event.
     */
    public function deleted(Notifikasi $notifikasi): void
    {
        //
    }

    /**
     * Handle the Notifikasi "restored" event.
     */
    public function restored(Notifikasi $notifikasi): void
    {
        //
    }

    /**
     * Handle the Notifikasi "force deleted" event.
     */
    public function forceDeleted(Notifikasi $notifikasi): void
    {
        //
    }
}
