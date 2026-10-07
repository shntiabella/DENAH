<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feature extends Model
{
    use HasFactory;
    use Translatable;

    protected $fillable = [
        'title', 'description', 'icon', 'sort_order',
        'title_id', 'title_en', 'description_id', 'description_en',
    ];
}
