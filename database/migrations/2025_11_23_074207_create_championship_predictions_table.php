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
        Schema::create('championship_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->integer('week_number');
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->decimal('win_probability', 5);
            $table->timestamps();

            $table->unique(['season_id', 'week_number', 'team_id']);
            $table->index(['season_id', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('championship_predictions');
    }
};
