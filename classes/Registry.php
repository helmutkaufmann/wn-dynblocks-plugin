<?php namespace Mercator\DynBlocks\Classes;

use Cms\Classes\ComponentManager;

class Registry
{
    private static ?array $cache = null;

    /**
     * Resolves a component name/alias like "blogPosts" to its component class.
     * Supports Winter's listComponents() formats:
     *  - alias => class (your current output)
     *  - class => info-array (older/other variants)
     */
    public static function resolveComponentClass(string $name): ?string
    {
        if (self::$cache === null) {
            self::$cache = [];

            $list = ComponentManager::instance()->listComponents();

            foreach ($list as $key => $val) {
                // Format A: alias => class (string => string)
                if (is_string($key) && is_string($val) && $val !== '' && (class_exists($val) || interface_exists($val))) {
                    self::$cache[$key] = $val;
                    continue;
                }

                // Format B: class => info-array
                if (is_string($key) && is_array($val)) {
                    $class = $key;
                    $info  = $val;

                    if (!empty($info['name'])) {
                        self::$cache[$info['name']] = $class;
                    }
                    if (!empty($info['plugin']) && !empty($info['name'])) {
                        self::$cache[$info['plugin'].'::'.$info['name']] = $class;
                    }
                }
            }
        }

        return self::$cache[$name] ?? null;
    }

    public static function isAllowed(string $name, string $class): bool
    {
        $cfg = \Config::get('mercator.dynblocks::dynblocks', []);
        $allow = $cfg['allowlist'] ?? [];
        $enabled = (bool)($allow['enabled'] ?? false);

        if (!$enabled) {
            return true;
        }

        $names = (array)($allow['allowed_names'] ?? []);
        $prefixes = (array)($allow['allowed_class_prefixes'] ?? []);

        if ($names && in_array($name, $names, true)) {
            return true;
        }

        foreach ($prefixes as $prefix) {
            if (is_string($prefix) && $prefix !== '' && str_starts_with($class, $prefix)) {
                return true;
            }
        }

        return false;
    }
}