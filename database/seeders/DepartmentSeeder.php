<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [

        /*
        |--------------------------------------------------------------------------
        | CLINICAL CARE UNITS
        |--------------------------------------------------------------------------
        */

        [
            'name' => 'Emergency Room (ER)',
            'description' => 'Immediate treatment for urgent cases.'
        ],
        [
            'name' => 'Intensive Care Unit (ICU)',
            'description' => 'Critical care for unstable patients.'
        ],
        [
            'name' => 'General Ward',
            'description' => 'Regular admission for stable patients.'
        ],
        [
            'name' => 'Pediatric Ward',
            'description' => 'Admission area for children.'
        ],
        [
            'name' => 'Maternity Ward',
            'description' => 'Care for pregnant and postpartum patients.'
        ],
        [
            'name' => 'Isolation Unit',
            'description' => 'For patients with infectious diseases.'
        ],
        [
            'name' => 'Operating Room (OR)',
            'description' => 'Surgical procedures area.'
        ],
        [
            'name' => 'Recovery Room',
            'description' => 'Post-surgery recovery monitoring.'
        ],

        /*
        |--------------------------------------------------------------------------
        | SUPPORT / REST / SPECIAL SERVICE AREAS
        |--------------------------------------------------------------------------
        */

        [
            'name' => 'Bahay Pahulayan',
            'description' => 'Resting area for patients under observation.'
        ],
        [
            'name' => 'Observation Room',
            'description' => 'Short-term monitoring before discharge or admission.'
        ],
        [
            'name' => 'Dialysis Unit',
            'description' => 'Kidney dialysis services.'
        ],
        [
            'name' => 'Rehabilitation Unit',
            'description' => 'Physical therapy and recovery services.'
        ],
    ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}
