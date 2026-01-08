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
            $now->lt($notification->start_time) ||
            $now->gt($notification->end_time)
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

        $devices = Device::with('asset')->get();
        $fullDevices = [];

        foreach ($devices as $device) {

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

            $message = $this->buildMessage($fullDevices);

            foreach ($phones as $phone) {
                $whatsapp->send($phone, $message);
            }

            $this->info('📲 WhatsApp alert sent.');
        } else {
            $this->info('🔕 WhatsApp alert not sent.');
        }
    }

    private function buildMessage($devices)
    {
        $now = Carbon::now();

        // ✅ Count total full bins
        $totalFull = count($devices);

        $deviceList = "";

        foreach ($devices as $index => $device) {
            $no = $index + 1;

            // 🔎 Get latest sensor reading for this device
            $latestSensor = Sensor::where('device_id', $device->id_device)
                                   ->latest('time')
                                   ->first();

            $capacityValue = $latestSensor ? $latestSensor->capacity : 'N/A';

            $deviceList .= 
            "🆔 : {$device->device_name}\n".
            "📍 Lokasi: {$device->asset->location}\n".
            "📊 Kapasiti: {$capacityValue}%\n\n" ;
        }

        return
        "🚨 *{$totalFull}* *TONG SAMPAH PENUH* 🚨\n\n" .
        "Berikut adalah senarai tong sampah yang telah penuh:\n\n" .
        $deviceList .
        "📅 Tarikh: {$now->format('d-m-Y')}\n" .
        "⏰ Masa: {$now->format('H:i')}\n" .
        "📋 Waktu Notifikasi Dihantar: {$now->format('d-m-Y H:i:s')}\n\n" .
        "⚠️ *Tindakan Segera Diperlukan:*\n" .
        "1. Sila kosongkan tong sampah\n" .
        "2. Bersihkan kawasan sekeliling\n" .
        "3. Pastikan tong diletakkan semula\n\n" .
        "Terima kasih atas kerjasama anda.";
    }
}
