<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Demo podatke možeš učitati samo uz APP_ENV=local.');
        }

        $admin = User::query()->firstOrNew(['email' => config('store.demo_admin_email')]);
        if (! $admin->exists) {
            $admin->fill(['name' => config('app.name').' Admin', 'password' => config('store.demo_admin_password')]);
            $admin->role = Role::Admin;
            $admin->save();
        } elseif ($admin->role !== Role::Admin) {
            throw new RuntimeException('Demo admin email je već u upotrebi na nalogu kupca.');
        }

        $categoryNames = [
            'bags' => 'Ranci i torbe',
            'camp' => 'Kampovanje',
            'kitchen' => 'Posuđe i boce',
            'clothing' => 'Odeća i zaštita',
            'trail' => 'Oprema za stazu',
            'lights' => 'Rasveta',
        ];
        $now = now();
        Category::query()->insertOrIgnore(array_values(array_map(
            fn (string $name) => ['name' => $name, 'created_at' => $now, 'updated_at' => $now],
            $categoryNames,
        )));
        $categoryIds = Category::query()->whereIn('name', array_values($categoryNames))->pluck('id', 'name');

        $products = [
            ['Podloga Logor', 'Lagano pakovanje sa kompresionim trakama za kampovanje i predah na dužim turama.', 429000, 16, '/images/oprema.webp#podloga', 'camp'],
            ['Torbica Prevoj', 'Kompaktna torbica oko struka sa podesivim kaišem. Drži telefon, ključeve i sitnice pri ruci.', 249000, 24, '/images/oprema.webp#torbica', 'bags'],
            ['Termos Sever 750 ml', 'Vakuumski termos od nerđajućeg čelika za tople i hladne napitke tokom celog dana.', 329000, 18, '/images/oprema.webp#termos', 'kitchen'],
            ['Ranac Greben 28 L', 'Planinarski ranac sa prozračnim leđnim panelom, podesivim naramenicama i džepovima za bocu.', 789000, 12, '/images/oprema.webp#ranac', 'bags'],
        ];

        $additionalProducts = [
            ['Čeona lampa Svitanje', 'LED lampa sa podesivom trakom za rane polaske i večernje šetnje.', 269000, 30, 'lights'],
            ['Štapovi za hodanje Uspon', 'Par sklopivih štapova s udobnim rukohvatima za duge deonice.', 549000, 14, 'trail'],
            ['Set prve pomoći Signal', 'Kompaktna torbica sa osnovnim materijalom za previjanje na izletu.', 219000, 20, 'trail'],
            ['Emajlirana šolja Izvor', 'Lagana šolja sa širokom drškom za čaj ili kafu u kampu.', 119000, 34, 'kitchen'],
            ['Vreća za spavanje Sumrak', 'Savitljiva vreća sa transportnim kaiševima za noćenje u šatoru.', 799000, 9, 'camp'],
            ['Kamp fenjer Svetionik', 'Prenosivi fenjer sa difuznim svetlom za šator i kamp sto.', 349000, 18, 'lights'],
            ['Sklopiva stolica Predah', 'Trokraka stolica sa platnenim sedištem za kratak odmor na turi.', 359000, 11, 'camp'],
            ['Savitljiva boca Kap 500 ml', 'Mekana boca koja zauzima malo prostora kada je prazna.', 179000, 25, 'kitchen'],
            ['Vodootporna vreća Tok 10 L', 'Vreća sa preklopnim zatvaranjem za presvlaku i sitnu opremu.', 209000, 23, 'bags'],
            ['Kamp pribor Trpeza', 'Kompaktna kašika i viljuška u jednom komadu, pogodna za obrok na stazi.', 89000, 40, 'kitchen'],
            ['Kompas Orijentir', 'Kompas sa preklopnim poklopcem za orijentaciju uz papirnu kartu.', 199000, 17, 'trail'],
            ['Planinarske čarape Kamen', 'Debele čarape sa ojačanom petom i prstima za duge šetnje.', 129000, 36, 'clothing'],
            ['Kišni pončo Pljusak', 'Lagani pončo koji se sklapa u mali paket za neplaniranu kišu.', 159000, 29, 'clothing'],
            ['Pletena kapa Mraz', 'Mekana kapa sa preklopnom ivicom za hladna jutra na otvorenom.', 139000, 19, 'clothing'],
            ['Rukavice Sneg', 'Tople rukavice sa vrhovima prstiju za korišćenje ekrana osetljivog na dodir.', 229000, 13, 'clothing'],
            ['Kopče za opremu Kuka', 'Dve kopče za pričvršćivanje sitne opreme na spoljašnju stranu ranca.', 69000, 38, 'trail'],
            ['Piknik prostirka Livada', 'Karirana prostirka koja se preklapa radi lakšeg nošenja do izletišta.', 379000, 12, 'camp'],
            ['Viseća ležaljka Krošnja', 'Lagano platno sa trakama koje se pakuje u malu vreću.', 449000, 10, 'camp'],
            ['Set posuđa Kotlić', 'Posude od nerđajućeg čelika sa preklopnim drškama, složene jedna u drugu.', 489000, 15, 'kitchen'],
            ['Podmetač za sedenje Vidikovac', 'Sklopiva penasta podloga za sedenje na kamenu ili vlažnoj travi.', 99000, 28, 'camp'],
        ];

        foreach ($additionalProducts as $index => [$name, $description, $price, $stock, $category]) {
            $column = $index % 4;
            $row = intdiv($index, 4);
            $products[] = [$name, $description, $price, $stock, "/images/oprema-dodatna.webp#{$column}-{$row}", $category];
        }

        $existingImages = Product::query()->whereIn('image_url', array_column($products, 4))
            ->pluck('image_url')->flip();
        $newProducts = [];
        $categoryByImage = [];

        foreach ($products as [$name, $description, $price, $stock, $imageUrl, $category]) {
            $categoryId = $categoryIds[$categoryNames[$category]];
            $categoryByImage[$imageUrl] = $categoryId;
            if ($existingImages->has($imageUrl)) {
                continue;
            }

            $newProducts[] = [
                'name' => $name,
                'description' => $description,
                'category_id' => $categoryId,
                'price_minor' => $price,
                'stock' => $stock,
                'image_url' => $imageUrl,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Product::query()->insert($newProducts);
        $this->categorizeExistingProducts($categoryByImage);
    }

    private function categorizeExistingProducts(array $categoryByImage): void
    {
        $branches = array_fill(0, count($categoryByImage), 'WHEN ? THEN ?');
        $cases = implode(' ', $branches);
        $images = array_keys($categoryByImage);
        $placeholders = implode(', ', array_fill(0, count($images), '?'));
        $bindings = [];

        foreach ($categoryByImage as $image => $categoryId) {
            $bindings[] = $image;
            $bindings[] = $categoryId;
        }

        DB::update(
            "UPDATE products
             SET category_id = CASE image_url {$cases} END, revision = revision + 1, updated_at = ?
             WHERE category_id IS NULL AND image_url IN ({$placeholders})",
            [...$bindings, now(), ...$images],
        );
    }
}
