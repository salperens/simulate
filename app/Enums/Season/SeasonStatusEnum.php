<?php

namespace App\Enums\Season;

/**
 * SeasonStatusEnum
 * 
 * Represents the possible statuses of a season.
 */
enum SeasonStatusEnum: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';

    /**
     * Get all status values as array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable label for the status.
     *
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
        };
    }
}

