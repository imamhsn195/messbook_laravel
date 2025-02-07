<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $members = [
            [
                'id' => 1,
                'name' => 'Imam Hasan',
                'created_at' => '2025-02-05T16:03:05.000000Z',
                'updated_at' => '2025-02-05T16:03:05.000000Z',
            ],
            [
                'id' => 2,
                'name' => 'Sajjad',
                'created_at' => '2025-02-05T16:03:11.000000Z',
                'updated_at' => '2025-02-05T16:03:11.000000Z',
            ],
            [
                'id' => 3,
                'name' => 'Iqbal',
                'created_at' => '2025-02-05T16:03:22.000000Z',
                'updated_at' => '2025-02-05T16:03:22.000000Z',
            ],
            [
                'id' => 4,
                'name' => 'Kamrul Hasan',
                'created_at' => '2025-02-05T16:03:29.000000Z',
                'updated_at' => '2025-02-05T16:03:29.000000Z',
            ],
            [
                'id' => 5,
                'name' => 'Siraj',
                'created_at' => '2025-02-05T16:03:35.000000Z',
                'updated_at' => '2025-02-05T16:03:35.000000Z',
            ],
            [
                'id' => 6,
                'name' => 'Nayeem',
                'created_at' => '2025-02-05T16:03:50.000000Z',
                'updated_at' => '2025-02-05T16:03:50.000000Z',
            ],
            [
                'id' => 7,
                'name' => 'Jafar',
                'created_at' => '2025-02-05T16:04:03.000000Z',
                'updated_at' => '2025-02-05T16:04:03.000000Z',
            ],
        ];

        // Insert data into the members table
        Member::insert($members);
    }
}
