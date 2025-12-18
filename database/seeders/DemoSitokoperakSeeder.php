<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoSitokoperakSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ==========================================================
            // 0) Bersihin (opsional kalau kamu migrate:fresh --seed)
            // ==========================================================
            // Kalau kamu selalu migrate:fresh, bagian ini boleh dihapus.
            DB::table('order_items')->delete();
            DB::table('orders')->delete();
            DB::table('produk_likes')->delete();
            DB::table('produk_views')->delete();
            DB::table('foto_produk')->delete();
            DB::table('usaha_produk')->delete();
            DB::table('produk')->delete();
            DB::table('usaha_pengerajin')->delete();
            DB::table('usaha_jenis')->delete();
            DB::table('usaha')->delete();
            DB::table('pengerajin')->delete();

            // HATI-HATI: users mungkin dipakai login kamu. Kalau aman, boleh delete juga.
            // Kalau tidak mau hapus semua users, comment 2 baris ini.
            DB::table('users')->whereIn('role', ['pengerajin', 'guest'])->delete();
            // DB::table('users')->delete();

            DB::table('kategori_produk')->delete();
            DB::table('jenis_usaha')->delete();

            // ==========================================================
            // 1) KATEGORI PRODUK
            // ==========================================================
            $kategoriData = [
                ['kode' => 'KAT-001', 'nama' => 'Anyaman', 'slug' => 'anyaman'],
                ['kode' => 'KAT-002', 'nama' => 'Batik', 'slug' => 'batik'],
                ['kode' => 'KAT-003', 'nama' => 'Keramik', 'slug' => 'keramik'],
                ['kode' => 'KAT-004', 'nama' => 'Kayu', 'slug' => 'kayu'],
                ['kode' => 'KAT-005', 'nama' => 'Kain', 'slug' => 'kain'],
            ];

            $kategoriIds = [];
            foreach ($kategoriData as $k) {
                $kategoriIds[] = DB::table('kategori_produk')->insertGetId([
                    'kode_kategori_produk' => $k['kode'],
                    'nama_kategori_produk' => $k['nama'],
                    'slug' => $k['slug'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ==========================================================
            // 2) JENIS USAHA
            // ==========================================================
            $jenisData = [
                ['kode' => 'JNS-001', 'nama' => 'Kerajinan Tangan'],
                ['kode' => 'JNS-002', 'nama' => 'Batik & Tekstil'],
                ['kode' => 'JNS-003', 'nama' => 'Keramik & Gerabah'],
                ['kode' => 'JNS-004', 'nama' => 'Kayu & Ukiran'],
            ];

            $jenisIds = [];
            foreach ($jenisData as $j) {
                $jenisIds[] = DB::table('jenis_usaha')->insertGetId([
                    'kode_jenis_usaha' => $j['kode'],
                    'nama_jenis_usaha' => $j['nama'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ==========================================================
            // 3) USERS (PENGERAJIN + CUSTOMER/GUEST)
            //    password pengerajin selalu 12345 (hashed)
            // ==========================================================
            $pengerajinUsers = [
                ['username' => 'pengerajin_andi', 'email' => 'andi.pengerajin@example.com', 'name' => 'Andi Pengerajin'],
                ['username' => 'pengerajin_siti', 'email' => 'siti.pengerajin@example.com', 'name' => 'Siti Pengerajin'],
                ['username' => 'pengerajin_budi', 'email' => 'budi.pengerajin@example.com', 'name' => 'Budi Pengerajin'],
                ['username' => 'pengerajin_rina', 'email' => 'rina.pengerajin@example.com', 'name' => 'Rina Pengerajin'],
                ['username' => 'pengerajin_dedi', 'email' => 'dedi.pengerajin@example.com', 'name' => 'Dedi Pengerajin'],
            ];

            $pengerajinUserIds = [];
            foreach ($pengerajinUsers as $u) {
                $pengerajinUserIds[] = DB::table('users')->insertGetId([
                    'username' => $u['username'],
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'phone' => '08' . rand(1111111111, 9999999999),
                    'address' => 'Alamat ' . $u['name'] . ' - Kota ' . ['Padang', 'Solo', 'Bandung', 'Jogja', 'Malang'][array_rand([0, 1, 2, 3, 4])],
                    'password' => Hash::make('12345'),
                    'role' => 'pengerajin',
                    'profile_picture_path' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // customer (role = guest)
            $guestIds = [];
            for ($i = 1; $i <= 12; $i++) {
                $guestIds[] = DB::table('users')->insertGetId([
                    'username' => 'customer_' . $i,
                    'name' => 'Customer ' . $i,
                    'email' => 'customer' . $i . '@example.com',
                    'phone' => '08' . rand(1111111111, 9999999999),
                    'address' => 'Jl. Pelanggan No.' . $i . ' - Kota ' . ['Jakarta', 'Bogor', 'Depok', 'Tangerang', 'Bekasi'][array_rand([0, 1, 2, 3, 4])],
                    'password' => Hash::make('12345'),
                    'role' => 'guest',
                    'profile_picture_path' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ==========================================================
            // 4) PENGERAJIN (1 user pengerajin -> 1 row pengerajin)
            // ==========================================================
            $pengerajinIds = [];
            foreach ($pengerajinUserIds as $idx => $userId) {
                $pengerajinIds[] = DB::table('pengerajin')->insertGetId([
                    'user_id' => $userId,
                    'kode_pengerajin' => 'PGR-' . str_pad((string) ($idx + 1), 3, '0', STR_PAD_LEFT),
                    'nama_pengerajin' => explode(' ', $pengerajinUsers[$idx]['name'])[0] . ' Craft',
                    'jk_pengerajin' => (rand(0, 1) ? 'P' : 'W'),
                    'usia_pengerajin' => rand(22, 55),
                    'telp_pengerajin' => '08' . rand(1111111111, 9999999999),
                    'email_pengerajin' => $pengerajinUsers[$idx]['email'],
                    'alamat_pengerajin' => 'Workshop ' . ($idx + 1) . ' - ' . ['Bali', 'NTT', 'Jawa Tengah', 'Jawa Barat', 'Sumbar'][$idx],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ==========================================================
            // 5) USAHA (TOKO)
            //    aturan: 1 usaha cuma 1 pengerajin & 1 pengerajin cuma 1 usaha (1:1)
            // ==========================================================
            $usahaData = [
                ['kode' => 'US-001', 'nama' => 'Toko Anyam Indah', 'jenis_idx' => 0],
                ['kode' => 'US-002', 'nama' => 'Batik Nusantara', 'jenis_idx' => 1],
                ['kode' => 'US-003', 'nama' => 'Kayu Jati Craft', 'jenis_idx' => 3],
                ['kode' => 'US-004', 'nama' => 'Keramik Rasa', 'jenis_idx' => 2],
                ['kode' => 'US-005', 'nama' => 'Kain Songket Cantik', 'jenis_idx' => 1],
            ];

            $usahaIds = [];
            foreach ($usahaData as $i => $u) {
                $usahaId = DB::table('usaha')->insertGetId([
                    'kode_usaha' => $u['kode'],
                    'nama_usaha' => $u['nama'],
                    'telp_usaha' => '08' . rand(1111111111, 9999999999),
                    'email_usaha' => Str::slug($u['nama'], '.') . '@example.com',
                    'deskripsi_usaha' => 'Menjual produk kerajinan asli dari pengrajin lokal.',
                    'foto_usaha' => null,
                    'link_gmap_usaha' => null,
                    'status_usaha' => 'aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $usahaIds[] = $usahaId;

                // pivot usaha_jenis
                DB::table('usaha_jenis')->insert([
                    'usaha_id' => $usahaId,
                    'jenis_usaha_id' => $jenisIds[$u['jenis_idx']],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // usaha_pengerajin (1:1)
            // map usaha[0] -> pengerajin[0], dst (unik di kedua sisi)
            foreach ($usahaIds as $i => $usahaId) {
                DB::table('usaha_pengerajin')->insert([
                    'usaha_id' => $usahaId,
                    'pengerajin_id' => $pengerajinIds[$i],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ==========================================================
            // 6) PRODUK + USAHA_PRODUK
            //    produk harus sesuai pengerajin (produk.pengerajin_id)
            // ==========================================================
            $produkByUsaha = []; // usaha_id => [produk_id...]
            $allProduk = [];     // list produk (id, harga)

            $namaProdukPool = [
                'Anyaman' => ['Tas Anyam', 'Keranjang Rotan', 'Tikar Pandan', 'Topi Anyam', 'Dompet Anyam'],
                'Batik' => ['Kain Batik', 'Kemeja Batik', 'Selendang Batik', 'Blouse Batik', 'Dress Batik'],
                'Kayu' => ['Ukiran Kayu', 'Talang Kayu', 'Hiasan Dinding Kayu', 'Miniatur Kayu', 'Kotak Kayu'],
                'Keramik' => ['Vas Keramik', 'Cangkir Keramik', 'Piring Keramik', 'Gelas Keramik', 'Mangkuk Keramik'],
                'Kain' => ['Kain Songket', 'Kain Tenun', 'Selendang Tenun', 'Scarf Tenun', 'Kain Tradisional'],
            ];

            for ($i = 0; $i < count($usahaIds); $i++) {
                $usahaId = $usahaIds[$i];
                $pengerajinId = $pengerajinIds[$i];

                // tentukan “tema” produk per toko biar realistis
                $tema = match ($i) {
                    0 => 'Anyaman',
                    1 => 'Batik',
                    2 => 'Kayu',
                    3 => 'Keramik',
                    default => 'Kain',
                };

                $produkByUsaha[$usahaId] = [];

                // bikin banyak produk per toko
                $jumlahProduk = 10; // bisa kamu gedein
                for ($p = 1; $p <= $jumlahProduk; $p++) {
                    $baseName = $namaProdukPool[$tema][array_rand($namaProdukPool[$tema])];
                    $nama = $baseName . ' ' . ($p);

                    $slug = Str::slug($tema . '-' . $u = $usahaData[$i]['kode'] . '-' . $nama . '-' . Str::random(4));
                    $harga = rand(25000, 250000);
                    $stok = rand(10, 150);

                    $produkId = DB::table('produk')->insertGetId([
                        'kode_produk' => 'PRD-' . strtoupper(Str::random(10)),
                        'kategori_produk_id' => $kategoriIds[array_rand($kategoriIds)],
                        'pengerajin_id' => $pengerajinId,
                        'nama_produk' => $nama,
                        'deskripsi' => 'Produk ' . $tema . ' handmade kualitas premium.',
                        'harga' => $harga,
                        'stok' => $stok,
                        'slug' => $slug,
                        'status' => 'approved',
                        'gambar' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // mapping usaha_produk
                    DB::table('usaha_produk')->insert([
                        'usaha_id' => $usahaId,
                        'produk_id' => $produkId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $produkByUsaha[$usahaId][] = $produkId;
                    $allProduk[] = ['id' => $produkId, 'harga' => $harga];

                    // foto_produk (opsional tapi bikin tabel kepake)
                    DB::table('foto_produk')->insert([
                        'kode_foto_produk' => 'FOTO-' . strtoupper(Str::random(10)),
                        'produk_id' => $produkId,
                        'file_foto_produk' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // ==========================================================
            // 7) PRODUK_VIEWS & PRODUK_LIKES (biar grafik gak kosong)
            // ==========================================================
            foreach ($allProduk as $prod) {
                $views = rand(0, 25);
                for ($v = 0; $v < $views; $v++) {
                    DB::table('produk_views')->insert([
                        'produk_id' => $prod['id'],
                        'session_id' => (string) Str::uuid(),
                        'created_at' => Carbon::now()->subDays(rand(0, 60)),
                        'updated_at' => now(),
                    ]);
                }

                $likes = rand(0, 8);
                for ($l = 0; $l < $likes; $l++) {
                    DB::table('produk_likes')->insert([
                        'produk_id' => $prod['id'],
                        'session_id' => (string) Str::uuid(),
                        'created_at' => Carbon::now()->subDays(rand(0, 60)),
                        'updated_at' => now(),
                    ]);
                }
            }

            // ==========================================================
            // 8) ORDERS (duluan) -> total_amount = 0 dulu
            // ==========================================================
            $statusList = ['baru', 'dibayar', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

            $orderIds = [];
            $jumlahOrders = 40;

            for ($i = 1; $i <= $jumlahOrders; $i++) {
                $userId = $guestIds[array_rand($guestIds)];

                // ambil data user buat nama/phone/address biar realistis
                $userRow = DB::table('users')->where('id', $userId)->first();

                $createdAt = Carbon::now()
                    ->subDays(rand(0, 60))
                    ->setTime(rand(8, 21), rand(0, 59), 0);

                $orderIds[] = DB::table('orders')->insertGetId([
                    'user_id' => $userId,
                    'order_number' => 'ORD-' . $createdAt->format('Ymd') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                    'customer_name' => $userRow->name ?? ('Customer ' . $i),
                    'customer_phone' => $userRow->phone ?? ('08' . rand(1111111111, 9999999999)),
                    'customer_address' => $userRow->address ?? ('Alamat Customer ' . $i),
                    'total_amount' => 0, // nanti diupdate setelah order_items masuk
                    'status' => $statusList[array_rand($statusList)],
                    'notes' => (rand(0, 10) > 7) ? 'Tolong packing rapi ya.' : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }

            // ==========================================================
            // 9) ORDER_ITEMS (setelah orders)
            //    Karena order_items cuma punya produk_id, kita ambil harga dari produk saat ini
            // ==========================================================
            foreach ($orderIds as $orderId) {

                // 70% order belanja di 1 toko, 30% multi-toko (lebih “realistis”)
                $multiToko = (rand(1, 100) <= 30);

                $usahaPick = $usahaIds[array_rand($usahaIds)];
                $produkPool = $produkByUsaha[$usahaPick];

                $produkPool2 = [];
                if ($multiToko) {
                    $usahaPick2 = $usahaIds[array_rand($usahaIds)];
                    if ($usahaPick2 !== $usahaPick) {
                        $produkPool2 = $produkByUsaha[$usahaPick2];
                    }
                }

                $jumlahItem = rand(1, 5);
                $pickedProduk = [];

                for ($j = 0; $j < $jumlahItem; $j++) {

                    $pool = ($multiToko && !empty($produkPool2) && rand(0, 1))
                        ? $produkPool2
                        : $produkPool;

                    $produkId = $pool[array_rand($pool)];
                    if (in_array($produkId, $pickedProduk, true)) {
                        continue; // biar gak duplikat produk dalam 1 order (lebih rapi)
                    }
                    $pickedProduk[] = $produkId;

                    $produkRow = DB::table('produk')->select('harga')->where('id', $produkId)->first();
                    $qty = rand(1, 4);

                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'produk_id' => $produkId,
                        'quantity' => $qty,
                        'price_at_purchase' => (int) ($produkRow->harga ?? 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // ==========================================================
            // 10) UPDATE orders.total_amount (setelah order_items)
            // ==========================================================
            foreach ($orderIds as $orderId) {
                $total = (int) DB::table('order_items')
                    ->where('order_id', $orderId)
                    ->selectRaw('COALESCE(SUM(quantity * price_at_purchase), 0) as total')
                    ->value('total');

                DB::table('orders')
                    ->where('id', $orderId)
                    ->update([
                        'total_amount' => $total,
                        'updated_at' => now(),
                    ]);
            }

        });
    }
}
