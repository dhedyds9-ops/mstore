<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected static ?array $cachedValues = null;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            self::forgetCache();
        });

        static::deleted(function (): void {
            self::forgetCache();
        });
    }

    /**
     * Get a setting value by key.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function getValue($key, $default = null)
    {
        $values = self::allValues();

        if (array_key_exists($key, $values) && !is_null($values[$key]) && $values[$key] !== '') {
            return $values[$key];
        }

        // Fallback otomatis ke profil perusahaan toko yang sedang aktif
        $user = auth()->user();
        if ($user && $user->company_id) {
            $company = $user->company;
            if ($company) {
                if (in_array($key, ['store_name', 'atk_store_name', 'wash_store_name'], true) && !empty($company->name)) {
                    return $company->name;
                }
                if (in_array($key, ['store_address', 'atk_store_address', 'wash_store_address'], true) && !empty($company->address)) {
                    return $company->address;
                }
            }
        }

        return $default;
    }

    public static function allValues(): array
    {
        if (self::$cachedValues === null) {
            self::$cachedValues = \Illuminate\Support\Facades\Cache::rememberForever('settings_all', function () {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                        return self::query()
                            ->pluck('value', 'key')
                            ->all();
                    }
                } catch (\Exception $e) {
                    // Do nothing if table doesn't exist
                }
                return [];
            });
        }

        return self::$cachedValues;
    }

    public static function setValue(string $key, mixed $value, string $group = 'general', string $type = 'string'): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'group' => $group,
                'type' => $type,
            ]
        );

        self::forgetCache();

        return $setting;
    }

    public static function forgetCache(): void
    {
        self::$cachedValues = null;
        \Illuminate\Support\Facades\Cache::forget('settings_all');
    }
}
