<?php

namespace Tests\Feature;

use App\Models\Obat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObatCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_edit_and_delete_obat(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('obat.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('obat.store'), [
                'nama_obat' => 'Paracetamol',
                'kemasan' => 'Strip',
                'harga' => 5000,
            ])
            ->assertRedirect(route('obat.index'));

        $obat = Obat::query()->firstOrFail();
        $this->assertSame('Paracetamol', $obat->nama_obat);

        $this->actingAs($admin)
            ->get(route('obat.edit', $obat))
            ->assertOk()
            ->assertSee('Paracetamol');

        $this->actingAs($admin)
            ->put(route('obat.update', $obat), [
                'nama_obat' => 'Paracetamol 500 mg',
                'kemasan' => 'Botol',
                'harga' => 7500,
            ])
            ->assertRedirect(route('obat.index'));

        $this->assertDatabaseHas('obat', [
            'id' => $obat->id,
            'nama_obat' => 'Paracetamol 500 mg',
            'kemasan' => 'Botol',
            'harga' => 7500,
        ]);

        $this->actingAs($admin)
            ->get(route('obat.index'))
            ->assertOk()
            ->assertSee('Rp 7.500');

        $this->actingAs($admin)
            ->delete(route('obat.destroy', $obat))
            ->assertRedirect(route('obat.index'));

        $this->assertDatabaseMissing('obat', ['id' => $obat->id]);
    }

    public function test_obat_validation_and_admin_role_are_enforced(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $pasien = User::factory()->create(['role' => 'pasien']);

        $this->actingAs($admin)
            ->from(route('obat.create'))
            ->post(route('obat.store'), [
                'nama_obat' => '',
                'kemasan' => str_repeat('a', 36),
                'harga' => -1,
            ])
            ->assertSessionHasErrors(['nama_obat', 'kemasan', 'harga']);

        $this->actingAs($pasien)->get(route('obat.index'))->assertForbidden();
    }
}
