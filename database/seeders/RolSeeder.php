<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin', 'profesor', 'estudiante'] as $nombre) {
            Rol::firstOrCreate(['nombre' => $nombre]);
        }
    }
}