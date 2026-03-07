<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Bank;
use App\Models\BusinessType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\City;
use App\Models\Commerce;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\DeliveryPayment;
use App\Models\Dispute;
use App\Models\CommerceInvoice;
use App\Models\Notification;
use App\Models\OperatorCode;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Phone;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Product;
use App\Models\ProductExtra;
use App\Models\ProductPreference;
use App\Models\Profile;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\State;
use App\Models\User;
use App\Models\UserLocation;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder único para demo Zonix Eats.
 * Simula el flujo completo con datos reales de Carabobo/Valencia:
 * El Socorro, Los Chorritos, Bella Florida, Mayorista (La Isabelica).
 *
 * Usuarios: 5 compradores (users) | 10 comercios | 1 empresa delivery | 2 repartidores empresa | 1 repartidor independiente | 1 admin.
 * Ejecutar: php artisan migrate:fresh --seed (DatabaseSeeder llama solo a este seeder).
 */
class ZonixDemoSeeder extends Seeder
{
    /** Coordenadas GPS reales Carabobo/Valencia (sectores) */
    private const ZONAS = [
        ['name' => 'El Socorro', 'street' => 'Av. Principal El Socorro', 'lat' => 10.1820, 'lng' => -68.0080],
        ['name' => 'Los Chorritos', 'street' => 'Calle Los Chorritos', 'lat' => 10.1750, 'lng' => -67.9980],
        ['name' => 'Bella Florida', 'street' => 'Bella Florida, Valencia', 'lat' => 10.1920, 'lng' => -68.0120],
        ['name' => 'Mayorista', 'street' => '1era Av. Este-Oeste, La Isabelica', 'lat' => 10.163461, 'lng' => -67.967541],
        ['name' => 'San Diego', 'street' => 'Centro Comercial San Diego', 'lat' => 10.2558, 'lng' => -67.9536],
        ['name' => 'La Honda', 'street' => 'Av. La Honda', 'lat' => 10.2050, 'lng' => -68.0020],
    ];

    /** Imágenes de productos (comida) - Themealdb */
    private const PRODUCT_IMAGES = [
        'https://www.themealdb.com/images/media/meals/wxywrq1468235067.jpg',
        'https://www.themealdb.com/images/media/meals/xvsurr1511719182.jpg',
        'https://www.themealdb.com/images/media/meals/adxcbq1619787919.jpg',
        'https://www.themealdb.com/images/media/meals/1550441275.jpg',
        'https://www.themealdb.com/images/media/meals/1520084413.jpg',
        'https://www.themealdb.com/images/media/meals/1529446352.jpg',
        'https://www.themealdb.com/images/media/meals/1529444830.jpg',
        'https://www.themealdb.com/images/media/meals/1550441883.jpg',
        'https://www.themealdb.com/images/media/meals/1529444830.jpg',
        'https://www.themealdb.com/images/media/meals/1550441275.jpg',
    ];

    /** Imágenes de comercios / restaurantes */
    private const COMMERCE_IMAGES = [
        'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800',
        'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=800',
        'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=800',
        'https://images.unsplash.com/photo-1552566626-52f8b828add9?w=800',
        'https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?w=800',
        'https://images.unsplash.com/photo-1544025162-d76694265947?w=800',
        'https://images.unsplash.com/photo-1559329007-40df8a9345d8?w=800',
        'https://images.unsplash.com/photo-1550966871-3ed3cdb5ed0c?w=800',
        'https://images.unsplash.com/photo-1424847651672-bf20ade79825?w=800',
        'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=800',
    ];

    /** Avatar por defecto para perfiles sin foto (ui-avatars con inicial) */
    private const DEFAULT_AVATAR = 'https://ui-avatars.com/api/?name=U&size=200&background=0dd3ff';

    private ?int $cityValenciaId = null;
    private ?int $operatorCodeId = null;
    private array $businessTypeIds = [];
    private array $categoryIds = [];
    private array $bankIds = [];

    public function run(): void
    {
        $this->command->info('ZonixDemoSeeder: iniciando datos de referencia y demo.');

        $this->seedReferenceData();
        $users = $this->seedUsersAndProfiles();
        $this->seedAddresses($users);
        $commerces = $this->seedCommerces($users);
        $this->seedProducts($commerces);
        $this->seedProductExtras();
        $this->seedProductPreferences();
        [$deliveryCompany, $agents] = $this->seedDelivery($users);
        $orders = $this->seedOrders($users, $commerces, $agents);
        $this->seedCarts($users);
        $this->seedCartItems($commerces);
        $this->seedCommercePaymentMethodsDemo($commerces[0]);
        $this->seedUser1PaymentMethods();
        $this->seedNotifications($users['users'][0]);
        $this->seedUserLocations($users);
        $this->seedPromotions($commerces[0]);
        $this->seedCoupons($users['users'][0]);
        $this->seedCouponUsages();
        $this->seedReviews();
        $this->seedDisputes();
        $this->seedDeliveryPayments();
        $this->seedCommerceInvoices($commerces);
        $this->seedPosts($commerces);
        $this->seedPostLikes($users);
        $this->fixDemoOrderTracking($orders);

        $this->command->info('ZonixDemoSeeder: finalizado.');
    }

    private function seedReferenceData(): void
    {
        Country::updateOrCreate(['id' => 1], ['id' => 1, 'sortname' => 'VE', 'name' => 'Venezuela', 'phonecode' => '58']);
        State::updateOrCreate(['id' => 7], ['id' => 7, 'name' => 'Carabobo', 'countries_id' => 1]);
        City::updateOrCreate(['id' => 90], ['id' => 90, 'name' => 'Valencia', 'state_id' => 7]);
        $this->cityValenciaId = 90;

        $banks = [
            ['name' => 'Banesco Banco Universal', 'code' => '0134', 'type' => 'privado'],
            ['name' => 'Banco Mercantil', 'code' => '0105', 'type' => 'privado'],
            ['name' => 'Banco de Venezuela', 'code' => '0102', 'type' => 'público'],
        ];
        foreach ($banks as $b) {
            $bank = Bank::updateOrCreate(['code' => $b['code']], $b);
            $this->bankIds[$b['name']] = $bank->id;
        }

        $codes = [['id' => 1, 'name' => '0412', 'code' => '0412'], ['id' => 2, 'name' => '0414', 'code' => '0414']];
        foreach ($codes as $c) {
            OperatorCode::updateOrCreate(['id' => $c['id']], $c);
        }
        $this->operatorCodeId = 1;

        $categories = [
            ['name' => 'Arepas', 'description' => 'Arepas venezolanas'],
            ['name' => 'Pizzas', 'description' => 'Pizzas artesanales'],
            ['name' => 'Hamburguesas', 'description' => 'Hamburguesas'],
            ['name' => 'Comida Criolla', 'description' => 'Platos típicos'],
            ['name' => 'Bebidas', 'description' => 'Jugos y refrescos'],
        ];
        foreach ($categories as $cat) {
            $c = Category::updateOrCreate(['name' => $cat['name']], $cat);
            $this->categoryIds[$cat['name']] = $c->id;
        }

        $types = [
            ['name' => 'Restaurant', 'icon' => 'restaurant', 'description' => 'Restaurantes'],
            ['name' => 'Comida Rápida', 'icon' => 'fastfood', 'description' => 'Comida rápida'],
            ['name' => 'Pizzería', 'icon' => 'local_pizza', 'description' => 'Pizzerías'],
            ['name' => 'Cafetería', 'icon' => 'coffee', 'description' => 'Cafeterías'],
            ['name' => 'Panadería', 'icon' => 'bakery_dining', 'description' => 'Panaderías'],
        ];
        foreach ($types as $t) {
            $bt = BusinessType::updateOrCreate(['name' => $t['name']], $t);
            $this->businessTypeIds[$t['name']] = $bt->id;
        }
    }

    private function seedUsersAndProfiles(): array
    {
        $password = Hash::make('password');
        $out = ['users' => [], 'commerce' => [], 'delivery_company' => null, 'delivery_agents' => [], 'delivery_independent' => null, 'admin' => null];

        // 1. Abrahan (user id 1) - comprador
        $u1 = User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Abrahan Pulido',
                'email' => 'ing.pulido.abrahan@gmail.com',
                'email_verified_at' => now(),
                'password' => $password,
                'google_id' => '111890855875234910207',
                'given_name' => 'Abrahan',
                'family_name' => 'Pulido',
                'profile_pic' => 'https://lh3.googleusercontent.com/a/ACg8ocIuLGJWAUiZXz3X-UKcCtla9yqtb8nK0sTu_33NkIv2O1x5d5-E=s96-c',
                'completed_onboarding' => true,
                'role' => 'users',
                'light' => '1',
            ]
        );
        $p1 = Profile::updateOrCreate(['user_id' => 1], [
            'firstName' => 'Abrahan', 'middleName' => '', 'lastName' => 'Pulido', 'secondLastName' => '',
            'photo_users' => $u1->profile_pic, 'date_of_birth' => '1990-01-15', 'maritalStatus' => 'single', 'sex' => 'M', 'status' => 'completeData',
        ]);
        // Teléfono: código operador (OperatorCodeSeeder) + 7 dígitos. Ej: 0412 4352014 o 0416 1234567
        $this->ensurePhone($p1->id, '4352014', 1); // 0412 4352014
        $out['users'][] = $p1;

        // 2-5. Cuatro compradores más (users)
        $buyers = [
            ['name' => 'María González', 'email' => 'maria.gonzalez@demo.zonix.eats', 'first' => 'María', 'last' => 'González'],
            ['name' => 'Carlos Rodríguez', 'email' => 'carlos.rodriguez@demo.zonix.eats', 'first' => 'Carlos', 'last' => 'Rodríguez'],
            ['name' => 'Ana Martínez', 'email' => 'ana.martinez@demo.zonix.eats', 'first' => 'Ana', 'last' => 'Martínez'],
            ['name' => 'Luis Pérez', 'email' => 'luis.perez@demo.zonix.eats', 'first' => 'Luis', 'last' => 'Pérez'],
        ];
        $buyerPhones = [['1234567', 4], ['7654321', 2], ['5544332', 4], ['9988776', 2]]; // [7 dígitos, operator_id] → 0416/0414
        foreach ($buyers as $i => $b) {
            $u = User::create([
                'name' => $b['name'], 'email' => $b['email'], 'email_verified_at' => now(), 'password' => $password,
                'given_name' => $b['first'], 'family_name' => $b['last'], 'completed_onboarding' => true, 'role' => 'users', 'light' => '1',
            ]);
            $p = Profile::create([
                'user_id' => $u->id, 'firstName' => $b['first'], 'lastName' => $b['last'], 'status' => 'completeData',
                'photo_users' => 'https://ui-avatars.com/api/?name=' . urlencode($b['first'] . '+' . $b['last']) . '&size=200&background=random',
                'maritalStatus' => 'single', 'sex' => $i % 2 === 0 ? 'F' : 'M', 'date_of_birth' => '1992-05-10',
            ]);
            $this->ensurePhone($p->id, $buyerPhones[$i][0], $buyerPhones[$i][1]); // 0416/0414 + 7 dígitos
            $out['users'][] = $p;
        }

        // 6-15. Diez comercios (cada uno con su usuario y perfil)
        $commerceNames = [
            'Restaurante El Socorro Grill', 'Pizzería Los Chorritos', 'Café Bella Florida', 'Panadería El Socorro',
            'Comedor Mayorista Express', 'Sushi San Diego', 'Restaurante La Honda', 'Arepera El Socorro',
            'Parrilla Los Chorritos', 'Cafetería Bella Florida',
        ];
        $commerceTypes = ['Restaurant', 'Pizzería', 'Cafetería', 'Panadería', 'Comida Rápida', 'Sushi Bar', 'Restaurant', 'Comida Rápida', 'Restaurant', 'Cafetería'];
        for ($i = 0; $i < 10; $i++) {
            $u = User::create([
                'name' => $commerceNames[$i] . ' (Dueño)',
                'email' => 'comercio' . ($i + 1) . '@demo.zonix.eats',
                'email_verified_at' => now(),
                'password' => $password,
                'completed_onboarding' => true,
                'role' => 'commerce',
                'light' => '1',
            ]);
            $p = Profile::create([
                'user_id' => $u->id,
                'firstName' => 'Dueño',
                'lastName' => 'Comercio ' . ($i + 1),
                'status' => 'completeData',
                'photo_users' => self::DEFAULT_AVATAR,
                'maritalStatus' => 'single',
                'sex' => 'M',
            ]);
            $this->ensurePhone($p->id, (string)(5012345 + $i), 1); // 0412 + 7 dígitos (ej: 0412 5012345)
            $out['commerce'][] = $p;
        }

        // 16. Empresa delivery
        $u = User::create([
            'name' => 'Envíos Carabobo C.A.',
            'email' => 'delivery.company@demo.zonix.eats',
            'email_verified_at' => now(),
            'password' => $password,
            'completed_onboarding' => true,
            'role' => 'delivery_company',
            'light' => '1',
        ]);
        $p = Profile::create([
            'user_id' => $u->id, 'firstName' => 'Envíos', 'lastName' => 'Carabobo', 'status' => 'completeData',
            'photo_users' => self::DEFAULT_AVATAR, 'maritalStatus' => 'single', 'sex' => 'M',
        ]);
        $this->ensurePhone($p->id, '9123456', 2); // 0414 9123456 (teléfono de la empresa desde perfil)
        $out['delivery_company'] = $p;

        // 17-18. Dos repartidores de la empresa
        foreach (['José Repartidor', 'Pedro Motorizado'] as $idx => $name) {
            $u = User::create([
                'name' => $name,
                'email' => 'repartidor' . ($idx + 1) . '@demo.zonix.eats',
                'email_verified_at' => now(),
                'password' => $password,
                'completed_onboarding' => true,
                'role' => 'delivery_agent',
                'light' => '1',
            ]);
            $p = Profile::create([
                'user_id' => $u->id,
                'firstName' => explode(' ', $name)[0],
                'lastName' => explode(' ', $name)[1],
                'status' => 'completeData',
                'photo_users' => 'https://ui-avatars.com/api/?name=' . urlencode(str_replace(' ', '+', $name)) . '&size=200&background=random',
                'maritalStatus' => 'single',
                'sex' => 'M',
            ]);
            $this->ensurePhone($p->id, ['6161000', '6161001'][$idx], 4); // 0416 6161000 / 0416 6161001
            $out['delivery_agents'][] = $p;
        }

        // 19. Repartidor independiente
        $u = User::create([
            'name' => 'Miguel Independiente',
            'email' => 'delivery.independent@demo.zonix.eats',
            'email_verified_at' => now(),
            'password' => $password,
            'completed_onboarding' => true,
            'role' => 'delivery',
            'light' => '1',
        ]);
        $p = Profile::create([
            'user_id' => $u->id, 'firstName' => 'Miguel', 'lastName' => 'Independiente', 'status' => 'completeData',
            'photo_users' => self::DEFAULT_AVATAR, 'maritalStatus' => 'single', 'sex' => 'M',
        ]);
        $this->ensurePhone($p->id, '2612345', 4); // 0416 2612345
        $out['delivery_independent'] = $p;

        // 20. Admin
        $u = User::create([
            'name' => 'Admin Zonix',
            'email' => 'admin@demo.zonix.eats',
            'email_verified_at' => now(),
            'password' => $password,
            'completed_onboarding' => true,
            'role' => 'admin',
            'light' => '1',
        ]);
        $p = Profile::create([
            'user_id' => $u->id, 'firstName' => 'Admin', 'lastName' => 'Zonix', 'status' => 'completeData',
            'photo_users' => self::DEFAULT_AVATAR, 'maritalStatus' => 'single', 'sex' => 'M',
        ]);
        $this->ensurePhone($p->id, '4140000', 2); // 0414 4140000
        $out['admin'] = $p;

        return $out;
    }

    /**
     * Teléfono en dos partes: (1) código de operador desde OperatorCodeSeeder (operator_codes),
     * (2) 7 dígitos en phones.number. Ejemplos: 0412 4352014 o 0416 1234567.
     *
     * @param int         $profileId       Perfil al que se asocia el teléfono
     * @param string      $number          Solo 7 dígitos (sin el prefijo 0412/0414/0416)
     * @param int|null    $operatorCodeId  ID en operator_codes (1=0412, 2=0414, 4=0416). Null = usar el por defecto
     */
    private function ensurePhone(int $profileId, string $number, ?int $operatorCodeId = null): void
    {
        if (Phone::where('profile_id', $profileId)->exists()) {
            return;
        }
        $number = str_pad(substr(preg_replace('/\D/', '', $number), 0, 7), 7, '0', STR_PAD_LEFT);
        Phone::create([
            'profile_id' => $profileId,
            'operator_code_id' => $operatorCodeId ?? $this->operatorCodeId,
            'number' => $number,
            'is_primary' => true,
            'status' => true,
        ]);
    }

    private function seedAddresses(array $users): void
    {
        $allProfiles = array_merge(
            $users['users'],
            $users['commerce'],
            $users['delivery_agents'],
            $users['delivery_independent'] ? [$users['delivery_independent']] : [],
            $users['delivery_company'] ? [$users['delivery_company']] : [],
            $users['admin'] ? [$users['admin']] : []
        );
        $zoneIndex = 0;
        foreach ($allProfiles as $profile) {
            $zone = self::ZONAS[$zoneIndex % count(self::ZONAS)];
            $zoneIndex++;
            Address::firstOrCreate(
                ['profile_id' => $profile->id, 'is_default' => true],
                [
                    'street' => $zone['street'],
                    'house_number' => (string) rand(1, 200),
                    'latitude' => $zone['lat'] + (rand(-30, 30) / 10000.0),
                    'longitude' => $zone['lng'] + (rand(-30, 30) / 10000.0),
                    'status' => 'completeData',
                    'city_id' => $this->cityValenciaId,
                ]
            );
        }
    }

    private function seedCommerces(array $users): array
    {
        $commerces = [];
        $types = ['Restaurant', 'Pizzería', 'Cafetería', 'Panadería', 'Comida Rápida', 'Sushi Bar', 'Restaurant', 'Comida Rápida', 'Restaurant', 'Cafetería'];
        $names = [
            'Restaurante El Socorro Grill', 'Pizzería Los Chorritos', 'Café Bella Florida', 'Panadería El Socorro',
            'Comedor Mayorista Express', 'Sushi San Diego', 'Restaurante La Honda', 'Arepera El Socorro',
            'Parrilla Los Chorritos', 'Cafetería Bella Florida',
        ];
        foreach ($users['commerce'] as $i => $profile) {
            $zone = self::ZONAS[$i % count(self::ZONAS)];
            $typeName = $types[$i];
            $btId = $this->businessTypeIds[$typeName] ?? null;
            $commerce = Commerce::create([
                'profile_id' => $profile->id,
                'is_primary' => true,
                'business_name' => $names[$i],
                'business_type' => $typeName,
                'business_type_id' => $btId,
                'address' => $zone['street'] . ', Valencia',
                'image' => self::COMMERCE_IMAGES[$i % count(self::COMMERCE_IMAGES)],
                'open' => true,
                'tax_id' => 'J-' . (30000000 + $i),
            ]);
            $commerces[] = $commerce;
            // Dirección del establecimiento (commerce_id, role=commerce)
            Address::create([
                'commerce_id' => $commerce->id,
                'profile_id' => null,
                'role' => 'commerce',
                'city_id' => $this->cityValenciaId,
                'street' => $zone['street'] . ' - Local ' . ($i + 1),
                'house_number' => (string) ($i + 1),
                'latitude' => $zone['lat'],
                'longitude' => $zone['lng'],
                'status' => 'completeData',
                'is_default' => true,
            ]);
        }
        return $commerces;
    }

    private function seedProducts(array $commerces): void
    {
        $names = ['Arepa Reina Pepiada', 'Pizza Margarita', 'Hamburguesa Clásica', 'Pabellón Criollo', 'Jugo de Parchita', 'Cachapa con Queso', 'Empanada de Carne', 'Tostadas', 'Café Marrón', 'Tequeno'];
        $imgIndex = 0;
        foreach ($commerces as $commerce) {
            for ($j = 0; $j < 8; $j++) {
                Product::create([
                    'commerce_id' => $commerce->id,
                    'category_id' => array_values($this->categoryIds)[$j % count($this->categoryIds)],
                    'name' => $names[$j % count($names)] . ' ' . ($j + 1),
                    'description' => 'Producto de calidad Valencia.',
                    'price' => round(rand(5, 25) + rand(0, 99) / 100, 2),
                    'image' => self::PRODUCT_IMAGES[$imgIndex % count(self::PRODUCT_IMAGES)],
                    'available' => true,
                    'stock_quantity' => rand(20, 100),
                ]);
                $imgIndex++;
            }
        }
    }

    private function seedDelivery(array $users): array
    {
        $profileCompany = $users['delivery_company'];
        $company = DeliveryCompany::create([
            'profile_id' => $profileCompany->id,
            'name' => 'Envíos Carabobo C.A.',
            'tax_id' => 'J-12345678',
            'address' => self::ZONAS[3]['street'] . ', Valencia',
            'image' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=400',
            'active' => true,
            'open' => true,
        ]);
        $agents = [];
        foreach ($users['delivery_agents'] as $profile) {
            $agents[] = DeliveryAgent::create([
                'company_id' => $company->id,
                'profile_id' => $profile->id,
                'status' => 'activo',
                'working' => true,
                'rating' => 4.5,
                'vehicle_type' => 'motorcycle',
                'license_number' => 'LIC-' . str_pad((string) $profile->id, 5, '0', STR_PAD_LEFT),
                'current_latitude' => 10.159739,
                'current_longitude' => -68.000354,
                'last_location_update' => now(),
            ]);
        }
        $pInd = $users['delivery_independent'];
        $agents[] = DeliveryAgent::create([
            'company_id' => null,
            'profile_id' => $pInd->id,
            'status' => 'activo',
            'working' => true,
            'rating' => 4.2,
            'vehicle_type' => 'motorcycle',
            'license_number' => 'LIC-IND-001',
            'current_latitude' => 10.17,
            'current_longitude' => -68.00,
            'last_location_update' => now(),
        ]);
        return [$company, $agents];
    }

    private function seedOrders(array $users, array $commerces, array $agents): array
    {
        $buyerProfile = $users['users'][0];
        $commerce = $commerces[0];
        $products = Product::where('commerce_id', $commerce->id)->where('available', true)->get();
        if ($products->isEmpty()) {
            return [];
        }
        $created = [];
        $statuses = [
            ['status' => 'shipped', 'delivery' => true],
            ['status' => 'delivered', 'delivery' => true],
            ['status' => 'delivered', 'delivery' => true],
            ['status' => 'cancelled', 'delivery' => false],
        ];
        $elSocorro = self::ZONAS[0];
        foreach ($statuses as $i => $cfg) {
            $deliveryFee = $cfg['delivery'] ? 3.50 : 0;
            $order = Order::create([
                'profile_id' => $buyerProfile->id,
                'commerce_id' => $commerce->id,
                'delivery_type' => $cfg['delivery'] ? 'delivery' : 'pickup',
                'status' => $cfg['status'],
                'total' => 0,
                'delivery_fee' => $deliveryFee,
                'delivery_payment_amount' => in_array($cfg['status'], ['shipped', 'delivered']) ? $deliveryFee : null,
                'commission_amount' => 0,
                'cancellation_penalty' => 0,
                'cancelled_by' => $cfg['status'] === 'cancelled' ? 'user_id' : null,
                'estimated_delivery_time' => $cfg['delivery'] ? 25 : null,
                'payment_method' => $cfg['status'] !== 'pending_payment' ? 'cash' : null,
                'reference_number' => $cfg['status'] !== 'pending_payment' ? 'REF' . rand(10000, 99999) : null,
                'payment_validated_at' => in_array($cfg['status'], ['paid', 'processing', 'shipped', 'delivered']) ? now() : null,
                'delivery_address' => $cfg['delivery'] ? 'C. las Torres, El Socorro, Valencia 2001' : null,
                'delivery_latitude' => $cfg['delivery'] ? $elSocorro['lat'] : null,
                'delivery_longitude' => $cfg['delivery'] ? $elSocorro['lng'] : null,
                'cancellation_reason' => $cfg['status'] === 'cancelled' ? 'Solicitud del cliente' : null,
            ]);
            $total = 0;
            foreach ($products->take(rand(1, 3)) as $p) {
                $qty = rand(1, 2);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p->id,
                    'quantity' => $qty,
                    'unit_price' => $p->price,
                ]);
                $total += $p->price * $qty;
            }
            $order->update(['total' => $total]);
            if ($cfg['delivery'] && in_array($cfg['status'], ['shipped', 'delivered']) && isset($agents[0])) {
                OrderDelivery::create([
                    'order_id' => $order->id,
                    'agent_id' => $agents[0]->id,
                    'status' => $cfg['status'] === 'shipped' ? 'in_transit' : 'delivered',
                    'delivery_fee' => $deliveryFee,
                ]);
            }
            $created[] = $order;
        }
        return $created;
    }

    private function seedCarts(array $users): void
    {
        foreach (array_slice($users['users'], 0, 3) as $profile) {
            Cart::firstOrCreate(
                ['profile_id' => $profile->id],
                ['notes' => null]
            );
        }
    }

    private function seedProductExtras(): void
    {
        $extrasPool = [
            ['name' => 'Extra Queso Cheddar', 'price' => 1.00],
            ['name' => 'Doble Carne', 'price' => 3.50],
            ['name' => 'Tocino Extra', 'price' => 1.50],
            ['name' => 'Queso Mozzarella', 'price' => 2.00],
            ['name' => 'Guacamole', 'price' => 2.50],
            ['name' => 'Huevo', 'price' => 1.00],
            ['name' => 'Champiñones', 'price' => 1.25],
            ['name' => 'Jalapeños', 'price' => 0.75],
            ['name' => 'Extra Salsa', 'price' => 0.50],
        ];
        $products = Product::all();
        foreach ($products as $product) {
            $selected = collect($extrasPool)->random(min(3, count($extrasPool)))->values()->all();
            foreach ($selected as $i => $extra) {
                ProductExtra::create([
                    'product_id' => $product->id,
                    'name' => $extra['name'],
                    'price' => $extra['price'],
                    'sort_order' => $i,
                ]);
            }
        }
    }

    private function seedProductPreferences(): void
    {
        $preferencesPool = ['Sin Cebolla', 'Sin Tomate', 'Bien cocido', 'Poco cocido', 'Sin picante', 'Extra picante', 'Salsa aparte', 'Sin sal'];
        $products = Product::all();
        foreach ($products as $product) {
            $selected = collect($preferencesPool)->random(min(2, count($preferencesPool)))->values()->all();
            foreach ($selected as $i => $name) {
                ProductPreference::create([
                    'product_id' => $product->id,
                    'name' => $name,
                    'sort_order' => $i,
                ]);
            }
        }
    }

    private function seedCartItems(array $commerces): void
    {
        $carts = Cart::with('profile')->get();
        $commerce = $commerces[0];
        $products = Product::where('commerce_id', $commerce->id)->where('available', true)->take(4)->get();
        if ($products->isEmpty()) {
            return;
        }
        foreach ($carts as $cart) {
            foreach ($products->random(min(2, $products->count())) as $product) {
                CartItem::firstOrCreate(
                    ['cart_id' => $cart->id, 'product_id' => $product->id],
                    ['quantity' => rand(1, 2)]
                );
            }
        }
    }

    private function seedUserLocations(array $users): void
    {
        $allProfiles = array_merge(
            $users['users'],
            array_slice($users['commerce'], 0, 3)
        );
        $zone = self::ZONAS[0];
        foreach ($allProfiles as $profile) {
            for ($i = 0; $i < rand(1, 2); $i++) {
                UserLocation::create([
                    'profile_id' => $profile->id,
                    'latitude' => $zone['lat'] + (rand(-50, 50) / 10000.0),
                    'longitude' => $zone['lng'] + (rand(-50, 50) / 10000.0),
                    'address' => $zone['street'] . ', Valencia',
                    'recorded_at' => now()->subHours(rand(0, 48)),
                ]);
            }
        }
    }

    private function seedPromotions(Commerce $commerce): void
    {
        $start = now();
        $end = now()->addDays(30);
        $promos = [
            ['title' => '20% en tu primera orden', 'description' => 'Válido en pedidos mayores a $10.', 'discount_type' => 'percentage', 'discount_value' => 20, 'minimum_order' => 10],
            ['title' => '$5 de descuento', 'description' => 'En pedidos mayores a $25.', 'discount_type' => 'fixed', 'discount_value' => 5, 'minimum_order' => 25],
            ['title' => 'Combo familiar 15%', 'description' => 'Solo para pedidos delivery.', 'discount_type' => 'percentage', 'discount_value' => 15, 'minimum_order' => 30],
        ];
        foreach ($promos as $p) {
            Promotion::create([
                'commerce_id' => $commerce->id,
                'title' => $p['title'],
                'description' => $p['description'],
                'discount_type' => $p['discount_type'],
                'discount_value' => $p['discount_value'],
                'minimum_order' => $p['minimum_order'],
                'start_date' => $start,
                'end_date' => $end,
                'is_active' => true,
            ]);
        }
    }

    private function seedCoupons(Profile $user1Profile): void
    {
        $end = now()->addDays(60);
        Coupon::create([
            'code' => 'ZONIX20',
            'title' => '20% descuento',
            'description' => 'Válido una vez por usuario.',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'minimum_order' => 15,
            'usage_limit' => 1,
            'start_date' => now(),
            'end_date' => $end,
            'is_public' => true,
            'is_active' => true,
        ]);
        Coupon::create([
            'code' => 'DEMO' . $user1Profile->id,
            'title' => 'Cupón demo usuario 1',
            'description' => 'Cupón privado para Abrahan.',
            'discount_type' => 'fixed',
            'discount_value' => 3,
            'minimum_order' => 10,
            'usage_limit' => 5,
            'start_date' => now(),
            'end_date' => $end,
            'is_public' => false,
            'assigned_to_profile_id' => $user1Profile->id,
            'is_active' => true,
        ]);
    }

    private function seedCouponUsages(): void
    {
        $order = Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])->first();
        $coupon = Coupon::where('is_active', true)->first();
        if ($order && $coupon) {
            CouponUsage::firstOrCreate(
                ['coupon_id' => $coupon->id, 'profile_id' => $order->profile_id, 'order_id' => $order->id],
                [
                    'discount_amount' => $coupon->discount_type === 'percentage'
                        ? round($order->total * $coupon->discount_value / 100, 2)
                        : $coupon->discount_value,
                    'used_at' => $order->created_at,
                ]
            );
        }
    }

    private function seedReviews(): void
    {
        $orders = Order::where('status', 'delivered')->get();
        foreach ($orders->take(5) as $order) {
            Review::firstOrCreate(
                [
                    'profile_id' => $order->profile_id,
                    'order_id' => $order->id,
                    'reviewable_type' => Commerce::class,
                    'reviewable_id' => $order->commerce_id,
                ],
                ['rating' => rand(4, 5), 'comment' => 'Excelente servicio y comida.']
            );
            $od = $order->orderDelivery;
            if ($od && $od->agent) {
                Review::firstOrCreate(
                    [
                        'profile_id' => $order->profile_id,
                        'order_id' => $order->id,
                        'reviewable_type' => DeliveryAgent::class,
                        'reviewable_id' => $od->agent->id,
                    ],
                    ['rating' => rand(4, 5), 'comment' => 'Llegó a tiempo, muy amable.']
                );
            }
        }
    }

    private function seedDisputes(): void
    {
        $order = Order::whereIn('status', ['delivered', 'cancelled'])->first();
        if (!$order || !$order->profile) {
            return;
        }
        Dispute::firstOrCreate(
            [
                'order_id' => $order->id,
                'reported_by_type' => Profile::class,
                'reported_by_id' => $order->profile_id,
                'reported_against_type' => Commerce::class,
                'reported_against_id' => $order->commerce->profile_id,
            ],
            [
                'type' => 'quality_issue',
                'description' => 'Demo: producto llegó frío.',
                'status' => 'pending',
            ]
        );
    }

    private function seedDeliveryPayments(): void
    {
        $orderDeliveries = OrderDelivery::whereHas('order', fn ($q) => $q->whereIn('status', ['shipped', 'delivered']))->get();
        foreach ($orderDeliveries as $od) {
            $status = rand(0, 1) ? 'paid_to_delivery' : 'pending_payment_to_delivery';
            DeliveryPayment::firstOrCreate(
                ['order_id' => $od->order_id, 'delivery_agent_id' => $od->agent_id],
                [
                    'amount' => $od->delivery_fee,
                    'status' => $status,
                    'paid_at' => $status === 'paid_to_delivery' ? now() : null,
                ]
            );
        }
    }

    private function seedCommerceInvoices(array $commerces): void
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();
        foreach ($commerces as $commerce) {
            $commissionAmount = Order::where('commerce_id', $commerce->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
                ->sum('commission_amount');
            $membershipFee = $commerce->membership_monthly_fee ?? 50.00;
            $total = $membershipFee + $commissionAmount;
            if ($total > 0) {
                CommerceInvoice::firstOrCreate(
                    [
                        'commerce_id' => $commerce->id,
                        'invoice_date' => Carbon::now()->toDateString(),
                    ],
                    [
                        'membership_fee' => $membershipFee,
                        'commission_amount' => $commissionAmount,
                        'total' => $total,
                        'due_date' => Carbon::now()->addMonth()->toDateString(),
                        'status' => 'pending',
                    ]
                );
            }
        }
    }

    private function seedPosts(array $commerces): void
    {
        $promoImages = [
            'https://www.themealdb.com/images/media/meals/1529444830.jpg',
            'https://www.themealdb.com/images/media/meals/1550441275.jpg',
            'https://www.themealdb.com/images/media/meals/1520084413.jpg',
        ];
        foreach ($commerces as $commerce) {
            for ($i = 0; $i < rand(2, 3); $i++) {
                Post::create([
                    'commerce_id' => $commerce->id,
                    'tipo' => 'promo',
                    'name' => 'Promo del día ' . ($i + 1),
                    'description' => 'Oferta especial de ' . $commerce->business_name,
                    'price' => rand(5, 15),
                    'media_url' => $promoImages[$i % count($promoImages)],
                ]);
            }
        }
    }

    private function seedPostLikes(array $users): void
    {
        $posts = Post::all();
        $profiles = array_merge($users['users'], array_slice($users['commerce'], 0, 2));
        if ($posts->isEmpty() || empty($profiles)) {
            return;
        }
        foreach ($posts->take(10) as $post) {
            $likers = collect($profiles)->random(min(2, count($profiles)));
            foreach ($likers as $profile) {
                PostLike::firstOrCreate(
                    ['post_id' => $post->id, 'profile_id' => $profile->id]
                );
            }
        }
    }

    private function seedCommercePaymentMethodsDemo(Commerce $commerce): void
    {
        $commerce->paymentMethods()->delete();
        $banesco = Bank::where('name', 'like', '%Banesco%')->first();
        $mercantil = Bank::where('name', 'like', '%Mercantil%')->first();
        $demoMethods = [
            [
                'type' => 'mobile_payment', 'phone' => '04121234567', 'owner_name' => 'Juan Pérez', 'owner_id' => 'V-12.345.678',
                'bank_id' => $mercantil?->id, 'is_default' => true, 'is_active' => true,
                'reference_info' => ['alias' => 'Pago móvil - Personal', 'bank' => $mercantil?->name ?? 'Mercantil', 'currency' => 'VES'],
            ],
            [
                'type' => 'bank_transfer', 'account_number' => '01050000000000005678', 'owner_name' => 'Inversiones Zonix C.A.', 'owner_id' => 'J-123456789',
                'bank_id' => $banesco?->id, 'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Transferencia Bancaria', 'bank' => $banesco?->name ?? 'Banesco', 'currency' => 'VES'],
            ],
            [
                'type' => 'other', 'email' => 'cuenta@paypal.com', 'owner_name' => 'Juan Alberto Pérez', 'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Billetera Digital', 'display_type' => 'digital_wallet', 'platform' => 'PayPal', 'currency' => 'USD', 'notes' => 'Saldo disponible: $45.00'],
            ],
            [
                'type' => 'card', 'brand' => 'Visa', 'last4' => '4242', 'exp_month' => 12, 'exp_year' => 2026, 'cardholder_name' => 'JUAN PÉREZ', 'owner_name' => 'Juan Pérez',
                'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Visa Termina en 4242', 'exp' => '12/26', 'holder' => 'JUAN PÉREZ'],
            ],
        ];
        foreach ($demoMethods as $data) {
            $commerce->paymentMethods()->create($data);
        }
    }

    /** Todos los métodos de pago demo para el usuario 1 (comprador Abrahan). */
    private function seedUser1PaymentMethods(): void
    {
        $user = User::find(1);
        if (!$user) {
            return;
        }
        $user->paymentMethods()->delete();
        $banesco = Bank::where('name', 'like', '%Banesco%')->first();
        $mercantil = Bank::where('name', 'like', '%Mercantil%')->first();
        $demoMethods = [
            [
                'type' => 'mobile_payment', 'phone' => '04121234567', 'owner_name' => 'Abrahan Pulido', 'owner_id' => 'V-12.345.678',
                'bank_id' => $mercantil?->id, 'is_default' => true, 'is_active' => true,
                'reference_info' => ['alias' => 'Pago móvil - Personal', 'bank' => $mercantil?->name ?? 'Mercantil', 'currency' => 'VES'],
            ],
            [
                'type' => 'bank_transfer', 'account_number' => '01050000000000005678', 'owner_name' => 'Abrahan Pulido', 'owner_id' => 'V-12.345.678',
                'bank_id' => $banesco?->id, 'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Transferencia Bancaria', 'bank' => $banesco?->name ?? 'Banesco', 'currency' => 'VES'],
            ],
            [
                'type' => 'other', 'email' => 'ing.pulido.abrahan@gmail.com', 'owner_name' => 'Abrahan Pulido', 'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Billetera Digital', 'display_type' => 'digital_wallet', 'platform' => 'PayPal', 'currency' => 'USD', 'notes' => 'Saldo disponible: $45.00'],
            ],
            [
                'type' => 'card', 'brand' => 'Visa', 'last4' => '4242', 'exp_month' => 12, 'exp_year' => 2026, 'cardholder_name' => 'ABRAHAN PULIDO', 'owner_name' => 'Abrahan Pulido',
                'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Visa Termina en 4242', 'exp' => '12/26', 'holder' => 'ABRAHAN PULIDO'],
            ],
            [
                'type' => 'card', 'brand' => 'Mastercard', 'last4' => '5555', 'exp_month' => 6, 'exp_year' => 2027, 'cardholder_name' => 'ABRAHAN PULIDO', 'owner_name' => 'Abrahan Pulido',
                'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Mastercard Termina en 5555', 'exp' => '06/27', 'holder' => 'ABRAHAN PULIDO'],
            ],
            [
                'type' => 'cash', 'owner_name' => 'Abrahan Pulido', 'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Efectivo'],
            ],
        ];
        foreach ($demoMethods as $data) {
            $user->paymentMethods()->create($data);
        }
    }

    private function seedNotifications(Profile $profile): void
    {
        $items = [
            ['title' => 'Tu pedido está en camino', 'body' => 'Tu pedido #1234 está en camino. Llegará en unos 15 min.', 'type' => 'order_status'],
            ['title' => 'Pago confirmado', 'body' => 'Tu pago de $25.50 ha sido confirmado.', 'type' => 'payment_confirmation'],
            ['title' => '¡Oferta especial!', 'body' => '20% de descuento con el código ZONIX20.', 'type' => 'promotion'],
            ['title' => 'Tu pedido ha sido entregado', 'body' => '¡Disfruta tu comida!', 'type' => 'order_status'],
        ];
        foreach ($items as $i => $item) {
            Notification::create([
                'profile_id' => $profile->id,
                'title' => $item['title'],
                'body' => $item['body'],
                'type' => $item['type'],
                'read_at' => $i >= 2 ? now()->subDays(1) : null,
                'data' => ['order_id' => $i % 2 === 0 ? 1000 + $i : null],
            ]);
        }
    }

    private function fixDemoOrderTracking(array $orders): void
    {
        $elSocorroLat = 10.125277;
        $elSocorroLng = -68.051191;
        $deliveryLat = 10.159739;
        $deliveryLng = -68.000354;
        foreach ($orders as $order) {
            if ($order->delivery_type !== 'delivery') {
                continue;
            }
            $order->update([
                'delivery_latitude' => $elSocorroLat,
                'delivery_longitude' => $elSocorroLng,
            ]);
            $od = OrderDelivery::where('order_id', $order->id)->first();
            if ($od && $od->agent) {
                $od->agent->update([
                    'current_latitude' => $deliveryLat,
                    'current_longitude' => $deliveryLng,
                    'last_location_update' => now(),
                ]);
            }
            $addr = Address::where('profile_id', $order->profile_id)->where('is_default', true)->first();
            if ($addr) {
                $addr->update([
                    'street' => 'C. las Torres, Valencia 2001, Carabobo',
                    'latitude' => $elSocorroLat,
                    'longitude' => $elSocorroLng,
                ]);
            }
        }
    }
}
