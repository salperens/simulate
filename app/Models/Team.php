<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Team Model
 *
 * Represents a football team in the league simulation.
 * Each team has various attributes that affect match outcomes.
 *
 * @property int $id
 * @property string $name Team name
 * @property int $power_rating Team power rating (1-100, higher is stronger)
 * @property float $goalkeeper_factor Goalkeeper quality factor (0.50-1.50)
 * @property float $supporter_strength Supporter strength factor (0.50-1.50)
 * @property float $home_advantage_multiplier Home advantage multiplier (1.00-1.30)
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection|Fixture[] $homeFixtures Fixtures where this team is home
 * @property-read Collection|Fixture[] $awayFixtures Fixtures where this team is away
 * @property-read Collection|Season[] $seasons Seasons this team participates in
 */
class Team extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'power_rating',
        'goalkeeper_factor',
        'supporter_strength',
        'home_advantage_multiplier',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'power_rating'              => 'integer',
        'goalkeeper_factor'         => 'decimal:2',
        'supporter_strength'        => 'decimal:2',
        'home_advantage_multiplier' => 'decimal:2',
    ];

    /**
     * Get fixtures where this team is the home team.
     *
     * @return HasMany<Fixture>
     */
    public function homeFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'home_team_id');
    }

    /**
     * Get fixtures where this team is the away team.
     *
     * @return HasMany<Fixture>
     */
    public function awayFixtures(): HasMany
    {
        return $this->hasMany(Fixture::class, 'away_team_id');
    }

    /**
     * Get all fixtures for this team (both home and away).
     *
     * @return Collection<int, Fixture>
     */
    public function fixtures(): Collection
    {
        return $this->homeFixtures->merge($this->awayFixtures);
    }

    /**
     * Get seasons this team participates in.
     *
     * @return BelongsToMany<Season>
     */
    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Season::class, (new SeasonTeam())->getTable())
            ->using(SeasonTeam::class)
            ->withTimestamps();
    }
}
