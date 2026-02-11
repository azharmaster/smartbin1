<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\User;
use App\Services\WhatsAppSender;
use App\Models\NotificationLog;
use Illuminate\Support\Str;

class DetectOfflineSensors extends Command
{
    protected $signature = 'smartbin:undetected';
    protected $description = 'Detect sensors with no data for over 40 minutes and notify broken devices';

    public function handle()
    {
        $now = Carbon::now();
        $threshold = $now->subMinutes(40);

        $offlineDevices = [];

        $devices = Device::with('asset')
            ->where('is_active', 1)
            ->get();

        foreach ($devices as $device) {

            if (!$device->asset || $device->asset->is_active != 1) {
                continue;
            }

            $latestSensor = Sensor::where('device_id', $device->id_device)
                ->latest('time')
                ->first();

            // ❌ No data OR data too old
            if (
                !$latestSensor ||
                Carbon::parse($latestSensor->time)->lt($threshold)
            ) {

                // 🔹 Insert "undetected" sensor reading
                Sensor::create([
                    'device_id' => $device->id_device,
                    'capacity'  => null,
                    'battery'   => null,
                    'signal'    => 0,
                    'status'    => 'undetected',
                    'time'      => now(),
                ]);

                // (Optional) Mark device as offline
                $device->update([
                    'is_online' => 0
                ]);

                $offlineDevices[] = $device;

                $this->info("❌ {$device->device_name} marked as UNDETECTED");
            }
        }

        // 📲 Send WhatsApp notification
        if (!empty($offlineDevices)) {

            $phones = User::where('role', 4)
                ->whereNotNull('phone')
                ->pluck('phone')
                ->map(fn ($p) => '60' . ltrim(preg_replace('/\D+/', '', $p), '0'))
                ->unique()
                ->values();

            $message = $this->buildMessage($offlineDevices);

            $whatsapp = new WhatsAppSender();
            foreach ($phones as $phone) {
                $whatsapp->send($phone, $message);
            }

            NotificationLog::create([
                'channel'         => 'whatsapp',
                'message_preview' => Str::limit($message, 300),
                'message_full'    => $message,
                'sent_at'         => now(),
            ]);

            $this->info('📲 Offline sensor alert sent.');
        } else {
            $this->info('✅ No offline sensors detected.');
        }
    }

    private function buildMessage($devices)
    {
        $now = Carbon::now();
        $list = '';

        foreach ($devices as $device) {
            $list .=
                "🆔 {$device->device_name}\n" .
                "📍 Lokasi: {$device->asset->location}\n" .
                "⚠️ Status: Sensor tidak dikesan\n\n";
        }

        return
            "🚨 *AMARAN SENSOR TIDAK BERFUNGSI* 🚨\n\n" .
            "Sensor berikut tidak menghantar data melebihi 40 minit:\n\n" .
            $list .
            "📅 Tarikh: {$now->format('d-m-Y')}\n" .
            "⏰ Masa: {$now->format('H:i')}\n\n" .
            "⚠️ *Tindakan Diperlukan:*\n" .
            "1. Periksa sambungan sensor\n" .
            "2. Semak bekalan kuasa\n" .
            "3. Ganti sensor jika rosak\n\n" .
            "Terima kasih.";
    }
}

