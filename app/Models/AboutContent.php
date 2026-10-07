<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutContent extends Model
{
    use HasFactory;
    use Translatable;

    protected $fillable = [
        'body', 'image', 'background_image', 'more_link_text', 'more_link_url', 'updated_by',
        'body_id', 'body_en', 'more_link_text_id', 'more_link_text_en',
    ];

    /** @return BelongsTo<User, self> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        $about = self::first();
        if ($about) {
            return $about;
        }

        return self::create([
            'body' => '<p>DENAH adalah platform pemetaan dan klasifikasi permukiman...</p>',
            'more_link_text' => 'Selengkapnya',
            'more_link_url' => '#tentang',
        ])->refresh();
    }
}
