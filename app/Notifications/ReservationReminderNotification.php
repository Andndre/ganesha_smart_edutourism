<?php

namespace App\Notifications;

use App\Models\Reservation;
use App\Models\VillageSettings;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ReservationReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Reservation $reservation) {}

    public function via(object $notifiable): array
    {
        return [WebPushChannel::class];
    }

    public function toWebPush(object $notifiable, Notification $notification): WebPushMessage
    {
        $openTime = Carbon::parse(VillageSettings::get()->open_time)->format('H:i');
        $date = $this->reservation->scheduled_date->translatedFormat('d F Y');

        // TODO: add multi-language support for the notification message
        return new WebPushMessage()
            ->title('Pengingat Kunjungan Besok 🌿')
            ->body("Kunjungan Anda ke Desa Penglipuran dijadwalkan besok, {$date}. Desa buka pukul {$openTime} WITA.")
            ->icon('/favicon.ico')
            ->data(['url' => route('bookings')]);
    }
}
