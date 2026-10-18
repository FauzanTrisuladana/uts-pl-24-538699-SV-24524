<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsBendahara(): User
    {
        $bendahara = User::factory()->create([
            'role' => 'bendahara',
            'status' => 'Aktif',
        ]);

        return $bendahara;
    }

    public function test_bendahara_can_list_users_with_pagination_and_filters(): void
    {
        $bendahara = $this->actingAsBendahara();
        $token = $bendahara->createToken('auth_token')->plainTextToken;

        User::factory()->create([
            'name' => 'Andi Wijaya',
            'role' => 'biasa',
            'status' => 'Aktif',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user?page=1&per_page=10&search=Andi');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data user berhasil diambil')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Andi Wijaya');
    }

    public function test_bendahara_can_create_user_with_default_or_custom_password(): void
    {
        $bendahara = $this->actingAsBendahara();
        $token = $bendahara->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/user', [
                'nama' => 'Siti Aminah',
                'email' => 'siti@example.com',
                'role' => 'biasa',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'User berhasil dibuat')
            ->assertJsonPath('data.name', 'Siti Aminah')
            ->assertJsonPath('data.email', 'siti@example.com')
            ->assertJsonPath('data.role', 'biasa')
            ->assertJsonPath('data.status', 'Aktif');

        $createdUser = User::where('email', 'siti@example.com')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue(Hash::check('password', $createdUser->password));
    }

    public function test_bendahara_can_update_user_role(): void
    {
        $bendahara = $this->actingAsBendahara();
        $token = $bendahara->createToken('auth_token')->plainTextToken;

        $targetUser = User::factory()->create([
            'role' => 'biasa',
            'status' => 'Aktif',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/user/{$targetUser->id}", [
                'role' => 'bendahara',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'User berhasil diupdate')
            ->assertJsonPath('data.role', 'bendahara');
    }

    public function test_bendahara_can_toggle_user_status(): void
    {
        $bendahara = $this->actingAsBendahara();
        $token = $bendahara->createToken('auth_token')->plainTextToken;

        $targetUser = User::factory()->create([
            'status' => 'Tidak Aktif',
            'activated_at' => null,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/user/{$targetUser->id}/toggle-status");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'Aktif');

        $this->assertNotNull($targetUser->fresh()->activated_at);
    }

    public function test_bendahara_can_delete_user(): void
    {
        $bendahara = $this->actingAsBendahara();
        $token = $bendahara->createToken('auth_token')->plainTextToken;

        $targetUser = User::factory()->create([
            'status' => 'Aktif',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/user/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'User berhasil dihapus');

        $this->assertSoftDeleted('users', [
            'id' => $targetUser->id,
            'status' => 'Tidak Aktif',
        ]);
    }

    public function test_non_bendahara_cannot_access_user_management(): void
    {
        $regularUser = User::factory()->create([
            'role' => 'biasa',
            'status' => 'Aktif',
        ]);
        $token = $regularUser->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/user?page=1&per_page=10');

        $response->assertStatus(403)
            ->assertJsonPath('status', 'forbidden');
    }
}
