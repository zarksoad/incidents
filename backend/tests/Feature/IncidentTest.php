<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncidentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_must_be_authenticated_to_view_incidents()
    {
        $response = $this->getJson('/api/incidents');
        $response->assertStatus(401);
    }

    public function test_admin_can_view_all_incidents()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Incident::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/incidents');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function test_agent_can_only_view_assigned_incidents()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        
        Incident::factory()->count(2)->create(['assigned_id' => $agent->id]);
        
        Incident::factory()->count(3)->create();

        $response = $this->actingAs($agent)->getJson('/api/incidents');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    public function test_user_can_create_incident()
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Test Incident',
            'description' => 'Test description',
            'priority' => 'alta',
            'status' => 'abierto',
            'due_date' => '2026-12-31',
        ];

        $response = $this->actingAs($user)->postJson('/api/incidents', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('incidents', ['title' => 'Test Incident']);
    }

    public function test_admin_can_delete_incident()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $incident = Incident::factory()->create();

        $response = $this->actingAs($admin)->deleteJson('/api/incidents/' . $incident->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }

    public function test_agent_cannot_delete_incident()
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $incident = Incident::factory()->create();

        $response = $this->actingAs($agent)->deleteJson('/api/incidents/' . $incident->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
    }
}
