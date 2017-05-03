<?php

namespace SebC\AdditionalPhpCsFixers;

use ReflectionClass;
use PhpCsFixer\Utils;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;

class DisallowUnaliasedClasses implements FixerInterface, ConfigurableFixerInterface
{
    const NS_SEPARATOR = '\\';

    protected $namespaceReplacements = [];
    
    public function getName()
    {
        $fixerName = Utils::camelCaseToUnderscore((new ReflectionClass($this))->getShortName());

        return "SebCAdditionalPhpCsFixers/$fixerName";
    }

    public function configure(array $configuration = [])
    {
        if (isset($configuration['replace_namespaces'])) {
            foreach ($configuration['replace_namespaces'] as $from => $to) {
                // Ignoring leading separators in config.
                $from = ltrim($from, static::NS_SEPARATOR);
                $to = ltrim($to, static::NS_SEPARATOR);

                // Also ignoring trailing separators in config.
                $from = rtrim($from, static::NS_SEPARATOR);
                $to = rtrim($to, static::NS_SEPARATOR);

                if (empty($from)) {
                    continue; // We cannot safely detect an empty/root namespace
                }

                $this->namespaceReplacements[$from] = $to;
            }
        }
    }

    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_STRING);
    }

    public function fix(\SplFileInfo $file, Tokens $tokens)
    {
        foreach ($this->namespaceReplacements as $nsFrom => $nsTo) {
            $nsFrom = explode(static::NS_SEPARATOR, $nsFrom);
            $nsTo = empty($nsTo) ? null : explode(static::NS_SEPARATOR, $nsTo);

            for ($i = 0; $i < $tokens->count(); $i++) {
                $token = $tokens[$i];
                if ($token->getId() === T_STRING && $token->getContent() == $nsFrom[0]) {
                    // The previous token may be a leading NS_SEPARATOR. For consistency, we have to keep it as it is.

                    // Searching if the complete chain of namespace fragments is here
                    $namespaceFound = true;
                    for ($j = 0; $j < count($nsFrom); $j++) {
                        $fragmentIndex = $i + ($j * 2);
                        $nextSeparatorIndex = $fragmentIndex + 1;

                        if (!(
                            // For each symbol, we check the namespace fragment...
                            isset($tokens[$fragmentIndex])
                            && $tokens[$fragmentIndex]->getId() === T_STRING
                            && $tokens[$fragmentIndex]->getContent() == $nsFrom[$j]

                            // ...followed by a namespace separator
                            && isset($tokens[$nextSeparatorIndex])
                            && $tokens[$nextSeparatorIndex]->getId() === T_NS_SEPARATOR
                            && $tokens[$nextSeparatorIndex]->getContent() == static::NS_SEPARATOR
                        )) {
                            $namespaceFound = false;
                            break;
                        }
                    }

                    if ($namespaceFound) {
                        // First, removing all fragments matched and their trailing NS_SEPARATORS.
                        // No need to change $i since we are at the beginning.
                        for ($j = 0; $j < count($nsFrom) * 2; $j++) {
                            $tokens[$i + $j]->clear();
                        }

                        // Now we add the new namespace fragments if needed, with all trailing separators
                        $tokensToInsert = [];
                        for ($j = 0; $j < count($nsTo); $j++) {
                            $tokensToInsert[] = new Token([T_STRING, $nsTo[$j]]);
                            $tokensToInsert[] = new Token([T_NS_SEPARATOR, static::NS_SEPARATOR]);
                        }
                        $tokens->insertAt($i, $tokensToInsert);
                    }
                }
            }
        }
    }

    public function isRisky()
    {
        return false;
    }

    public function getPriority()
    {
        return 0;
    }

    public function getDefinition()
    {
        return new FixerDefinition(
            'TODO',
            [
                new CodeSample('TODO2'),
            ]
        );
    }

    public function supports(\SplFileInfo $file)
    {
        return true;
    }
}
