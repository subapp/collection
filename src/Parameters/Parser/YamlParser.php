<?php

namespace Subapp\Collection\Parameters\Parser;

use Subapp\Collection\Parameters\ParserInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlParser
 * @package Subapp\Collection\Parameters\Parser
 */
class YamlParser implements ParserInterface
{

    /**
     * @inheritDoc
     */
    public static function parse($contentString)
    {
        return Yaml::parse($contentString);
    }

    /**
     * @inheritDoc
     */
    public static function dump(array $parameters)
    {
        return Yaml::dump($parameters, 16);
    }

}