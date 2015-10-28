<?php
namespace Boekkooi\Broadway\CS\Checker;

use Boekkooi\Broadway\CS\TokensHelper;
use Boekkooi\CS\AbstractChecker;
use Boekkooi\CS\Message;
use Boekkooi\CS\Tokenizer\Tokens;
use Symfony\Component\Finder\SplFileInfo as FinderSplFileInfo;
use Symfony\CS\DocBlock\DocBlock;
use Symfony\CS\Tokenizer\Token;
use Symfony\CS\Tokenizer\TokensAnalyzer;

class EventClassChecker extends AbstractChecker
{
    /**
     * @inheritdoc
     */
    public function isCandidate(Tokens $tokens)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'Event class constraint checks';
    }

    /**
     * @inheritdoc
     */
    public function check(\SplFileInfo $file, Tokens $tokens)
    {
        $analyzer = new TokensAnalyzer($tokens);

        $constructorIndex = null;
        $constructorArgs = [];
        $properties = [];

        // Find the class
        $classIndex = $tokens->getNextTokenOfKind(0, [ [ T_CLASS ] ]);
        if ($classIndex === null) {
            return;
        }
        $classAttributes = TokensHelper::getClassAttributes($tokens, $classIndex);

        // Event must be final or abstract
        if (!$classAttributes['abstract'] && !$classAttributes['final']) {
            $tokens->reportAt(
                $classIndex,
                new Message(E_ERROR, 'check_class_must_be_final_or_abstract', [
                    'class' => $classAttributes['name']
                ])
            );
        }

        $elements = $analyzer->getClassyElements();
        foreach ($elements as $index => $element) {
            /** @var Token $token */
            if ($element['type'] === 'method') {
                $methodAttributes = TokensHelper::getMethodAttributes($tokens, $index, $analyzer);
                if ($methodInfo = $this->checkMethod($tokens, $index, $methodAttributes)) {
                    $constructorIndex = $index;
                    $constructorArgs = $methodInfo['arguments'];
                }
            } elseif ($element['type'] === 'property' && ($propertyInfo = $this->checkProperty($tokens, $index))) {
                $properties[$index] = $propertyInfo;
            }
        }

        // A event without data is not a error
        if ($constructorIndex === null && count($properties) === 0) {
            return;
        }

        // Event with properties must have a constructor
        if ($constructorIndex === null) {
            $tokens->reportAt(
                $classIndex,
                new Message(E_ERROR, 'check_class_must_have_constructor', [
                    'class' => $classAttributes['name']
                ])
            );
        } else {
            $expectedProperties = array_map(function ($info) {
                return $info['token']->getContent();
            }, $constructorArgs);

            $unknownConstructorArgs = array_diff($expectedProperties, $properties);
            if (count($unknownConstructorArgs) > 0) {
                $tokens->reportAt(
                    $constructorIndex,
                    new Message(E_ERROR, 'check_class_constructor_has_arguments_without_related_property', [
                        'arguments' => implode(', ', $unknownConstructorArgs)
                    ])
                );
            }

            $unassignedProperties = array_diff($properties, $expectedProperties);
            if (count($unassignedProperties) > 0) {
                foreach ($unassignedProperties as $index => $property) {
                    $tokens->reportAt(
                        $index,
                        new Message(E_ERROR, 'check_class_property_must_be_know_as_constructor_argument', [
                            'property' => $property
                        ])
                    );
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function supports(\SplFileInfo $file)
    {
        $path = $file->getPathname();
        if ($file instanceof FinderSplFileInfo) {
            $path = $file->getRelativePathname();
        }

        return strpos($path, '/Event/') !== false;
    }

    protected function checkProperty(Tokens $tokens, $index)
    {
        $variableName = $tokens[$index]->getContent();

        $visibilityIndex = $tokens->getPrevMeaningfulToken($index);
        $visibilityToken = $tokens[$visibilityIndex];

        // Only public properties are allowed
        if ($visibilityToken->getId() !== T_PUBLIC) {
            $tokens->reportAt(
                $index,
                new Message(E_ERROR, 'check_class_property_must_be_public', [
                    'property' => $variableName,
                    'visibility' => ($visibilityToken->getId() === T_PRIVATE ? 'private' : 'protected')
                ])
            );
            return false;
        }

        // A doc block is required with a @var
        for ($i = $visibilityIndex - 1; $i >= 0; --$i) {
            $token = $tokens[$i];
            if ($token->isGivenKind([T_WHITESPACE, T_ENCAPSED_AND_WHITESPACE])) {
                continue;
            }

            if (!$token->isGivenKind(T_DOC_COMMENT)) {
                $tokens->reportAt(
                    $index,
                    new Message(E_ERROR, 'check_class_property_must_have_a_phpdoc', [
                        'property' => $variableName
                    ])
                );
                break;
            }

            $doc = new DocBlock($token->getContent());
            $annotations = $doc->getAnnotationsOfType('var');

            if (count($annotations) === 0) {
                $tokens->reportAt(
                    $i,
                    new Message(E_ERROR, 'check_class_property_phpdoc_must_have_var', [
                        'property' => $variableName
                    ])
                );
                break;
            }
            if (count($annotations) > 1) {
                $tokens->reportAt(
                    $i,
                    new Message(E_ERROR, 'check_class_property_phpdoc_invalid_var', [
                        'property' => $variableName
                    ])
                );
                break;
            }

            foreach ($annotations as $annotation) {
                $parts = preg_split(
                    '/(\s+)/Su',
                    trim($annotation->getContent(), " \t\n\r\0\x0B*"),
                    3
                );

                if (count($parts) <= 1) {
                    $tokens->reportAt(
                        $i,
                        new Message(E_ERROR, 'check_class_property_phpdoc_invalid_var', [
                            'property' => $variableName
                        ])
                    );
                }
            }

            break;
        }

        return $variableName;
    }


    private function checkMethod(Tokens $tokens, $index, $attributes)
    {
        if ($attributes['name'] !== '__construct') {
            $tokens->reportAt(
                $index,
                new Message(E_ERROR, 'check_class_must_not_contain_method', [
                    'method' => $attributes['name']
                ])
            );

            return false;
        }

        if ($attributes['visibility'] === null) {
            $tokens->reportAt(
                $index,
                new Message(E_ERROR, 'check_class_method_must_have_a_visibility', [
                    'method' => $attributes['name']
                ])
            );
        } elseif ($attributes['visibility'] !== T_PUBLIC) {
            $tokens->reportAt(
                $index,
                new Message(E_ERROR, 'check_class_method_must_be_public', [
                    'method' => $attributes['name'],
                    'visibility' => ($attributes['visibility'] === T_PRIVATE ? 'private' : 'protected')
                ])
            );
        }

        $functionArguments = TokensHelper::getFunctionArguments($tokens, $index);

        // Ensure that all arguments are used
        $startBodyIndex = $tokens->getNextTokenOfKind($index, ['{']);
        $endBodyIndex = $tokens->findBlockEnd(Tokens::BLOCK_TYPE_CURLY_BRACE, $startBodyIndex, true);

        $usedVariables = [];
        for ($index = $endBodyIndex - 1; $index > $startBodyIndex; --$index) {
            $token = $tokens[$index];

            if (
                !$token->isGivenKind(T_VARIABLE) ||
                $tokens[$index-1]->isGivenKind(T_OBJECT_OPERATOR) ||
                $tokens[$index+1]->isGivenKind(T_OBJECT_OPERATOR)
            ) {
                continue;
            }

            $usedVariables[$index] = $token->getContent();
        }

        // Report unused arguments
        $unusedArguments = array_diff(
            array_map(function ($info) { return $info['token']->getContent(); }, $functionArguments),
            $usedVariables
        );

        foreach ($unusedArguments as $argumentIndex => $argument) {
            $tokens->reportAt(
                $argumentIndex,
                new Message(E_ERROR, 'check_class_method_argument_not_used', [
                    'method' => $attributes['name'],
                    'argument' => $argument
                ])
            );
        }

        return [
            'arguments' => $functionArguments
        ];
    }
}
