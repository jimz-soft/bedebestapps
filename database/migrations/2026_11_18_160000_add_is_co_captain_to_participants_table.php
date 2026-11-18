<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->boolean('is_co_captain')->default(false)->after('is_captain');
        });

        $teamIds = DB::table('participants')
            ->where('is_captain', true)
            ->distinct()
            ->pluck('team_id');

        foreach ($teamIds as $teamId) {
            $captains = DB::table('participants')
                ->where('team_id', $teamId)
                ->where('is_captain', true)
                ->orderBy('id')
                ->get(['id']);

            if ($captains->count() < 2) {
                continue;
            }

            $captains
                ->slice(1)
                ->values()
                ->each(function ($participant, int $index): void {
                    DB::table('participants')->where('id', $participant->id)->update([
                        'is_captain' => false,
                        'is_co_captain' => $index === 0,
                    ]);
                });
        }
    }

    public function down(): void
    {
        DB::table('participants')
            ->where('is_co_captain', true)
            ->update([
                'is_captain' => true,
                'is_co_captain' => false,
            ]);

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('is_co_captain');
        });
    }
};
