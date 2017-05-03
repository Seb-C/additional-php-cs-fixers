<?php

namespace SebC\AdditionalPhpCsFixers;

class Helper
{
    /**
     * Helper to easily declare custom fixers to PhpCsFixer
     * `$phpCsFixerConfig->registerCustomFixers(Helper::getCustomFixers())`
     */
    public static function getCustomFixers()
    {
        return [
            new DisallowUnaliasedClasses(),
        ];
    }
}
