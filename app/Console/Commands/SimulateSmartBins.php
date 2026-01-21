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
        // 🇲🇾 Timezone
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $now = Carbon::now();

        //Whatsapp on or not
        $notification = WhatsAppNotification::first();
        $canSendWhatsApp = true;

        if (
            !$notification ||
            !$notification->is_active ||
            !$notification->start_date ||
            !$notification->end_date ||
            !$notification->start_time ||
            !$notification->end_time ||
            $now->toDateString() < $notification->start_date ||
            $now->toDateString() > $notification->end_date ||
            Carbon::parse($now->format('H:i'))->lt(Carbon::parse($notification->start_time)) ||
            Carbon::parse($now->format('H:i'))->gt(Carbon::parse($notification->end_time))
        ) {
            $canSendWhatsApp = false;
            $this->info('🔕 WhatsApp notifications disabled.');
        }

        // Work hours
        $workStart = Carbon::createFromTime(7, 0);
        $workEnd   = Carbon::createFromTime(19, 0);

        if (!$now->between($workStart, $workEnd)) {
            $canSendWhatsApp = false;
            $this->info('⏰ Outside work hours.');
        }

        // Check active holidays or events
        $isHolidayToday = Holiday::where('is_active', 1)
            ->where('start_date', '<=', $now->toDateString())
            ->where(function($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now->toDateString());
            })->exists();

        $hasActiveEvent = Event::where('is_active', 1)
            ->where('start_date', '<=', $now->toDateString())
            ->where(function($q) use ($now) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $now->toDateString());
            })->exists();

        if ($isHolidayToday) {
            $canSendWhatsApp = false;
            $this->info('🎉 Today is a holiday. Notifications skipped.');
        }

        if (!$hasActiveEvent) {
            $this->info('ℹ️ No active events today.');
        }

        $capacity = CapacitySetting::first();
        $emptyMax = $capacity->empty_to;
        $halfMax  = $capacity->half_to;
        $fullMin  = $halfMax + 1;

        $phones = User::where('role', 4)
            ->whereNotNull('phone')
            ->pluck('phone')
            ->map(function ($phone) {
                $phone = preg_replace('/\D+/', '', $phone);
                $phone = ltrim($phone, '0');
                return '60' . $phone;
            })
            ->unique()
            ->values();

        $whatsapp = new WhatsAppSender();

        $devices = Device::with('asset')->where('is_active', 1)->get(); // Only active devices
        $fullDevices = [];

        foreach ($devices as $device) {

            // Skip if asset is not active
            if (!$device->asset || $device->asset->is_active != 1) {
                $this->info("⚠️ Skipping {$device->device_name} (Asset inactive)");
                continue;
            }

            // Previous reading
            $prev = Sensor::where('device_id', $device->id_device)
                        ->latest('time')
                        ->first();

            $capacityValue = rand(0, 100);

            //Insert sensor data REGARDLESS of WhatsApp state
            Sensor::create([
                'device_id' => $device->id_device,
                'battery'   => rand(30, 100),
                'capacity'  => $capacityValue,
                'time'      => now(),
                'network'   => 'LTE'
            ]);

            $this->info("{$device->device_name} → {$capacityValue}%");

            // Full-bin detection
            if ($capacityValue >= $fullMin) {
                if (!$prev || $prev->capacity < $fullMin) {
                    $fullDevices[] = $device;
                }
            }
        }

        if ($canSendWhatsApp && !empty($fullDevices)) {

            $deviceList = '';
            $message = $this->buildMessage($fullDevices, $deviceList);

            foreach ($phones as $phone) {
                $whatsapp->send($phone, $message);
            }

            // ✅ Save notification log
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
