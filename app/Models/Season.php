<?php

namespace App\Models;

use App\Enums\Season\SeasonStatusEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Season Model
 *
 * Represents a football season in the league simulation.
 * Each season can have a variable number of teams and fixtures.
 *
 * @property int $id
 * @property int $year Season year (e.g., 2024)
 * @property string|null $name Season name (e.g., "2024-2025 Season")
 * @property SeasonStatusEnum $status Season status
 * @property Carbon|null $start_date Season start date
 * @property Carbon|null $end_date Season end date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection|Team[] $teams Teams participating in this season
 * @property-read Collection|Fixture[] $fixtures All fixtures for this season
 */
class Season extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'year',
        'name',
        'status',
        'start_date',
        'end_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year'       => 'integer',
            'status'     => SeasonStatusEnum::class,
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    /**
     * Get teams participating in this season.
     *
     * @return BelongsToMany<Team>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, (new SeasonTeam())->getTable())
            ->using(SeasonTeam::class)
            ->withTimestamps();
    }

    /**
     * Get all fixtures for this season.
     *
     * @return HasMany<Fixture>
     */
    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    /**
     * Get fixtures for a specific week.
     *
     * @param int $weekNumber
     * @return Collection<int, Fixture>
     */
    public function fixturesForWeek(int $weekNumber): Collection
    {
        return $this->fixtures()->where('week_number', $weekNumber)->get();
    }

    /**
     * Check if season has fixtures generated.
     *
     * @return bool
     */
    public function hasFixtures(): bool
    {
        return $this->fixtures()->exists();
    }

    /**
     * Get total number of weeks in this season.
     *
     * @return int
     */
    public function getTotalWeeks(): int
    {
        return $this->fixtures()->max('week_number') ?? 0;
    }
}
