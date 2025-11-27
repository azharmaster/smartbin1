<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetSeeder extends Seeder
{
    public function run()
    {
        DB::table('assets')->insert([
            [
                'asset_name' => 'Projector Epson X100',
                'floor_id' => 1,
                'serialNo' => 'SN-EPS-00123',
                'description' => 'Main hall projector',
                'model' => 'Epson X100',
                'maintenance' => Carbon::now()->subDays(15),
                'category' => 'Electronics',
            ],
            [
                'asset_name' => 'Dell Desktop PC',
                'floor_id' => 2,
                'serialNo' => 'DL-PC-45678',
                'description' => 'Staff workstation',
                'model' => 'Dell Optiplex 3080',
                'maintenance' => Carbon::now()->subDays(40),
                'category' => 'Computer',
            ],
            [
                'asset_name' => 'Air Conditioner LG',
                'floor_id' => 3,
                'serialNo' => 'AC-LG-90012',
                'description' => 'Meeting room AC',
                'model' => 'LG CoolPro 2.0HP',
                'maintenance' => Carbon::now()->subDays(7),
                'category' => 'Appliance',
            ],
            [
                'asset_name' => 'CCTV Camera Hikvision',
                'floor_id' => 1,
                'serialNo' => 'HV-CCTV-33421',
                'description' => 'Entrance security camera',
                'model' => 'Hikvision DS-2CD2143',
                'maintenance' => Carbon::now()->subDays(20),
                'category' => 'Security',
            ],
            [
                'asset_name' => 'Fire Extinguisher ABC',
                'floor_id' => 2,
                'serialNo' => 'FE-ABC-77889',
                'description' => 'Emergency fire equipment',
                'model' => 'ABC 2kg Safety',
                'maintenance' => Carbon::now()->subDays(90),
                'category' => 'Safety',
            ],
            [
                'asset_name' => 'WiFi Router TP-Link',
                'floor_id' => 4,
                'serialNo' => 'TPL-RT-12121',
                'description' => 'Floor network router',
                'model' => 'TP-Link Archer AX20',
                'maintenance' => Carbon::now()->subDays(30),
                'category' => 'Network',
            ],
            [
                'asset_name' => 'Printer HP LaserJet 1020',
                'floor_id' => 3,
                'serialNo' => 'HP-PR-56789',
                'description' => 'Admin office printer',
                'model' => 'LaserJet 1020',
                'maintenance' => Carbon::now()->subDays(5),
                'category' => 'Printer',
            ],
            [
                'asset_name' => 'LED TV Samsung 50"',
                'floor_id' => 1,
                'serialNo' => 'SS-TV-99887',
                'description' => 'Lobby screen',
                'model' => 'Samsung U50A',
                'maintenance' => Carbon::now()->subDays(18),
                'category' => 'Electronics',
            ],
            [
                'asset_name' => 'Smartbin 001"',
                'floor_id' => 1,
                'serialNo' => 'TR001',
                'description' => 'Sensor attached trashbin',
                'model' => 'TRX001',
                'maintenance' => Carbon::now()->subDays(18),
                'category' => 'SmartBin',
            ],
            [
                'asset_name' => 'Ceiling Fan Panasonic',
                'floor_id' => 4,
                'serialNo' => 'PN-FAN-66541',
                'description' => 'Classroom fan unit',
                'model' => 'Panasonic AirFlow A1',
                'maintenance' => Carbon::now()->subDays(22),
                'category' => 'Appliance',
                'x'=> 215.00,
                'y' => 212.00,
            ],
            [
                'asset_name' => 'Server Rack UPS',
                'floor_id' => 5,
                'serialNo' => 'UPS-778899',
                'description' => 'Backup power system',
                'model' => 'APC Smart UPS 1500VA',
                'maintenance' => Carbon::now()->subDays(12),
                'category' => 'Power',
            ],
        ]);
    }
}
