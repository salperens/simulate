<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * SeasonTeam Model (Pivot)
 *
 * Represents the many-to-many relationship between Season and Team.
 * Tracks which teams participate in which seasons.
 *
 * @property int $id
 * @property int $season_id
 * @property int $team_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Season $season
 * @property-read Team $team
 */
class SeasonTeam extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'season_teams';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'season_id',
        'team_id',
    ];

    /**
     * Get the season that owns this relationship.
     *
     * @return BelongsTo<Season, SeasonTeam>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Get the team that owns this relationship.
     *
     * @return BelongsTo<Team, SeasonTeam>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
