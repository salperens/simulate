<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Fixture Model
 *
 * Represents a single match fixture in a season.
 * Each fixture belongs to a season and has home/away teams.
 *
 * @property int $id
 * @property int $season_id
 * @property int $week_number
 * @property int $home_team_id
 * @property int $away_team_id
 * @property int|null $home_score
 * @property int|null $away_score
 * @property Carbon|null $played_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Season $season
 * @property-read Team $homeTeam
 * @property-read Team $awayTeam
 */
class Fixture extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'season_id',
        'week_number',
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'played_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'week_number' => 'integer',
        'home_score'  => 'integer',
        'away_score'  => 'integer',
        'played_at'   => 'datetime',
    ];

    /**
     * Get the season that owns this fixture.
     *
     * @return BelongsTo<Season, Fixture>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Get the home team.
     *
     * @return BelongsTo<Team, Fixture>
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team.
     *
     * @return BelongsTo<Team, Fixture>
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Check if fixture has been played.
     *
     * @return bool
     */
    public function isPlayed(): bool
    {
        return $this->played_at !== null;
    }

    /**
     * Get the winner team ID, or null if draw.
     *
     * @return int|null
     */
    public function getWinnerId(): ?int
    {
        if ($this->played_at === null || $this->home_score === null || $this->away_score === null) {
            return null;
        }

        if ($this->home_score > $this->away_score) {
            return $this->home_team_id;
        }

        if ($this->away_score > $this->home_score) {
            return $this->away_team_id;
        }

        return null; // Draw
    }

    /**
     * Get the loser team ID, or null if draw.
     *
     * @return int|null
     */
    public function getLoserId(): ?int
    {
        if ($this->played_at === null || $this->home_score === null || $this->away_score === null) {
            return null;
        }

        if ($this->home_score < $this->away_score) {
            return $this->home_team_id;
        }

        if ($this->away_score < $this->home_score) {
            return $this->away_team_id;
        }

        return null; // Draw
    }
}
