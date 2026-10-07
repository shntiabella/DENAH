<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    use HasFactory;
    use Translatable;

    protected $fillable = [
        'label', 'value', 'unit', 'icon', 'counter', 'sort_order',
        'label_id', 'label_en', 'unit_id', 'unit_en',
    ];

    protected $casts = [
        'value' => 'float',
        'counter' => 'boolean',
    ];
}
