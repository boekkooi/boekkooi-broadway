<?php
namespace Boekkooi\Broadway\CS;

use Symfony\CS\Tokenizer\Token;
use Symfony\CS\Tokenizer\Tokens;
use Symfony\CS\Tokenizer\TokensAnalyzer;

final class TokensHelper
{
    private function __construct()
    {
    }

    /**
     * Returns the attributes of the class under the given index.
     *
     * The array has the following items:
     * 'abstract'   bool
     * 'final'      bool
     * 'name'       string
     * 'extends'    null|string
     * 'implements' string[]
     *
     * @param int $index Token index of the method (T_FUNCTION)
     *
     * @return array
     */
    public static function getClassAttributes(Tokens $tokens, $index)
    {
        $token = $tokens[$index];

        if (!$token->isGivenKind(T_CLASS)) {
            throw new \LogicException(sprintf(
                'No T_CLASS at given index %d, got %s',
                $index,
                $token->getName()
            ));
        }

        $attributes = array(
            'abstract' => false,
            'final' => false,
            'name' => null,
            'extends' => null,
            'implements' => [],
        );

        // class-modifier
        for ($i = $index; $i >= 0; $i--) {
            $tokenIndex = $tokens->getPrevMeaningfulToken($i);
            if (null === $tokenIndex) {
                break;
            }

            $i = $tokenIndex;
            $token = $tokens[$tokenIndex];

            if ($token->isGivenKind(T_FINAL)) {
                $attributes['final'] = true;
                continue;
            }

            if ($token->isGivenKind(T_ABSTRACT)) {
                $attributes['abstract'] = true;
                continue;
            }

            // found a meaningful token that is not part of
            // the class signature; stop looking
            break;
        }

        $current = 'name';
        for ($i = $index; $i < $tokens->count(); $i++) {
            $tokenIndex = $tokens->getNextMeaningfulToken($i);
            if (null === $tokenIndex) {
                break;
            }

            $i = $tokenIndex;
            $token = $tokens[$tokenIndex];

            // name
            if ($token->isGivenKind(T_STRING) ){
                if (is_array($attributes[$current])) {
                    $attributes[$current][] = $token->getContent();
                } else {
                    $attributes[$current] = $token->getContent();
                }
                continue;
            }

            // class-base-clause
            if ($token->isGivenKind(T_EXTENDS)) {
                $current = 'extends';
                continue;
            }

            // class-interface-clause
            if ($token->isGivenKind(T_IMPLEMENTS)) {
                $current = 'implements';
                continue;
            }

            // found a meaningful token that is not part of
            // the class signature; stop looking
            break;
        }

        return $attributes;


    }

    /**
     * Returns the attributes of the method under the given index.
     *
     * The array has the following items:
     * 'name'       string
     * 'visibility' int|null  T_PRIVATE, T_PROTECTED or T_PUBLIC
     * 'static'     bool
     * 'abstract'   bool
     * 'final'      bool
     *
     * @param int $index Token index of the method (T_FUNCTION)
     *
     * @return array
     */
    public static function getMethodAttributes(Tokens $tokens, $index, TokensAnalyzer $tokensAnalyzer = null)
    {
        if (!$tokens[$index]->isGivenKind(T_FUNCTION)) {
            throw new \LogicException(sprintf(
                'No T_FUNCTION at given index %d, got %s',
                $index,
                $tokens[$index]->getName()
            ));
        }

        $analyzer = $tokensAnalyzer ?: new TokensAnalyzer($tokens);

        $methodNameToken = $tokens[$tokens->getNextTokenOfKind($index, [[T_STRING]])];

        $attributes = $analyzer->getMethodAttributes($index);
        $attributes['name'] = $methodNameToken->getContent();

        return $attributes;
    }

    /**
     * Returns the arguments of the function under the given index.
     *
     * The array items have the following items:
     * 'token'      string
     * 'type'       null|string
     * 'default'    bool
     *
     * @param int $index Token index of the method (T_FUNCTION)
     *
     * @return array
     */
    public static function getFunctionArguments(Tokens $tokens, $index)
    {
        $token = $tokens[$index];
        if ($token->isGivenKind(T_FUNCTION)) {
            $index = $tokens->getNextTokenOfKind($index, ['(']);
            $token = $tokens[$index];
        }

        if ($token->getContent() !== '(') {
            throw new \LogicException(sprintf(
                'No "(" at given index %d, got %s',
                $index,
                $token->getName()
            ));
        }

        $endIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $index);

        $arguments = [];

        for ($i = $endIndex - 1; $i > $index; --$i) {
            $token = $tokens[$i];

            if ($token->equals(')')) {
                $i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $i, false);
                continue;
            }

            if ($token->isGivenKind(CT_ARRAY_SQUARE_BRACE_CLOSE)) {
                $i = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_ARRAY_SQUARE_BRACE, $i, false);
                continue;
            }

            if ($token->equals(',')) {
                continue;
            }

            if ($token->isGivenKind(T_VARIABLE)) {
                $assignedToken = $tokens[$tokens->getNextMeaningfulToken($i)];
                $typeToken = $tokens[$tokens->getPrevMeaningfulToken($i)];

                $argument = [
                    'token' => $token,
                    'type' => (
                    $typeToken !== null && $typeToken->isGivenKind(T_STRING) ?
                        $typeToken->getContent() :
                        null
                    ),
                    'default' => ($assignedToken !== null && $assignedToken->getContent() === '=')
                ];

                $arguments[$i] = $argument;
            }
        }

        ksort($arguments);
        return $arguments;
    }
}
