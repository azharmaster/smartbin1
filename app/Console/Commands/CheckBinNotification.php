<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\User;

class CheckBinStatus extends Command
{
    protected $signature = 'bin:check-status';

    protected $description = 'Send WhatsApp when bin becomes FULL';

    public function handle()
    {
        // Get all bins with WhatsApp enabled
        $bins = Asset::where('whatsapp_enabled', 1)->get();

        foreach ($bins as $bin) {

            // Get latest sensor data from all devices in this bin
            $latestSensors = $bin->devices()
                ->with('latestSensor')
                ->get()
                ->pluck('latestSensor')
                ->filter();

            if ($latestSensors->isEmpty()) {
                continue;
            }

            // If ANY sensor is FULL (>=85%)
            $isFull = $latestSensors->contains(function ($sensor) {
                return $sensor->capacity >= 85;
            });

            // Prevent repeat spam
            if ($isFull && $bin->last_whatsapp_sent_at === null) {
                $this->sendWhatsApp($bin);

                // Save timestamp
                $bin->update([
                    'last_whatsapp_sent_at' => now(),
                    'bin_status' => 'FULL'
                ]);
            }
        }

        return Command::SUCCESS;
    }

    protected function sendWhatsApp($bin)
    {
        $token = "PDVc#7eH-4YXkXcR5Yvn";

        $recipients = User::where('whatsapp_notify', 1)
            ->whereNotNull('phone')
            ->pluck('phone');

        $message = "⚠️ *BIN FULL ALERT*\n\n";
        $message .= "Bin: {$bin->asset_name}\n";
        $message .= "Status: FULL\n\n";
        $message .= "Please take immediate action.";

        foreach ($supervisors as $phone) {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '60',
                ],
                CURLOPT_HTTPHEADER => [
                    "Authorization: $token"
                ],
            ]);

            curl_exec($curl);
            curl_close($curl);
        }
    }
}
