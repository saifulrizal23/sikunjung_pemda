<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Kegiatan;
use App\Models\Kunjungan;
use App\Models\Pemohon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_log_in_and_reach_the_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'correct-password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'correct-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Administrator');
    }

    public function test_invalid_login_shows_a_validation_error(): void
    {
        User::factory()->create([
            'email' => 'admin@example.test',
            'password' => 'correct-password',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'admin@example.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_page_shows_only_a_standalone_sign_in_form(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke akun')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('Kembali ke beranda')
            ->assertDontSee('Navigasi SI Kunjung Pemda')
            ->assertDontSee('Akun demo');
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_sidebar_exposes_public_and_dashboard_pages(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Navigasi SI Kunjung Pemda')
            ->assertSee(route('home'))
            ->assertSee(route('jadwal'))
            ->assertSee(route('struktur'))
            ->assertSee(route('profile.edit'))
            ->assertSee(route('dashboard'))
            ->assertSee(route('logout'))
            ->assertSee('aria-current="page"', false)
            ->assertSee('data-bs-toggle="offcanvas"', false);
    }

    public function test_administrator_profile_appears_once_and_logout_uses_neutral_styling(): void
    {
        $user = User::factory()->create([
            'name' => 'Administrator',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('class="profile-card"', false)
            ->assertSee('class="profile-name d-block"', false)
            ->assertDontSee('class="topbar-user"', false)
            ->assertDontSee('class="profile-role"', false)
            ->assertSee('linear-gradient(135deg, #eef1f4, #d6dde3)', false)
            ->assertDontSee('#a83246', false)
            ->assertDontSee('#c23e54', false);
    }

    public function test_applicant_dashboard_only_shows_their_visits(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PEMOHON]);
        $pemohon = Pemohon::create([
            'user_id' => $user->id,
            'nik' => '3201000000000001',
            'nama' => 'Dedi',
        ]);
        $otherPemohon = Pemohon::create([
            'nik' => '3201000000000002',
            'nama' => 'Nadia',
        ]);
        $karyawan = Karyawan::create([
            'nik' => '3201000000000003',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);

        Kunjungan::create([
            'pemohon_id' => $pemohon->id,
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2026-11-07',
            'jam' => '10:00:00',
            'keperluan' => 'Agenda milik Dedi',
        ]);
        Kunjungan::create([
            'pemohon_id' => $otherPemohon->id,
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2026-11-08',
            'jam' => '11:00:00',
            'keperluan' => 'Agenda rahasia Nadia',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Agenda milik Dedi')
            ->assertDontSee('Agenda rahasia Nadia');
    }

    public function test_employee_dashboard_only_shows_visits_addressed_to_them(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $karyawan = Karyawan::create([
            'user_id' => $user->id,
            'nik' => '3201000000000001',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $otherKaryawan = Karyawan::create([
            'nik' => '3201000000000002',
            'nama' => 'Andi',
            'jabatan' => 'Staf',
        ]);
        $pemohon = Pemohon::create([
            'nik' => '3201000000000003',
            'nama' => 'Dedi',
        ]);

        Kunjungan::create([
            'pemohon_id' => $pemohon->id,
            'karyawan_id' => $karyawan->id,
            'tanggal' => '2026-11-07',
            'jam' => '10:00:00',
            'keperluan' => 'Kunjungan untuk Rina',
        ]);
        Kunjungan::create([
            'pemohon_id' => $pemohon->id,
            'karyawan_id' => $otherKaryawan->id,
            'tanggal' => '2026-11-08',
            'jam' => '11:00:00',
            'keperluan' => 'Kunjungan untuk Andi',
        ]);
        Kegiatan::create([
            'karyawan_id' => $karyawan->id,
            'jabatan' => $karyawan->jabatan,
            'tanggal_awal' => '2026-11-09',
            'tanggal_akhir' => '2026-11-09',
            'deskripsi_kegiatan' => 'Rapat koordinasi',
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard Karyawan')
            ->assertSee('Kunjungan untuk Rina')
            ->assertDontSee('Kunjungan untuk Andi')
            ->assertSee('Kegiatan saya');
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
