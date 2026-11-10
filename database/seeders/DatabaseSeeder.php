<?php

namespace Database\Seeders;

use App\Models\Kegiatan;
use App\Models\Karyawan;
use App\Models\Kunjungan;
use App\Models\Pemohon;
use App\Models\SettingKunjungan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@sikunjung.test'], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $karyawanUser = User::updateOrCreate(['email' => 'rina@sikunjung.test'], [
            'name' => 'Rina',
            'password' => Hash::make('password'),
            'role' => User::ROLE_KARYAWAN,
        ]);

        $pemohonUser = User::updateOrCreate(['email' => 'dedi@sikunjung.test'], [
            'name' => 'Dedi',
            'password' => Hash::make('password'),
            'role' => User::ROLE_PEMOHON,
        ]);

        $karyawan = Karyawan::updateOrCreate(['user_id' => $karyawanUser->id], [
            'nik' => '3201000000000001',
            'nama' => 'Rina',
            'jabatan' => 'Sekretaris Dinas',
            'no_telepon' => '081234567890',
        ]);

        $pemohon = Pemohon::updateOrCreate(['user_id' => $pemohonUser->id], [
            'nik' => '3201000000000002',
            'nama' => 'Dedi',
            'no_telepon' => '081298765432',
            'alamat' => 'Bandung',
        ]);

        $karyawanAndi = Karyawan::updateOrCreate(['nik' => '3201000000000003'], [
            'nama' => 'Andi Pratama',
            'jabatan' => 'Kepala Bidang Pelayanan',
            'no_telepon' => '081234567891',
        ]);
        $karyawanSiti = Karyawan::updateOrCreate(['nik' => '3201000000000004'], [
            'nama' => 'Siti Rahmawati',
            'jabatan' => 'Analis Kebijakan',
            'no_telepon' => '081234567892',
        ]);

        $pemohonNadia = Pemohon::updateOrCreate(['nik' => '3201000000000005'], [
            'nama' => 'Nadia Putri',
            'no_telepon' => '081298765433',
            'alamat' => 'Cimahi',
        ]);
        $pemohonBudi = Pemohon::updateOrCreate(['nik' => '3201000000000006'], [
            'nama' => 'Budi Santoso',
            'no_telepon' => '081298765434',
            'alamat' => 'Sumedang',
        ]);
        $pemohonMaya = Pemohon::updateOrCreate(['nik' => '3201000000000007'], [
            'nama' => 'Maya Lestari',
            'no_telepon' => '081298765435',
            'alamat' => 'Garut',
        ]);

        $schedules = [
            [$karyawan, today()->addDay(), '09:00:00', '15:00:00'],
            [$karyawanAndi, today()->addDays(2), '09:30:00', '15:30:00'],
            [$karyawanSiti, today()->addDays(3), '08:30:00', '14:30:00'],
            [$karyawan, today()->addDays(4), '09:00:00', '15:00:00'],
        ];

        foreach ($schedules as [$employee, $date, $startTime, $endTime]) {
            SettingKunjungan::updateOrCreate([
                'karyawan_id' => $employee->id,
                'tanggal' => $date,
            ], [
                'jabatan' => $employee->jabatan,
                'jam_aktif' => $startTime,
                'jam_non_aktif' => $endTime,
                'status' => 'Y',
            ]);
        }

        $visits = [
            [$pemohon, $karyawan, today()->addDay(), '10:00:00', 'Konsultasi layanan', Kunjungan::STATUS_MENUNGGU, null],
            [$pemohonNadia, $karyawanAndi, today()->addDays(2), '11:00:00', 'Koordinasi program daerah', Kunjungan::STATUS_DISETUJUI, 'Silakan membawa dokumen pendukung.'],
            [$pemohonBudi, $karyawanSiti, today()->addDays(3), '13:00:00', 'Konsultasi kebijakan publik', Kunjungan::STATUS_MENUNGGU, null],
            [$pemohonMaya, $karyawan, today()->addDays(4), '10:30:00', 'Permohonan informasi layanan', Kunjungan::STATUS_DITOLAK, 'Jadwal perlu diajukan kembali.'],
        ];

        foreach ($visits as [$applicant, $employee, $date, $time, $purpose, $status, $note]) {
            Kunjungan::updateOrCreate([
                'pemohon_id' => $applicant->id,
                'karyawan_id' => $employee->id,
                'tanggal' => $date,
                'jam' => $time,
            ], [
                'keperluan' => $purpose,
                'status' => $status,
                'catatan' => $note,
            ]);
        }

        $activities = [
            [$karyawan, today()->addDays(3), 'Rapat koordinasi pelayanan publik'],
            [$karyawanAndi, today()->addDays(5), 'Evaluasi standar pelayanan daerah'],
            [$karyawanSiti, today()->addDays(7), 'Sosialisasi kebijakan pelayanan'],
        ];

        foreach ($activities as [$employee, $date, $description]) {
            Kegiatan::updateOrCreate([
                'karyawan_id' => $employee->id,
                'tanggal_awal' => $date,
                'tanggal_akhir' => $date,
            ], [
                'jabatan' => $employee->jabatan,
                'deskripsi_kegiatan' => $description,
            ]);
        }
    }
}
