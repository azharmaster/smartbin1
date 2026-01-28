<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Device;
use App\Models\CapacitySetting;
use App\Models\WhatsAppNotification;
use App\Models\User;
use App\Models\Sensor;
use App\Services\WhatsAppSender;
use App\Models\NotificationLog;
use App\Models\Holiday;
use App\Models\Event;
use Illuminate\Support\Str;

class SimulateSmartBins extends Command
{
    protected $signature = 'smartbin:simulate';
    protected $description = 'Simulate SmartBin sensor data and send notifications';

    public function handle()
{
    date_default_timezone_set('Asia/Kuala_Lumpur');
    $now = Carbon::now();

    $canSendWhatsApp = true;

    // 1️⃣ WhatsApp notification active and within schedule
    $notification = WhatsAppNotification::first();
    if (
        !$notification ||
        !$notification->is_active ||
        !$notification->start_time ||
        !$notification->end_time ||
        Carbon::parse($now->format('H:i'))->lt(Carbon::parse($notification->start_time)) ||
        Carbon::parse($now->format('H:i'))->gt(Carbon::parse($notification->end_time))
    ) {
        $canSendWhatsApp = false;
        $this->info('🔕 WhatsApp notifications disabled (inactive or outside schedule).');
    }

    // 2️⃣ Work hours check
    $workStart = Carbon::createFromTime(7, 0);
    $workEnd   = Carbon::createFromTime(19, 0);

    if (!$now->between($workStart, $workEnd)) {
        $canSendWhatsApp = false;
        $this->info('⏰ Outside work hours.');
    }

    // 3️⃣ Holiday / Event check
    $isHolidayToday = Holiday::where('is_active', 1)
        ->whereDate('start_date', '<=', $now)
        ->where(function ($q) use ($now) {
            $q->whereDate('start_date', $now)
              ->orWhere(function ($q2) use ($now) {
                  $q2->whereNotNull('end_date')
                     ->whereDate('end_date', '>=', $now);
              });
        })->exists();

    $hasActiveEvent = Event::where('is_active', 1)
        ->where(function ($q) use ($now) {
            $q->where(function ($q2) use ($now) {
                $q2->whereNull('end_date')
                ->whereDate('start_date', $now);
            })
            ->orWhere(function ($q2) use ($now) {
                $q2->whereNotNull('end_date')
                ->whereDate('start_date', '<=', $now)
                ->whereDate('end_date', '>=', $now);
            });
        })->exists();

    if ($isHolidayToday || $hasActiveEvent) {
        $canSendWhatsApp = false;
        $this->info('🎉 Today is a holiday or has an active event. Notifications skipped.');
    }

    // 4️⃣ Read REAL sensor data (no simulation)
    $capacity = CapacitySetting::first();
    $fullMin  = $capacity->half_to + 1;

    $devices = Device::with('asset')->where('is_active', 1)->get();
    $fullDevices = [];

    foreach ($devices as $device) {
        if (!$device->asset || $device->asset->is_active != 1) {
            $this->info("⚠️ Skipping {$device->device_name} (Asset inactive)");
            continue;
        }

        $latestSensor = Sensor::where('device_id', $device->id_device)
            ->latest('time')
            ->first();

        if (!$latestSensor) {
            $this->info("⚠️ No sensor data for {$device->device_name}");
            continue;
        }

        $capacityValue = $latestSensor->capacity;

        $this->info("{$device->device_name} → {$capacityValue}%");

        if ($capacityValue >= $fullMin) {
            $fullDevices[] = $device;
        }
    }

    // 5️⃣ Send WhatsApp alerts
    if ($canSendWhatsApp && !empty($fullDevices)) {
        $phones = User::where('role', 4)
            ->whereNotNull('phone')
            ->pluck('phone')
            ->map(fn ($p) => '60' . ltrim(preg_replace('/\D+/', '', $p), '0'))
            ->unique()
            ->values();

        $whatsapp = new WhatsAppSender();
        $deviceList = '';
        $message = $this->buildMessage($fullDevices, $deviceList);

        foreach ($phones as $phone) {
            $whatsapp->send($phone, $message);
        }

        NotificationLog::create([
            'channel'         => 'whatsapp',
            'message_preview' => Str::limit($deviceList, 300),
            'message_full'    => $deviceList,
            'sent_at'         => now(),
        ]);

        $this->info('📲 WhatsApp alert sent and logged.');
    } else {
        $this->info('🔕 WhatsApp alert not sent.');
    }
}

    private function buildMessage($devices, &$deviceList = null)
    {
        $now = Carbon::now();

        // ✅ Count total full bins
        $totalFull = count($devices);

        $deviceList = "";

        foreach ($devices as $index => $device) {
            $latestSensor = Sensor::where('device_id', $device->id_device)
                                ->latest('time')
                                ->first();

            $capacityValue = $latestSensor ? $latestSensor->capacity : 'N/A';

            $deviceList .= 
                "🆔 : {$device->device_name}\n".
                "📍 Lokasi: {$device->asset->location}\n".
                "📊 Kapasiti: {$capacityValue}%\n\n";
        }

        return
        "🚨 *{$totalFull}* *TONG SAMPAH PENUH* 🚨\n\n" .
        "Berikut adalah senarai tong sampah yang telah penuh:\n\n" .
        $deviceList .
        "📅 Tarikh: {$now->format('d-m-Y')}\n" .
        "⏰ Masa: {$now->format('H:i')}\n\n" .
        "⚠️ *Tindakan Segera Diperlukan:*\n" .
        "1. Sila kosongkan tong sampah\n" .
        "2. Bersihkan kawasan sekeliling\n" .
        "3. Pastikan tong diletakkan semula\n\n" .
        "Terima kasih atas kerjasama anda.";
    }
}