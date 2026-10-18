<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_own_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'status' => 'Aktif',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/profile/me');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Profile berhasil diambil')
            ->assertJsonPath('data.email', 'budi@example.com')
            ->assertJsonPath('data.name', 'Budi Santoso');
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'password' => 'password',
            'status' => 'Aktif',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/profile/update', [
                'name' => 'New Name',
                'email' => 'new@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Profile berhasil diupdate')
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.email', 'new@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_authenticated_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
            'status' => 'Aktif',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/profile/update-password', [
                'current_password' => 'password',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Password berhasil diupdate');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_update_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
            'status' => 'Aktif',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson('/api/profile/update-password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['errors' => ['current_password']]);
    }

    public function test_authenticated_user_can_delete_own_account(): void
    {
        $user = User::factory()->create([
            'status' => 'Aktif',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson('/api/profile/delete');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Akun berhasil dihapus');

        $this->assertSoftDeleted('users', [
            'id' => $user->id,
            'status' => 'Tidak Aktif',
        ]);
    }
}
