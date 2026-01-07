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

date_default_timezone_set('Asia/Kuala_Lumpur');

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

        if (
            !$notification ||
            !$notification->is_active ||
            $now->lt($notification->start_time) ||
            $now->gt($notification->end_time)
        ) {
            $this->info('🔕 WhatsApp notifications disabled.');
            return;
        }

        //set work hours

        $workStart = Carbon::createFromTime(7, 0);   // 08:00
        $workEnd   = Carbon::createFromTime(19, 0);  // 18:00

        if (!$now->between($workStart, $workEnd)) {
            $this->info('⏰ Outside work hours.');
            return;
        }

        //capacity range

        $capacity = CapacitySetting::first();
        $emptyMax = $capacity->empty_to;
        $halfMax  = $capacity->half_to;
        $fullMin  = $halfMax + 1;

        //send to sv only

        $phones = User::where('role', 4)
                    ->whereNotNull('phone')
                    ->pluck('phone')               // get all phone numbers
                    ->map(fn($phone) => '6' . ltrim($phone, '0')); 

        if ($phones->isEmpty()) {
            $this->warn('⚠️ No supervisors found.');
            return;
        }

        $whatsapp = new WhatsAppSender();

        
        $devices = Device::with('asset')->get();

        foreach ($devices as $device) {

            $capacityValue = rand(0, 100);

            // Insert sensor reading
            Sensor::create([
                'device_id' => $device->id_device,
                'battery'   => rand(30, 100),
                'capacity'  => $capacityValue,
                'time'      => now(),
                'network'   => 'LTE'
            ]);

            $this->info("{$device->device_name} → {$capacityValue}%");

            //detect when bin is full
            
            if ($capacityValue >= $fullMin) {

                $prev = Sensor::where('device_id', $device->id_device)
                              ->orderBy('time', 'desc')
                              ->skip(1)
                              ->first();

                if (!$prev || $prev->capacity < $fullMin) {

                    $message = $this->buildMessage($device);

                    foreach ($phones as $phone) {
                        $whatsapp->send($phone, $message);
                    }

                    $this->info('📲 WhatsApp alert sent.');
                }
            }
        }
    }

    private function buildMessage($device)
    {
        $now = Carbon::now();

        return
        "🚨 *TONG SAMPAH PENUH* 🚨\n\n" .
        "📍 Lokasi: {$device->asset->location}\n" .
        "🗑️ Status: Tong sampah telah penuh\n\n" .
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
