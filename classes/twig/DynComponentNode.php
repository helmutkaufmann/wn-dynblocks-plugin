<?php namespace Mercator\DynBlocks\Classes\Twig;

use Twig\Compiler;
use Twig\Node\Node;

class DynComponentNode extends \Twig\Node\Node
{
    public function __construct($nameExpr, $propsExpr, int $lineno, string $tag = null)
    {
        parent::__construct(
            ['name' => $nameExpr, 'props' => $propsExpr],
            [],
            $lineno,
            $tag
        );
    }

    public function compile(Compiler $compiler)
    {
        // IMPORTANT: must match your registered function name
        // dynComponentRender(name, props, alias)
        $compiler
            ->addDebugInfo($this)
            ->write("echo call_user_func_array(")
            ->raw("\$this->env->getFunction('dynComponentRender')->getCallable(), [")
            ->subcompile($this->getNode('name'))
            ->raw(", ")
            ->subcompile($this->getNode('props'))
            ->raw(", null]);\n");
    }
}