<?php namespace Mercator\DynBlocks\Classes\Twig;

use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class DynComponentTokenParser extends AbstractTokenParser
{
    public function parse(Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();
        $exprParser = $parser->getExpressionParser();

        // Erstes Argument: Komponentenname als Expression (typisch String)
        $nameExpr = $exprParser->parseExpression();

        // Danach: key=value Paare bis Tag-Ende
        $props = [];
        while (!$stream->test(Token::BLOCK_END_TYPE)) {
            $keyToken = $stream->expect(Token::NAME_TYPE);
            $stream->expect(Token::OPERATOR_TYPE, '=');
            $valueExpr = $exprParser->parseExpression();
            $props[] = [$keyToken->getValue(), $valueExpr];
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new DynComponentNode($nameExpr, $props, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'dyncomponent';
    }
}
