<?php

namespace App\Support;

/**
 * Gerbang modul terpusat (Module Gating).
 *
 * Membaca SSOT dari config/module_gating.php untuk menentukan modul mana yang aktif,
 * memfilter sidebar, dan memblokir route dari modul yang tidak aktif.
 */
class ModuleGate
{
    /** @return array<int, string> */
    public static function enabled(): array
    {
        $user = auth()->user();
        if ($user && $user->company_id) {
            $company = $user->company;
            if ($company) {
                if (method_exists($user, 'hasRole') && $user->hasRole(\App\Models\Role::SUPER_ADMIN)) {
                    return (array) config('module_gating.enabled', []);
                }
                return $company->getEffectiveModules();
            }
        }

        return (array) config('module_gating.enabled', []);
    }

    public static function isEnabled(?string $module): bool
    {
        if ($module === null || $module === '') {
            return true;
        }

        return in_array($module, self::enabled(), true);
    }

    /**
     * Tentukan modul dari sebuah nama route berdasarkan prefix di config.
     */
    public static function moduleForRoute(?string $routeName): ?string
    {
        if (! $routeName) {
            return null;
        }

        foreach ((array) config('module_gating.route_prefixes', []) as $module => $prefixes) {
            foreach ((array) $prefixes as $prefix) {
                if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                    return $module;
                }
            }
        }

        return null;
    }

    public static function isRouteBlocked(?string $routeName): bool
    {
        $module = self::moduleForRoute($routeName);

        return $module !== null && ! self::isEnabled($module);
    }

    /**
     * Tentukan modul dari path URL (tanpa leading slash).
     */
    public static function moduleForPath(?string $path): ?string
    {
        $path = trim((string) $path, '/');

        if ($path === '') {
            return null;
        }

        foreach ((array) config('module_gating.path_prefixes', []) as $module => $prefixes) {
            foreach ((array) $prefixes as $prefix) {
                if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                    return $module;
                }
            }
        }

        return null;
    }

    public static function isPathBlocked(?string $path): bool
    {
        $module = self::moduleForPath($path);

        return $module !== null && ! self::isEnabled($module);
    }

    /**
     * Hapus section/item/group dari sidebar yang modulnya tidak aktif.
     *
     * @param  array<int, array<string, mixed>>  $sections
     * @return array<int, array<string, mixed>>
     */
    public static function filterSidebar(array $sections): array
    {
        $sectionModules = (array) config('module_gating.sidebar_sections', []);
        $itemModules = (array) config('module_gating.sidebar_items', []);

        $result = [];

        foreach ($sections as $section) {
            $sectionId = $section['id'] ?? null;

            if ($sectionId !== null && isset($sectionModules[$sectionId]) && ! self::isEnabled($sectionModules[$sectionId])) {
                continue;
            }

            $items = [];
            foreach ((array) ($section['items'] ?? []) as $item) {
                $filtered = self::filterItem($item, $itemModules);
                if ($filtered !== null) {
                    $items[] = $filtered;
                }
            }

            if (empty($items)) {
                continue;
            }

            $section['items'] = $items;
            $result[] = $section;
        }

        return $result;
    }

    private static function filterItem(array $item, array $itemModules): ?array
    {
        $id = $item['id'] ?? null;

        if ($id !== null && isset($itemModules[$id]) && ! self::isEnabled($itemModules[$id])) {
            return null;
        }

        if (($item['type'] ?? null) === 'group' && ! empty($item['children'])) {
            $children = [];
            foreach ($item['children'] as $child) {
                $filtered = self::filterItem((array) $child, $itemModules);
                if ($filtered !== null) {
                    $children[] = $filtered;
                }
            }

            if (empty($children)) {
                return null;
            }

            $item['children'] = $children;
        }

        return $item;
    }
}
