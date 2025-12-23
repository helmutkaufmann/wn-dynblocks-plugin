<?php namespace Mercator\DynBlocks;

use System\Classes\PluginBase;
use Cms\Classes\Controller;
use Session;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Dyn Blocks',
            'description' => 'Dynamic components in Static Pages blocks (.block)',
            'author'      => 'Mercator',
            'icon'        => 'icon-puzzle-piece'
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                // Render + output HTML
                'dynComponentRender' => function ($name, $props = [], $alias = null) {
                    return self::renderDynamicComponent(
                        (string) $name,
                        (array) $props,
                        $alias ? (string) $alias : null
                    );
                },

                // Create instance (no output)
                'dynComponent' => function ($name, $props = [], $alias = null) {
                    return self::makeDynamicComponent(
                        (string) $name,
                        (array) $props,
                        $alias ? (string) $alias : null
                    );
                },

                // Read controller/page vars set by components
                'dynComponentPageVars' => function ($key, $default = null) {
                    $controller = Controller::getController();
                    if (!$controller) {
                        return $default;
                    }
                    $vars = $controller->vars ?? [];
                    return $vars[$key] ?? $default;
                },
            ],
        ];
    }


    public function register()
    {
        $this->app->extend('twig.environment.cms', function ($twig, $app) {
            $twig->addTokenParser(new \Mercator\DynBlocks\Classes\Twig\DynComponentTokenParser());
            return $twig;
        });
    }
    
    public function boot()
    {
        
        // REQUIRED for AJAX: attach dynamic component before handler dispatch
        \Event::listen('cms.ajax.beforeRunHandler', function ($controller, $handler) {
            if (!$handler || !str_contains($handler, '::')) {
                return;
            }

            [$alias] = explode('::', $handler, 2);

            if ($controller->findComponentByName($alias)) {
                return;
            }

            $all = (array) Session::get('mercator.dynblocks.components', []);
            if (!isset($all[$alias])) {
                return;
            }

            $def   = (array) $all[$alias];
            $name  = (string) ($def['name'] ?? '');
            $props = (array) ($def['props'] ?? []);

            if ($name === '') {
                return;
            }

            // Use the controller instance provided by Winter (do not rely on any global controller accessor)
            self::makeDynamicComponent($name, $props, $alias, $controller);
        });
    }

    /**
     * Create (or return) a component instance on a given controller.
     * - During normal rendering, $controller can be omitted.
     * - During AJAX pre-dispatch, pass the $controller from cms.ajax.beforeRunHandler.
     */
    public static function makeDynamicComponent(string $name, array $props = [], ?string $alias = null, $controller = null)
    {
        $controller = $controller ?: Controller::getController();
        if (!$controller) {
            return null;
        }

        $cfg = \Config::get('mercator.dynblocks::dynblocks', []);
        $staticOnly = (bool)($cfg['static_pages_only'] ?? true);

        if ($staticOnly && !$controller->findComponentByName('staticPage')) {
            return null;
        }

        $class = \Mercator\DynBlocks\Classes\Registry::resolveComponentClass($name);
        if (!$class) {
            return null;
        }

        if (!\Mercator\DynBlocks\Classes\Registry::isAllowed($name, $class)) {
            return null;
        }

        $alias = $alias ?: 'dyn_' . substr(md5($name . json_encode($props) . microtime(true)), 0, 10);

        // Remember by alias for AJAX pre-mount
        self::dynblocksRemember($alias, $name, $props);

        $cmp = $controller->findComponentByName($alias);
        if (!$cmp) {
            $cmp = $controller->addComponent($class, $alias, $props);
        }

        if (method_exists($cmp, 'init')) {
            $cmp->init();
        }
        if (method_exists($cmp, 'onRun')) {
            $cmp->onRun();
        }

        return $cmp;
    }

    public static function renderDynamicComponent(string $name, array $props = [], ?string $alias = null): string
    {
        $controller = Controller::getController();
        if (!$controller) {
            return '';
        }

        $cfg = \Config::get('mercator.dynblocks::dynblocks', []);
        $failLoud = (bool)($cfg['fail_loud'] ?? false);

        $alias = $alias ?: 'dyn_' . substr(md5($name . json_encode($props) . microtime(true)), 0, 10);

        $cmp = self::makeDynamicComponent($name, $props, $alias, $controller);
        if (!$cmp) {
            return $failLoud ? self::failHtml("Could not create component: {$name}") : '';
        }

        if (method_exists($controller, 'renderComponent')) {
            return (string) $controller->renderComponent($alias);
        }

        return $failLoud ? self::failHtml("Controller::renderComponent not available") : '';
    }

    private static function failHtml(string $message): string
    {
        $safe = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<div class="dyncomponent-error" style="border:1px solid #c00;padding:8px;margin:8px 0;color:#c00;">' . $safe . '</div>';
    }

    private static function dynblocksRemember(string $alias, string $name, array $props): void
    {
        $all = (array) Session::get('mercator.dynblocks.components', []);
        $all[$alias] = [
            'name'  => $name,
            'props' => $props,
        ];
        Session::put('mercator.dynblocks.components', $all);
    }
}