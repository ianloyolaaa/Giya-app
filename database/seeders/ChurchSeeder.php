<?php

namespace Database\Seeders;

use App\Models\Church;
use App\Models\ChurchCategory;
use App\Models\ChurchImage;
use Illuminate\Database\Seeder;

/**
 * Rewritten for the ERD schema:
 *   - `category` string  -> category_id FK into ChurchCategories
 *   - `image_url` column -> a ChurchImages row flagged is_primary
 *   - `address` merged into location (the ERD has no separate address field)
 *   - `rating` and `daily_visits` dropped; both are now derived at read time
 *   - `mass_schedule` json moved to ScheduleSeeder
 *
 * Run ReferenceDataSeeder first so the categories exist.
 */
class ChurchSeeder extends Seeder
{
    public function run(): void
    {
        $churches = [
            [
                'name' => 'Basilica del Santo Niño', 'location' => 'Osmeña Blvd, Cebu City', 'category' => 'Basilica',
                'description' => 'The oldest Roman Catholic church in the Philippines, home to the sacred image of the Holy Child Jesus.',
                'latitude' => 10.29390, 'longitude' => 123.90190,
                'image' => 'images/churches/basilica-santo-nino.svg',
                'opening_time' => '05:00', 'closing_time' => '20:00', 'is_featured' => true,
            ],
            [
                'name' => 'Simala Shrine', 'location' => 'Lindogon, Sibonga, Cebu', 'category' => 'Shrine',
                'description' => 'A breathtaking castle-like shrine perched on a hill, known for miraculous answers to prayers.',
                'latitude' => 10.00410, 'longitude' => 123.57600,
                'image' => 'images/churches/simala-shrine.svg',
                'opening_time' => '06:00', 'closing_time' => '18:00', 'is_featured' => true,
            ],
            [
                'name' => "Magellan's Cross Chapel", 'location' => 'P. Burgos St, Cebu City', 'category' => 'Heritage',
                'description' => 'Historic cross planted by Ferdinand Magellan in 1521, symbolizing the arrival of Christianity in the Philippines.',
                'latitude' => 10.29364, 'longitude' => 123.90227,
                'image' => 'images/churches/magellans-cross.svg',
                'opening_time' => '08:00', 'closing_time' => '18:00', 'is_featured' => true,
            ],
            [
                'name' => 'Cebu Metropolitan Cathedral', 'location' => 'P. Burgos St, Cebu City', 'category' => 'Cathedral',
                'description' => 'The seat of the Archdiocese of Cebu, known for its coral-stone facade and trefoil pediment.',
                'latitude' => 10.29691, 'longitude' => 123.90197,
                'image' => 'images/churches/metropolitan-cathedral.svg',
                'opening_time' => '05:30', 'closing_time' => '19:30', 'is_featured' => true,
            ],
            [
                'name' => 'San Agustin Church', 'location' => 'Cebu City', 'category' => 'Church',
                'description' => 'A historic parish with deep Augustinian roots in the heart of the city.',
                'latitude' => 10.29520, 'longitude' => 123.90340,
                'opening_time' => '05:30', 'closing_time' => '19:00', 'is_featured' => false,
            ],
            [
                'name' => 'San Pedro Calungsod Shrine', 'location' => 'Cebu City', 'category' => 'Shrine',
                'description' => 'Dedicated to the young Visayan catechist and second Filipino saint.',
                'latitude' => 10.29810, 'longitude' => 123.90560,
                'opening_time' => '06:00', 'closing_time' => '19:00', 'is_featured' => false,
            ],
            [
                'name' => 'Redemptorist Church', 'location' => 'Cebu City', 'category' => 'Church',
                'description' => 'Known for the Wednesday novena to Our Mother of Perpetual Help.',
                'latitude' => 10.29050, 'longitude' => 123.89180,
                'opening_time' => '05:00', 'closing_time' => '20:00', 'is_featured' => false,
            ],
            [
                'name' => 'Our Lady of Guadalupe Parish', 'location' => 'Guadalupe, Cebu City', 'category' => 'Church',
                'description' => 'A hillside parish with a strong local devotion to Our Lady of Guadalupe.',
                'latitude' => 10.31159, 'longitude' => 123.87688,
                'opening_time' => '05:00', 'closing_time' => '20:00', 'is_featured' => false,
            ],
            [
                'name' => 'Our Lady of the Rule Parish', 'location' => 'Poblacion, Lapu-Lapu City', 'category' => 'Church',
                'description' => 'The parish of the Virgin of the Rule, patroness of Opon, on Mactan Island.',
                'latitude' => 10.30980, 'longitude' => 123.94950,
                'opening_time' => '05:30', 'closing_time' => '19:00', 'is_featured' => false,
            ],
            [
                'name' => 'Sacred Heart Parish', 'location' => 'A.C. Cortes Ave, Mandaue City', 'category' => 'Church',
                'description' => 'A welcoming parish serving the Mandaue community with daily masses and devotions.',
                'latitude' => 10.32340, 'longitude' => 123.92210,
                'opening_time' => '06:00', 'closing_time' => '18:30', 'is_featured' => false,
            ],
        ];

        foreach ($churches as $data) {
            $category = ChurchCategory::firstOrCreate(
                ['name' => $data['category']],
                ['created_at' => now(), 'updated_at' => now()]
            );

            $church = Church::updateOrCreate(
                ['name' => $data['name']],
                [
                    'category_id'  => $category->id,
                    'location'     => $data['location'],
                    'description'  => $data['description'],
                    'latitude'     => $data['latitude'],
                    'longitude'    => $data['longitude'],
                    'opening_time' => $data['opening_time'],
                    'closing_time' => $data['closing_time'],
                    'is_featured'  => $data['is_featured'],
                    'is_active'    => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );

            if (! empty($data['image'])) {
                ChurchImage::updateOrCreate(
                    ['church_id' => $church->id, 'image_url' => $data['image']],
                    [
                        'caption'     => $data['name'],
                        'is_primary'  => true,
                        'uploaded_at' => now(),
                        'created_at'  => now(),
                    ]
                );
            }
        }
    }
}
