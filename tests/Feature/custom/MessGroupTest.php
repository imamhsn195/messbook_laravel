<?php

namespace Tests\Feature\custom;

use App\Models\Expense;
use App\Models\Member;
use App\Models\MessGroup;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessGroupTest extends TestCase
{
    use DatabaseMigrations, WithFaker;

    // Tests for Creating MessGroups
    /** @test */
    public function it_creates_a_mess_group_successfully_with_start_end_date_and_fixed_cost()
    {
        $response = $this->postJson('/api/mess-groups', [
            'name' => 'Test Mess Group',
            'start_date' => '2024-02-01',
            'end_date' => '2024-02-28',
            'fixed_cost' => 50.75,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('mess_groups', [
            'name' => 'Test Mess Group',
            'fixed_cost' => 50.75
        ]);
    }

    /** @test */
    public function it_creates_a_mess_group_and_sets_end_date_to_end_of_month_with_fixed_cost()
    {
        $response = $this->postJson('/api/mess-groups', [
            'name' => 'Auto-End Mess Group',
            'start_date' => '2024-02-10',
            'fixed_cost' => 75.00,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('mess_groups', [
            'name' => 'Auto-End Mess Group',
            'end_date' => '2024-02-29',
            'fixed_cost' => 75.00
        ]);
    }

    /** @test */
    public function it_fails_to_create_mess_group_with_invalid_fixed_cost()
    {
        $response = $this->postJson('/api/mess-groups', [
            'name' => 'Invalid Cost Group',
            'start_date' => '2024-02-10',
            'fixed_cost' => -10, // Invalid negative value
        ]);

        $response->assertStatus(400);
    }

    // Tests for Retrieving MessGroups
    /** @test */
    public function it_fetches_all_mess_groups_with_fixed_cost()
    {
        MessGroup::factory()->count(3)->create(['fixed_cost' => 100]);

        $response = $this->getJson('/api/mess-groups');
        $response->assertStatus(200);
        $response->assertJsonFragment(['fixed_cost' => 100]);
    }

    /** @test */
    public function it_fetches_a_single_mess_group_with_fixed_cost()
    {
        $messGroup = MessGroup::factory()->create(['fixed_cost' => 200.50]);

        $response = $this->getJson("/api/mess-groups/{$messGroup->id}");
        $response->assertStatus(200);
        $response->assertJson(['fixed_cost' => 200.50]);
    }

    // Tests for Updating MessGroups
    /** @test */
    public function it_updates_a_mess_group_with_fixed_cost()
    {
        $messGroup = MessGroup::factory()->create(['fixed_cost' => 80]);

        $response = $this->putJson("/api/mess-groups/{$messGroup->id}", [
            'name' => 'Updated Mess Group',
            'fixed_cost' => 120.75
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('mess_groups', [
            'name' => 'Updated Mess Group',
            'fixed_cost' => 120.75
        ]);
    }

    /** @test */
    public function it_fails_to_update_mess_group_with_invalid_fixed_cost()
    {
        $messGroup = MessGroup::factory()->create(['fixed_cost' => 100]);

        $response = $this->putJson("/api/mess-groups/{$messGroup->id}", [
            'fixed_cost' => -50, // Invalid negative value
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function it_calculates_balances_with_fixed_cost_70_and_28_days()
    {
        // Create MessGroup with new parameters
        $messGroup = MessGroup::factory()->create([
            'fixed_cost' => 70.00, // Updated fixed cost
        ]);

        // Create all 7 members
        $members = [
            'Imam Hasan'  => Member::factory()->create(['name' => 'Imam Hasan']),
            'Sajjad'      => Member::factory()->create(['name' => 'Sajjad']),
            'Iqbal'       => Member::factory()->create(['name' => 'Iqbal']),
            'Kamrul Hasan'=> Member::factory()->create(['name' => 'Kamrul Hasan']),
            'Siraj'       => Member::factory()->create(['name' => 'Siraj']),
            'Nayeem'      => Member::factory()->create(['name' => 'Nayeem']),
            'Jafar'       => Member::factory()->create(['name' => 'Jafar']),
        ];

        // Attach all members to the mess group
        $messGroup->members()->attach(collect($members)->pluck('id'));

        // Add expenses (same as original data)
        $expenses = [
            ['date' => '2025-02-01', 'name' => 'Iqbal', 'amount' => 34.00],
            ['date' => '2025-02-01', 'name' => 'Iqbal', 'amount' => 10.75],
            ['date' => '2025-02-01', 'name' => 'Iqbal', 'amount' => 197.50],
            ['date' => '2025-02-02', 'name' => 'Siraj', 'amount' => 63.00],
            ['date' => '2025-02-02', 'name' => 'Siraj', 'amount' => 126.00],
            ['date' => '2025-02-02', 'name' => 'Jafar', 'amount' => 6.50],
            ['date' => '2025-02-04', 'name' => 'Kamrul Hasan', 'amount' => 23.50],
        ];

        foreach ($expenses as $expense) {
            $member = $members[$expense['name']];
//            dd($member);
            $messGroup->expenses()->create([
                'member_id' => $member->id,
                'amount' => $expense['amount'],
                'description' => 'Groceries',
                'created_at' => $expense['date']
            ]);
//            dd($messGroup);
        }

        // Trigger calculation
        $this->postJson("/api/mess-groups/{$messGroup->id}/calculate-balances");

        // Calculate expected values
        $totalVariable = 34.00 + 10.75 + 197.50 + 63.00 + 126.00 + 6.50 + 23.50; // 461.25
        $variableShare = $totalVariable / 7; // ~65.8929
        $totalOwedPerMember = $variableShare + 70.00; // ~135.8929

        // Assert balances
        $assertBalance = function ($name, $paidTotal) use ($totalOwedPerMember, $messGroup) {
            $expected = round($totalOwedPerMember - $paidTotal, 2);
            $actual = $messGroup->members()
                ->where('name', $name)
                ->first()
                ->pivot
                ->balance;

            $this->assertEquals($expected, $actual, "Balance mismatch for $name");
        };

        // Verify all balances
        $assertBalance('Iqbal', 242.25);      // 135.89 - 242.25 = -106.36
        $assertBalance('Siraj', 189.00);      // 135.89 - 189.00 = -53.11
        $assertBalance('Jafar', 6.50);        // 135.89 - 6.50 = 129.39
        $assertBalance('Kamrul Hasan', 23.50);// 135.89 - 23.50 = 112.39
        $assertBalance('Imam Hasan', 0);      // 135.89 - 0 = 135.89
        $assertBalance('Sajjad', 0);          // 135.89 - 0 = 135.89
        $assertBalance('Nayeem', 0);          // 135.89 - 0 = 135.89
    }}
