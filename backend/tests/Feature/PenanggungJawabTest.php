<?php

namespace Tests\Feature;

use App\Models\PenanggungJawab;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenanggungJawabTest extends TestCase
{
    use RefreshDatabase;

    private function getBendaharaToken(): string
    {
        $bendahara = User::factory()->create([
            'role' => 'bendahara',
            'status' => 'Aktif',
        ]);

        return $bendahara->createToken('auth_token')->plainTextToken;
    }

    public function test_bendahara_can_list_penanggung_jawab(): void
    {
        $token = $this->getBendaharaToken();

        PenanggungJawab::create(['nama' => 'Ahmad Fauzi']);
        PenanggungJawab::create(['nama' => 'Rina Kartika']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/penanggung-jawab?page=1&per_page=10&search=Ahmad');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data penanggung jawab berhasil diambil')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nama', 'Ahmad Fauzi');
    }

    public function test_bendahara_can_get_penanggung_jawab_dropdown(): void
    {
        $token = $this->getBendaharaToken();

        PenanggungJawab::create(['nama' => 'Ahmad Fauzi']);
        PenanggungJawab::create(['nama' => 'Rina Kartika']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/penanggung-jawab/dropdown');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data');
    }

    public function test_bendahara_can_create_penanggung_jawab(): void
    {
        $token = $this->getBendaharaToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/penanggung-jawab', [
                'nama' => 'Dimas Pratama',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Penanggung jawab berhasil dibuat')
            ->assertJsonPath('data.nama', 'Dimas Pratama');

        $this->assertDatabaseHas('penanggung_jawab', [
            'nama' => 'Dimas Pratama',
        ]);
    }

    public function test_bendahara_can_show_penanggung_jawab_detail(): void
    {
        $token = $this->getBendaharaToken();

        $pj = PenanggungJawab::create(['nama' => 'Dimas Pratama']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson("/api/penanggung-jawab/{$pj->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.nama', 'Dimas Pratama');
    }

    public function test_bendahara_can_update_penanggung_jawab(): void
    {
        $token = $this->getBendaharaToken();

        $pj = PenanggungJawab::create(['nama' => 'Old PJ']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->putJson("/api/penanggung-jawab/{$pj->id}", [
                'nama' => 'Updated PJ',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Penanggung jawab berhasil diperbarui')
            ->assertJsonPath('data.nama', 'Updated PJ');
    }

    public function test_bendahara_can_delete_penanggung_jawab(): void
    {
        $token = $this->getBendaharaToken();

        $pj = PenanggungJawab::create(['nama' => 'To Delete']);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson("/api/penanggung-jawab/{$pj->id}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Penanggung jawab berhasil dihapus');

        $this->assertSoftDeleted('penanggung_jawab', [
            'id' => $pj->id,
        ]);
    }
}
