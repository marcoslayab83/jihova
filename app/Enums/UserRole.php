<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case MerchantAdmin = 'merchant_admin';
    case MerchantStaff = 'merchant_staff';
    case Driver = 'driver';
    case PlatformAdmin = 'platform_admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Cliente',
            self::MerchantAdmin => 'Dueño de Tienda',
            self::MerchantStaff => 'Personal de Tienda',
            self::Driver => 'Repartidor Delivery',
            self::PlatformAdmin => 'Administrador del Mall',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Customer => 'mall.home',
            self::MerchantAdmin, self::MerchantStaff => 'merchant.dashboard',
            self::Driver => 'driver.dashboard',
            self::PlatformAdmin => 'admin.dashboard',
        };
    }
}
