<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'value' => 'encrypted',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Cache::remember(
            'setting.' . $key,
            now()->addDay(),
            fn () => static::query()->where('key', $key)->first()
        );

        return $setting?->value ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        Cache::forget('setting.' . $key);
    }
}
