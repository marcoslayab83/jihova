<?php

namespace App\Enums;

enum VehicleType: string
{
    case Motorcycle = 'motorcycle';
    case Bicycle = 'bicycle';
    case Car = 'car';

    public function label(): string
    {
        return match ($this) {
            self::Motorcycle => 'Motocicleta',
            self::Bicycle => 'Bicicleta',
            self::Car => 'Automóvil',
        };
    }
}
