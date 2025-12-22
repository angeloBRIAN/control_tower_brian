<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can access admin routes
     */
    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
    }

    /**
     * Test that non-admin cannot access admin routes
     */
    public function test_manager_cannot_access_user_management(): void
    {
        $manager = User::factory()->create(['role' => 'manager']);

        $response = $this->actingAs($manager)->get('/admin/users');

        $response->assertStatus(403);
    }

    /**
     * Test that control_tower can access imports
     */
    public function test_control_tower_can_access_imports(): void
    {
        $ctUser = User::factory()->create(['role' => 'control_tower']);

        $response = $this->actingAs($ctUser)->get('/imports');

        $response->assertStatus(200);
    }

    /**
     * Test that SA cannot access imports
     */
    public function test_sa_cannot_access_imports(): void
    {
        $saUser = User::factory()->create(['role' => 'sa']);

        $response = $this->actingAs($saUser)->get('/imports');

        $response->assertStatus(403);
    }

    /**
     * Test that audit user can access audit logs
     */
    public function test_audit_can_access_audit_logs(): void
    {
        $auditUser = User::factory()->create(['role' => 'audit']);

        $response = $this->actingAs($auditUser)->get('/audit-logs');

        $response->assertStatus(200);
    }

    /**
     * Test that SA can view jobs
     */
    public function test_sa_can_view_jobs(): void
    {
        $saUser = User::factory()->create(['role' => 'sa']);

        $response = $this->actingAs($saUser)->get('/jobs');

        $response->assertStatus(200);
    }

    /**
     * Test that foreman can view jobs
     */
    public function test_foreman_can_view_jobs(): void
    {
        $foremanUser = User::factory()->create(['role' => 'foreman']);

        $response = $this->actingAs($foremanUser)->get('/jobs');

        $response->assertStatus(200);
    }

    /**
     * Test that sparepart can view jobs
     */
    public function test_sparepart_can_view_jobs(): void
    {
        $sparepartUser = User::factory()->create(['role' => 'sparepart']);

        $response = $this->actingAs($sparepartUser)->get('/jobs');

        $response->assertStatus(200);
    }

    /**
     * Test that all users can access dashboard
     */
    public function test_all_roles_can_access_dashboard(): void
    {
        $roles = ['admin', 'manager', 'control_tower', 'sparepart', 'sa', 'foreman', 'audit'];

        foreach ($roles as $role) {
            $user = User::factory()->create(['role' => $role]);
            
            $response = $this->actingAs($user)->get('/');
            
            $response->assertStatus(200);
        }
    }

    /**
     * Test that sparepart can update order parts on jobs needing parts
     */
    public function test_sparepart_can_update_order_parts(): void
    {
        $sparepartUser = User::factory()->create(['role' => 'sparepart']);
        $job = \App\Models\Job::factory()->create(['need_part' => true]);

        $response = $this->actingAs($sparepartUser)->patch("/jobs/{$job->id}/order-parts", [
            'rq' => 'RQ-001',
            'no_order_part_mbina' => 'MBINA-001',
        ]);

        $response->assertRedirect();
    }
}
