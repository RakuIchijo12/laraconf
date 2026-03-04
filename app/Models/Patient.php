<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['ref_id', 'name', 'birthdate', 'sex', 'classification'];
    protected $casts = [
        'classification' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($patient) {
            $patient->ref_id = 'PAT-' . strtoupper(uniqid());
        });
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    # Accessor to compute discount rate based on classification and age
    public function getDiscountRateAttribute(): float
    {
        $age = (int) Carbon::parse($this->birthdate)->diffInYears(now());
        $classifications = $this->classification ?? [];

        $discounts = [];

        if ($age >= 60 || in_array('senior-citizen', $classifications)) {
            $discounts[] = 20;
        }

        if (in_array('person-with-disability', $classifications)) {
            $discounts[] = 20;
        }

        if (in_array('employee', $classifications)) {
            $discounts[] = 100;
        }

        if (in_array('dependent', $classifications)) {
            $discounts[] = 25;
        }

        return count($discounts) > 0 ? max($discounts) : 0;
    }

    # Method to compute age display in years, months, or days
    public static function computeAgeDisplay(string $birthdate): string
    {
        $birthdate = Carbon::parse($birthdate);
        $now = Carbon::now();

        if ($birthdate->isFuture()) {
            return '0';
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
    
    public function getAgeDisplayAttribute(): string
    {
        return self::computeAgeDisplay($this->birthdate);
    }

}
