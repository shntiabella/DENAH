<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'key';

    public static function get(string $key, mixed $default = null): mixed
    {
        $model = static::find($key);

        return $model?->value ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => is_array($value) ? json_encode($value) : $value]);
    }
}
