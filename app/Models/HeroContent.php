<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeroContent extends Model
{
    use HasFactory;
    use Translatable;

    protected $fillable = [
        'badge_text', 'headline_1', 'headline_2', 'subheadline',
        'cta_primary_text', 'cta_secondary_text', 'coordinates',
        'background_image', 'updated_by',
        'badge_text_id', 'badge_text_en',
        'headline_1_id', 'headline_1_en',
        'headline_2_id', 'headline_2_en',
        'subheadline_id', 'subheadline_en',
        'cta_primary_text_id', 'cta_primary_text_en',
        'cta_secondary_text_id', 'cta_secondary_text_en',
    ];

    /** @return BelongsTo<User, self> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        $hero = self::first();
        if ($hero) {
            return $hero;
        }

        return self::create([
            'subheadline' => 'Eksplorasi informasi spasial mengenai persebaran dan klasifikasi permukiman melalui peta interaktif.',
        ])->refresh();
    }
}
