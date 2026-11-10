<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_is_complete_and_can_be_seeded_more_than_once(): void
    {
        $this->travelTo('2026-11-06 12:00:00');

        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('karyawan', 3);
        $this->assertDatabaseCount('pemohon', 4);
        $this->assertDatabaseCount('setting_kunjungan', 4);
        $this->assertDatabaseCount('kunjungan', 4);
        $this->assertDatabaseCount('kegiatan', 3);
    }
}
