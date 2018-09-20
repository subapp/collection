<?php

namespace Subapp\Collection\Parameters\Parser;

use Subapp\Collection\Parameters\ParserInterface;

/**
 * Class JsonParser
 * @package Subapp\Collection\Parameters\Parser
 */
class JsonParser implements ParserInterface
{

    /**
     * @inheritDoc
     */
    public static function parse($contentString)
    {
        return json_decode($contentString, JSON_OBJECT_AS_ARRAY);
    }

    /**
     * @inheritDoc
     */
    public static function dump(array $parameters)
    {
        return json_encode($parameters, JSON_PRETTY_PRINT);
    }

}