<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFile extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'path', 'disk', 'mime_type', 'size', 'module', 'uploaded_by'];

    protected $casts = ['size' => 'integer'];

    /** @return BelongsTo<User, self> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
