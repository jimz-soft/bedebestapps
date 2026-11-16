<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_profile_and_change_own_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'panitia')->firstOrFail();
        $admin->password = 'lama123';
        $admin->save();

        $this->actingAs($admin)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertSee($admin->username)
            ->assertSee('Ubah password');

        $this->actingAs($admin)
            ->put(route('profile.password'), [
                'current_password' => 'lama123',
                'password' => 'baru456',
                'password_confirmation' => 'baru456',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('baru456', $admin->fresh()->password));
    }

    public function test_member_can_view_profile_and_change_own_password(): void
    {
        $this->seed(DatabaseSeeder::class);
        $team = Team::query()->firstOrFail();
        $member = User::factory()->member()->create([
            'name' => 'User Achilles',
            'username' => 'user_achilles',
            'password' => 'lama123',
            'team_id' => $team->id,
        ]);

        $this->actingAs($member)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('User Achilles')
            ->assertSee('user_achilles')
            ->assertSee($team->name);

        $this->actingAs($member)
            ->from(route('profile.edit'))
            ->put(route('profile.password'), [
                'current_password' => 'salah',
                'password' => 'baru456',
                'password_confirmation' => 'baru456',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->actingAs($member)
            ->put(route('profile.password'), [
                'current_password' => 'lama123',
                'password' => 'baru456',
                'password_confirmation' => 'baru456',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('baru456', $member->fresh()->password));
    }

    public function test_guest_cannot_open_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }
}
