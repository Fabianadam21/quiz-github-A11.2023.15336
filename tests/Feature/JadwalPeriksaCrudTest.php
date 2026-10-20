<?php

namespace Tests\Feature;

use App\Models\JadwalPeriksa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class JadwalPeriksaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_and_edit_pages_render_the_weekday_options(): void
    {
        $dokter = User::factory()->create(['role' => 'dokter']);
        $jadwalPeriksa = JadwalPeriksa::query()->create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'status' => 'Aktif',
        ]);

        $this->actingAs($dokter)
            ->get(route('jadwal-periksa.create'))
            ->assertOk()
            ->assertSee('Senin')
            ->assertSee('Minggu');

        $this->actingAs($dokter)
            ->get(route('jadwal-periksa.edit', $jadwalPeriksa))
            ->assertOk()
            ->assertSee('Senin')
            ->assertSee('Minggu');
    }

    public function test_doctor_can_manage_only_their_own_schedule(): void
    {
        $dokter = User::factory()->create(['role' => 'dokter']);
        $dokterLain = User::factory()->create(['role' => 'dokter']);
        $jadwalLain = JadwalPeriksa::query()->create([
            'id_dokter' => $dokterLain->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
            'status' => 'Aktif',
        ]);

        $this->actingAs($dokter)
            ->get(route('jadwal-periksa.create'))
            ->assertOk();

        $this->actingAs($dokter)
            ->post(route('jadwal-periksa.store'), [
                'hari' => 'Senin',
                'jam_mulai' => '08:00',
                'jam_selesai' => '12:00',
                'id_dokter' => $dokterLain->id,
                'status' => 'Tidak Aktif',
            ])
            ->assertRedirect(route('jadwal-periksa.index'));

        $jadwalMilikDokter = JadwalPeriksa::query()->where('id_dokter', $dokter->id)->firstOrFail();
        $this->assertSame('Aktif', $jadwalMilikDokter->status);
        $this->assertDatabaseHas('jadwal_periksa', [
            'id' => $jadwalMilikDokter->id,
            'id_dokter' => $dokter->id,
        ]);

        $this->actingAs($dokter)
            ->get(route('jadwal-periksa.index'))
            ->assertOk()
            ->assertSee('Senin')
            ->assertDontSee('Selasa');

        $this->actingAs($dokter)
            ->get(route('jadwal-periksa.edit', $jadwalLain))
            ->assertNotFound();

        $this->actingAs($dokter)
            ->put(route('jadwal-periksa.update', $jadwalLain), [
                'hari' => 'Rabu',
                'jam_mulai' => '10:00',
                'jam_selesai' => '13:00',
            ])
            ->assertNotFound();

        $this->actingAs($dokter)
            ->delete(route('jadwal-periksa.destroy', $jadwalLain))
            ->assertNotFound();

        $this->assertDatabaseHas('jadwal_periksa', [
            'id' => $jadwalLain->id,
            'hari' => 'Selasa',
        ]);

        $this->actingAs($dokter)
            ->get(route('jadwal-periksa.edit', $jadwalMilikDokter))
            ->assertOk()
            ->assertSee('08:00');

        $this->actingAs($dokter)
            ->put(route('jadwal-periksa.update', $jadwalMilikDokter), [
                'hari' => 'Kamis',
                'jam_mulai' => '09:30',
                'jam_selesai' => '15:00',
            ])
            ->assertRedirect(route('jadwal-periksa.index'));

        $this->actingAs($dokter)
            ->delete(route('jadwal-periksa.destroy', $jadwalMilikDokter))
            ->assertRedirect(route('jadwal-periksa.index'));

        $this->assertDatabaseMissing('jadwal_periksa', ['id' => $jadwalMilikDokter->id]);
    }

    public function test_schedule_validation_and_doctor_role_are_enforced(): void
    {
        $dokter = User::factory()->create(['role' => 'dokter']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($dokter)
            ->from(route('jadwal-periksa.create'))
            ->post(route('jadwal-periksa.store'), [
                'hari' => 'Hari Palsu',
                'jam_mulai' => '12:00',
                'jam_selesai' => '11:00',
            ])
            ->assertSessionHasErrors(['hari', 'jam_selesai']);

        $this->actingAs($admin)->get(route('jadwal-periksa.index'))->assertForbidden();
    }
}
