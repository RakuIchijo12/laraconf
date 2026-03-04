<?php

namespace App\Enums;

enum ClassificationEnum: string
{
    case SENIORCITIZEN        = 'senior-citizen';
    case PERSONWITHDISABILITY = 'person-with-disability';
    case EMPLOYEE             = 'employee';
    case DEPENDENT            = 'dependent';

    public function label(): string
    {
        return match($this) {
            self::SENIORCITIZEN        => 'Senior Citizen',
            self::PERSONWITHDISABILITY => 'Person with Disability',
            self::EMPLOYEE             => 'Employee',
            self::DEPENDENT            => 'Dependent',
        };
    }

    public function discountRate(): int
    {
        return match($this) {
            self::SENIORCITIZEN        => 20,
            self::PERSONWITHDISABILITY => 20,
            self::EMPLOYEE             => 100,
            self::DEPENDENT            => 25,
        };
    }

    public static function toArray(): array
    {
        return array_combine(
            array_map(fn($case) => $case->value, self::cases()),
            array_map(fn($case) => $case->label() . ' (' . $case->discountRate() . '%)', self::cases()),
        );
    }

    public static function getStatusColor($state): string
    {
        return match($state) {
            self::SENIORCITIZEN->value        => 'gray',
            self::PERSONWITHDISABILITY->value => 'success',
            self::EMPLOYEE->value             => 'warning',
            self::DEPENDENT->value            => 'info',
        };
    }
}