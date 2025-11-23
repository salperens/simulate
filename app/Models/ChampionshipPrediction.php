<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * ChampionshipPrediction Model
 *
 * Represents championship prediction probabilities for teams at specific weeks.
 * Used for caching prediction results to avoid recalculation.
 *
 * @property int $id
 * @property int $season_id
 * @property int $week_number
 * @property int $team_id
 * @property float $win_probability Win probability percentage (0.00 - 100.00)
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Season $season
 * @property-read Team $team
 */
class ChampionshipPrediction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'season_id',
        'week_number',
        'team_id',
        'win_probability',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_number'     => 'integer',
            'win_probability' => 'decimal:2',
        ];
    }

    /**
     * Get the season this prediction belongs to.
     *
     * @return BelongsTo<Season>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Get the team this prediction is for.
     *
     * @return BelongsTo<Team>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
