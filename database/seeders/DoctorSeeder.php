<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        Doctor::create([
            'name' => 'Dr. John Smith',
            'role' => 'Doctor',
            'department_id' => 1,
            'email' => 'john.smith@example.com',
            'contact_number' => '09171234567',
            'specialization' => 'Cardiology',
        ]);

        Doctor::create([
            'name' => 'Nurse Mary Johnson',
            'role' => 'Nurse',
            'department_id' => 2,
            'email' => 'mary.johnson@example.com',
            'contact_number' => '09179876543',
            'specialization' => null,
        ]);

        Doctor::create([
            'name' => 'Dr. Alan Brown',
            'role' => 'Specialist',
            'department_id' => 3,
            'email' => 'alan.brown@example.com',
            'contact_number' => '09221234567',
            'specialization' => 'Pediatrics',
        ]);
    }
}
