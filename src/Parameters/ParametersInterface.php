<?php

namespace Subapp\Collection\Parameters;

use Subapp\Collection\CollectionInterface;

/**
 * Interface ParametersInterface
 * @package Subapp\Parametres
 */
interface ParametersInterface extends CollectionInterface
{

    const PATH_SEPARATOR = '.';

    const PARSER_INI = 1;
    const PARSER_JSON = 2;
    const PARSER_YAML = 4;

    /**
     * @param $content
     * @param int $parser
     * @return ParametersInterface
     */
    public static function createFromString($content, $parser = self::PARSER_YAML);

    /**
     * @param string $filepath
     * @return ParametersInterface
     */
    public static function createFromFile($filepath);

    /**
     * @param $content
     * @return ParametersInterface
     */
    public static function createFromYaml($content);

    /**
     * @param $content
     * @return ParametersInterface
     */
    public static function createFromJson($content);

    /**
     * @param $content
     * @return ParametersInterface
     */
    public static function createFromIni($content);

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return string
     */
    public function toPHP();

    /**
     * @return string
     */
    public function toJSON();

    /**
     * @return string
     */
    public function toINI();

    /**
     * @return string
     */
    public function toYaml();

}