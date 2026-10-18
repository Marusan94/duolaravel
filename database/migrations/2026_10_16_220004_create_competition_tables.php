<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('tier')->default(1);
            $table->string('season', 20);
            $table->date('week_start');
            $table->timestamps();
            $table->unique(['tier', 'season', 'week_start']);
        });

        Schema::create('league_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('weekly_xp')->default(0);
            $table->unsignedInteger('rank')->nullable();
            $table->timestamps();
            $table->unique(['league_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_members');
        Schema::dropIfExists('leagues');
    }
};
