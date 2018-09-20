<?php

namespace Subapp\Collection\Parameters\Parser;

use Subapp\Collection\Parameters\ParserInterface;

/**
 * Class IniParser
 * @package Subapp\Collection\Parameters\Parser
 */
class IniParser implements ParserInterface
{

    /**
     * @inheritDoc
     */
    public static function parse($contentString)
    {
        $parameters = parse_ini_string($contentString, INI_SCANNER_NORMAL);
        $parametersArray = [];

        foreach ($parameters as $path => $parameter) {

            $temporaryArray = &$parametersArray;

            foreach (explode('.', $path) as $key) {
                $temporaryArray = &$temporaryArray[$key];
            }

            $temporaryArray = $parameter;
        }

        return $parametersArray;
    }

    /**
     * @inheritDoc
     */
    public static function dump(array $parameters)
    {
        $lines = [sprintf('# Dumper: %s DateTime: %s', __CLASS__, (new \DateTime())->format(\DateTime::W3C))];

        static::dumpIniContent($parameters, [], $lines);

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param array $parameters
     * @param array $indexes
     * @param array $lines
     */
    private static function dumpIniContent(array $parameters = [], $indexes = [], &$lines = [])
    {
        foreach ($parameters as $index => $value) {
            $indexes[] = $index;

            if (is_array($value)) {
                static::dumpIniContent($value, $indexes, $lines);
            } else {
                $lines[] = implode('.', $indexes) . '="' . addcslashes($value, '"') . '"';
            }

            array_pop($indexes);
        }
    }

}