<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\StoreStatus;
use App\Enums\SubOrderStatus;
use App\Enums\UserRole;
use App\Enums\VehicleType;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\DeliveryTrackingEvent;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. Usuarios clave por rol
        $admin = User::create([
            'full_name' => 'Valentina Rojas (Admin Mall)',
            'email' => 'admin@jihova.test',
            'phone' => '+58 412 100 0001',
            'password' => $password,
            'role' => UserRole::PlatformAdmin,
        ]);

        $merchantOne = User::create([
            'full_name' => 'Mateo Mendoza',
            'email' => 'comercio@jihova.test',
            'phone' => '+58 412 200 0002',
            'password' => $password,
            'role' => UserRole::MerchantAdmin,
        ]);

        $merchantTwo = User::create([
            'full_name' => 'Camila Herrera',
            'email' => 'tech@jihova.test',
            'phone' => '+58 412 200 0003',
            'password' => $password,
            'role' => UserRole::MerchantAdmin,
        ]);

        $driverOne = User::create([
            'full_name' => 'Carlos Bolívar',
            'email' => 'repartidor@jihova.test',
            'phone' => '+58 414 300 0004',
            'password' => $password,
            'role' => UserRole::Driver,
        ]);

        $driverTwo = User::create([
            'full_name' => 'Diego Navarro',
            'email' => 'ciclista@jihova.test',
            'phone' => '+58 414 300 0005',
            'password' => $password,
            'role' => UserRole::Driver,
        ]);

        $customer = User::create([
            'full_name' => 'Sofía Paredes',
            'email' => 'cliente@jihova.test',
            'phone' => '+58 424 400 0006',
            'password' => $password,
            'role' => UserRole::Customer,
        ]);

        // 2. Direcciones del cliente
        $homeAddress = CustomerAddress::create([
            'user_id' => $customer->id,
            'title' => 'Casa · Los Palos Grandes',
            'street_address' => 'Av. Andrés Bello con 3ra Transversal, Res. Parque Verde, Piso 4',
            'reference' => 'Portón blanco frente a la plaza, llamar al llegar',
            'latitude' => 10.4982,
            'longitude' => -66.8458,
            'is_default' => true,
        ]);

        CustomerAddress::create([
            'user_id' => $customer->id,
            'title' => 'Oficina · Chacao',
            'street_address' => 'Torre Empresarial Este, Piso 9, Ofic. 9B',
            'reference' => 'Dejar en recepción si no contesto',
            'latitude' => 10.4952,
            'longitude' => -66.8548,
            'is_default' => false,
        ]);

        // 3. Perfiles de repartidores propios de la plataforma
        DriverProfile::create([
            'user_id' => $driverOne->id,
            'vehicle_type' => VehicleType::Motorcycle,
            'license_plate' => 'PLZ-408K',
            'current_latitude' => 10.4910,
            'current_longitude' => -66.8620,
            'is_active' => true,
            'is_available' => true,
            'completed_deliveries' => 42,
            'last_ping_at' => now(),
        ]);

        DriverProfile::create([
            'user_id' => $driverTwo->id,
            'vehicle_type' => VehicleType::Bicycle,
            'license_plate' => 'BICI-09',
            'current_latitude' => 10.4865,
            'current_longitude' => -66.8740,
            'is_active' => true,
            'is_available' => true,
            'completed_deliveries' => 19,
            'last_ping_at' => now(),
        ]);

        // 4. Categorías / Rubros del Centro Comercial
        $categories = collect([
            [
                'name' => 'Desayunos y Café',
                'slug' => 'desayunos-cafe',
                'description' => 'Desayunos recién hechos, panadería artesanal y café de especialidad.',
                'icon_name' => 'coffee',
                'sort_order' => 1,
            ],
            [
                'name' => 'Calzado y Moda',
                'slug' => 'calzado-moda',
                'description' => 'Tenis, zapatos de vestir, ropa urbana y accesorios.',
                'icon_name' => 'footprints',
                'sort_order' => 2,
            ],
            [
                'name' => 'Tecnología',
                'slug' => 'tecnologia',
                'description' => 'Audio, wearables, periféricos y carga rápida.',
                'icon_name' => 'cpu',
                'sort_order' => 3,
            ],
            [
                'name' => 'Farmacia y Bienestar',
                'slug' => 'farmacia-salud',
                'description' => 'Cuidado diario, vitaminas, dermocosmética y botiquín.',
                'icon_name' => 'heart-pulse',
                'sort_order' => 4,
            ],
            [
                'name' => 'Supermercado y Hogar',
                'slug' => 'supermercado-hogar',
                'description' => 'Despensa fresca, frutas seleccionadas y esenciales del hogar.',
                'icon_name' => 'shopping-basket',
                'sort_order' => 5,
            ],
        ])->mapWithKeys(function (array $data): array {
            $category = Category::create($data);

            return [$category->slug => $category];
        });

        // 5. Tiendas, sucursales, catálogo e inventario
        $storesConfig = [
            [
                'owner' => $merchantOne,
                'category' => 'desayunos-cafe',
                'business_name' => 'Aurora Café & Desayunos',
                'slug' => 'aurora-cafe',
                'tagline' => 'Desayunos calientes y café de origen en 20 min',
                'description' => 'Cocina matutina artesanal con huevos al gusto, tostadas francesas, croissants hojaldrados y espresso recién molido.',
                'accent_color' => '#ea580c',
                'phone' => '+58 212 555 0101',
                'commission_rate' => 12.00,
                'rating' => 4.9,
                'branches' => [
                    [
                        'branch_name' => 'Aurora · Mall Central',
                        'address' => 'Nivel Feria, Local F-12, Las Mercedes',
                        'latitude' => 10.4812,
                        'longitude' => -66.8610,
                        'delivery_radius_km' => 10.00,
                        'base_delivery_fee' => 2.00,
                        'avg_prep_time_minutes' => 12,
                    ],
                    [
                        'branch_name' => 'Aurora · Chacao',
                        'address' => 'Av. Francisco de Miranda, Edif. Sol, PB',
                        'latitude' => 10.4960,
                        'longitude' => -66.8535,
                        'delivery_radius_km' => 9.00,
                        'base_delivery_fee' => 1.80,
                        'avg_prep_time_minutes' => 10,
                    ],
                ],
                'products' => [
                    [
                        'name' => 'Desayuno Criollo Completo',
                        'description' => 'Dos huevos revueltos o fritos, queso fresco, aguacate, pan tostado artesanal y café americano caliente.',
                        'sku' => 'AUR-DES-01',
                        'base_price' => 9.50,
                        'image_url' => '/images/catalog/desayuno.jpg',
                        'badge' => 'Más pedido',
                        'prep_note' => 'Listo en 12 min',
                    ],
                    [
                        'name' => 'Croissant de Almendras & Espresso',
                        'description' => 'Combo mañanero con croissant recién horneado relleno de crema de almendras y espresso doble.',
                        'sku' => 'AUR-PAN-02',
                        'base_price' => 7.20,
                        'image_url' => '/images/catalog/panaderia.jpg',
                        'badge' => 'Combo',
                        'prep_note' => 'Horneado hoy',
                    ],
                    [
                        'name' => 'Café Latte Caramelo 12oz',
                        'description' => 'Doble carga de café arábica con leche vaporizada cremosa y toque de caramelo salado.',
                        'sku' => 'AUR-CAF-03',
                        'base_price' => 4.50,
                        'image_url' => '/images/catalog/cafe.jpg',
                        'badge' => null,
                        'prep_note' => 'Envase térmico',
                    ],
                    [
                        'name' => 'Tostadas Francesas Frutos Rojos',
                        'description' => 'Pan brioche dorado en mantequilla con miel pura, fresas y arándanos frescos.',
                        'sku' => 'AUR-DES-04',
                        'base_price' => 8.90,
                        'image_url' => '/images/catalog/desayuno.jpg',
                        'badge' => 'Brunch',
                        'prep_note' => 'Listo en 14 min',
                    ],
                ],
            ],
            [
                'owner' => $merchantOne,
                'category' => 'desayunos-cafe',
                'business_name' => 'Bistro La Mañana',
                'slug' => 'bistro-la-manana',
                'tagline' => 'Brunch saludable, panquecas y jugos prensados',
                'description' => 'Tu parada rápida en el centro comercial para empezar el día con energía y sabor casero.',
                'accent_color' => '#d97706',
                'phone' => '+58 212 555 0108',
                'commission_rate' => 11.00,
                'rating' => 4.7,
                'branches' => [
                    [
                        'branch_name' => 'Bistro · Plaza Norte',
                        'address' => 'Av. San Juan Bosco, Altamira, Local 4',
                        'latitude' => 10.4995,
                        'longitude' => -66.8490,
                        'delivery_radius_km' => 10.00,
                        'base_delivery_fee' => 2.20,
                        'avg_prep_time_minutes' => 14,
                    ],
                ],
                'products' => [
                    [
                        'name' => 'Desayuno Brioche Huevo & Tocino',
                        'description' => 'Sándwich tibio en pan brioche con huevo pochado, queso cheddar fundido y tocino crujiente.',
                        'sku' => 'BLM-DES-01',
                        'base_price' => 8.40,
                        'image_url' => '/images/catalog/panaderia.jpg',
                        'badge' => 'Desayuno',
                        'prep_note' => 'Listo en 10 min',
                    ],
                    [
                        'name' => 'Panquecas de Avena y Miel',
                        'description' => 'Torre de 3 panquecas esponjosas de avena con cambur caramelizado y miel orgánica.',
                        'sku' => 'BLM-DES-02',
                        'base_price' => 7.80,
                        'image_url' => '/images/catalog/desayuno.jpg',
                        'badge' => null,
                        'prep_note' => 'Sin azúcar añadida',
                    ],
                    [
                        'name' => 'Capuccino Vainilla Artesanal',
                        'description' => 'Espresso balanceado con espuma sedosa y esencia natural de vainilla.',
                        'sku' => 'BLM-CAF-03',
                        'base_price' => 4.10,
                        'image_url' => '/images/catalog/cafe.jpg',
                        'badge' => null,
                        'prep_note' => 'Listo en 5 min',
                    ],
                ],
            ],
            [
                'owner' => $merchantOne,
                'category' => 'calzado-moda',
                'business_name' => 'StepUp Calzado',
                'slug' => 'stepup-calzado',
                'tagline' => 'Zapatos urbanos, running y cuero con envío en el día',
                'description' => 'Zapatería multimarca dentro del centro comercial con despacho inmediato desde nuestras tiendas físicas.',
                'accent_color' => '#4f46e5',
                'phone' => '+58 212 555 0202',
                'commission_rate' => 10.00,
                'rating' => 4.9,
                'branches' => [
                    [
                        'branch_name' => 'StepUp · Mall Central',
                        'address' => 'Nivel Galería, Local G-28, Las Mercedes',
                        'latitude' => 10.4808,
                        'longitude' => -66.8618,
                        'delivery_radius_km' => 12.00,
                        'base_delivery_fee' => 2.50,
                        'avg_prep_time_minutes' => 10,
                    ],
                    [
                        'branch_name' => 'StepUp · Centro',
                        'address' => 'Boulevard Sabana Grande, Local 19',
                        'latitude' => 10.4945,
                        'longitude' => -66.8790,
                        'delivery_radius_km' => 12.00,
                        'base_delivery_fee' => 2.50,
                        'avg_prep_time_minutes' => 12,
                    ],
                ],
                'products' => [
                    [
                        'name' => 'Zapatos Deportivos Running AirPulse',
                        'description' => 'Par de zapatos deportivos ultraligeros con suela de amortiguación doble y malla respirable. Tallas 38 a 44.',
                        'sku' => 'STP-ZAP-01',
                        'base_price' => 68.00,
                        'image_url' => '/images/catalog/tenis.jpg',
                        'badge' => 'Top Ventas',
                        'prep_note' => 'Empaque original + cambio gratis',
                    ],
                    [
                        'name' => 'Zapatos Urbanos Cuero Negro Classic',
                        'description' => 'Par de zapatos casuales en cuero genuino acabado mate, plantilla anatómica y costura reforzada.',
                        'sku' => 'STP-ZAP-02',
                        'base_price' => 84.00,
                        'image_url' => '/images/catalog/zapatos.jpg',
                        'badge' => 'Cuero 100%',
                        'prep_note' => 'Caja sellada',
                    ],
                    [
                        'name' => 'Zapatos Mocasines Flex Confort',
                        'description' => 'Mocasines versátiles sin cordones para oficina o fin de semana con suela antideslizante.',
                        'sku' => 'STP-ZAP-03',
                        'base_price' => 59.50,
                        'image_url' => '/images/catalog/zapatos.jpg',
                        'badge' => null,
                        'prep_note' => 'Entrega hoy',
                    ],
                    [
                        'name' => 'Tenis Retro Court Blanco/Índigo',
                        'description' => 'Zapatillas clásicas de perfil bajo ideales para uso diario con acabado premium.',
                        'sku' => 'STP-ZAP-04',
                        'base_price' => 62.00,
                        'image_url' => '/images/catalog/tenis.jpg',
                        'badge' => 'Nuevo',
                        'prep_note' => 'Stock inmediato',
                    ],
                ],
            ],
            [
                'owner' => $merchantOne,
                'category' => 'calzado-moda',
                'business_name' => 'Vértice Streetwear',
                'slug' => 'vertice-streetwear',
                'tagline' => 'Ropa contemporánea, sudaderas y básicos esenciales',
                'description' => 'Prendas de algodón pesado y cortes modernos diseñadas para durar.',
                'accent_color' => '#7c3aed',
                'phone' => '+58 212 555 0215',
                'commission_rate' => 10.00,
                'rating' => 4.8,
                'branches' => [
                    [
                        'branch_name' => 'Vértice · Mall Central',
                        'address' => 'Nivel Galería, Local G-15, Las Mercedes',
                        'latitude' => 10.4810,
                        'longitude' => -66.8622,
                        'delivery_radius_km' => 12.00,
                        'base_delivery_fee' => 2.50,
                        'avg_prep_time_minutes' => 10,
                    ],
                ],
                'products' => [
                    [
                        'name' => 'Sudadera Hoodie Algodón 420g',
                        'description' => 'Sudadera oversize de algodón peinado suave al tacto con capucha doble.',
                        'sku' => 'VRT-MOD-01',
                        'base_price' => 44.00,
                        'image_url' => '/images/catalog/moda.jpg',
                        'badge' => 'Nuevo',
                        'prep_note' => 'Tallas S a XL',
                    ],
                    [
                        'name' => 'Chaqueta Rompevientos Urbana',
                        'description' => 'Chaqueta ligera resistente a llovizna con bolsillos con cierre oculto.',
                        'sku' => 'VRT-MOD-02',
                        'base_price' => 54.00,
                        'image_url' => '/images/catalog/moda.jpg',
                        'badge' => null,
                        'prep_note' => 'Empaque regalo disponible',
                    ],
                ],
            ],
            [
                'owner' => $merchantTwo,
                'category' => 'tecnologia',
                'business_name' => 'VoltTech Store',
                'slug' => 'volttech-store',
                'tagline' => 'Audio, smartwatches y accesorios originales con garantía',
                'description' => 'Tienda oficial de electrónica y gadgets del centro comercial con entrega rápida asegurada.',
                'accent_color' => '#0891b2',
                'phone' => '+58 212 555 0303',
                'commission_rate' => 8.50,
                'rating' => 4.9,
                'branches' => [
                    [
                        'branch_name' => 'VoltTech · Mall Central',
                        'address' => 'Nivel Plaza, Local P-08, Las Mercedes',
                        'latitude' => 10.4804,
                        'longitude' => -66.8609,
                        'delivery_radius_km' => 12.00,
                        'base_delivery_fee' => 2.90,
                        'avg_prep_time_minutes' => 8,
                    ],
                ],
                'products' => [
                    [
                        'name' => 'Audífonos Inalámbricos ANC Studio',
                        'description' => 'Cancelación activa de ruido híbrida, 40 horas de batería y carga rápida USB-C.',
                        'sku' => 'VLT-TEC-01',
                        'base_price' => 79.00,
                        'image_url' => '/images/catalog/tecnologia.jpg',
                        'badge' => 'Garantía 1 año',
                        'prep_note' => 'Sellado de fábrica',
                    ],
                    [
                        'name' => 'Smartwatch FitTrack GPS Pro',
                        'description' => 'Pantalla AMOLED brillante, monitor cardíaco continuo, GPS integrado y resistencia al agua 5ATM.',
                        'sku' => 'VLT-TEC-02',
                        'base_price' => 95.00,
                        'image_url' => '/images/catalog/watch.jpg',
                        'badge' => 'Destacado',
                        'prep_note' => 'Envío asegurado',
                    ],
                    [
                        'name' => 'PowerBank 20.000 mAh PD 45W',
                        'description' => 'Batería portátil de alta capacidad para laptops, tablets y teléfonos.',
                        'sku' => 'VLT-TEC-03',
                        'base_price' => 38.00,
                        'image_url' => '/images/catalog/tecnologia.jpg',
                        'badge' => null,
                        'prep_note' => 'Listo para despacho',
                    ],
                ],
            ],
            [
                'owner' => $merchantTwo,
                'category' => 'farmacia-salud',
                'business_name' => 'FarmaVida Express',
                'slug' => 'farmavida-express',
                'tagline' => 'Farmacia y cuidado personal en la puerta de tu casa',
                'description' => 'Productos de salud, dermocosmética y bienestar con despacho prioritario.',
                'accent_color' => '#e11d48',
                'phone' => '+58 212 555 0404',
                'commission_rate' => 9.00,
                'rating' => 4.8,
                'branches' => [
                    [
                        'branch_name' => 'FarmaVida · Chacao',
                        'address' => 'Av. Libertador, Torre Salud, Local 1',
                        'latitude' => 10.4949,
                        'longitude' => -66.8555,
                        'delivery_radius_km' => 10.00,
                        'base_delivery_fee' => 1.90,
                        'avg_prep_time_minutes' => 8,
                    ],
                ],
                'products' => [
                    [
                        'name' => 'Kit Vitamina C + Zinc Inmunidad',
                        'description' => 'Frasco de 60 tabletas efervescentes sabor naranja + suplemento de zinc.',
                        'sku' => 'FRM-SAL-01',
                        'base_price' => 16.50,
                        'image_url' => '/images/catalog/farmacia.jpg',
                        'badge' => 'Express',
                        'prep_note' => 'Despacho prioritario',
                    ],
                    [
                        'name' => 'Protector Solar Toque Seco FPS 50+',
                        'description' => 'Protección UVA/UVB de amplio espectro sin efecto graso para rostro y cuerpo.',
                        'sku' => 'FRM-SAL-02',
                        'base_price' => 22.00,
                        'image_url' => '/images/catalog/farmacia.jpg',
                        'badge' => null,
                        'prep_note' => 'Dermatológico',
                    ],
                ],
            ],
            [
                'owner' => $merchantTwo,
                'category' => 'supermercado-hogar',
                'business_name' => 'Mercado Fresco 24',
                'slug' => 'mercado-fresco-24',
                'tagline' => 'Frutas frescas, café molido y despensa seleccionada',
                'description' => 'Minimarket gourmet del centro comercial con selección diaria de productos frescos.',
                'accent_color' => '#16a34a',
                'phone' => '+58 212 555 0505',
                'commission_rate' => 9.50,
                'rating' => 4.8,
                'branches' => [
                    [
                        'branch_name' => 'Mercado Fresco · Santa Mónica',
                        'address' => 'Av. Teresa de la Parra, Local 12',
                        'latitude' => 10.4755,
                        'longitude' => -66.8870,
                        'delivery_radius_km' => 11.00,
                        'base_delivery_fee' => 2.00,
                        'avg_prep_time_minutes' => 12,
                    ],
                ],
                'products' => [
                    [
                        'name' => 'Canasta de Frutas de Temporada 3kg',
                        'description' => 'Selección fresca de fresas, manzanas rojas, cambures, naranjas y piña dulce.',
                        'sku' => 'MRC-SUP-01',
                        'base_price' => 14.90,
                        'image_url' => '/images/catalog/supermercado.jpg',
                        'badge' => 'Fresco hoy',
                        'prep_note' => 'Cosecha del día',
                    ],
                    [
                        'name' => 'Café Molido de Origen 500g',
                        'description' => 'Tueste medio artesanal con notas a cacao y caramelo, ideal para prensa o greca.',
                        'sku' => 'MRC-SUP-02',
                        'base_price' => 11.50,
                        'image_url' => '/images/catalog/cafe.jpg',
                        'badge' => null,
                        'prep_note' => 'Empaque hermético',
                    ],
                ],
            ],
        ];

        $seededProductsBySku = [];
        $seededBranchesByName = [];

        foreach ($storesConfig as $storeData) {
            $category = $categories[$storeData['category']];
            $bannerMap = [
                'aurora-cafe' => '/images/catalog/desayuno.jpg',
                'bistro-la-manana' => '/images/catalog/panaderia.jpg',
                'stepup-calzado' => '/images/catalog/tenis.jpg',
                'vertice-streetwear' => '/images/catalog/moda.jpg',
                'volttech-store' => '/images/catalog/tecnologia.jpg',
                'farmavida-express' => '/images/catalog/farmacia.jpg',
                'mercado-fresco-24' => '/images/catalog/supermercado.jpg',
            ];

            $store = Store::create([
                'owner_id' => $storeData['owner']->id,
                'category_id' => $category->id,
                'business_name' => $storeData['business_name'],
                'slug' => $storeData['slug'],
                'tagline' => $storeData['tagline'],
                'description' => $storeData['description'],
                'banner_url' => $bannerMap[$storeData['slug']] ?? '/images/catalog/hero-mall.jpg',
                'accent_color' => $storeData['accent_color'],
                'phone' => $storeData['phone'],
                'status' => StoreStatus::Active,
                'commission_rate' => $storeData['commission_rate'],
                'rating' => $storeData['rating'],
            ]);

            $branches = [];
            foreach ($storeData['branches'] as $branchData) {
                $branch = StoreBranch::create([
                    'store_id' => $store->id,
                    'branch_name' => $branchData['branch_name'],
                    'address' => $branchData['address'],
                    'latitude' => $branchData['latitude'],
                    'longitude' => $branchData['longitude'],
                    'delivery_radius_km' => $branchData['delivery_radius_km'],
                    'base_delivery_fee' => $branchData['base_delivery_fee'],
                    'avg_prep_time_minutes' => $branchData['avg_prep_time_minutes'],
                    'is_open' => true,
                ]);
                $branches[] = $branch;
                $seededBranchesByName[$branch->branch_name] = $branch;
            }

            foreach ($storeData['products'] as $productData) {
                $product = Product::create([
                    'store_id' => $store->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'description' => $productData['description'],
                    'sku' => $productData['sku'],
                    'base_price' => $productData['base_price'],
                    'image_url' => $productData['image_url'],
                    'badge' => $productData['badge'],
                    'prep_note' => $productData['prep_note'],
                    'is_active' => true,
                ]);

                $seededProductsBySku[$productData['sku']] = $product;

                foreach ($branches as $idx => $branch) {
                    BranchInventory::create([
                        'branch_id' => $branch->id,
                        'product_id' => $product->id,
                        'price' => $productData['base_price'],
                        'stock_quantity' => $idx === 0 ? 35 : 20,
                        'is_available' => true,
                    ]);
                }
            }
        }

        // 6. Pedido demo multi-tienda activo (Desayuno + Zapatos en la misma orden)
        $auroraBranch = $seededBranchesByName['Aurora · Chacao'];
        $stepUpBranch = $seededBranchesByName['StepUp · Mall Central'];
        $breakfast = $seededProductsBySku['AUR-DES-01'];
        $sneakers = $seededProductsBySku['STP-ZAP-01'];

        $demoOrder = Order::create([
            'order_number' => 'CC-DEMO-1001',
            'customer_id' => $customer->id,
            'address_id' => $homeAddress->id,
            'total_items_amount' => 77.50,
            'total_delivery_fee' => 4.30,
            'platform_fee' => 2.33,
            'grand_total' => 84.13,
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => 'card',
            'payment_reference' => 'CARD-PLZ90421',
            'paid_at' => now()->subMinutes(18),
            'customer_notes' => 'Tocar el timbre del piso 4, por favor.',
            'delivery_snapshot' => [
                'title' => $homeAddress->title,
                'street_address' => $homeAddress->street_address,
                'reference' => $homeAddress->reference,
                'latitude' => (float) $homeAddress->latitude,
                'longitude' => (float) $homeAddress->longitude,
            ],
        ]);

        // Sub-orden 1: Desayuno ya en camino con repartidor
        $subOrderOne = SubOrder::create([
            'order_id' => $demoOrder->id,
            'sub_order_code' => 'CC-DEMO-1001-T1',
            'branch_id' => $auroraBranch->id,
            'driver_id' => $driverOne->id,
            'status' => SubOrderStatus::OutForDelivery,
            'subtotal' => 9.50,
            'delivery_fee' => 1.80,
            'commission_amount' => 1.14,
            'pickup_pin' => '4821',
            'delivery_pin' => '9134',
            'estimated_delivery_at' => now()->addMinutes(10),
        ]);

        OrderItem::create([
            'sub_order_id' => $subOrderOne->id,
            'product_id' => $breakfast->id,
            'product_name' => $breakfast->name,
            'unit_price' => 9.50,
            'quantity' => 1,
            'total_price' => 9.50,
        ]);

        DeliveryTrackingEvent::create([
            'sub_order_id' => $subOrderOne->id,
            'status' => SubOrderStatus::Placed->value,
            'event_timestamp' => now()->subMinutes(18),
            'latitude' => (float) $auroraBranch->latitude,
            'longitude' => (float) $auroraBranch->longitude,
            'notes' => 'Pedido recibido por Aurora Café & Desayunos.',
        ]);

        DeliveryTrackingEvent::create([
            'sub_order_id' => $subOrderOne->id,
            'status' => SubOrderStatus::ReadyForPickup->value,
            'event_timestamp' => now()->subMinutes(8),
            'latitude' => (float) $auroraBranch->latitude,
            'longitude' => (float) $auroraBranch->longitude,
            'notes' => 'Desayuno empacado en bolsa térmica, listo para recoger.',
        ]);

        DeliveryTrackingEvent::create([
            'sub_order_id' => $subOrderOne->id,
            'status' => SubOrderStatus::OutForDelivery->value,
            'event_timestamp' => now()->subMinutes(4),
            'latitude' => 10.4968,
            'longitude' => -66.8501,
            'notes' => 'Carlos Bolívar retiró tu desayuno y va camino a Los Palos Grandes.',
        ]);

        // Sub-orden 2: Zapatos listos para que un repartidor los tome desde /panel-repartidor
        $subOrderTwo = SubOrder::create([
            'order_id' => $demoOrder->id,
            'sub_order_code' => 'CC-DEMO-1001-T2',
            'branch_id' => $stepUpBranch->id,
            'driver_id' => null,
            'status' => SubOrderStatus::ReadyForPickup,
            'subtotal' => 68.00,
            'delivery_fee' => 2.50,
            'commission_amount' => 6.80,
            'pickup_pin' => '7350',
            'delivery_pin' => '2048',
            'estimated_delivery_at' => now()->addMinutes(24),
        ]);

        OrderItem::create([
            'sub_order_id' => $subOrderTwo->id,
            'product_id' => $sneakers->id,
            'product_name' => $sneakers->name,
            'unit_price' => 68.00,
            'quantity' => 1,
            'total_price' => 68.00,
        ]);

        DeliveryTrackingEvent::create([
            'sub_order_id' => $subOrderTwo->id,
            'status' => SubOrderStatus::Placed->value,
            'event_timestamp' => now()->subMinutes(18),
            'latitude' => (float) $stepUpBranch->latitude,
            'longitude' => (float) $stepUpBranch->longitude,
            'notes' => 'Pedido recibido por StepUp Calzado (Mall Central).',
        ]);

        DeliveryTrackingEvent::create([
            'sub_order_id' => $subOrderTwo->id,
            'status' => SubOrderStatus::ReadyForPickup->value,
            'event_timestamp' => now()->subMinutes(5),
            'latitude' => (float) $stepUpBranch->latitude,
            'longitude' => (float) $stepUpBranch->longitude,
            'notes' => 'Zapatos verificados y sellados en mostrador, esperando repartidor.',
        ]);

        $this->call(CuadradosFoodSeeder::class);
    }
}
