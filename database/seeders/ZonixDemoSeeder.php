<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Bank;
use App\Models\BusinessType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\City;
use App\Models\Commerce;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\DeliveryPayment;
use App\Models\DeliveryZone;
use App\Models\Dispute;
use App\Models\Document;
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
 * Geolocalización exacta: Venezuela, Carabobo, Valencia.
 * Sectores: El Socorro, Los Chorritos, Mayorista (La Isabelica), Bella Florida, San Diego, Santa Rosa.
 *
 * Usuarios: 5 compradores | 10 comercios | 1 empresa delivery | 2 repartidores empresa | 1 independiente | 1 admin.
 * Órdenes: 7 para comprador 1 (pending_payment, paid, processing, shipped, delivered x2, cancelled) + 1 para comprador 2 (delivered).
 * Notificaciones: comprador (order/promotion/points/support, Hoy/Ayer, leídas/no leídas) y comercio (user 6).
 *
 * Usuarios fijos (NO se modifican en tabla users): id 1 (Abrahan, role=users), id 6 (Wistremiro, role=commerce).
 * Tablas conectadas a user 1 y 6 que SÍ se mejoran:
 * - users: no se tocan (datos fijos).
 * - profiles (user_id): creados/actualizados en seedUsersAndProfiles.
 * - phones (profile_id): ensurePhone en seedUsersAndProfiles.
 * - addresses (profile_id): seedAddresses + ensureUser1AndUser6AddressesAndData (El Socorro exacto).
 * - documents (profile_id): seedAllProfilesDocuments (CI/RIF).
 * - carts (profile_id): seedCarts (user 1 tiene carrito).
 * - cart_items: seedCartItems.
 * - orders (profile_id): seedOrders (user 1 es comprador); fixDemoOrderTracking actualiza dirección entrega.
 * - order_items: por orden.
 * - notifications (profile_id): seedNotifications (user 1).
 * - user_locations (profile_id): seedUserLocations + ensureUser1AndUser6UserLocations (El Socorro).
 * - coupons / coupon_usages (profile_id): seedCoupons (user 1), seedCouponUsages.
 * - reviews (profile_id): seedReviews.
 * - disputes: seedDisputes (puede involucrar orden de user 1).
 * - user_payment_methods (user_id=1): seedUser1PaymentMethods.
 * Solo user 6 (commerce): commerces (profile_id), addresses (commerce_id), products, promotions,
 * payment_methods (commerce), posts, commerce_invoices; seedCommercePaymentMethodsDemo(commerces[0]).
 *
 * CONEXIONES ENTRE ROLES (todos los usuarios según su role quedan conectados entre sí):
 * - Buyer (users[0]) → Order (profile_id) → Commerce (commerces[0], user 6) → OrderItem → Product.
 * - Order → OrderDelivery (order_id, agent_id) → DeliveryAgent (company_id → DeliveryCompany, o null independiente).
 * - DeliveryCompany → Profile (delivery_company). DeliveryAgent → Profile (repartidor empresa o independiente).
 * - Review: profile_id (buyer) revisa reviewable_type/reviewable_id (Commerce o DeliveryAgent).
 * - Dispute: order_id, reported_by (buyer profile), reported_against (commerce profile).
 * - DeliveryPayment: order_id + delivery_agent_id (pago al repartidor).
 * - Cart/CartItem: profile (buyer) + product (commerce). PostLike: perfiles (buyers + commerce) → Post (commerce).
 * - Admin: tiene profile, address, documents; en la app se relaciona por permisos (ve órdenes, disputas, etc.), no por FK en este seed.
 * - ChatMessage: seedChatMessages (mensajes en órdenes entregadas: cliente, restaurante, repartidor).
 * - DeliveryZone: seedDeliveryZones (zonas activas Valencia: El Socorro, Los Chorritos).
 */
class ZonixDemoSeeder extends Seeder
{
    /** Coordenadas GPS exactas - Valencia, Carabobo, Venezuela (sectores reales para pruebas) */
    private const ZONAS = [
        ['name' => 'El Socorro', 'street' => 'Av. Principal El Socorro', 'lat' => 10.1146, 'lng' => -68.0401],
        ['name' => 'Los Chorritos', 'street' => 'Sector Los Chorritos, Valencia', 'lat' => 10.1200, 'lng' => -68.0200],
        ['name' => 'Mayorista', 'street' => '1ra Av. Este-Oeste, La Isabelica (Mayorista)', 'lat' => 10.163461, 'lng' => -67.967541],
        ['name' => 'Bella Florida', 'street' => 'Bella Florida (La Florida)', 'lat' => 10.1528, 'lng' => -68.0403],
        ['name' => 'San Diego', 'street' => 'Av. Principal San Diego, CC San Diego', 'lat' => 10.26057, 'lng' => -67.95363],
        ['name' => 'Santa Rosa', 'street' => 'Calle 86 Sucre, Santa Rosa', 'lat' => 10.16561, 'lng' => -68.000375],
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
        $this->ensureUser1AndUser6AddressesAndData($users);
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
        $this->seedAllProfilesDocuments($users);
        $this->seedNotifications($users['users'][0]);
        $this->seedUserLocations($users);
        $this->ensureUser1AndUser6UserLocations();
        $this->seedPromotions($commerces[0]);
        $this->seedCoupons($users['users'][0]);
        $this->seedCouponUsages();
        $this->seedReviews();
        $this->seedDisputes();
        $this->seedDeliveryPayments();
        $this->seedCommerceInvoices($commerces);
        $this->seedPosts($commerces);
        $this->seedPostLikes($users);
        $this->seedDeliveryZones();
        $this->seedChatMessages();
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

        // OperatorCode lo puebla OperatorCodeSeeder (code numérico 412, 414, 424, 416, 426)
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
            ['name' => 'Sushi Bar', 'icon' => 'restaurant', 'description' => 'Sushi y comida japonesa'],
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

        // Usuario 6: Wistremiro (commerce, Google) - datos fijos para desarrollo
        $u6 = User::updateOrCreate(
            ['id' => 6],
            [
                'name' => 'Wistremiro A Pulido B',
                'email' => 'wistremiropulido@gmail.com',
                'email_verified_at' => null,
                'password' => null,
                'google_id' => '107212919897356810816',
                'given_name' => 'Wistremiro A',
                'family_name' => 'Pulido B',
                'profile_pic' => 'https://lh3.googleusercontent.com/a/ACg8ocKgWH29et0okV9S-wV6quri0609QRDbCoqH_C2OmUKMl_mi5Q=s96-c',
                'AccessToken' => null,
                'completed_onboarding' => true,
                'role' => 'commerce',
                'light' => '1',
            ]
        );
        $p6 = Profile::updateOrCreate(
            ['user_id' => 6],
            [
                'firstName' => 'Wistremiro A',
                'lastName' => 'Pulido B',
                'photo_users' => $u6->profile_pic,
                'status' => 'completeData',
                'maritalStatus' => 'single',
                'sex' => 'M',
            ]
        );
        $this->ensurePhone($p6->id, '6000000', 1);
        $out['commerce'][] = $p6;

        // 7-15. Nueve comercios más (cada uno con su usuario y perfil)
        $commerceNames = [
            'Restaurante El Socorro Grill', 'Pizzería Los Chorritos', 'Café Bella Florida', 'Panadería El Socorro',
            'Comedor Mayorista Express', 'Sushi San Diego', 'Restaurante La Honda', 'Arepera El Socorro',
            'Parrilla Los Chorritos', 'Cafetería Bella Florida',
        ];
        $commerceTypes = ['Restaurant', 'Pizzería', 'Cafetería', 'Panadería', 'Comida Rápida', 'Sushi Bar', 'Restaurant', 'Comida Rápida', 'Restaurant', 'Cafetería'];
        for ($i = 1; $i < 10; $i++) {
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
            $this->ensurePhone($p->id, (string)(5012345 + $i), 1);
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
        $zonas = self::ZONAS;
        $nZonas = count($zonas);
        foreach ($allProfiles as $i => $profile) {
            $zone = $zonas[$i % $nZonas];
            // Variación mínima para pruebas reales (aprox. ±20 m)
            $lat = $zone['lat'] + (rand(-20, 20) / 100000.0);
            $lng = $zone['lng'] + (rand(-20, 20) / 100000.0);
            Address::firstOrCreate(
                ['profile_id' => $profile->id, 'is_default' => true],
                [
                    'street' => $zone['street'],
                    'house_number' => (string) rand(1, 150),
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status' => 'completeData',
                    'city_id' => $this->cityValenciaId,
                ]
            );
        }
    }

    /**
     * Mejora explícita de todas las tablas vinculadas a usuarios 1 y 6.
     * - profiles: ya creados/actualizados en seedUsersAndProfiles (no se tocan users 1 y 6).
     * - addresses: dirección por defecto en El Socorro (coords exactas) para ambos.
     * - phones, documents, carts, orders, notifications, etc. se generan en sus seeders.
     * Tablas conectadas: profiles (user_id), phones (profile_id), addresses (profile_id),
     * documents (profile_id), carts (profile_id), orders (profile_id), notifications (profile_id),
     * user_locations (profile_id), coupon/coupon_usages (profile_id), reviews (profile_id);
     * user 6 además: commerces (profile_id), addresses (commerce_id), products, promotions, payment_methods, posts.
     */
    private function ensureUser1AndUser6AddressesAndData(array $users): void
    {
        $elSocorro = self::ZONAS[0];
        $profile1 = Profile::where('user_id', 1)->first();
        $profile6 = Profile::where('user_id', 6)->first();
        if ($profile1) {
            Address::updateOrCreate(
                ['profile_id' => $profile1->id, 'is_default' => true],
                [
                    'street' => $elSocorro['street'],
                    'house_number' => '1',
                    'latitude' => $elSocorro['lat'],
                    'longitude' => $elSocorro['lng'],
                    'status' => 'completeData',
                    'city_id' => $this->cityValenciaId,
                ]
            );
        }
        if ($profile6) {
            Address::updateOrCreate(
                ['profile_id' => $profile6->id, 'is_default' => true],
                [
                    'street' => $elSocorro['street'],
                    'house_number' => '6',
                    'latitude' => $elSocorro['lat'],
                    'longitude' => $elSocorro['lng'],
                    'status' => 'completeData',
                    'city_id' => $this->cityValenciaId,
                ]
            );
        }
    }

    /** Asegura que usuario 1 y 6 tengan al menos una ubicación reciente en El Socorro (pruebas de geolocalización). */
    private function ensureUser1AndUser6UserLocations(): void
    {
        $elSocorro = self::ZONAS[0];
        foreach ([1, 6] as $userId) {
            $profile = Profile::where('user_id', $userId)->first();
            if (!$profile) {
                continue;
            }
            UserLocation::firstOrCreate(
                [
                    'profile_id' => $profile->id,
                    'latitude' => $elSocorro['lat'],
                    'longitude' => $elSocorro['lng'],
                ],
                [
                    'address' => $elSocorro['street'] . ', Valencia, Carabobo',
                    'recorded_at' => now(),
                ]
            );
        }
    }

    private function seedCommerces(array $users): array
    {
        $commerces = [];
        $zonas = self::ZONAS;
        $types = ['Restaurant', 'Pizzería', 'Cafetería', 'Panadería', 'Comida Rápida', 'Sushi Bar', 'Restaurant', 'Comida Rápida', 'Restaurant', 'Cafetería'];
        $names = [
            'Restaurante El Socorro Grill', 'Pizzería Los Chorritos', 'Café Bella Florida', 'Panadería Mayorista',
            'Comedor San Diego Express', 'Sushi San Diego', 'Restaurante Santa Rosa', 'Arepera El Socorro',
            'Parrilla Los Chorritos', 'Cafetería Bella Florida',
        ];
        foreach ($users['commerce'] as $i => $profile) {
            $zone = $zonas[$i % count($zonas)];
            $typeName = $types[$i];
            $btId = $this->businessTypeIds[$typeName] ?? null;
            $commerce = Commerce::create([
                'profile_id' => $profile->id,
                'is_primary' => true,
                'business_name' => $names[$i],
                'business_type' => $typeName,
                'business_type_id' => $btId,
                'address' => $zone['street'] . ', Valencia, Carabobo',
                'image' => self::COMMERCE_IMAGES[$i % count(self::COMMERCE_IMAGES)],
                'open' => true,
                'tax_id' => 'J-' . (30000000 + $i),
            ]);
            $commerces[] = $commerce;
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
        $mayorista = self::ZONAS[2]; // Mayorista, La Isabelica - sede empresa
        $company = DeliveryCompany::create([
            'profile_id' => $profileCompany->id,
            'name' => 'Envíos Carabobo C.A.',
            'tax_id' => 'J-12345678',
            'address' => $mayorista['street'] . ', Valencia, Carabobo',
            'image' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=400',
            'active' => true,
            'open' => true,
        ]);
        $santaRosa = self::ZONAS[5]; // Santa Rosa - repartidores operando en zona
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
                'current_latitude' => $santaRosa['lat'],
                'current_longitude' => $santaRosa['lng'],
                'last_location_update' => now(),
            ]);
        }
        $elSocorro = self::ZONAS[0];
        $pInd = $users['delivery_independent'];
        $agents[] = DeliveryAgent::create([
            'company_id' => null,
            'profile_id' => $pInd->id,
            'status' => 'activo',
            'working' => true,
            'rating' => 4.2,
            'vehicle_type' => 'motorcycle',
            'license_number' => 'LIC-IND-001',
            'current_latitude' => $elSocorro['lat'],
            'current_longitude' => $elSocorro['lng'],
            'last_location_update' => now(),
        ]);
        return [$company, $agents];
    }

    /**
     * Órdenes de prueba para evaluar todo el flujo: pending_payment, paid, processing, shipped, delivered, cancelled.
     * Buyer 1 (Abrahan): 6 órdenes. Buyer 2 (María): 1 orden entregada para listados más ricos.
     */
    private function seedOrders(array $users, array $commerces, array $agents): array
    {
        $buyerProfile = $users['users'][0];
        $commerce = $commerces[0];
        $products = Product::where('commerce_id', $commerce->id)->where('available', true)->get();
        if ($products->isEmpty()) {
            return [];
        }
        $created = [];
        $elSocorro = self::ZONAS[0];
        $deliveryAddress = 'Av. Principal El Socorro, Valencia 2001, Carabobo';

        // 6 órdenes para Abrahan (user 1): cubrir todos los estados para tests
        $statuses = [
            ['status' => 'pending_payment', 'delivery' => true, 'created_at' => now()],
            ['status' => 'paid', 'delivery' => true, 'created_at' => now()->subHours(2)],
            ['status' => 'processing', 'delivery' => true, 'created_at' => now()->subHours(1)],
            ['status' => 'shipped', 'delivery' => true, 'created_at' => now()->subMinutes(30)],
            ['status' => 'delivered', 'delivery' => true, 'created_at' => now()->subDay()],
            ['status' => 'delivered', 'delivery' => true, 'created_at' => now()->subDays(2)],
            ['status' => 'cancelled', 'delivery' => false, 'created_at' => now()->subDays(1)],
        ];
        foreach ($statuses as $i => $cfg) {
            $deliveryFee = $cfg['delivery'] ? 3.50 : 0;
            $isPaidOrBeyond = in_array($cfg['status'], ['paid', 'processing', 'shipped', 'delivered']);
            $order = Order::create([
                'profile_id' => $buyerProfile->id,
                'commerce_id' => $commerce->id,
                'delivery_type' => $cfg['delivery'] ? 'delivery' : 'pickup',
                'status' => $cfg['status'],
                'approved_for_payment' => $isPaidOrBeyond,
                'total' => 0,
                'delivery_fee' => $deliveryFee,
                'delivery_payment_amount' => in_array($cfg['status'], ['shipped', 'delivered']) ? $deliveryFee : null,
                'commission_amount' => 0,
                'cancellation_penalty' => 0,
                'cancelled_by' => $cfg['status'] === 'cancelled' ? 'user_id' : null,
                'estimated_delivery_time' => $cfg['delivery'] ? 25 : null,
                'payment_method' => $cfg['status'] !== 'pending_payment' ? 'cash' : null,
                'reference_number' => $cfg['status'] !== 'pending_payment' ? 'REF' . (10000 + $i) : null,
                'payment_validated_at' => $isPaidOrBeyond ? ($cfg['created_at'] ?? now()) : null,
                'delivery_address' => $cfg['delivery'] ? $deliveryAddress : null,
                'delivery_latitude' => $cfg['delivery'] ? $elSocorro['lat'] : null,
                'delivery_longitude' => $cfg['delivery'] ? $elSocorro['lng'] : null,
                'cancellation_reason' => $cfg['status'] === 'cancelled' ? 'Solicitud del cliente' : null,
                'created_at' => $cfg['created_at'] ?? now(),
            ]);
            $total = 0;
            $selected = $products->random(min(3, $products->count()));
            foreach ($selected as $p) {
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

        // 1 orden entregada para segundo comprador (María) - listados admin/commerce más ricos
        if (isset($users['users'][1])) {
            $secondBuyer = $users['users'][1];
            $order = Order::create([
                'profile_id' => $secondBuyer->id,
                'commerce_id' => $commerce->id,
                'delivery_type' => 'delivery',
                'status' => 'delivered',
                'approved_for_payment' => true,
                'total' => 0,
                'delivery_fee' => 3.50,
                'delivery_payment_amount' => 3.50,
                'commission_amount' => 0,
                'estimated_delivery_time' => 25,
                'payment_method' => 'cash',
                'reference_number' => 'REF20001',
                'payment_validated_at' => now()->subDays(3),
                'delivery_address' => $deliveryAddress,
                'delivery_latitude' => $elSocorro['lat'],
                'delivery_longitude' => $elSocorro['lng'],
                'created_at' => now()->subDays(3),
            ]);
            $total = 0;
            foreach ($products->take(2) as $p) {
                $qty = 1;
                OrderItem::create(['order_id' => $order->id, 'product_id' => $p->id, 'quantity' => $qty, 'unit_price' => $p->price]);
                $total += $p->price * $qty;
            }
            $order->update(['total' => $total]);
            if (isset($agents[0])) {
                OrderDelivery::create([
                    'order_id' => $order->id,
                    'agent_id' => $agents[0]->id,
                    'status' => 'delivered',
                    'delivery_fee' => 3.50,
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

    /**
     * Items en carritos: 3 productos en el primer carrito (Abrahan) para tests de checkout; 2 en el resto.
     */
    private function seedCartItems(array $commerces): void
    {
        $carts = Cart::with('profile')->get();
        $commerce = $commerces[0];
        $products = Product::where('commerce_id', $commerce->id)->where('available', true)->take(6)->get();
        if ($products->isEmpty()) {
            return;
        }
        foreach ($carts as $idx => $cart) {
            $howMany = $idx === 0 ? 3 : 2;
            $selected = $products->random(min($howMany, $products->count()));
            foreach ($selected as $product) {
                CartItem::firstOrCreate(
                    ['cart_id' => $cart->id, 'product_id' => $product->id],
                    ['quantity' => $idx === 0 ? 2 : rand(1, 2)]
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
        $zonas = self::ZONAS;
        foreach ($allProfiles as $i => $profile) {
            $zone = $zonas[$i % count($zonas)];
            for ($j = 0; $j < rand(1, 2); $j++) {
                $lat = $zone['lat'] + (rand(-15, 15) / 100000.0);
                $lng = $zone['lng'] + (rand(-15, 15) / 100000.0);
                UserLocation::create([
                    'profile_id' => $profile->id,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'address' => $zone['street'] . ', Valencia, Carabobo',
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

    /**
     * Cupones de prueba: ZONIX20 (público), BIENVENIDO (público 10%), DEMO{id} (privado user 1).
     */
    private function seedCoupons(Profile $user1Profile): void
    {
        $end = now()->addDays(60);
        Coupon::firstOrCreate(
            ['code' => 'ZONIX20'],
            [
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
            ]
        );
        Coupon::firstOrCreate(
            ['code' => 'BIENVENIDO'],
            [
                'title' => '10% bienvenida',
                'description' => 'Descuento para nuevas órdenes mayores a $12.',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'minimum_order' => 12,
                'usage_limit' => 100,
                'start_date' => now(),
                'end_date' => $end,
                'is_public' => true,
                'is_active' => true,
            ]
        );
        Coupon::firstOrCreate(
            ['code' => 'DEMO' . $user1Profile->id],
            [
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
            ]
        );
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

    /** Documentos demo (CI y RIF) para todos los perfiles de todos los roles. */
    private function seedAllProfilesDocuments(array $users): void
    {
        $allProfiles = array_merge(
            $users['users'],
            $users['commerce'],
            $users['delivery_agents'],
            $users['delivery_company'] ? [$users['delivery_company']] : [],
            $users['delivery_independent'] ? [$users['delivery_independent']] : [],
            $users['admin'] ? [$users['admin']] : []
        );

        $issued = now()->subYears(2);
        $expiresCi = now()->addYears(8);
        $expiresRif = now()->addYears(1);
        $zonas = self::ZONAS;

        foreach ($allProfiles as $i => $profile) {
            if (Document::where('profile_id', $profile->id)->exists()) {
                continue;
            }
            $base = 19000000 + $profile->id;
            $numberCi = $base % 100000000;
            $rifNum = str_pad((string) ($numberCi % 100000000), 8, '0', STR_PAD_LEFT);
            $letras = ['J', 'V', 'E', 'G', 'P'];
            $rif = $letras[$i % count($letras)] . '-' . $rifNum . '-' . ($i % 10);
            $zona = $zonas[$i % count($zonas)];
            $taxDomicile = $zona['street'] . ', Valencia, Carabobo';

            Document::create([
                'profile_id' => $profile->id,
                'type' => 'ci',
                'number_ci' => $numberCi,
                'rif_number' => null,
                'taxDomicile' => null,
                'front_image' => null,
                'issued_at' => $issued,
                'expires_at' => $expiresCi,
                'approved' => true,
                'status' => true,
            ]);

            Document::create([
                'profile_id' => $profile->id,
                'type' => 'rif',
                'number_ci' => null,
                'rif_number' => $rif,
                'taxDomicile' => $taxDomicile,
                'front_image' => null,
                'issued_at' => $issued,
                'expires_at' => $expiresRif,
                'approved' => true,
                'status' => true,
            ]);
        }
    }

    /**
     * Notificaciones de prueba para comprador (user 1) y comercio (user 6).
     * Tipos alineados con el front: order, promotion, points, support.
     * Mix Hoy/Ayer (created_at) y leídas/no leídas (read_at) para evaluar pantalla Notificaciones.
     */
    private function seedNotifications(Profile $profile): void
    {
        $now = now();
        $today = $now->copy();
        $yesterday = $now->copy()->subDay();

        // Comprador (Abrahan): notificaciones variadas para pantalla "Hoy" / "Ayer"
        $buyerItems = [
            ['title' => 'Pedido entregado', 'body' => '¡Buen provecho! Tu pedido de Burger King ha llegado a su destino.', 'type' => 'order', 'at' => $today, 'read' => true],
            ['title' => 'Promoción activa: 30% OFF', 'body' => 'Disfruta de un descuento exclusivo en restaurantes seleccionados solo por hoy.', 'type' => 'promotion', 'at' => $today, 'read' => false],
            ['title' => 'Nuevos Zonix Points', 'body' => '¡Felicidades! Has ganado 150 puntos por tu última compra. ¡Canjéalos pronto!', 'type' => 'points', 'at' => $today, 'read' => true],
            ['title' => 'Pedido confirmado', 'body' => 'Pizzería Napoli ha recibido tu pedido y ya está en preparación.', 'type' => 'order', 'at' => $yesterday, 'read' => true],
            ['title' => 'Consulta resuelta', 'body' => 'Tu solicitud de soporte #8821 ha sido finalizada con éxito.', 'type' => 'support', 'at' => $yesterday, 'read' => true],
            ['title' => 'Tu pedido está en camino', 'body' => 'Tu pedido está en camino. Llegará en unos 15 min.', 'type' => 'order', 'at' => $today->copy()->subHours(1), 'read' => false],
            ['title' => '20% en tu próxima orden', 'body' => 'Usa el código ZONIX20 en tu próximo pedido.', 'type' => 'promotion', 'at' => $yesterday, 'read' => true],
        ];
        foreach ($buyerItems as $item) {
            Notification::create([
                'profile_id' => $profile->id,
                'title' => $item['title'],
                'body' => $item['body'],
                'type' => $item['type'],
                'read_at' => $item['read'] ? $item['at'] : null,
                'data' => [],
                'created_at' => $item['at'],
            ]);
        }

        // Comercio (user 6): notificaciones de pedidos/pagos para evaluar con rol commerce
        $commerceProfile = Profile::where('user_id', 6)->first();
        if ($commerceProfile) {
            $commerceItems = [
                ['title' => 'Nuevo pedido recibido', 'body' => 'Pedido #' . (Order::max('id') ?? 1) . ' - Revisa y confirma.', 'type' => 'order', 'at' => $today],
                ['title' => 'Pago validado', 'body' => 'El pago del pedido ha sido confirmado por el cliente.', 'type' => 'order', 'at' => $today->copy()->subMinutes(30)],
                ['title' => 'Pedido en preparación', 'body' => 'Recuerda marcar como listo cuando esté preparado.', 'type' => 'order', 'at' => $yesterday],
            ];
            foreach ($commerceItems as $item) {
                Notification::create([
                    'profile_id' => $commerceProfile->id,
                    'title' => $item['title'],
                    'body' => $item['body'],
                    'type' => $item['type'],
                    'read_at' => null,
                    'data' => [],
                    'created_at' => $item['at'],
                ]);
            }
        }
    }

    /** Zonas de entrega activas para Valencia (El Socorro, Los Chorritos). */
    private function seedDeliveryZones(): void
    {
        $zones = [
            [
                'name' => 'El Socorro',
                'center_latitude' => self::ZONAS[0]['lat'],
                'center_longitude' => self::ZONAS[0]['lng'],
                'radius' => 3.5,
                'delivery_fee' => 2.00,
                'delivery_time' => 25,
                'is_active' => true,
                'description' => 'Zona El Socorro y alrededores, Valencia.',
            ],
            [
                'name' => 'Los Chorritos',
                'center_latitude' => self::ZONAS[1]['lat'],
                'center_longitude' => self::ZONAS[1]['lng'],
                'radius' => 4.0,
                'delivery_fee' => 2.50,
                'delivery_time' => 30,
                'is_active' => true,
                'description' => 'Zona Los Chorritos y sectores cercanos, Valencia.',
            ],
        ];
        foreach ($zones as $z) {
            DeliveryZone::firstOrCreate(
                ['name' => $z['name']],
                $z
            );
        }
    }

    /** Mensajes de chat en órdenes entregadas (cliente, restaurante, repartidor). */
    private function seedChatMessages(): void
    {
        $orders = Order::with(['profile', 'commerce', 'orderDelivery.agent'])
            ->where('status', 'delivered')
            ->orderBy('id')
            ->take(2)
            ->get();

        foreach ($orders as $order) {
            $buyerProfileId = $order->profile_id;
            $commerceProfileId = $order->commerce?->profile_id;
            $od = $order->orderDelivery;
            $deliveryProfileId = $od && $od->agent ? $od->agent->profile_id : null;

            if (!$commerceProfileId) {
                continue;
            }

            $baseTime = $order->created_at ?? now()->subDay();

            $messages = [];
            $messages[] = ['sender_id' => $buyerProfileId, 'sender_type' => 'customer', 'recipient_type' => 'all', 'content' => 'Hola, ¿a qué hora aproximada llega el pedido?', 'at' => $baseTime->copy()->addMinutes(5)];
            if ($deliveryProfileId) {
                $messages[] = ['sender_id' => $deliveryProfileId, 'sender_type' => 'delivery_agent', 'recipient_type' => 'all', 'content' => 'En unos 15-20 minutos estaré llegando.', 'at' => $baseTime->copy()->addMinutes(8)];
            }
            $messages[] = ['sender_id' => $commerceProfileId, 'sender_type' => 'restaurant', 'recipient_type' => 'all', 'content' => 'Tu pedido ya salió del local. Cualquier cosa nos avisas.', 'at' => $baseTime->copy()->addMinutes(10)];
            $messages[] = ['sender_id' => $buyerProfileId, 'sender_type' => 'customer', 'recipient_type' => 'all', 'content' => 'Perfecto, gracias.', 'at' => $baseTime->copy()->addMinutes(12)];

            foreach ($messages as $m) {
                ChatMessage::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'sender_id' => $m['sender_id'],
                        'content' => $m['content'],
                    ],
                    [
                        'order_id' => $order->id,
                        'sender_id' => $m['sender_id'],
                        'sender_type' => $m['sender_type'],
                        'recipient_type' => $m['recipient_type'],
                        'content' => $m['content'],
                        'type' => 'text',
                        'read_at' => $m['at']->copy()->addMinutes(1),
                        'created_at' => $m['at'],
                        'updated_at' => $m['at'],
                    ]
                );
            }
        }
    }

    private function fixDemoOrderTracking(array $orders): void
    {
        $elSocorro = self::ZONAS[0];
        $santaRosa = self::ZONAS[5];
        foreach ($orders as $order) {
            if ($order->delivery_type !== 'delivery') {
                continue;
            }
            $order->update([
                'delivery_latitude' => $elSocorro['lat'],
                'delivery_longitude' => $elSocorro['lng'],
            ]);
            $od = OrderDelivery::where('order_id', $order->id)->first();
            if ($od && $od->agent) {
                $od->agent->update([
                    'current_latitude' => $santaRosa['lat'],
                    'current_longitude' => $santaRosa['lng'],
                    'last_location_update' => now(),
                ]);
            }
            $addr = Address::where('profile_id', $order->profile_id)->where('is_default', true)->first();
            if ($addr) {
                $addr->update([
                    'street' => $elSocorro['street'],
                    'latitude' => $elSocorro['lat'],
                    'longitude' => $elSocorro['lng'],
                ]);
            }
        }
    }
}
