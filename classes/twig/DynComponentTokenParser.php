<?php namespace Mercator\DynBlocks\Classes\Twig;

use Twig\Token;
use Twig\Node\Node;
use Twig\TokenParser\AbstractTokenParser;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;

class DynComponentTokenParser extends \Twig\TokenParser\AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        // First argument: component name expression
        $nameExpr = $this->parser->getExpressionParser()->parseExpression();

        // Parse key=value pairs into array
        $pairs = [];
        while (!$stream->test(Token::BLOCK_END_TYPE)) {
            $keyToken = $stream->expect(Token::NAME_TYPE);
            $stream->expect(Token::OPERATOR_TYPE, '=');
            $valueExpr = $this->parser->getExpressionParser()->parseExpression();

            $pairs[] = new ConstantExpression($keyToken->getValue(), $lineno);
            $pairs[] = $valueExpr;
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $propsExpr = new ArrayExpression($pairs, $lineno);

        return new DynComponentNode($nameExpr, $propsExpr, $lineno, $this->getTag());
    }

    public function getTag(): string
    {
        return 'dynComponent';
    }
}