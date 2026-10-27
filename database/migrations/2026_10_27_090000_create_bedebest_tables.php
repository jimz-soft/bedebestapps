<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('venue')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('logo_icon_path')->nullable();
            $table->string('hero_banner_path')->nullable();
            $table->string('hero_kicker')->nullable();
            $table->string('hero_highlight')->nullable();
            $table->text('hero_body')->nullable();
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code', 8);
            $table->string('color', 16)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('motto')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->string('role')->default('panitia');
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('format');
            $table->boolean('requires_roster')->default(false);
            $table->boolean('uses_period_score')->default(false);
            $table->string('period_label')->nullable();
            $table->unsignedTinyInteger('period_count_default')->nullable();
            $table->string('score_mode')->nullable();
            $table->unsignedTinyInteger('heat_size')->nullable();
            $table->longText('rules_html')->nullable();
            $table->string('banner_path')->nullable();
            $table->timestamps();
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_player')->default(false);
            $table->boolean('is_personil')->default(false);
            $table->boolean('is_captain')->default(false);
            $table->timestamps();
        });

        Schema::create('game_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unique(['game_id', 'team_id', 'participant_id']);
            $table->timestamps();
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('round_label')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('upcoming');
            $table->string('banner_path')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('bracket_node_id')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('match_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedInteger('points_awarded')->default(0);
            $table->unsignedInteger('poin_personil')->default(0);
            $table->unsignedInteger('bonus_pemenang')->default(0);
            $table->string('bonus_keterangan')->nullable();
            $table->string('result')->nullable();
            $table->unsignedSmallInteger('score_cache')->nullable();
            $table->unique(['match_id', 'team_id']);
            $table->timestamps();
        });

        Schema::create('match_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('period_no');
            $table->string('status')->default('pending');
            $table->unique(['match_id', 'period_no']);
            $table->timestamps();
        });

        Schema::create('match_period_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unique(['match_period_id', 'team_id']);
            $table->timestamps();
        });

        Schema::create('bracket_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('round_no');
            $table->unsignedTinyInteger('slot_no');
            $table->foreignId('match_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('home_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('away_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedBigInteger('next_node_id')->nullable();
            $table->unique(['game_id', 'round_no', 'slot_no']);
            $table->timestamps();
        });

        Schema::table('bracket_nodes', function (Blueprint $table) {
            $table->foreign('next_node_id')->references('id')->on('bracket_nodes')->nullOnDelete();
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreign('bracket_node_id')->references('id')->on('bracket_nodes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['bracket_node_id']);
        });

        Schema::dropIfExists('bracket_nodes');
        Schema::dropIfExists('match_period_scores');
        Schema::dropIfExists('match_periods');
        Schema::dropIfExists('match_teams');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('game_rosters');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('games');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
            $table->dropColumn(['username', 'role']);
        });

        Schema::dropIfExists('teams');
        Schema::dropIfExists('settings');
    }
};
