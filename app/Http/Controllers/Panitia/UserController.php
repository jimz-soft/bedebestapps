<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('panitia.users.index', [
            'users' => User::query()->with('team')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('panitia.users.create', [
            'teams' => Team::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['username'].'@bedebest.local',
            'password' => $data['password'],
            'role' => $data['role'],
            'team_id' => $data['role'] === User::ROLE_USER ? $data['team_id'] : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('panitia.users.index')->with('status', 'User baru sudah disimpan.');
    }

    public function edit(User $user): View
    {
        return view('panitia.users.edit', [
            'user' => $user,
            'teams' => Team::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        $user->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'role' => $data['role'],
            'team_id' => $data['role'] === User::ROLE_USER ? $data['team_id'] : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        if ($user->isAdministrator() === false || $user->is_active !== true) {
            $remainingAdmins = User::query()
                ->where('role', User::ROLE_ADMINISTRATOR)
                ->where('is_active', true)
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingAdmins === 0) {
                return back()->withErrors([
                    'role' => 'Minimal satu administrator aktif harus tetap ada.',
                ]);
            }
        }

        $user->save();

        return redirect()->route('panitia.users.index')->with('status', 'Data user sudah diubah.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?User $user = null): array
    {
        $passwordRule = $user ? ['nullable', 'string', 'min:6'] : ['required', 'string', 'min:6'];

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'password' => $passwordRule,
            'role' => ['required', Rule::in([User::ROLE_ADMINISTRATOR, User::ROLE_USER])],
            'team_id' => ['nullable', 'required_if:role,'.User::ROLE_USER, 'exists:teams,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
