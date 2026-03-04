<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use Illuminate\Support\Str;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Manual sample patients
        Patient::create([
            'name' => 'Alice Smith',
            'date_of_birth' => '1990-05-12',
            'email' => 'alice.smith@example.com',
            'contact_number' => '09171234567',
        ]);

        Patient::create([
            'name' => 'Bob Johnson',
            'date_of_birth' => '1985-10-22',
            'email' => 'bob.johnson@example.com',
            'contact_number' => '09179876543',
        ]);

        Patient::create([
            'name' => 'Catherine Lee',
            'date_of_birth' => '2000-01-15',
            'email' => 'catherine.lee@example.com',
            'contact_number' => '09221234567',
        ]);

        // Optional: generate more patients using a factory
        // Patient::factory()->count(10)->create();
    }
}
