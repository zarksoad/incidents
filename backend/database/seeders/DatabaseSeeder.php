<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@incidentes.com',
            'password' => bcrypt('P4ssw0rd'),
            'role' => 'admin',
        ]);

        $agent = User::factory()->create([
            'name' => 'Agente Demo',
            'email' => 'agente@incidentes.com',
            'password' => bcrypt('P4ssw0rd'),
            'role' => 'agent',
        ]);

        $agents = User::factory(8)->create([
            'password' => bcrypt('P4ssw0rd'),
            'role' => 'agent',
        ]);

        $allUsers = $agents->push($admin)->push($agent);

        Incident::factory(500)->create([
            'user_id' => fn () => $allUsers->random()->id,
            'assigned_id' => fn () => fake()->boolean(80) ? $allUsers->random()->id : null,
        ]);
    }
}
