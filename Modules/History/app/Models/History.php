<?php

namespace Modules\History\Models;

use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\History\Database\Factories\HistoryFactory;
use Modules\Shared\Enums\DatabaseIdentifier;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property-read int $id
 * @property-read string $slug
 * @property string $name
 * @property string $type
 * @property bool $is_scanned
 * @property int $user_id
 * @property array|null $metadata
 * @property-read Carbon|null $created_at
 * @property-read Carbon|null $updated_at
 * @property-read Carbon|null $deleted_at
 */
#[UseFactory(HistoryFactory::class)]
final class History extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $connection = DatabaseIdentifier::HISTORY;

    protected $fillable = [
        'name',
        'type',
        'is_scanned',
        'user_id',
        'metadata',
    ];

    /**
     * @inheritDoc
     */
    public function casts(): array
    {
        return [
            'metadata' => 'json',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @inheritDoc
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fieldName: ['name', 'type'])
            ->saveSlugsTo(fieldName: 'slug');
    }
}
