<?php

namespace Tests\Feature;

use App\Events\QueueUpdated;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use App\Notifications\PaymentProofUploaded;
use App\Notifications\PaymentVerified;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PoliklinikFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_cannot_have_two_unfinished_registrations_and_queue_advances_after_examination(): void
    {
        Event::fake([QueueUpdated::class]);
        [$pasien, $dokter, $jadwal] = $this->clinicSetup();

        $this->actingAs($pasien)
            ->post(route('pasien.daftar-poli.store'), [
                'id_poli' => $jadwal->dokter->id_poli,
                'id_jadwal' => $jadwal->id,
                'keluhan' => 'Demam',
            ])
            ->assertRedirect(route('pasien.dashboard'));

        $pendaftaran = DaftarPoli::query()->firstOrFail();
        $this->assertSame(1, $pendaftaran->no_antrian);

        $this->actingAs($pasien)
            ->from(route('pasien.daftar-poli.create'))
            ->post(route('pasien.daftar-poli.store'), [
                'id_poli' => $jadwal->dokter->id_poli,
                'id_jadwal' => $jadwal->id,
                'keluhan' => 'Batuk',
            ])
            ->assertSessionHasErrors('id_jadwal');

        $this->actingAs($dokter)
            ->post(route('dokter.periksa.store', $pendaftaran), [
                'catatan' => 'Istirahat dan minum air.',
                'biaya_periksa' => 25000,
            ])
            ->assertRedirect(route('dokter.antrian.index'));

        $this->assertDatabaseHas('periksa', [
            'id_daftar_poli' => $pendaftaran->id,
            'status_pembayaran' => 'belum_bayar',
        ]);
        Event::assertDispatched(
            QueueUpdated::class,
            fn (QueueUpdated $event) => $event->completedQueueNumber === $pendaftaran->no_antrian,
        );
    }

    public function test_examination_is_saved_when_queue_broadcast_fails(): void
    {
        [, $dokter, $jadwal] = $this->clinicSetup();
        $pendaftaran = DaftarPoli::query()->create([
            'id_pasien' => User::factory()->create(['role' => 'pasien'])->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Demam',
            'no_antrian' => 1,
        ]);

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.options.host' => '127.0.0.1',
            'broadcasting.connections.reverb.options.port' => 1,
        ]);
        Broadcast::forgetDrivers();
        Log::spy();

        $this->actingAs($dokter)
            ->post(route('dokter.periksa.store', $pendaftaran), [
                'catatan' => 'Istirahat dan minum air.',
                'biaya_periksa' => 25000,
            ])
            ->assertRedirect(route('dokter.antrian.index'))
            ->assertSessionHas('success', 'Pemeriksaan berhasil disimpan.');

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Realtime queue update broadcast failed after examination was saved.', [
                'jadwal_id' => $jadwal->id,
            ]);

        $this->assertDatabaseHas('periksa', [
            'id_daftar_poli' => $pendaftaran->id,
            'status_pembayaran' => 'belum_bayar',
        ]);
    }

    public function test_payment_upload_notifies_assigned_doctor_and_verification_notifies_patient_once(): void
    {
        Notification::fake();
        Storage::fake('local');
        [$pasien, $dokter, $jadwal] = $this->clinicSetup();

        $pendaftaran = DaftarPoli::query()->create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Pusing',
            'no_antrian' => 1,
        ]);
        $periksa = Periksa::query()->create([
            'id_daftar_poli' => $pendaftaran->id,
            'tgl_periksa' => now(),
            'catatan' => 'Istirahat.',
            'biaya_periksa' => 30000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        $this->actingAs($pasien)
            ->post(route('pasien.pembayaran.upload', $periksa), [
                'bukti_pembayaran' => UploadedFile::fake()->image('bukti.png'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('periksa', [
            'id' => $periksa->id,
            'status_pembayaran' => 'menunggu_verifikasi',
        ]);
        Notification::assertSentTo($dokter, PaymentProofUploaded::class);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('admin.pembayaran.proof', $periksa))
            ->assertOk();

        $this->actingAs($pasien)
            ->get(route('admin.pembayaran.proof', $periksa))
            ->assertForbidden();

        $this->actingAs($admin)
            ->patch(route('admin.pembayaran.verify', $periksa))
            ->assertRedirect();

        $this->assertDatabaseHas('periksa', [
            'id' => $periksa->id,
            'status_pembayaran' => 'lunas',
        ]);
        Notification::assertSentTo($pasien, PaymentVerified::class);

        $this->actingAs($admin)
            ->patch(route('admin.pembayaran.verify', $periksa))
            ->assertStatus(409);
    }

    public function test_users_can_only_read_and_mark_their_own_notifications(): void
    {
        [$pasien] = $this->clinicSetup();
        $userLain = User::factory()->create(['role' => 'pasien']);
        $notification = $pasien->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test',
            'data' => ['message' => 'Tagihan lunas'],
        ]);

        $this->actingAs($userLain)
            ->patch(route('notifikasi.read', $notification->id))
            ->assertNotFound();

        $this->actingAs($pasien)
            ->get(route('notifikasi.index'))
            ->assertOk()
            ->assertSee('Tagihan lunas');

        $this->actingAs($pasien)
            ->patch(route('notifikasi.read', $notification->id))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_password_change_checks_old_password_and_confirmation(): void
    {
        $pasien = User::factory()->create([
            'role' => 'pasien',
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($pasien)
            ->from(route('profile.edit'))
            ->put(route('profile.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->actingAs($pasien)
            ->put(route('profile.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('new-password', $pasien->fresh()->password));
    }

    public function test_export_endpoints_produce_excel_downloads_scoped_by_role(): void
    {
        [$pasien, $dokter] = $this->clinicSetup();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.export.pasien'))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($dokter)
            ->get(route('dokter.export.jadwal'))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($pasien)
            ->get(route('admin.export.pasien'))
            ->assertForbidden();
    }

    public function test_role_specific_feature_pages_render(): void
    {
        [$pasien, $dokter] = $this->clinicSetup();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($pasien)->get(route('pasien.dashboard'))->assertOk();
        $this->actingAs($pasien)->get(route('pasien.daftar-poli.create'))->assertOk();
        $this->actingAs($pasien)
            ->get(route('pasien.daftar-poli.create'))
            ->assertDontSee('Data Poli');
        $this->actingAs($pasien)->get(route('pasien.riwayat.index'))->assertOk();
        $this->actingAs($pasien)->get(route('pasien.pembayaran.index'))->assertOk();

        $this->actingAs($dokter)->get(route('dokter.dashboard'))->assertOk();
        $this->actingAs($dokter)->get(route('dokter.antrian.index'))->assertOk();
        $this->actingAs($dokter)->get(route('dokter.riwayat.index'))->assertOk();
        $this->actingAs($dokter)->get(route('jadwal-periksa.index'))->assertOk();

        $this->actingAs($admin)->get(route('admin.pembayaran.index'))->assertOk();
        $this->actingAs($admin)->get(route('dokter.index'))->assertOk();
        $this->actingAs($admin)->get(route('pasien.index'))->assertOk();
        $this->actingAs($admin)->get(route('obat.index'))->assertOk();
    }

    public function test_patient_can_choose_poli_before_available_doctor_schedule(): void
    {
        [$pasien, $dokter, $jadwal] = $this->clinicSetup();
        $jadwal->update([
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);
        JadwalPeriksa::query()->create([
            'id_dokter' => $dokter->id,
            'hari' => 'Rabu',
            'jam_mulai' => '14:00',
            'jam_selesai' => '16:00',
            'status' => 'Tidak Aktif',
        ]);
        $poliGigi = Poli::query()->create([
            'nama_poli' => 'Poli Gigi',
            'keterangan' => 'Pemeriksaan gigi',
        ]);
        $dokterGigi = User::factory()->create([
            'role' => 'dokter',
            'id_poli' => $poliGigi->id,
        ]);
        JadwalPeriksa::query()->create([
            'id_dokter' => $dokterGigi->id,
            'hari' => 'Selasa',
            'jam_mulai' => '13:00',
            'jam_selesai' => '16:00',
            'status' => 'Aktif',
        ]);
        Poli::query()->create([
            'nama_poli' => 'Poli Jantung',
            'keterangan' => 'Pemeriksaan jantung',
        ]);
        Poli::query()->create([
            'nama_poli' => 'Poli Hati',
            'keterangan' => 'Pemeriksaan hati',
        ]);

        $response = $this->actingAs($pasien)
            ->get(route('pasien.daftar-poli.create'))
            ->assertOk()
            ->assertSee('name="id_poli"', false)
            ->assertSee('name="id_jadwal"', false)
            ->assertSee('Poli Umum')
            ->assertSee('Poli Gigi')
            ->assertSee('Poli Jantung')
            ->assertSee('Poli Hati')
            ->assertSee($dokter->nama)
            ->assertSee($dokterGigi->nama)
            ->assertSee('Senin 08:00–12:00')
            ->assertSee('Selasa 13:00–16:00')
            ->assertDontSee('Rabu 14:00–16:00');
        $this->assertStringNotContainsString('Data Poli</a>', $response->getContent());

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('pasien.daftar-poli.create'))
            ->assertForbidden();
    }

    public function test_seeded_doctor_schedules_are_available_for_patient_registration(): void
    {
        Event::fake([QueueUpdated::class]);
        $this->seed(UserSeeder::class);

        $pasien = User::query()->where('email', 'pasien@gmail.com')->firstOrFail();
        $dokter = User::query()->where('email', 'dokter@gmail.com')->firstOrFail();
        $jadwal = JadwalPeriksa::query()
            ->where('id_dokter', $dokter->id)
            ->where('status', 'Aktif')
            ->firstOrFail();

        $this->assertSame('Jantung', $dokter->poli->nama_poli);

        $this->actingAs($pasien)
            ->get(route('pasien.daftar-poli.create'))
            ->assertOk()
            ->assertSee($dokter->nama)
            ->assertSee($jadwal->hari.' '.substr($jadwal->jam_mulai, 0, 5).'–'.substr($jadwal->jam_selesai, 0, 5));

        $this->actingAs($pasien)
            ->post(route('pasien.daftar-poli.store'), [
                'id_poli' => $dokter->id_poli,
                'id_jadwal' => $jadwal->id,
                'keluhan' => 'Pemeriksaan rutin',
            ])
            ->assertRedirect(route('pasien.dashboard'));

        $this->assertDatabaseHas('daftar_poli', [
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'no_antrian' => 1,
        ]);
    }

    public function test_patient_cannot_submit_schedule_from_a_different_poli(): void
    {
        Event::fake([QueueUpdated::class]);
        [$pasien, , $jadwal] = $this->clinicSetup();
        $poliLain = Poli::query()->create([
            'nama_poli' => 'Poli Gigi',
            'keterangan' => 'Pemeriksaan gigi',
        ]);

        $this->actingAs($pasien)
            ->from(route('pasien.daftar-poli.create'))
            ->post(route('pasien.daftar-poli.store'), [
                'id_poli' => $poliLain->id,
                'id_jadwal' => $jadwal->id,
                'keluhan' => 'Sakit gigi',
            ])
            ->assertRedirect(route('pasien.daftar-poli.create'))
            ->assertSessionHasErrors('id_jadwal');

        $this->assertDatabaseCount('daftar_poli', 0);
    }

    public function test_admin_can_add_schedule_for_a_doctor(): void
    {
        [, $dokter] = $this->clinicSetup();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dokter.jadwal', $dokter))
            ->assertOk()
            ->assertSee($dokter->nama)
            ->assertSee('Tambah Jadwal Praktik');

        $response = $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.dokter.jadwal.store', $dokter), [
                'hari' => 'Rabu',
                'jam_mulai' => '09:00',
                'jam_selesai' => '13:00',
            ])
            ->assertOk();
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'Jadwal dokter berhasil ditambahkan.'),
        );

        $this->assertDatabaseHas('jadwal_periksa', [
            'id_dokter' => $dokter->id,
            'hari' => 'Rabu',
            'jam_mulai' => '09:00',
            'jam_selesai' => '13:00',
            'status' => 'Aktif',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.dokter.jadwal.store', $dokter), [
                'hari' => 'Rabu',
                'jam_mulai' => '13:00',
                'jam_selesai' => '09:00',
            ])
            ->assertSessionHasErrors('jam_selesai');

        $this->actingAs($dokter)
            ->get(route('admin.dokter.jadwal', $dokter))
            ->assertForbidden();
    }

    public function test_admin_can_create_doctor_with_poli_and_login_details(): void
    {
        $poli = Poli::query()->create([
            'nama_poli' => 'Poli Gigi',
            'keterangan' => 'Pemeriksaan gigi',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('dokter.create'))
            ->assertOk()
            ->assertSee('Tambah Dokter')
            ->assertSee('name="nama"', false)
            ->assertSee('name="id_poli"', false)
            ->assertSee('name="email"', false);

        $this->actingAs($admin)
            ->post(route('dokter.store'), [
                'nama' => 'Dr. Gigi',
                'alamat' => 'Jl. Sehat 10',
                'no_ktp' => '1234567890123456',
                'no_hp' => '081234567890',
                'id_poli' => $poli->id,
                'email' => 'dokter.gigi@example.test',
                'password' => 'rahasia123',
            ])
            ->assertRedirect(route('dokter.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'nama' => 'Dr. Gigi',
            'role' => 'dokter',
            'id_poli' => $poli->id,
            'email' => 'dokter.gigi@example.test',
        ]);
        $this->assertTrue(Hash::check(
            'rahasia123',
            User::query()->where('email', 'dokter.gigi@example.test')->value('password'),
        ));
    }

    public function test_admin_can_edit_doctor_details_and_optionally_change_password(): void
    {
        [$pasien, $dokter] = $this->clinicSetup();
        $poliBaru = Poli::query()->create([
            'nama_poli' => 'Poli Gigi',
            'keterangan' => 'Pemeriksaan gigi',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('dokter.edit', $dokter))
            ->assertOk()
            ->assertSee('Edit Dokter')
            ->assertSee('value="'.$dokter->nama.'"', false)
            ->assertSee('name="password"', false);

        $this->actingAs($admin)
            ->put(route('dokter.update', $dokter), [
                'nama' => 'Dr. Dokter Diperbarui',
                'alamat' => 'Alamat baru',
                'no_ktp' => '1234567890123456',
                'no_hp' => '081111222233',
                'id_poli' => $poliBaru->id,
                'email' => $dokter->email,
                'password' => 'password-baru',
            ])
            ->assertRedirect(route('dokter.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $dokter->id,
            'nama' => 'Dr. Dokter Diperbarui',
            'alamat' => 'Alamat baru',
            'no_ktp' => '1234567890123456',
            'no_hp' => '081111222233',
            'id_poli' => $poliBaru->id,
        ]);
        $this->assertTrue(Hash::check('password-baru', $dokter->fresh()->password));

        $this->actingAs($pasien)
            ->get(route('dokter.edit', $dokter))
            ->assertForbidden();
    }

    public function test_profile_page_keeps_role_sidebar_visible(): void
    {
        [$pasien, $dokter] = $this->clinicSetup();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($pasien)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Dashboard Pasien')
            ->assertSee('Ganti Password');

        $this->actingAs($dokter)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Dashboard Dokter')
            ->assertSee('Ganti Password');

        $this->actingAs($admin)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Ganti Password');
    }

    /**
     * @return array{User, User, JadwalPeriksa}
     */
    private function clinicSetup(): array
    {
        $poli = Poli::query()->create([
            'nama_poli' => 'Poli Umum',
            'keterangan' => 'Pemeriksaan umum',
        ]);
        $dokter = User::factory()->create([
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);
        $pasien = User::factory()->create(['role' => 'pasien']);
        $jadwal = JadwalPeriksa::query()->create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
            'status' => 'Aktif',
        ]);

        return [$pasien, $dokter, $jadwal];
    }
}
