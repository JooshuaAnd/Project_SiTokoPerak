<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Pengerajin;
use Illuminate\Support\Facades\DB;


class PengerajinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('pengerajin')->insert([
        //     'nama_pengerajin' => Str::random(10),
        //     'alamat' => Str::random(20),
        //     'no_telp' => Str::random(10),
        //     'email' => Str::random(10).'@example.com',
        // ]);
    }
}
