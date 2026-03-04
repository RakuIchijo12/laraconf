<?php


namespace App\Enums;

enum ReferralEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public static function toArray(): array
    {
        return array_combine(
            array_map(fn($case) => ucfirst(strtolower($case->name)), self::cases()),
            array_map(fn ($case) => $case->value, self::cases())
        );
    }

    public static function getStatusColor($state){
        return  match ($state) {
            self::PENDING->value => 'gray',
            self::APPROVED->value => 'success',
            self::REJECTED->value => 'danger',
        };
    }
}