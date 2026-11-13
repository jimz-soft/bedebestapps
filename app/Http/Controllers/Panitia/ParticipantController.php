<?php

namespace App\Http\Controllers\Panitia;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
            'name' => ['nullable', 'string', 'max:100'],
            'names' => ['nullable', 'string'],
            'import_file' => ['nullable', 'file', 'mimes:txt,csv,text/plain', 'max:2048'],
            'is_captain' => ['nullable', 'boolean'],
        ]);

        $teamId = (int) $data['team_id'];
        $names = $this->collectNames($request);

        if ($names->isEmpty()) {
            throw ValidationException::withMessages([
                'names' => 'Isi minimal satu nama peserta, atau unggah file import (.txt / .csv).',
            ]);
        }

        $existing = Participant::query()
            ->where('team_id', $teamId)
            ->pluck('name')
            ->map(fn (string $name) => mb_strtolower($name));

        $toCreate = $names
            ->reject(fn (string $name) => $existing->contains(mb_strtolower($name)))
            ->values();

        if ($toCreate->isEmpty()) {
            throw ValidationException::withMessages([
                'names' => 'Semua nama sudah ada di grup ini.',
            ]);
        }

        $markCaptain = $request->boolean('is_captain') && $toCreate->count() === 1;

        if ($markCaptain) {
            $this->guardCaptainLimit($teamId, true);
        }

        foreach ($toCreate as $index => $name) {
            Participant::query()->create([
                'team_id' => $teamId,
                'name' => $name,
                'is_player' => true,
                'is_personil' => true,
                'is_captain' => $markCaptain && $index === 0,
            ]);
        }

        $skipped = $names->count() - $toCreate->count();
        $status = $toCreate->count().' peserta ditambahkan.';
        if ($skipped > 0) {
            $status .= ' '.$skipped.' nama dilewati karena sudah ada.';
        }

        return redirect()
            ->route('panitia.participants.index', ['team_id' => $teamId])
            ->with('status', $status);
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

    /**
     * @return Collection<int, string>
     */
    private function collectNames(Request $request): Collection
    {
        $names = collect();

        if ($request->filled('names')) {
            $names = $names->merge($this->parseNameBlob((string) $request->input('names')));
        }

        if ($request->filled('name')) {
            $names->push(trim((string) $request->input('name')));
        }

        if ($request->hasFile('import_file')) {
            $path = $request->file('import_file')?->getRealPath();

            if (is_string($path) && is_file($path)) {
                $content = (string) file_get_contents($path);
                $names = $names->merge($this->parseNameBlob($content));
            }
        }

        return $names
            ->map(function (string $name) {
                $clean = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

                return mb_substr($clean, 0, 100);
            })
            ->filter(fn (string $name) => $name !== '')
            ->unique(fn (string $name) => mb_strtolower($name))
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function parseNameBlob(string $blob): Collection
    {
        $blob = preg_replace('/^\xEF\xBB\xBF/', '', $blob) ?? $blob;
        $lines = preg_split('/\r\n|\r|\n/', $blob) ?: [];
        $names = collect();

        foreach ($lines as $index => $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if ($index === 0 && preg_match('/^(nama|name|peserta)\b/i', $line) === 1) {
                continue;
            }

            if (str_contains($line, ',') || str_contains($line, ';') || str_contains($line, "\t")) {
                $delimiter = str_contains($line, "\t") ? "\t" : (str_contains($line, ';') ? ';' : ',');
                $parts = str_getcsv($line, $delimiter);
                $first = trim((string) ($parts[0] ?? ''));

                if ($first !== '') {
                    $names->push($first);
                }

                continue;
            }

            $names->push($line);
        }

        return $names;
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
