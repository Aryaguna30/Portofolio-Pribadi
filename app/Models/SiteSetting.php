<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Accessor: auto-decode JSON values, return raw string otherwise.
     */
    public function getValueAttribute(mixed $raw): mixed
    {
        if ($raw === null) {
            return null;
        }

        $decoded = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $raw;
    }

    /**
     * Mutator: JSON-encode arrays/objects, store strings as-is.
     */
    public function setValueAttribute(mixed $value): void
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Find a setting by key and return its decoded value, or $default if not found.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Create or update a setting by key.
     */
    public static function set(string $key, mixed $value): void
    {
        $encoded = (is_array($value) || is_object($value))
            ? json_encode($value)
            : $value;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $encoded]
        );
    }
}
