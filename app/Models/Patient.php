<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($patient) {
            $patient->ref_id = 'PAT-' . strtoupper(uniqid());
        });
    }

    public function getAgeDisplayAttribute(): string
    {
        $birthdate = Carbon::parse($this->birthdate);
        $now = Carbon::now();

        if ($birthdate->isFuture()) {
            return '0 days';
        }

        $years = (int) $birthdate->diffInYears($now);

        if ($years >= 1) {
            return $years . ' year' . ($years > 1 ? 's' : '');
        }

        $months = (int) $birthdate->diffInMonths($now);

        if ($months >= 1) {
            return $months . ' month' . ($months > 1 ? 's' : '');
        }

        $days = (int) $birthdate->diffInDays($now);

        return $days === 0 ? '0' : $days . ' day' . ($days > 1 ? 's' : '');
    }

}
