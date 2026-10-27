<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function index(Request $request): View
    {
        $teams = Team::query()->orderBy('sort_order')->get();
        $teamId = (int) $request->query('team_id', $teams->first()?->id);

        return view('panitia.participants.index', [
            'teams' => $teams,
            'teamId' => $teamId,
            'participants' => Participant::query()
                ->where('team_id', $teamId)
                ->orderByDesc('is_captain')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
            'name' => ['required', 'string', 'max:100'],
            'is_captain' => ['nullable', 'boolean'],
        ]);

        $isCaptain = $request->boolean('is_captain');
        $this->guardCaptainLimit((int) $data['team_id'], $isCaptain);

        Participant::query()->create([
            'team_id' => $data['team_id'],
            'name' => $data['name'],
            'is_player' => true,
            'is_personil' => true,
            'is_captain' => $isCaptain,
        ]);

        return redirect()
            ->route('panitia.participants.index', ['team_id' => $data['team_id']])
            ->with('status', 'Peserta sudah ditambahkan.');
    }

    public function update(Request $request, Participant $participant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_captain' => ['nullable', 'boolean'],
        ]);

        $isCaptain = $request->boolean('is_captain');
        $this->guardCaptainLimit($participant->team_id, $isCaptain, $participant->id);

        $participant->update([
            'name' => $data['name'],
            'is_player' => true,
            'is_personil' => true,
            'is_captain' => $isCaptain,
        ]);

        return redirect()
            ->route('panitia.participants.index', ['team_id' => $participant->team_id])
            ->with('status', 'Data peserta sudah diubah.');
    }

    public function destroy(Participant $participant): RedirectResponse
    {
        $teamId = $participant->team_id;
        $participant->delete();

        return redirect()
            ->route('panitia.participants.index', ['team_id' => $teamId])
            ->with('status', 'Peserta sudah dihapus.');
    }

    private function guardCaptainLimit(int $teamId, bool $isCaptain, ?int $ignoreId = null): void
    {
        if (! $isCaptain) {
            return;
        }

        $captains = Participant::query()
            ->where('team_id', $teamId)
            ->where('is_captain', true)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->count();

        if ($captains >= 2) {
            throw ValidationException::withMessages([
                'is_captain' => 'Maksimal dua kapten per tim.',
            ]);
        }
    }
}
