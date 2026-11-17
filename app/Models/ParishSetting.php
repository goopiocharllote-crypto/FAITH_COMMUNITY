<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

/** Parish information and system preferences edited by administrators (key/value rows). */
class ParishSetting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /** Values used until an administrator saves their own. */
    public const DEFAULTS = [
        'parish_name' => 'Parish of Saint Peter the Apostle',
        'address' => 'Loboc, Bohol, Philippines',
        'contact_number' => '',
        'email' => '',
        'timezone' => 'Asia/Manila',
        'pending_expiry_minutes' => '10',
    ];

    /** @var array<string, string>|null */
    private static ?array $cache = null;

    /** @return array<string, string> */
    public static function values(): array
    {
        if (self::$cache === null) {
            try {
                self::$cache = array_merge(self::DEFAULTS, static::query()->pluck('value', 'key')->map(fn ($v) => (string) $v)->all());
            } catch (Throwable) {
                // Table not migrated yet: fall back to defaults instead of breaking public pages.
                self::$cache = self::DEFAULTS;
            }
        }

        return self::$cache;
    }

    public static function get(string $key): string
    {
        return self::values()[$key] ?? self::DEFAULTS[$key] ?? '';
    }

    /** @param array<string, scalar|null> $values */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        self::$cache = null;
    }
}
