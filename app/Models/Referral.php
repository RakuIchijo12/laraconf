<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function fromDoctor()
    {
        return $this->belongsTo(Doctor::class, 'from_doctor_id');
    }

    public function toDoctor()
    {
        return $this->belongsTo(Doctor::class, 'to_doctor_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

}
