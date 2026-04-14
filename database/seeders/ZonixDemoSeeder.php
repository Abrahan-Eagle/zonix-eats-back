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
use App\Models\CommerceInvoice;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\DeliveryPayment;
use App\Models\DeliverySetting;
use App\Models\DeliveryZone;
use App\Models\Dispute;
use App\Models\Document;
use App\Models\Notification;
use App\Models\OperatorCode;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\OrderPayment;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder único para demo Zonix Eats — simula el marketplace completo entre roles (misma BD que usarán las apps).
 *
 * Objetivo: tras `migrate:fresh --seed` (o `db:seed --class=ZonixDemoSeeder`), cada rol tiene datos coherentes
 * y relaciones cruzadas (comprador ↔ comercio ↔ reparto empresa ↔ reparto independiente ↔ admin).
 *
 * Geolocalización: Venezuela, Carabobo, Valencia. Sectores: El Socorro, Los Chorritos, Mayorista, Bella Florida,
 * San Diego, Santa Rosa.
 *
 * --- Tras el seed completo: cuentas "día 1" para E2E ---
 * Al final se ejecuta cleanDemoForFlowTesting(): elimina órdenes y notificaciones del perfil de user 1 (buyer principal)
 * y vacía su carrito. El marketplace sigue activo (buyers 2–5, otros agentes, comercios). Jarvis (user 17) queda sin
 * OrderDelivery en demo porque solo estaba asignado a órdenes de ese comprador.
 *
 * --- Simulación con 4 usuarios reales (Google) ---
 * Los ids 1, 6, 16 y 17 son usuarios reales para pruebas: login con Google, password null, datos y foto reales.
 * El resto son usuarios demo (@demo.zonix.eats) con password común "password".
 *
 * Rol                | user_id | Email / acceso                              | Acceso
 * ------------------|---------|-----------------------------------------------|----------------------
 * Buyer (principal)  | 1       | ing.pulido.abrahan@gmail.com                 | Google (Abrahan)
 * Buyers demo        | 2–5     | maria.gonzalez@… / carlos… @demo.zonix.eats  | password
 * Commerce (principal)| 6      | wistremiropulido@gmail.com                   | Google (Wistremiro)
 *
 * Commerce demo      | 7–15   | comercio*@demo.zonix.eats                   | password
 * Delivery company   | 16     | towdah.yadah@gmail.com                       | Google (TOWDAH YADAH)
 * Delivery agent     | 17     | jarvispulido1@gmail.com                      | Google (Jarvis)
 * Delivery agent     | 18     | repartidor2@demo.zonix.eats                 | password
 * Delivery independ. | 19     | delivery.independent@demo.zonix.eats        | password
 * Admin              | 20     | jarvispulido5@gmail.com                      | Google (Jarvis Pulido5)
 *
 * Usuarios reales (no cambiar ids): 1 Abrahan, 6 Wistremiro, 16 TOWDAH YADAH, 17 Jarvis, 20 Jarvis Pulido5 (Admin). Resto: demo.
 *
 * --- Repartidores y órdenes (array $agents tras seedDelivery: 10 con empresa + 1 independiente al final) ---
 * - agents[0]: Jarvis (user 17), agents[1]: Pedro (user 18); agents[2..9]: repartidores demo misma empresa.
 * - agents[10]: Miguel independiente (company_id null) — no confundir con índice [2].
 * Órdenes: el seed genera filas para el comprador principal y luego cleanDemoForFlowTesting() las elimina (E2E “día 1”).
 * Otros buyers y agentes conservan órdenes demo: shipped con/sin agente (Disponibles + Asignar empresa), Pedro, más agentes, independiente.
 *
 * --- Grafo de relaciones (probar cada app con estos vínculos) ---
 * - Buyer → Order → Commerce (Wistremiro = commerces[0]) → Products / PaymentMethods / Posts.
 * - Order (shipped sin agente) → delivery_agent/delivery ven "Disponibles"; empresa delivery_company asigna en pestaña Asignar; al aceptar/asignar → OrderDelivery.
 * - OrderDelivery → DeliveryAgent → (DeliveryCompany vía company_id | null = independiente).
 * - Delivery company (user 16): perfil enlazado a DeliveryCompany; API usa primer agente de la empresa para /me
 *   y lista órdenes de todos los agentes de la empresa — por eso deben existir agentes 17 y 18 en seed.
 * - Review: buyer → Commerce y buyer → DeliveryAgent en órdenes delivered.
 * - Dispute: buyer → commerce (orden delivered/cancelled).
 * - DeliveryPayment: por OrderDelivery (pago al motorizado).
 * - ChatMessage: órdenes delivered (customer + restaurant + delivery_agent).
 * - Admin: perfil + documentos; gestión vía APIs admin (usuarios, disputas, etc.).
 * - Notificaciones: perfiles buyer 1, commerce 6, cada DeliveryAgent, delivery_company 16.
 *
 * Tablas tocadas (resumen): profiles, phones, addresses, commerces, products, orders, order_items, order_deliveries,
 * carts, notifications, reviews, disputes, chat_messages, delivery_zones, coupons, posts, commerce_invoices, etc.
 *
 * --- Tablas y campos requeridos por rol (migraciones) ---
 * User 16 (delivery_company): users (id, email, role, google_id, completed_onboarding), profiles (user_id, firstName,
 * lastName, status, photo_users, maritalStatus, sex), phones (profile_id, context personal + opcional delivery_company),
 * addresses (profile_id, city_id, street, latitude, longitude, is_default), delivery_companies (profile_id, name,
 * tax_id, address, active, image, open, schedule), documents (profile_id, type ci/rif). La empresa debe tener al menos
 * un delivery_agent (company_id) para que /api/delivery/me resuelva agente.
 * Delivery agent (17, 18): users, profiles, phones (personal), addresses, delivery_agents (company_id, profile_id,
 * status, working, rating, vehicle_type, license_number, current_latitude, current_longitude, last_location_update),
 * documents. Repartidor independiente (19): igual pero delivery_agents.company_id = null.
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
        $this->seedDeliverySettings();
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
        $this->seedDeliveryCompanyPaymentMethods();
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

        $this->cleanDemoForFlowTesting();

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

        // OperatorCode lo puebla OperatorCodeSeeder (code numérico 412, 414, 424, 416, 426).
        // Fallback defensivo para evitar fallos si se ejecuta el seeder aislado.
        $defaultOperator = OperatorCode::whereIn('code', [412, '412', '0412'])->first();
        if (! $defaultOperator) {
            $defaultOperator = OperatorCode::create([
                'name' => '0412',
                'code' => 412,
            ]);
        }
        $this->operatorCodeId = (int) $defaultOperator->id;

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

    private function seedDeliverySettings(): void
    {
        DeliverySetting::updateOrCreate(
            ['id' => 1],
            [
                'base_cost' => 1.50,
                'cost_per_km' => 0.50,
                'free_km' => 0.00,
                'fee_min' => 2.00,
                'fee_max' => 15.00,
            ]
        );
    }

    private function seedUsersAndProfiles(): array
    {
        $password = Hash::make('password');
        $out = ['users' => [], 'commerce' => [], 'delivery_company' => null, 'delivery_agents' => [], 'delivery_independent' => null, 'admin' => null];

        // 1. Abrahan — comprador (usuario real Google; mismo patrón que 6, 16, 17: password null)
        $u1 = User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Abrahan Pulido',
                'email' => 'ing.pulido.abrahan@gmail.com',
                'email_verified_at' => null,
                'password' => null,
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
            $u = User::updateOrCreate(
                ['email' => $b['email']],
                [
                    'name' => $b['name'], 'email_verified_at' => now(), 'password' => $password,
                    'given_name' => $b['first'], 'family_name' => $b['last'], 'completed_onboarding' => true, 'role' => 'users', 'light' => '1',
                ]
            );
            $p = Profile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'firstName' => $b['first'], 'lastName' => $b['last'], 'status' => 'completeData',
                    'photo_users' => 'https://ui-avatars.com/api/?name='.urlencode($b['first'].'+'.$b['last']).'&size=200&background=random',
                    'maritalStatus' => 'single', 'sex' => $i % 2 === 0 ? 'F' : 'M', 'date_of_birth' => '1992-05-10',
                ]
            );
            $this->ensurePhone($p->id, $buyerPhones[$i][0], $buyerPhones[$i][1]); // 0416/0414 + 7 dígitos
            $out['users'][] = $p;
        }

        // 6. Wistremiro — comercio (usuario real Google)
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
            $email = 'comercio'.($i + 1).'@demo.zonix.eats';
            $u = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $commerceNames[$i].' (Dueño)',
                    'email_verified_at' => now(),
                    'password' => $password,
                    'completed_onboarding' => true,
                    'role' => 'commerce',
                    'light' => '1',
                ]
            );
            $p = Profile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'firstName' => 'Dueño',
                    'lastName' => 'Comercio '.($i + 1),
                    'status' => 'completeData',
                    'photo_users' => self::DEFAULT_AVATAR,
                    'maritalStatus' => 'single',
                    'sex' => 'M',
                ]
            );
            $this->ensurePhone($p->id, (string) (5012345 + $i), 1);
            $out['commerce'][] = $p;
        }

        // 16. Empresa delivery — TOWDAH YADAH (usuario real Google)
        $u16 = User::updateOrCreate(
            ['id' => 16],
            [
                'name' => 'TOWDAH YADAH',
                'email' => 'towdah.yadah@gmail.com',
                'email_verified_at' => null,
                'password' => null,
                'google_id' => '102585538744854928843',
                'given_name' => 'TOWDAH',
                'family_name' => 'YADAH',
                'profile_pic' => 'https://lh3.googleusercontent.com/a/ACg8ocLQWQonRPYna_OTsFql1mhypE7Jb_5kr5T_CjMGuyN7Qay5Iz0=s96-c',
                'completed_onboarding' => true,
                'role' => 'delivery_company',
                'light' => '1',
            ]
        );
        $p = Profile::updateOrCreate(
            ['user_id' => $u16->id],
            [
                'firstName' => 'TOWDAH', 'lastName' => 'YADAH', 'status' => 'completeData',
                'photo_users' => $u16->profile_pic, 'maritalStatus' => 'single', 'sex' => 'M',
            ]
        );
        $this->ensurePhone($p->id, '9123456', 2);
        $out['delivery_company'] = $p;

        // 17. Repartidor empresa — Jarvis (usuario real Google; mismo id que órdenes en demo)
        $jarvisProfilePic = 'https://lh3.googleusercontent.com/a/ACg8ocJHPs6q_0F17y1oo6e4qeYmaS6-xSajvyyKuV0cArGyv4ga7Q=s96-c';
        $u17 = User::updateOrCreate(
            ['id' => 17],
            [
                'name' => 'Jarvis Pulido1',
                'email' => 'jarvispulido1@gmail.com',
                'email_verified_at' => null,
                'password' => null,
                'google_id' => '106793640636932855620',
                'given_name' => 'Jarvis',
                'family_name' => 'Pulido1',
                'profile_pic' => $jarvisProfilePic,
                'completed_onboarding' => true,
                'role' => 'delivery_agent',
                'light' => '1',
            ]
        );
        $p = Profile::updateOrCreate(
            ['user_id' => $u17->id],
            [
                'firstName' => 'Jarvis',
                'lastName' => 'Pulido1',
                'status' => 'completeData',
                'photo_users' => $jarvisProfilePic,
                'maritalStatus' => 'single',
                'sex' => 'M',
            ]
        );
        $this->ensurePhone($p->id, '6161000', 4); // 0416 6161000
        $out['delivery_agents'][] = $p;

        // 18. Segundo repartidor de la empresa (Pedro)
        $u18 = User::updateOrCreate(
            ['id' => 18],
            [
                'name' => 'Pedro Motorizado',
                'email' => 'repartidor2@demo.zonix.eats',
                'email_verified_at' => now(),
                'password' => $password,
                'completed_onboarding' => true,
                'role' => 'delivery_agent',
                'light' => '1',
            ]
        );
        $p = Profile::updateOrCreate(
            ['user_id' => $u18->id],
            [
                'firstName' => 'Pedro',
                'lastName' => 'Motorizado',
                'status' => 'completeData',
                'photo_users' => 'https://ui-avatars.com/api/?name='.urlencode('Pedro+Motorizado').'&size=200&background=random',
                'maritalStatus' => 'single',
                'sex' => 'M',
            ]
        );
        $this->ensurePhone($p->id, '6161001', 4);
        $out['delivery_agents'][] = $p;

        // Agentes demo adicionales (ids auto-generados)
        $demoAgents = [
            ['Carlos', 'Ramírez', 'M'], ['Ana', 'Torres', 'F'], ['Luis', 'Mendoza', 'M'],
            ['María', 'González', 'F'], ['Diego', 'Herrera', 'M'], ['Sofía', 'Castro', 'F'],
            ['Andrés', 'Rojas', 'M'], ['Valentina', 'López', 'F'],
        ];
        foreach ($demoAgents as $idx => $da) {
            $email = strtolower($da[0]).'.'.strtolower($da[1]).'@demo.zonix.eats';
            $u = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => "{$da[0]} {$da[1]}",
                    'email_verified_at' => now(),
                    'password' => $password,
                    'completed_onboarding' => true,
                    'role' => 'delivery_agent',
                    'light' => '1',
                ]
            );
            $avatarUrl = 'https://ui-avatars.com/api/?name='.urlencode("{$da[0]}+{$da[1]}").'&size=200&background=random';
            $p = Profile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'firstName' => $da[0],
                    'lastName' => $da[1],
                    'status' => 'completeData',
                    'photo_users' => $avatarUrl,
                    'maritalStatus' => 'single',
                    'sex' => $da[2],
                ]
            );
            $this->ensurePhone($p->id, '616'.str_pad((string) ($idx + 10), 4, '0', STR_PAD_LEFT), 4);
            $out['delivery_agents'][] = $p;
        }

        // 19. Repartidor independiente
        $u = User::updateOrCreate(
            ['email' => 'delivery.independent@demo.zonix.eats'],
            [
                'name' => 'Miguel Independiente',
                'email_verified_at' => now(),
                'password' => $password,
                'completed_onboarding' => true,
                'role' => 'delivery',
                'light' => '1',
            ]
        );
        $p = Profile::updateOrCreate(
            ['user_id' => $u->id],
            [
                'firstName' => 'Miguel', 'lastName' => 'Independiente', 'status' => 'completeData',
                'photo_users' => self::DEFAULT_AVATAR, 'maritalStatus' => 'single', 'sex' => 'M',
            ]
        );
        $this->ensurePhone($p->id, '2612345', 4); // 0416 2612345
        $out['delivery_independent'] = $p;

        // 20. Admin — Jarvis Pulido5 (usuario real Google)
        $adminPic = 'https://lh3.googleusercontent.com/a/ACg8ocJo-XHm-39I7M___37sfYO4hznJcooBYmTl4cwfIokgXWTW=s96-c';
        $u = User::updateOrCreate(
            ['id' => 20],
            [
                'name' => 'Admin Zonix',
                'email' => 'jarvispulido5@gmail.com',
                'email_verified_at' => now(),
                'password' => null,
                'google_id' => '106491522856845756064',
                'given_name' => 'Jarvis',
                'family_name' => 'Pulido5',
                'profile_pic' => $adminPic,
                'completed_onboarding' => true,
                'role' => 'admin',
                'light' => '1',
            ]
        );
        $p = Profile::updateOrCreate(
            ['user_id' => $u->id],
            [
                'firstName' => 'Jarvis',
                'lastName' => 'Pulido5',
                'status' => 'completeData',
                'photo_users' => $adminPic,
                'maritalStatus' => 'single',
                'sex' => 'M',
            ]
        );
        $this->ensurePhone($p->id, '4140000', 2); // 0414 4140000
        $out['admin'] = $p;

        return $out;
    }

    /**
     * Teléfono en dos partes: (1) código de operador desde OperatorCodeSeeder (operator_codes),
     * (2) 7 dígitos en phones.number. Ejemplos: 0412 4352014 o 0416 1234567.
     *
     * @param  int  $profileId  Perfil al que se asocia el teléfono
     * @param  string  $number  Solo 7 dígitos (sin el prefijo 0412/0414/0416)
     * @param  int|null  $operatorCodeId  ID en operator_codes (1=0412, 2=0414, 4=0416). Null = usar el por defecto
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

    /**
     * Teléfono de contacto de la empresa de delivery (context=delivery_company, delivery_company_id).
     * Requerido para APIs que listan teléfonos por contexto. El perfil ya tiene un teléfono personal en ensurePhone.
     */
    private function ensureDeliveryCompanyPhone(DeliveryCompany $company, string $number = '9123457', ?int $operatorCodeId = null): void
    {
        if (Phone::where('profile_id', $company->profile_id)
            ->where('context', Phone::CONTEXT_DELIVERY_COMPANY)
            ->where('delivery_company_id', $company->id)
            ->exists()) {
            return;
        }
        $number = str_pad(substr(preg_replace('/\D/', '', $number), 0, 7), 7, '0', STR_PAD_LEFT);
        Phone::create([
            'profile_id' => $company->profile_id,
            'context' => Phone::CONTEXT_DELIVERY_COMPANY,
            'delivery_company_id' => $company->id,
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
            if (! $profile) {
                continue;
            }
            UserLocation::firstOrCreate(
                [
                    'profile_id' => $profile->id,
                    'latitude' => $elSocorro['lat'],
                    'longitude' => $elSocorro['lng'],
                ],
                [
                    'address' => $elSocorro['street'].', Valencia, Carabobo',
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
                'address' => $zone['street'].', Valencia, Carabobo',
                'image' => self::COMMERCE_IMAGES[$i % count(self::COMMERCE_IMAGES)],
                'open' => true,
                'tax_id' => 'J-'.(30000000 + $i),
                'preparation_time' => $i === 0 ? 15 : rand(10, 25),
                'status' => 'approved',
            ]);
            $commerces[] = $commerce;
            Address::create([
                'commerce_id' => $commerce->id,
                'profile_id' => null,
                'role' => 'commerce',
                'city_id' => $this->cityValenciaId,
                'street' => $zone['street'].' - Local '.($i + 1),
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
                    'name' => $names[$j % count($names)].' '.($j + 1),
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
        $schedule = [
            'monday' => ['open' => '08:00', 'close' => '20:00'],
            'tuesday' => ['open' => '08:00', 'close' => '20:00'],
            'wednesday' => ['open' => '08:00', 'close' => '20:00'],
            'thursday' => ['open' => '08:00', 'close' => '20:00'],
            'friday' => ['open' => '08:00', 'close' => '21:00'],
            'saturday' => ['open' => '09:00', 'close' => '18:00'],
            'sunday' => ['open' => '09:00', 'close' => '14:00'],
        ];
        $company = DeliveryCompany::create([
            'profile_id' => $profileCompany->id,
            'name' => 'Envíos Carabobo C.A.',
            'tax_id' => 'J-12345678',
            'address' => $mayorista['street'].', Valencia, Carabobo',
            'image' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?w=400',
            'active' => true,
            'open' => true,
            'schedule' => $schedule,
            'status' => 'approved',
        ]);
        $this->ensureDeliveryCompanyPhone($company, '9123457', 2);

        // Sede de la empresa: fijar dirección del perfil en Mayorista (para mapa)
        Address::updateOrCreate(
            ['profile_id' => $profileCompany->id, 'is_default' => true],
            [
                'street' => $mayorista['street'],
                'house_number' => '1',
                'latitude' => $mayorista['lat'],
                'longitude' => $mayorista['lng'],
                'status' => 'completeData',
                'city_id' => $this->cityValenciaId,
            ]
        );

        // 10 agentes dispersos en zonas de Valencia para mapa realista
        $allZones = self::ZONAS;
        $vehicleTypes = ['motorcycle', 'bicycle', 'motorcycle', 'car', 'motorcycle', 'bicycle', 'motorcycle', 'motorcycle', 'bicycle', 'motorcycle'];
        $ratings = [4.7, 4.3, 4.5, 4.0, 4.8, 3.9, 4.6, 4.1, 4.4, 4.2];
        $working = [true, true, true, false, true, true, false, true, true, true];
        $statuses = ['activo', 'activo', 'activo', 'activo', 'activo', 'activo', 'inactivo', 'activo', 'activo', 'activo'];
        $agents = [];
        foreach ($users['delivery_agents'] as $i => $profile) {
            $zone = $allZones[$i % count($allZones)];
            $isWorking = $working[$i] ?? true;
            $isActive = ($statuses[$i] ?? 'activo') === 'activo';
            $lastUpdate = match (true) {
                ! $isActive => now()->subHours(rand(2, 12)),
                ! $isWorking => now()->subHours(rand(1, 6)),
                $i <= 4 => now()->subMinutes(rand(1, 5)),
                default => now()->subMinutes(rand(5, 20)),
            };
            $agents[] = DeliveryAgent::create([
                'company_id' => $company->id,
                'profile_id' => $profile->id,
                'status' => $statuses[$i] ?? 'activo',
                'working' => $isWorking,
                'rating' => $ratings[$i] ?? 4.0,
                'vehicle_type' => $vehicleTypes[$i] ?? 'motorcycle',
                'license_number' => 'LIC-'.str_pad((string) $profile->id, 5, '0', STR_PAD_LEFT),
                'current_latitude' => $zone['lat'] + (rand(-80, 80) / 100000.0),
                'current_longitude' => $zone['lng'] + (rand(-80, 80) / 100000.0),
                'last_location_update' => $lastUpdate,
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
            'current_latitude' => $elSocorro['lat'] + (rand(-30, 30) / 100000.0),
            'current_longitude' => $elSocorro['lng'] + (rand(-30, 30) / 100000.0),
            'last_location_update' => now()->subMinutes(rand(1, 15)),
        ]);

        return [$company, $agents];
    }

    /**
     * Ordenes realistas: multiples compradores, multiples comercios, delivery y pickup,
     * fees variados, comisiones; shipped con/sin agente (sin agente = Disponibles + Asignar empresa).
     */
    private function seedOrders(array $users, array $commerces, array $agents): array
    {
        $zonas = self::ZONAS;
        $paymentMethods = ['cash', 'mobile_payment', 'bank_transfer', 'mobile_payment', 'cash'];
        $deliveryCompanyId = DeliveryCompany::where('active', true)->first()?->id;
        $created = [];

        $orderConfigs = [
            // Buyer 0 (Abrahan) — cubrir todos los estados
            ['buyer' => 0, 'commerce' => 0, 'status' => 'pending_payment', 'type' => 'delivery', 'zone' => 0, 'fee' => 2.50, 'ago' => 'now'],
            ['buyer' => 0, 'commerce' => 1, 'status' => 'paid',            'type' => 'delivery', 'zone' => 1, 'fee' => 3.00, 'ago' => '2h'],
            ['buyer' => 0, 'commerce' => 2, 'status' => 'shipped',           'type' => 'delivery', 'zone' => 0, 'fee' => 2.00, 'ago' => '1h'],
            ['buyer' => 0, 'commerce' => 0, 'status' => 'shipped',         'type' => 'delivery', 'zone' => 0, 'fee' => 3.50, 'ago' => '30m', 'agent' => 0],
            ['buyer' => 0, 'commerce' => 3, 'status' => 'delivered',        'type' => 'delivery', 'zone' => 3, 'fee' => 4.00, 'ago' => '1d',  'agent' => 0],
            ['buyer' => 0, 'commerce' => 0, 'status' => 'delivered',        'type' => 'pickup',   'zone' => 0, 'fee' => 0,    'ago' => '2d'],
            ['buyer' => 0, 'commerce' => 4, 'status' => 'cancelled',        'type' => 'delivery', 'zone' => 4, 'fee' => 5.00, 'ago' => '1d'],
            // Buyer 1 (Maria) — ordenes entregadas para historial
            ['buyer' => 1, 'commerce' => 0, 'status' => 'delivered',        'type' => 'delivery', 'zone' => 1, 'fee' => 2.50, 'ago' => '3d',  'agent' => 1],
            // Buyer 2-3 — ordenes shipped con agentes nuevos (rutas visibles en mapa)
            ['buyer' => 2, 'commerce' => 1, 'status' => 'shipped',         'type' => 'delivery', 'zone' => 5, 'fee' => 3.00, 'ago' => '20m', 'agent' => 2],
            ['buyer' => 3, 'commerce' => 2, 'status' => 'shipped',         'type' => 'delivery', 'zone' => 3, 'fee' => 4.50, 'ago' => '15m', 'agent' => 4],
            // Shipped sin agente — Disponibles (repartidor) + Asignar (empresa); coincide con pending de delivery-company API
            ['buyer' => 2, 'commerce' => 3, 'status' => 'shipped',           'type' => 'delivery', 'zone' => 2, 'fee' => 3.00, 'ago' => '10m'],
            ['buyer' => 3, 'commerce' => 5, 'status' => 'shipped',           'type' => 'delivery', 'zone' => 4, 'fee' => 4.00, 'ago' => '5m'],
        ];

        foreach ($orderConfigs as $i => $cfg) {
            $buyerProfile = $users['users'][$cfg['buyer']] ?? $users['users'][0];
            $commerce = $commerces[$cfg['commerce']] ?? $commerces[0];
            $products = Product::where('commerce_id', $commerce->id)->where('available', true)->get();
            if ($products->isEmpty()) {
                continue;
            }

            $zone = $zonas[$cfg['zone']];
            $isDelivery = $cfg['type'] === 'delivery';
            $deliveryFee = $isDelivery ? $cfg['fee'] : 0;
            $isPaidOrBeyond = in_array($cfg['status'], ['paid', 'processing', 'shipped', 'delivered']);
            $hasAgent = isset($cfg['agent']) && isset($agents[$cfg['agent']]);
            $createdAt = match ($cfg['ago']) {
                'now' => now(),
                '30m' => now()->subMinutes(30),
                '20m' => now()->subMinutes(20),
                '15m' => now()->subMinutes(15),
                '10m' => now()->subMinutes(10),
                '5m' => now()->subMinutes(5),
                '1h' => now()->subHours(1),
                '2h' => now()->subHours(2),
                '1d' => now()->subDay(),
                '2d' => now()->subDays(2),
                '3d' => now()->subDays(3),
                default => now(),
            };

            $buyerAddr = Address::where('profile_id', $buyerProfile->id)->where('is_default', true)->first();
            $delLat = $isDelivery ? ($buyerAddr->latitude ?? $zone['lat']) : null;
            $delLng = $isDelivery ? ($buyerAddr->longitude ?? $zone['lng']) : null;
            $delAddress = $isDelivery ? ($buyerAddr->street ?? $zone['street']).', Valencia, Carabobo' : null;

            $pickupToken = in_array($cfg['status'], ['shipped', 'delivered']) ? substr(hash_hmac('sha256', "order:seed:$i", config('app.key')), 0, 16) : null;
            $deliveryToken = $cfg['status'] === 'delivered' ? substr(hash_hmac('sha256', "order:seed:dt:$i", config('app.key')), 0, 16) : null;
            $payMethod = $isPaidOrBeyond ? $paymentMethods[$i % count($paymentMethods)] : null;

            $order = Order::create([
                'profile_id' => $buyerProfile->id,
                'commerce_id' => $commerce->id,
                'delivery_company_id' => $isDelivery ? $deliveryCompanyId : null,
                'delivery_type' => $cfg['type'],
                'status' => $cfg['status'],
                'approved_for_payment' => $isPaidOrBeyond,
                'total' => 0,
                'delivery_fee' => $deliveryFee,
                'delivery_payment_amount' => in_array($cfg['status'], ['shipped', 'delivered']) ? $deliveryFee : null,
                'commission_amount' => 0,
                'cancellation_penalty' => 0,
                'cancelled_by' => $cfg['status'] === 'cancelled' ? 'user_id' : null,
                'estimated_delivery_time' => $isDelivery ? rand(15, 40) : null,
                'payment_method' => $payMethod,
                'reference_number' => $isPaidOrBeyond ? 'REF'.(10000 + $i) : null,
                'payment_validated_at' => $isPaidOrBeyond ? $createdAt : null,
                'delivery_address' => $delAddress,
                'delivery_latitude' => $delLat,
                'delivery_longitude' => $delLng,
                'cancellation_reason' => $cfg['status'] === 'cancelled' ? 'Solicitud del cliente' : null,
                'agent_accepted_at' => $hasAgent ? $createdAt : null,
                'pickup_token' => $pickupToken,
                'delivery_token' => $deliveryToken,
                'created_at' => $createdAt,
            ]);

            $total = 0;
            $selected = $products->random(min(rand(2, 4), $products->count()));
            foreach ($selected as $p) {
                $qty = rand(1, 3);
                OrderItem::create(['order_id' => $order->id, 'product_id' => $p->id, 'quantity' => $qty, 'unit_price' => $p->price]);
                $total += $p->price * $qty;
            }
            $commission = round($total * 0.05, 2);
            $order->update(['total' => $total + $deliveryFee, 'commission_amount' => $commission]);

            OrderPayment::create([
                'order_id' => $order->id, 'type' => 'food', 'amount' => max(0, $total),
                'payee_type' => 'commerce', 'payee_id' => $commerce->id,
                'payment_method_label' => $payMethod, 'reference_number' => $isPaidOrBeyond ? 'REF'.(10000 + $i) : null,
                'payment_proof' => $isPaidOrBeyond ? 'payment_proofs/demo_food.jpg' : null,
                'payment_proof_uploaded_at' => $isPaidOrBeyond ? now() : null,
                'validated_at' => $isPaidOrBeyond ? now() : null,
            ]);
            if ($isDelivery && $deliveryFee > 0 && $deliveryCompanyId) {
                OrderPayment::create([
                    'order_id' => $order->id, 'type' => 'delivery', 'amount' => $deliveryFee,
                    'payee_type' => 'delivery_company', 'payee_id' => $deliveryCompanyId,
                    'payment_method_label' => $payMethod, 'reference_number' => $isPaidOrBeyond ? 'REF-D'.(10000 + $i) : null,
                    'payment_proof' => $isPaidOrBeyond ? 'payment_proofs/demo_delivery.jpg' : null,
                    'payment_proof_uploaded_at' => $isPaidOrBeyond ? now() : null,
                    'validated_at' => $isPaidOrBeyond ? now() : null,
                ]);
            }
            if ($hasAgent && in_array($cfg['status'], ['shipped', 'delivered'])) {
                OrderDelivery::create([
                    'order_id' => $order->id,
                    'agent_id' => $agents[$cfg['agent']]->id,
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
                    [
                        'cart_id' => $cart->id,
                        // Seed estable por carrito-producto para evitar colisiones del unique(cart_id, line_id)
                        'line_id' => 'seed-'.$cart->id.'-'.$product->id,
                    ],
                    [
                        'product_id' => $product->id,
                        'quantity' => $idx === 0 ? 2 : rand(1, 2),
                    ]
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
                    'address' => $zone['street'].', Valencia, Carabobo',
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
            ['code' => 'DEMO'.$user1Profile->id],
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
        $commerceComments = [
            5 => 'Excelente comida, la mejor de Valencia.',
            4 => 'Buena comida, pedido correcto.',
            3 => 'Regular, la comida llegó tibia.',
            5 => '10/10, sabor y presentación impecables.',
            4 => 'Buena relación calidad-precio.',
        ];
        $deliveryComments = [
            5 => 'Llegó súper rápido, muy amable.',
            4 => 'Buen servicio, puntual.',
            3 => 'Tardó un poco pero todo correcto.',
            5 => 'Excelente motorizado, muy profesional.',
            4 => 'Sin problemas, entrega rápida.',
        ];

        $orders = Order::where('status', 'delivered')->get();
        $cRatings = array_keys($commerceComments);
        $dRatings = array_keys($deliveryComments);
        foreach ($orders as $i => $order) {
            $cRating = $cRatings[$i % count($cRatings)];
            Review::firstOrCreate(
                ['profile_id' => $order->profile_id, 'order_id' => $order->id, 'reviewable_type' => Commerce::class, 'reviewable_id' => $order->commerce_id],
                ['rating' => $cRating, 'comment' => $commerceComments[$cRating]]
            );
            $od = $order->orderDelivery;
            if ($od && $od->agent) {
                $dRating = $dRatings[$i % count($dRatings)];
                Review::firstOrCreate(
                    ['profile_id' => $order->profile_id, 'order_id' => $order->id, 'reviewable_type' => DeliveryAgent::class, 'reviewable_id' => $od->agent->id],
                    ['rating' => $dRating, 'comment' => $deliveryComments[$dRating]]
                );
            }
        }
    }

    private function seedDisputes(): void
    {
        $disputes = [
            ['type' => 'quality_issue',    'desc' => 'Producto llegó frío y en mal estado.',              'status' => 'pending'],
            ['type' => 'delivery_problem', 'desc' => 'Faltaron 2 items del pedido, bolsa incompleta.',    'status' => 'in_review'],
            ['type' => 'payment_issue',    'desc' => 'Me cobraron de más en el delivery fee.',            'status' => 'resolved'],
            ['type' => 'other',            'desc' => 'Me entregaron un pedido que no era el mío.',        'status' => 'pending'],
        ];
        $orders = Order::whereIn('status', ['delivered', 'cancelled'])->with('commerce')->take(4)->get();
        foreach ($orders as $i => $order) {
            if (! $order->profile || ! $order->commerce || $i >= count($disputes)) {
                continue;
            }
            $d = $disputes[$i];
            Dispute::firstOrCreate(
                ['order_id' => $order->id, 'reported_by_type' => Profile::class, 'reported_by_id' => $order->profile_id],
                [
                    'reported_against_type' => Commerce::class,
                    'reported_against_id' => $order->commerce_id,
                    'type' => $d['type'],
                    'description' => $d['desc'],
                    'status' => $d['status'],
                    'admin_notes' => $d['status'] === 'resolved' ? 'Reembolso procesado al cliente.' : null,
                ]
            );
        }
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
                    'name' => 'Promo del día '.($i + 1),
                    'description' => 'Oferta especial de '.$commerce->business_name,
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

    /** Métodos de pago demo para la delivery company (para que el buyer vea datos al pagar envío). */
    private function seedDeliveryCompanyPaymentMethods(): void
    {
        $company = DeliveryCompany::where('active', true)->first();
        if (! $company) {
            return;
        }
        $company->paymentMethods()->delete();
        $banesco = Bank::where('name', 'like', '%Banesco%')->first();
        $mercantil = Bank::where('name', 'like', '%Mercantil%')->first();
        $demoMethods = [
            [
                'type' => 'mobile_payment', 'phone' => '04149876543', 'owner_name' => 'Envíos Carabobo C.A.', 'owner_id' => 'J-40987654-3',
                'bank_id' => $banesco?->id, 'is_default' => true, 'is_active' => true,
                'reference_info' => ['alias' => 'Pago móvil - Empresa', 'bank' => $banesco?->name ?? 'Banesco', 'currency' => 'VES', 'number_ci' => 'J-40987654-3'],
            ],
            [
                'type' => 'bank_transfer', 'account_number' => '01340000000000001234', 'owner_name' => 'Envíos Carabobo C.A.', 'owner_id' => 'J-40987654-3',
                'bank_id' => $mercantil?->id, 'is_default' => false, 'is_active' => true,
                'reference_info' => ['alias' => 'Transferencia Bancaria', 'bank' => $mercantil?->name ?? 'Mercantil', 'currency' => 'VES', 'rif_number' => 'J-40987654-3'],
            ],
        ];
        foreach ($demoMethods as $data) {
            $company->paymentMethods()->create($data);
        }
    }

    /** Todos los métodos de pago demo para el usuario 1 (comprador Abrahan). */
    private function seedUser1PaymentMethods(): void
    {
        $user = User::find(1);
        if (! $user) {
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
            $rif = $letras[$i % count($letras)].'-'.$rifNum.'-'.($i % 10);
            $zona = $zonas[$i % count($zonas)];
            $taxDomicile = $zona['street'].', Valencia, Carabobo';

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
                ['title' => 'Nuevo pedido recibido', 'body' => 'Pedido #'.(Order::max('id') ?? 1).' - Revisa y confirma.', 'type' => 'order', 'at' => $today],
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

        // Delivery agents: notificaciones de asignación y entregas
        $deliveryAgents = DeliveryAgent::with('profile')->get();
        foreach ($deliveryAgents as $agent) {
            if (! $agent->profile) {
                continue;
            }
            $deliveryItems = [
                ['title' => 'Nueva orden disponible', 'body' => 'Hay una orden lista para recoger cerca de ti.', 'type' => 'order', 'at' => $today, 'read' => false],
                ['title' => 'Entrega completada', 'body' => '¡Buen trabajo! Has completado una entrega exitosamente.', 'type' => 'order', 'at' => $yesterday, 'read' => true],
                ['title' => 'Actualización de ganancias', 'body' => 'Tus ganancias de hoy han sido actualizadas.', 'type' => 'points', 'at' => $today->copy()->subHours(2), 'read' => false],
            ];
            foreach ($deliveryItems as $item) {
                Notification::create([
                    'profile_id' => $agent->profile->id,
                    'title' => $item['title'],
                    'body' => $item['body'],
                    'type' => $item['type'],
                    'read_at' => $item['read'] ? $item['at'] : null,
                    'data' => [],
                    'created_at' => $item['at'],
                ]);
            }
        }

        // Delivery company (user 16 - TOWDAH YADAH): notificaciones de gestión
        $companyProfile = Profile::where('user_id', 16)->first();
        if ($companyProfile) {
            $demoOrderRef = Order::where('status', 'delivered')
                ->whereHas('orderDelivery.agent', fn ($q) => $q->whereNotNull('company_id'))
                ->orderByDesc('id')
                ->value('id') ?? Order::max('id');
            $companyItems = [
                ['title' => 'Nuevo repartidor registrado', 'body' => 'Jarvis Pulido1 se ha unido a tu equipo de entregas.', 'type' => 'order', 'at' => $today, 'read' => false],
                ['title' => 'Entrega completada por tu equipo', 'body' => 'Pedro Motorizado entregó un pedido #'.$demoOrderRef.' exitosamente.', 'type' => 'order', 'at' => $today->copy()->subHours(1), 'read' => false],
                ['title' => 'Resumen de ganancias', 'body' => 'Tu equipo generó entregas hoy; revisa Ganancias en la app.', 'type' => 'points', 'at' => $yesterday, 'read' => true],
                ['title' => 'Orden disponible en tu zona', 'body' => 'Hay órdenes en ruta esperando repartidor cerca de El Socorro.', 'type' => 'order', 'at' => $today->copy()->subMinutes(30), 'read' => false],
            ];
            foreach ($companyItems as $item) {
                Notification::create([
                    'profile_id' => $companyProfile->id,
                    'title' => $item['title'],
                    'body' => $item['body'],
                    'type' => $item['type'],
                    'read_at' => $item['read'] ? $item['at'] : null,
                    'data' => [],
                    'created_at' => $item['at'],
                ]);
            }
        }
    }

    /** Zonas de entrega activas para Valencia — todas las 6 zonas del ZONAS constant. */
    private function seedDeliveryZones(): void
    {
        $zoneConfigs = [
            ['index' => 0, 'radius' => 3.5, 'fee' => 2.00, 'time' => 20, 'desc' => 'Zona El Socorro y alrededores, Valencia.'],
            ['index' => 1, 'radius' => 4.0, 'fee' => 2.50, 'time' => 25, 'desc' => 'Zona Los Chorritos y sectores cercanos, Valencia.'],
            ['index' => 2, 'radius' => 3.0, 'fee' => 3.00, 'time' => 30, 'desc' => 'Zona Mayorista (La Isabelica) y alrededores, Valencia.'],
            ['index' => 3, 'radius' => 2.5, 'fee' => 2.00, 'time' => 20, 'desc' => 'Zona Bella Florida (La Florida), Valencia.'],
            ['index' => 4, 'radius' => 5.0, 'fee' => 4.00, 'time' => 35, 'desc' => 'Zona San Diego, periferia Valencia.'],
            ['index' => 5, 'radius' => 3.0, 'fee' => 2.50, 'time' => 25, 'desc' => 'Zona Santa Rosa y alrededores, Valencia.'],
        ];
        foreach ($zoneConfigs as $cfg) {
            $zona = self::ZONAS[$cfg['index']];
            DeliveryZone::firstOrCreate(
                ['name' => $zona['name']],
                [
                    'name' => $zona['name'],
                    'center_latitude' => $zona['lat'],
                    'center_longitude' => $zona['lng'],
                    'radius' => $cfg['radius'],
                    'delivery_fee' => $cfg['fee'],
                    'delivery_time' => $cfg['time'],
                    'is_active' => true,
                    'description' => $cfg['desc'],
                ]
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

            if (! $commerceProfileId) {
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

    /**
     * Deja el buyer principal (user 1) sin historial de pedidos ni ítems en carrito para recorrer el flujo como primer uso.
     * Jarvis (user 17) queda sin entregas en seed: en seedOrders solo tenía OrderDelivery en órdenes de buyer 0.
     * No vacía perfil, dirección, teléfono ni documentos.
     */
    private function cleanDemoForFlowTesting(): void
    {
        $profile1Id = Profile::where('user_id', 1)->value('id');
        if (! $profile1Id) {
            return;
        }

        $orderIds = Order::where('profile_id', $profile1Id)->pluck('id');
        if ($orderIds->isNotEmpty()) {
            DB::table('delivery_assignment_timeouts')->whereIn('order_id', $orderIds)->delete();
        }
        DB::table('order_idempotency_keys')->where('profile_id', $profile1Id)->delete();
        Notification::where('profile_id', $profile1Id)->delete();

        Order::where('profile_id', $profile1Id)->delete();

        $cart = Cart::where('profile_id', $profile1Id)->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
        }
    }
}
