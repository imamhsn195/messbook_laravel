<?php

namespace Tests\Feature\custom;

use App\Models\Member;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class MemberControllerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test the index method (GET /members)
     */
    public function test_index()
    {
        // Seed the database with some members
        Member::factory()->count(3)->create();

        // Send GET request to index route
        $response = $this->getJson('/api/members');

        // Assert successful response with all members
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data'
            ])
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test the store method (POST /members)
     */
    public function test_store()
    {
        $data = [
            'name' => 'Imam Hasan',
        ];

        $response = $this->postJson('/api/members', $data);

        // Decode JSON response
        $responseData = $response->json();

        // Assert response status is 200 (HTTP_OK)
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Member created successfully',
                'data' => [
                    'name' => 'Imam Hasan',
                ]
            ]);

        // Ensure `id`, `created_at`, and `updated_at` exist in the response
        $this->assertArrayHasKey('id', $responseData['data']);
        $this->assertArrayHasKey('created_at', $responseData['data']);
        $this->assertArrayHasKey('updated_at', $responseData['data']);

        // Ensure timestamps are not null
        $this->assertNotNull($responseData['data']['created_at']);
        $this->assertNotNull($responseData['data']['updated_at']);
    }

    /**
     * Test store method with invalid data (POST /members)
     */
    public function test_store_validation_fail()
    {
        // Invalid data (missing 'name')
        $data = [];

        // Send POST request to store route
        $response = $this->postJson('/api/members', $data);

        // Assert validation failure
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors'
            ]);
    }

    /**
     * Test the show method (GET /members/{id})
     */
    public function test_show()
    {
        // Create a member
        $member = Member::factory()->create();

        // Send GET request to show route
        $response = $this->getJson('/api/members/' . $member->id);

        // Assert successful response with member data
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Member retrieved successfully',
                'data' => [
                    'name' => $member->name,
                    'created_at' => $member->created_at->toISOString(),
                    'updated_at' => $member->updated_at->toISOString(),
                ]
            ]);
    }

    /**
     * Test show method when member not found (GET /members/{id})
     */
    public function test_show_member_not_found()
    {
        // Send GET request with non-existent member ID
        $response = $this->getJson('/api/members/9999');

        // Assert not found response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Member not found',
                'errors' => 'No query results for model [App\\Models\\Member] 9999'
            ]);
    }

    /**
     * Test the update method (PUT/PATCH /members/{id})
     */
    public function test_update()
    {
        // Create a member
        $member = Member::factory()->create();

        // Data to update the member
        $data = [
            'name' => 'Updated Name'
        ];

        // Send PUT/PATCH request to update route
        $response = $this->putJson('/api/members/' . $member->id, $data);

        // Assert successful response with updated member data
        $response->assertStatus(200)
            ->assertJson([
                'id' => $member->id,
                'name' => 'Updated Name',
                'created_at' => $member->created_at->toISOString(),
                'updated_at' => $member->updated_at->toISOString(),
            ]);
    }

    /**
     * Test update method when member not found (PUT/PATCH /members/{id})
     */
    public function test_update_member_not_found()
    {
        // Send PUT/PATCH request with non-existent member ID
        $response = $this->putJson('/api/members/9999', [
            'name' => 'Updated Name'
        ]);

        // Assert not found response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Member not found'
            ]);
    }

    /**
     * Test the destroy method (DELETE /members/{id})
     */
    public function test_destroy()
    {
        // Create a member
        $member = Member::factory()->create();

        // Send DELETE request to destroy route
        $response = $this->deleteJson('/api/members/' . $member->id);

        // Assert successful response with deletion message
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Member deleted successfully'
            ]);
    }

    /**
     * Test destroy method when member not found (DELETE /members/{id})
     */
    public function test_destroy_member_not_found()
    {
        // Send DELETE request with non-existent member ID
        $response = $this->deleteJson('/api/members/9999');

        // Assert not found response
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Member not found',
                'errors' => 'No query results for model [App\\Models\\Member] 9999'
            ]);
    }
}
