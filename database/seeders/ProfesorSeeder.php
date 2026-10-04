<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfesorSeeder extends Seeder
{
    public function run(): void
    {
        $ids = Usuario::whereHas('roles', fn ($q) => $q->where('nombre', 'profesor'))
            ->pluck('id_usuario');

        $rows = $ids->map(fn ($id) => [
            'id_usuario' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        DB::table('profesor')->insertOrIgnore($rows);
    }
}