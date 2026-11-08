<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['name' => 'Pangkas Rambut', 'category' => 'Perawatan Pribadi', 'description' => 'Layanan pangkas rambut. Sampaikan model atau kebutuhan Anda pada detail permintaan.'],
            ['name' => 'Cuci Motor', 'category' => 'Otomotif', 'description' => 'Layanan cuci motor.'],
            ['name' => 'Cuci Mobil', 'category' => 'Otomotif', 'description' => 'Layanan cuci mobil.'],
            ['name' => 'Tambal Ban', 'category' => 'Otomotif', 'description' => 'Layanan tambal ban untuk kebutuhan kendaraan Anda.'],
            ['name' => 'Ganti Oli', 'category' => 'Otomotif', 'description' => 'Layanan penggantian oli kendaraan.'],
            ['name' => 'Sewa Tenda', 'category' => 'Penyewaan', 'description' => 'Sewa tenda untuk acara. Cantumkan ukuran, jumlah, dan durasi sewa yang dibutuhkan.'],
            ['name' => 'Sewa Lampu', 'category' => 'Penyewaan', 'description' => 'Sewa lampu untuk berbagai kebutuhan. Cantumkan jenis, jumlah, dan durasi sewa.'],
            ['name' => 'Rental Pickup', 'category' => 'Transportasi', 'description' => 'Rental pickup untuk kebutuhan angkut. Cantumkan tanggal, rute, muatan, dan durasi.'],
        ];

        foreach ($catalog as $data) {
            $service = Service::firstOrNew(['name' => $data['name']]);
            $isNew = !$service->exists;
            $service->category = $data['category'];
            $service->description = $service->description ?: $data['description'];
            $service->status = 'active';
            if ($isNew) {
                $service->harga = 0;
                $service->duration = null;
            }
            $service->save();
        }

        Service::whereIn('name', ['Servis Ringan', 'Servis Besar', 'Tune Up', 'Cek Rem'])
            ->update(['status' => 'inactive']);
    }
}
