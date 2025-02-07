<?php
namespace Tests\Feature\custom;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Expense;
use App\Models\MessGroup;
use App\Models\Member;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_returns_all_expenses_with_relationships()
    {
        $messGroup = MessGroup::factory()->create();
        $member = Member::factory()->create();
        Expense::factory()->create(['mess_group_id' => $messGroup->id, 'member_id' => $member->id]);

        $response = $this->getJson('/api/expenses');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Expenses retrieved',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id', 'mess_group_id', 'member_id', 'date', 'description', 'amount',
                        'created_at', 'updated_at',
                        'mess_group' => ['id', 'name', 'start_date', 'end_date'],
                        'member' => ['id', 'name']
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_creates_an_expense()
    {
        $messGroup = MessGroup::factory()->create();
        $member = Member::factory()->create();
        $data = [
            'mess_group_id' => $messGroup->id,
            'member_id' => $member->id,
            'date' => '2025-02-05',
            'description' => 'Grocery shopping',
            'amount' => 100,
        ];

        $response = $this->postJson('/api/expenses', $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Expense created',
                'data' => $data,
            ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_expense()
    {
        $response = $this->postJson('/api/expenses', []);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors([
                'mess_group_id', 'member_id', 'date', 'description', 'amount'
            ]);
    }

    /** @test */
    public function it_returns_a_specific_expense_with_relationships()
    {
        $messGroup = MessGroup::factory()->create();
        $member = Member::factory()->create();
        $expense = Expense::factory()->create(['mess_group_id' => $messGroup->id, 'member_id' => $member->id]);

        $response = $this->getJson('/api/expenses/' . $expense->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Expense retrieved',
            ])
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id', 'mess_group_id', 'member_id', 'date', 'description', 'amount',
                    'created_at', 'updated_at',
                    'mess_group' => ['id', 'name', 'start_date', 'end_date'],
                    'member' => ['id', 'name']
                ]
            ]);
    }

    /** @test */
    public function it_returns_404_if_expense_not_found()
    {
        $response = $this->getJson('/api/expenses/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Expense not found'
            ]);
    }

    /** @test */
    public function it_updates_an_expense()
    {
        $messGroup = MessGroup::factory()->create();
        $member = Member::factory()->create();
        $expense = Expense::factory()->create(['mess_group_id' => $messGroup->id, 'member_id' => $member->id]);

        $data = ['amount' => 200];

        $response = $this->putJson('/api/expenses/' . $expense->id, $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Expense updated',
                'data' => array_merge($expense->toArray(), $data),
            ]);
    }

    /** @test */
    public function it_allows_partial_updates_for_expense()
    {
        // Arrange
        $messGroup = MessGroup::factory()->create();
        $member = Member::factory()->create();
        $expense = Expense::factory()->create([
            'mess_group_id' => $messGroup->id,
            'member_id' => $member->id,
            'amount' => 100, // Initial amount
        ]);

        // Act
        $response = $this->putJson('/api/expenses/' . $expense->id, []); // Sending an empty payload

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Expense updated',
                'data' => [
                    'id' => $expense->id,
                    'amount' => 100, // The amount should remain unchanged
                ]
            ]);
    }

    /** @test */
    public function it_deletes_an_expense()
    {
        $expense = Expense::factory()->create();

        $response = $this->deleteJson('/api/expenses/' . $expense->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Expense deleted'
            ]);

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    /** @test */
    public function it_returns_404_when_expense_not_found_for_deletion()
    {
        $response = $this->deleteJson('/api/expenses/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Expense not found'
            ]);
    }
}
