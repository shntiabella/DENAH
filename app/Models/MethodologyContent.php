<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MethodologyContent extends Model
{
    use HasFactory;
    use Translatable;

    protected $fillable = ['title', 'title_id', 'title_en', 'body', 'background_image', 'updated_by', 'body_id', 'body_en'];

    /** @return BelongsTo<User, self> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        $mc = self::first();
        if ($mc) {
            return $mc;
        }

        return self::create(['body' => '<p>Penjelasan metodologi pemetaan DENAH...</p>'])->refresh();
    }
}
