<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_sign_in_from_profile_routes(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));

        $this->patch(route('profile.update'), [])
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_their_profile_form(): void
    {
        $user = User::factory()->create([
            'name' => 'Reza Admin',
            'email' => 'reza@example.test',
        ]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Profil saya')
            ->assertSee('Reza Admin')
            ->assertSee('reza@example.test')
            ->assertSee(route('profile.update'));
    }

    public function test_user_can_update_profile_without_changing_their_role_or_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Reza Admin',
            'email' => 'reza@example.test',
            'password' => 'old-password',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Reza Pratama',
                'email' => 'reza.pratama@example.test',
                'role' => User::ROLE_PEMOHON,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Profil berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Reza Pratama',
            'email' => 'reza.pratama@example.test',
            'role' => User::ROLE_ADMIN,
        ]);
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_user_can_change_their_password_with_the_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'old-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_profile_update_rejects_an_incorrect_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => 'incorrect-password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors([
                'current_password' => 'Kata sandi saat ini tidak sesuai.',
            ]);

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_profile_update_rejects_a_duplicate_email_address(): void
    {
        $user = User::factory()->create(['email' => 'reza@example.test']);
        User::factory()->create(['email' => 'other@example.test']);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => 'Reza Admin',
                'email' => 'other@example.test',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'reza@example.test',
        ]);
    }
}
