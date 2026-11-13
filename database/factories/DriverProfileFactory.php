<?php

namespace Database\Factories;

use App\Enums\VehicleType;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverProfile>
 */
class DriverProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->driver(),
            'vehicle_type' => VehicleType::Motorcycle,
            'license_plate' => strtoupper(fake()->bothify('AB-###??')),
            'current_latitude' => 10.4900,
            'current_longitude' => -66.8760,
            'is_active' => true,
            'is_available' => true,
            'completed_deliveries' => 0,
            'last_ping_at' => now(),
        ];
    }
}
