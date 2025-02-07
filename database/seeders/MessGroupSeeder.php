<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MessGroup;
use App\Models\Member;

class MessGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a Mess Group
        $messGroup = MessGroup::create([
            'name' => 'Laster Group',
            'fixed_cost' => 70,
            'start_date' => '2025-02-01',
            'end_date' => '2025-02-28',
            'total_days' => 28,
            'created_at' => '2025-02-05 20:50:16',
            'updated_at' => '2025-02-05 20:50:16',
        ]);

        // Get all existing members
        $members = Member::all();

        // Attach all members to the Mess Group with pivot data
        foreach ($members as $member) {
            $messGroup->members()->attach($member->id, [
                'shopping' => 0,
                'deposits' => 0,
                'balance' => 0,
                'created_at' => '2025-02-05 20:54:55',
                'updated_at' => '2025-02-05 20:54:55',
            ]);
        }
    }
}
