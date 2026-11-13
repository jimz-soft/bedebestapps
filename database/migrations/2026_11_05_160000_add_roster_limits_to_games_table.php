<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedTinyInteger('roster_min')->nullable()->after('requires_roster');
            $table->unsignedTinyInteger('roster_max')->nullable()->after('roster_min');
        });

        DB::table('games')
            ->where('requires_roster', true)
            ->update([
                'roster_min' => 1,
                'roster_max' => 11,
            ]);
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['roster_min', 'roster_max']);
        });
    }
};
