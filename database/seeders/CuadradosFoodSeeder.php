<?php

namespace Database\Seeders;

use App\Enums\StoreStatus;
use App\Enums\UserRole;
use App\Models\BranchInventory;
use App\Models\Category;
use App\Models\MallAdBanner;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreBranch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CuadradosFoodSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Obtener comercio propietario o crear si no existe
        $owner = User::where('role', UserRole::MerchantAdmin)->first();
        if (! $owner) {
            $owner = User::create([
                'full_name' => 'Comerciante Cuadrados Food',
                'email' => 'cuadrados@jihova.test',
                'phone' => '+58 412 275 8427',
                'password' => bcrypt('password'),
                'role' => UserRole::MerchantAdmin,
            ]);
        }

        // 2. Obtener categoría Desayunos y Café
        $category = Category::where('slug', 'desayunos-cafe')->first();
        if (! $category) {
            $category = Category::create([
                'name' => 'Desayunos y Café',
                'slug' => 'desayunos-cafe',
                'description' => 'Desayunos recién hechos, panadería artesanal, pastelitos y café de especialidad.',
                'icon_name' => 'coffee',
                'sort_order' => 1,
            ]);
        }

        // 3. Crear o actualizar la tienda CUADRADOS FOOD
        $store = Store::updateOrCreate(
            ['slug' => 'cuadrados-food'],
            [
                'owner_id' => $owner->id,
                'category_id' => $category->id,
                'business_name' => 'CUADRADOS FOOD',
                'tagline' => 'Pastelitos hechos en casa · Dorados y crujientes',
                'description' => 'En Cuadrados Food elaboramos los auténticos pastelitos cuadrados venezolanos, 100% hechos en casa con masa artesanal crujiente, rellenos generosos de primera calidad y combos irresistibles. Atención de lunes a domingo en Maracay.',
                'logo_url' => '/images/stores/cuadrados-food/logo.jpg',
                'banner_url' => '/images/stores/cuadrados-food/banner.jpg',
                'accent_color' => '#FF6600',
                'phone' => '+58 412 275 8427',
                'status' => StoreStatus::Active,
                'commission_rate' => 10.00,
                'rating' => 5.0,
            ]
        );

        // 4. Crear o actualizar la sucursal de Maracay Las Acacias
        // Horario: Lun-Vie 7:30am - 1:00pm, Sáb-Dom 7:30am - 12:00pm
        $branch = StoreBranch::updateOrCreate(
            [
                'store_id' => $store->id,
                'branch_name' => 'CUADRADOS FOOD · Maracay Las Acacias',
            ],
            [
                'address' => 'Maracay centro, Las Acacias, Estado Aragua (Lun-Vie: 7:30am-1:00pm, Sáb-Dom: 7:30am-12:00pm)',
                'latitude' => 10.2469,
                'longitude' => -67.5958,
                'delivery_radius_km' => 15.00,
                'base_delivery_fee' => 1.50,
                'avg_prep_time_minutes' => 12,
                'is_open' => true,
            ]
        );

        // 5. Catálogo de productos y bebidas de Cuadrados Food
        // Tasa referencial USD: 1 pastel = 2.000 Bs (~$1.00 USD), Combo 2 = 3.500 Bs (~$1.75 USD)
        $productsData = [
            // Pastelitos individuales (2.000 Bs c/u)
            [
                'name' => 'Pastelito de Carne Mechada',
                'slug' => 'pastelito-carne-mechada',
                'description' => 'Pastelito cuadrado frito y crujiente, con relleno abundante de jugosa carne de res mechada en sofrito criollo tradicional.',
                'sku' => 'CUA-PAS-01',
                'base_price' => 1.00,
                'cost_price' => 0.55,
                'profit_margin_percent' => 45.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/pastelito-mechada.jpg',
                'badge' => 'Más pedido',
                'prep_note' => 'Recién frito · Crujiente',
                'stock' => 50,
            ],
            [
                'name' => 'Pastelito de Pollo Desmechado',
                'slug' => 'pastelito-pollo-desmechado',
                'description' => 'Pastelito artesanal dorado relleno con pechuga de pollo desmechada en salsa guisada criolla casera.',
                'sku' => 'CUA-PAS-02',
                'base_price' => 1.00,
                'cost_price' => 0.50,
                'profit_margin_percent' => 50.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/pastelito-pollo.jpg',
                'badge' => 'Favorito',
                'prep_note' => '100% Casero',
                'stock' => 50,
            ],
            [
                'name' => 'Pastelito de Carne Molida',
                'slug' => 'pastelito-carne-molida',
                'description' => 'Pastelito de masa hojaldrada frita relleno de carne molida sazonada con ajo, pimentón dulce y cebollín fresco.',
                'sku' => 'CUA-PAS-03',
                'base_price' => 1.00,
                'cost_price' => 0.52,
                'profit_margin_percent' => 48.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/pastelito-molida.jpg',
                'badge' => 'Clásico',
                'prep_note' => 'Listo en 8 min',
                'stock' => 45,
            ],
            [
                'name' => 'Pastelito de Atún',
                'slug' => 'pastelito-atun',
                'description' => 'Relleno marinero de atún desmenuzado sofrito con aliños criollos, ligero y lleno de sabor.',
                'sku' => 'CUA-PAS-04',
                'base_price' => 1.00,
                'cost_price' => 0.55,
                'profit_margin_percent' => 45.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/pastelito-atun.jpg',
                'badge' => 'Especial',
                'prep_note' => 'Sabor fresco',
                'stock' => 40,
            ],
            [
                'name' => 'Pastelito de Ricota con Espinaca',
                'slug' => 'pastelito-ricota-con-espinaca',
                'description' => 'Fusión gourmet de suave ricota fresca y espinacas salteadas al dente. Opción vegetariana irresistible.',
                'sku' => 'CUA-PAS-05',
                'base_price' => 1.00,
                'cost_price' => 0.48,
                'profit_margin_percent' => 52.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/pastelito-ricota.jpg',
                'badge' => 'Vegetariano',
                'prep_note' => 'Cremoso y ligero',
                'stock' => 40,
            ],
            [
                'name' => 'Pastelito de Papa con Queso',
                'slug' => 'pastelito-papa-con-queso',
                'description' => 'El clásico andino: suave puré de papas con queso blanco derretido en su interior, crujiente por fuera y tierno por dentro.',
                'sku' => 'CUA-PAS-06',
                'base_price' => 1.00,
                'cost_price' => 0.45,
                'profit_margin_percent' => 55.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/pastelito-queso.jpg',
                'badge' => 'Tradición',
                'prep_note' => 'Queso fundido',
                'stock' => 50,
            ],

            // Combos especiales
            [
                'name' => 'Combo Dúo Cuadrados (2 Pastelitos)',
                'slug' => 'combo-duo-cuadrados',
                'description' => '2 pastelitos cuadrados a tu elección con salsa de la casa incluida (tártara o ajo). Precio especial combo: 3.500 Bs.',
                'sku' => 'CUA-CMB-07',
                'base_price' => 1.75,
                'cost_price' => 0.95,
                'profit_margin_percent' => 45.71,
                'discount_percent' => 12.50,
                'image_url' => '/images/stores/cuadrados-food/combo-2-pastelitos.jpg',
                'badge' => 'Ahorro Combo',
                'prep_note' => '2 sabores a elegir',
                'stock' => 80,
            ],
            [
                'name' => 'Combo Especial Cuadrados Box (4 Pastelitos + Bebida)',
                'slug' => 'combo-especial-cuadrados-box',
                'description' => 'Caja de presentación Cuadrados Food con 4 pastelitos variados crujientes y bebida fría de regalo incluida.',
                'sku' => 'CUA-CMB-08',
                'base_price' => 3.50,
                'cost_price' => 1.90,
                'profit_margin_percent' => 45.71,
                'discount_percent' => 22.00,
                'image_url' => '/images/stores/cuadrados-food/combo-box-4.jpg',
                'badge' => 'Bebida Regalo',
                'prep_note' => 'Caja de regalo + Bebida',
                'stock' => 35,
            ],

            // Bebidas
            [
                'name' => 'Café Negrito Venezolano',
                'slug' => 'cafe-negrito',
                'description' => 'Café criollo recién colado en greca, aromático, caliente y con notas achocolatadas.',
                'sku' => 'CUA-BEB-09',
                'base_price' => 0.50,
                'cost_price' => 0.18,
                'profit_margin_percent' => 64.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/catalog/cafe.jpg',
                'badge' => 'Mañanero',
                'prep_note' => 'Colado al instante',
                'stock' => 100,
            ],
            [
                'name' => 'Malta Polar 250ml Bien Fría',
                'slug' => 'malta-polar-250ml',
                'description' => 'Auténtica Malta Polar en botella de vidrio bien fría, el maridaje perfecto para unos pastelitos calientes.',
                'sku' => 'CUA-BEB-10',
                'base_price' => 1.25,
                'cost_price' => 0.70,
                'profit_margin_percent' => 44.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/malta-polar.jpg',
                'badge' => 'Punto de Nieve',
                'prep_note' => 'Muy fría',
                'stock' => 70,
            ],
            [
                'name' => 'Refresco Pepsi 250ml',
                'slug' => 'refresco-pepsi-250ml',
                'description' => 'Pepsi cola en botella de vidrio 250ml, efervescente y helada.',
                'sku' => 'CUA-BEB-11',
                'base_price' => 1.00,
                'cost_price' => 0.55,
                'profit_margin_percent' => 45.00,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/pepsi-250ml.jpg',
                'badge' => 'Bien Fría',
                'prep_note' => '250ml vidrio',
                'stock' => 60,
            ],
            [
                'name' => 'Jugo Yukery 250ml Vidrio',
                'slug' => 'jugo-yukery-250ml',
                'description' => 'Jugo pasteurizado de frutas Yukery en botella clásica de vidrio (durazno, naranja, pera).',
                'sku' => 'CUA-BEB-12',
                'base_price' => 1.25,
                'cost_price' => 0.72,
                'profit_margin_percent' => 42.40,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/jugo-yukery.jpg',
                'badge' => 'Fruta Natural',
                'prep_note' => 'Sabores surtidos',
                'stock' => 50,
            ],
            [
                'name' => 'Agua Mineral Crystal 500ml',
                'slug' => 'agua-mineral-crystal-500ml',
                'description' => 'Agua mineral Crystal sin gas, pura, ligera y refrescante.',
                'sku' => 'CUA-BEB-13',
                'base_price' => 0.75,
                'cost_price' => 0.35,
                'profit_margin_percent' => 53.33,
                'discount_percent' => 0.00,
                'image_url' => '/images/stores/cuadrados-food/agua-crystal.jpg',
                'badge' => 'Pura',
                'prep_note' => 'Fría o al clima',
                'stock' => 60,
            ],
        ];

        foreach ($productsData as $prod) {
            $product = Product::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'sku' => $prod['sku'],
                ],
                [
                    'category_id' => $category->id,
                    'name' => $prod['name'],
                    'slug' => $prod['slug'],
                    'description' => $prod['description'],
                    'base_price' => $prod['base_price'],
                    'cost_price' => $prod['cost_price'],
                    'profit_margin_percent' => $prod['profit_margin_percent'],
                    'discount_percent' => $prod['discount_percent'],
                    'image_url' => $prod['image_url'],
                    'badge' => $prod['badge'],
                    'prep_note' => $prod['prep_note'],
                    'is_active' => true,
                ]
            );

            BranchInventory::updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                ],
                [
                    'price' => $prod['base_price'],
                    'stock_quantity' => $prod['stock'],
                    'is_available' => true,
                ]
            );
        }

        // 6. Publicidad destacada en la vitrina del Mall (MallAdBanner)
        MallAdBanner::updateOrCreate(
            [
                'store_id' => $store->id,
                'headline' => '¡CUADRADOS FOOD EN JIHOVA!',
            ],
            [
                'subline' => 'No te hagas la vida de cuadritos, ¡mejor cómete un pastelito! Hechos en casa en Maracay.',
                'badge' => 'NUEVA TIENDA · MARACAY',
                'tag' => 'Desayunos & Pastelitos',
                'image_url' => '/images/stores/cuadrados-food/hero-promo.jpg',
                'target_url' => '/tiendas/cuadrados-food',
                'placement' => 'home_hero',
                'is_active' => true,
                'clicks_count' => 0,
                'impressions_count' => 120,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(3),
            ]
        );

        $this->command?->info("Tienda CUADRADOS FOOD, sucursal, productos y publicidad creados con éxito.");
    }
}
