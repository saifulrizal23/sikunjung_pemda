<?php

namespace Tests\Feature;

use App\Models\Karyawan;
use App\Models\Kegiatan;
use App\Models\Kunjungan;
use App\Models\Pemohon;
use App\Models\SettingKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_linked_employee_account_and_manage_all_data(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Ayu Karyawan',
                'email' => 'ayu@example.test',
                'password' => 'secure-password',
                'role' => User::ROLE_KARYAWAN,
                'nik' => '3201000000000101',
                'jabatan' => 'Analis Layanan',
                'no_telepon' => '081234567801',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'ayu@example.test',
            'role' => User::ROLE_KARYAWAN,
        ]);
        $this->assertDatabaseHas('karyawan', [
            'nik' => '3201000000000101',
            'nama' => 'Ayu Karyawan',
        ]);

        $this->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Akun terdaftar')
            ->assertSee('Data karyawan')
            ->assertSee('Data pemohon')
            ->assertSee('Semua pengajuan kunjungan')
            ->assertSee('Kegiatan instansi');
    }

    public function test_admin_can_attach_an_account_to_an_existing_unlinked_employee_profile(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employee = Karyawan::create([
            'nik' => '3201000000000102',
            'nama' => 'Andi Lama',
            'jabatan' => 'Staf',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Andi Baru',
                'email' => 'andi@example.test',
                'password' => 'secure-password',
                'role' => User::ROLE_KARYAWAN,
                'nik' => '3201000000000102',
                'jabatan' => 'Kepala Bidang',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseCount('karyawan', 1);
        $this->assertDatabaseHas('karyawan', [
            'id' => $employee->id,
            'nama' => 'Andi Baru',
            'jabatan' => 'Kepala Bidang',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'andi@example.test',
            'role' => User::ROLE_KARYAWAN,
        ]);
    }

    public function test_admin_employee_account_updates_keep_linked_name_and_job_titles_synchronized(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employeeUser = User::factory()->create([
            'name' => 'Nama Lama',
            'role' => User::ROLE_KARYAWAN,
        ]);
        $employee = Karyawan::create([
            'user_id' => $employeeUser->id,
            'nik' => '3201000000000103',
            'nama' => 'Nama Lama',
            'jabatan' => 'Jabatan Lama',
        ]);
        $schedule = $employee->schedules()->create([
            'jabatan' => $employee->jabatan,
            'tanggal' => '2026-11-10',
            'jam_aktif' => '09:00:00',
            'jam_non_aktif' => '15:00:00',
            'status' => 'Y',
        ]);
        $activity = $employee->activities()->create([
            'jabatan' => $employee->jabatan,
            'tanggal_awal' => '2026-11-10',
            'tanggal_akhir' => '2026-11-10',
            'deskripsi_kegiatan' => 'Rapat koordinasi',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $employeeUser), [
                'name' => 'Nama Baru',
                'email' => $employeeUser->email,
                'role' => User::ROLE_KARYAWAN,
                'nik' => $employee->nik,
                'jabatan' => 'Jabatan Baru',
                'no_telepon' => '',
                'alamat' => '',
                'password' => '',
            ])
            ->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'id' => $employeeUser->id,
            'name' => 'Nama Baru',
        ]);
        $this->assertDatabaseHas('karyawan', [
            'id' => $employee->id,
            'nama' => 'Nama Baru',
            'jabatan' => 'Jabatan Baru',
        ]);
        $this->assertDatabaseHas('setting_kunjungan', [
            'id' => $schedule->id,
            'jabatan' => 'Jabatan Baru',
        ]);
        $this->assertDatabaseHas('kegiatan', [
            'id' => $activity->id,
            'jabatan' => 'Jabatan Baru',
        ]);
    }

    public function test_employee_and_applicant_cannot_access_admin_management(): void
    {
        $employee = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $applicant = User::factory()->create(['role' => User::ROLE_PEMOHON]);

        $this->actingAs($employee)->get(route('admin.index'))->assertForbidden();
        $this->actingAs($applicant)->get(route('admin.index'))->assertForbidden();
    }

    public function test_applicant_cannot_access_employee_workspace(): void
    {
        $applicant = User::factory()->create(['role' => User::ROLE_PEMOHON]);

        $this->actingAs($applicant)
            ->get(route('employee.workspace'))
            ->assertForbidden();
    }

    public function test_employee_can_create_their_own_schedule_and_cannot_edit_another_employees_schedule(): void
    {
        $employeeUser = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $employee = Karyawan::create([
            'user_id' => $employeeUser->id,
            'nik' => '3201000000000201',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $otherEmployee = Karyawan::create([
            'nik' => '3201000000000202',
            'nama' => 'Andi',
            'jabatan' => 'Staf',
        ]);
        $otherSchedule = SettingKunjungan::create([
            'karyawan_id' => $otherEmployee->id,
            'jabatan' => $otherEmployee->jabatan,
            'tanggal' => '2026-11-10',
            'jam_aktif' => '09:00:00',
            'jam_non_aktif' => '15:00:00',
            'status' => 'Y',
        ]);

        $this->travelTo('2026-11-06 09:00:00');
        $this->actingAs($employeeUser)
            ->post(route('employee.schedules.store'), [
                'tanggal' => '2026-11-09',
                'jam_aktif' => '09:00',
                'jam_non_aktif' => '15:00',
                'status' => 'Y',
            ])
            ->assertRedirect(route('employee.workspace'));

        $this->assertDatabaseHas('setting_kunjungan', [
            'karyawan_id' => $employee->id,
            'tanggal' => '2026-11-09 00:00:00',
        ]);

        $this->actingAs($employeeUser)
            ->patch(route('employee.schedules.update', $otherSchedule), [
                'tanggal' => '2026-11-11',
                'jam_aktif' => '09:00',
                'jam_non_aktif' => '15:00',
                'status' => 'Y',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('setting_kunjungan', [
            'id' => $otherSchedule->id,
            'tanggal' => '2026-11-10 00:00:00',
        ]);
    }

    public function test_employee_can_manage_only_their_own_activities(): void
    {
        $employeeUser = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $employee = Karyawan::create([
            'user_id' => $employeeUser->id,
            'nik' => '3201000000000251',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $otherEmployee = Karyawan::create([
            'nik' => '3201000000000252',
            'nama' => 'Andi',
            'jabatan' => 'Staf',
        ]);
        $ownActivity = $employee->activities()->create([
            'jabatan' => $employee->jabatan,
            'tanggal_awal' => '2026-11-09',
            'tanggal_akhir' => '2026-11-09',
            'deskripsi_kegiatan' => 'Rapat awal',
        ]);
        $otherActivity = $otherEmployee->activities()->create([
            'jabatan' => $otherEmployee->jabatan,
            'tanggal_awal' => '2026-11-10',
            'tanggal_akhir' => '2026-11-10',
            'deskripsi_kegiatan' => 'Kegiatan karyawan lain',
        ]);

        $this->actingAs($employeeUser)
            ->post(route('employee.activities.store'), [
                'tanggal_awal' => '2026-11-11',
                'tanggal_akhir' => '2026-11-11',
                'deskripsi_kegiatan' => 'Rapat baru',
            ])
            ->assertRedirect(route('employee.workspace'));

        $this->assertDatabaseHas('kegiatan', [
            'karyawan_id' => $employee->id,
            'deskripsi_kegiatan' => 'Rapat baru',
        ]);

        $this->actingAs($employeeUser)
            ->patch(route('employee.activities.update', $ownActivity), [
                'tanggal_awal' => '2026-11-09',
                'tanggal_akhir' => '2026-11-10',
                'deskripsi_kegiatan' => 'Rapat diperbarui',
            ])
            ->assertRedirect(route('employee.workspace'));

        $this->actingAs($employeeUser)
            ->patch(route('employee.activities.update', $otherActivity), [
                'tanggal_awal' => '2026-11-10',
                'tanggal_akhir' => '2026-11-10',
                'deskripsi_kegiatan' => 'Perubahan tidak sah',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('kegiatan', [
            'id' => $ownActivity->id,
            'deskripsi_kegiatan' => 'Rapat diperbarui',
        ]);
        $this->assertDatabaseHas('kegiatan', [
            'id' => $otherActivity->id,
            'deskripsi_kegiatan' => 'Kegiatan karyawan lain',
        ]);

        $this->actingAs($employeeUser)
            ->delete(route('employee.activities.destroy', $ownActivity))
            ->assertRedirect(route('employee.workspace'));

        $this->assertDatabaseMissing('kegiatan', ['id' => $ownActivity->id]);
    }

    public function test_employee_can_process_only_a_pending_visit_assigned_to_them(): void
    {
        $employeeUser = User::factory()->create(['role' => User::ROLE_KARYAWAN]);
        $employee = Karyawan::create([
            'user_id' => $employeeUser->id,
            'nik' => '3201000000000301',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $otherEmployee = Karyawan::create([
            'nik' => '3201000000000302',
            'nama' => 'Andi',
            'jabatan' => 'Staf',
        ]);
        $applicant = Pemohon::create([
            'nik' => '3201000000000303',
            'nama' => 'Dedi',
        ]);
        $ownVisit = $this->createVisit($applicant, $employee, '2026-11-09', '10:00:00');
        $otherVisit = $this->createVisit($applicant, $otherEmployee, '2026-11-10', '10:00:00');

        $this->actingAs($employeeUser)
            ->post(route('employee.visits.decide', $ownVisit), [
                'status' => Kunjungan::STATUS_DISETUJUI,
                'catatan' => 'Silakan hadir tepat waktu.',
            ])
            ->assertRedirect(route('employee.workspace'));

        $this->assertDatabaseHas('kunjungan', [
            'id' => $ownVisit->id,
            'status' => Kunjungan::STATUS_DISETUJUI,
        ]);
        $this->assertDatabaseHas('kunjungan', [
            'id' => $otherVisit->id,
            'status' => Kunjungan::STATUS_MENUNGGU,
        ]);

        $this->actingAs($employeeUser)
            ->post(route('employee.visits.decide', $otherVisit), [
                'status' => Kunjungan::STATUS_DITOLAK,
            ])
            ->assertNotFound();
    }

    public function test_applicant_can_submit_and_track_a_visit_for_an_available_schedule(): void
    {
        $this->travelTo('2026-11-06 09:00:00');
        $applicantUser = User::factory()->create(['role' => User::ROLE_PEMOHON]);
        $applicant = Pemohon::create([
            'user_id' => $applicantUser->id,
            'nik' => '3201000000000401',
            'nama' => 'Dedi',
        ]);
        $employee = Karyawan::create([
            'nik' => '3201000000000402',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $schedule = SettingKunjungan::create([
            'karyawan_id' => $employee->id,
            'jabatan' => $employee->jabatan,
            'tanggal' => '2026-11-09',
            'jam_aktif' => '09:00:00',
            'jam_non_aktif' => '15:00:00',
            'status' => 'Y',
        ]);

        $this->actingAs($applicantUser)
            ->post(route('visits.store'), [
                'schedule_id' => $schedule->id,
                'jam' => '10:00',
                'keperluan' => 'Konsultasi layanan',
            ])
            ->assertRedirect(route('visits.index'))
            ->assertSessionHas('status', 'Pengajuan kunjungan berhasil dikirim.');

        $this->assertDatabaseHas('kunjungan', [
            'pemohon_id' => $applicant->id,
            'karyawan_id' => $employee->id,
            'status' => Kunjungan::STATUS_MENUNGGU,
            'keperluan' => 'Konsultasi layanan',
        ]);

        $this->actingAs($applicantUser)
            ->get(route('visits.index'))
            ->assertOk()
            ->assertSee('Konsultasi layanan')
            ->assertSee('Batalkan');
    }

    public function test_applicant_cannot_book_outside_service_hours_or_cancel_another_applicants_visit(): void
    {
        $this->travelTo('2026-11-06 09:00:00');
        $applicantUser = User::factory()->create(['role' => User::ROLE_PEMOHON]);
        $applicant = Pemohon::create([
            'user_id' => $applicantUser->id,
            'nik' => '3201000000000501',
            'nama' => 'Dedi',
        ]);
        $otherApplicant = Pemohon::create([
            'nik' => '3201000000000502',
            'nama' => 'Nadia',
        ]);
        $employee = Karyawan::create([
            'nik' => '3201000000000503',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $schedule = SettingKunjungan::create([
            'karyawan_id' => $employee->id,
            'jabatan' => $employee->jabatan,
            'tanggal' => '2026-11-09',
            'jam_aktif' => '09:00:00',
            'jam_non_aktif' => '15:00:00',
            'status' => 'Y',
        ]);
        $otherVisit = $this->createVisit($otherApplicant, $employee, '2026-11-09', '10:00:00');

        $this->actingAs($applicantUser)
            ->from(route('visits.create'))
            ->post(route('visits.store'), [
                'schedule_id' => $schedule->id,
                'jam' => '15:00',
                'keperluan' => 'Konsultasi layanan',
            ])
            ->assertRedirect(route('visits.create'))
            ->assertSessionHasErrors('jam');

        $this->assertDatabaseMissing('kunjungan', [
            'pemohon_id' => $applicant->id,
            'karyawan_id' => $employee->id,
        ]);

        $this->actingAs($applicantUser)
            ->delete(route('visits.destroy', $otherVisit))
            ->assertNotFound();

        $this->assertDatabaseHas('kunjungan', [
            'id' => $otherVisit->id,
            'status' => Kunjungan::STATUS_MENUNGGU,
        ]);
    }

    public function test_applicant_can_cancel_only_their_own_pending_visit(): void
    {
        $applicantUser = User::factory()->create(['role' => User::ROLE_PEMOHON]);
        $applicant = Pemohon::create([
            'user_id' => $applicantUser->id,
            'nik' => '3201000000000601',
            'nama' => 'Dedi',
        ]);
        $employee = Karyawan::create([
            'nik' => '3201000000000602',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $pendingVisit = $this->createVisit($applicant, $employee, '2026-11-09', '10:00:00');

        $this->actingAs($applicantUser)
            ->delete(route('visits.destroy', $pendingVisit))
            ->assertRedirect(route('visits.index'));

        $this->assertDatabaseMissing('kunjungan', ['id' => $pendingVisit->id]);
    }

    public function test_applicant_cannot_cancel_a_visit_after_it_has_been_processed(): void
    {
        $applicantUser = User::factory()->create(['role' => User::ROLE_PEMOHON]);
        $applicant = Pemohon::create([
            'user_id' => $applicantUser->id,
            'nik' => '3201000000000701',
            'nama' => 'Dedi',
        ]);
        $employee = Karyawan::create([
            'nik' => '3201000000000702',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
        ]);
        $processedVisit = $this->createVisit($applicant, $employee, '2026-11-09', '10:00:00');
        $processedVisit->update(['status' => Kunjungan::STATUS_DISETUJUI]);

        $this->actingAs($applicantUser)
            ->delete(route('visits.destroy', $processedVisit))
            ->assertNotFound();

        $this->assertDatabaseHas('kunjungan', [
            'id' => $processedVisit->id,
            'status' => Kunjungan::STATUS_DISETUJUI,
        ]);
    }

    private function createVisit(Pemohon $applicant, Karyawan $employee, string $date, string $time): Kunjungan
    {
        return Kunjungan::query()->create([
            'pemohon_id' => $applicant->id,
            'karyawan_id' => $employee->id,
            'tanggal' => $date,
            'jam' => $time,
            'keperluan' => 'Konsultasi layanan',
            'status' => Kunjungan::STATUS_MENUNGGU,
        ]);
    }
}
