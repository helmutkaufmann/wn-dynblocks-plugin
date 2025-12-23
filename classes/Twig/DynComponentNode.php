<?php namespace Mercator\DynBlocks\Classes\Twig;

use Twig\Compiler;
use Twig\Node\Node;

class DynComponentNode extends Node
{
    public function __construct($nameExpr, array $props, int $line, string $tag = null)
    {
        $nodes = ['name' => $nameExpr];
        parent::__construct($nodes, ['props' => $props], $line, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write('$__props = [];'.PHP_EOL);

        foreach ($this->getAttribute('props') as [$key, $valueExpr]) {
            $compiler
                ->write('$__props['.var_export($key, true).'] = ')
                ->subcompile($valueExpr)
                ->raw(';'.PHP_EOL);
        }

        $compiler
            ->write('echo \\Mercator\\DynBlocks\\Plugin::renderDynamicComponent(')
            ->subcompile($this->getNode('name'))
            ->raw(', $__props);'.PHP_EOL);
    }
}
