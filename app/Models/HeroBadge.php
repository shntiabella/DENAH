<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroBadge extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'sort_order'];
}
