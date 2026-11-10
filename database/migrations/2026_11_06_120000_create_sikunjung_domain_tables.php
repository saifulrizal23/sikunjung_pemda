<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('no_telepon', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('pemohon', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->string('no_telepon', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });

        Schema::create('setting_kunjungan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->string('jabatan');
            $table->date('tanggal');
            $table->time('jam_aktif');
            $table->time('jam_non_aktif');
            $table->char('status', 1)->default('Y');
            $table->unique(['karyawan_id', 'tanggal']);
            $table->timestamps();
        });

        Schema::create('kunjungan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pemohon_id')->constrained('pemohon')->cascadeOnDelete();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam');
            $table->string('keperluan');
            $table->string('status', 10)->default('menunggu');
            $table->text('catatan')->nullable();
            $table->unique(['pemohon_id', 'karyawan_id', 'tanggal', 'jam']);
            $table->timestamps();
        });

        Schema::create('kegiatan', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawan')->cascadeOnDelete();
            $table->string('jabatan');
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->text('deskripsi_kegiatan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
        Schema::dropIfExists('kunjungan');
        Schema::dropIfExists('setting_kunjungan');
        Schema::dropIfExists('pemohon');
        Schema::dropIfExists('karyawan');
    }
};
