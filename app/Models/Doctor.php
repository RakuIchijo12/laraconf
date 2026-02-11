<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function sentReferrals()
    {
        return $this->hasMany(Referral::class, 'from_doctor_id');
    }

    public function receivedReferrals()
    {
        return $this->hasMany(Referral::class, 'to_doctor_id');
    }
}
