<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\SettingKunjungan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_service_summary(): void
    {
        Karyawan::create([
            'nik' => '3201000000000001',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Selamat datang di SI Kunjung Pemda')
            ->assertSee('Navigasi publik')
            ->assertSee(route('jadwal'))
            ->assertSee(route('struktur'))
            ->assertSee(route('login'))
            ->assertDontSee('Navigasi SI Kunjung Pemda')
            ->assertSee('Sekretaris Dinas');
    }

    public function test_schedule_page_filters_by_employee_name_and_status(): void
    {
        $rina = Karyawan::create([
            'nik' => '3201000000000001',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $andi = Karyawan::create([
            'nik' => '3201000000000002',
            'nama' => 'Andi',
            'jabatan' => 'Staf',
        ]);

        SettingKunjungan::create([
            'karyawan_id' => $rina->id,
            'jabatan' => $rina->jabatan,
            'status' => 'Y',
            'tanggal' => '2026-11-07',
            'jam_aktif' => '09:00:00',
            'jam_non_aktif' => '15:00:00',
        ]);
        SettingKunjungan::create([
            'karyawan_id' => $andi->id,
            'jabatan' => $andi->jabatan,
            'status' => 'T',
            'tanggal' => '2026-11-08',
            'jam_aktif' => '10:00:00',
            'jam_non_aktif' => '16:00:00',
        ]);

        $this->get('/jadwal?search=Rina&status=Y')
            ->assertOk()
            ->assertSee('Rina')
            ->assertDontSee('Andi');
    }

    public function test_organization_page_loads_without_employee_data(): void
    {
        $this->get('/struktur')
            ->assertOk()
            ->assertSee('Data karyawan belum tersedia.');
    }
}
