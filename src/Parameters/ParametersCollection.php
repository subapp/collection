<?php

namespace Subapp\Collection\Parameters;

use Subapp\Collection\Collection;
use Subapp\Collection\Parameters\Parser\IniParser;
use Subapp\Collection\Parameters\Parser\JsonParser;
use Subapp\Collection\Parameters\Parser\YamlParser;

/**
 * Class ParametersCollection
 * @package Subapp\Collection\Parameters
 */
class ParametersCollection extends Collection implements ParametersInterface
{

    /**
     * @inheritDoc
     */
    protected function doSet($keyName = null, $element, $prepend = false)
    {
        if (is_array($element)) {
            $element = new static($element);
        }
    
        return parent::doSet($keyName, $element, $prepend);
    }
    
    /**
     * @inheritDoc
     */
    public static function createFromFile($filepath)
    {
        $file = new \SplFileInfo($filepath);

        switch ($file->getExtension()) {
            case 'yml':
            case 'yaml':
                return static::createFromYaml(file_get_contents($file->getRealPath()));

            case 'json':
                return static::createFromJson(file_get_contents($file->getRealPath()));

            case 'ini':
                return static::createFromIni(file_get_contents($file->getRealPath()));

            case 'php':
                return new static(include_once $file->getRealPath());

            default:
                throw new \InvalidArgumentException(sprintf('Not supported file format ".%s" for parsing parameters', $file->getExtension()));
        }
    }

    /**
     * @inheritDoc
     */
    public static function createFromYaml($content)
    {
        return static::createFromString($content, ParametersInterface::PARSER_YAML);
    }

    /**
     * @inheritDoc
     */
    public static function createFromString($content, $parser = self::PARSER_YAML)
    {
        switch ($parser) {
            case ParametersInterface::PARSER_YAML:
                $parameters = YamlParser::parse($content);
                break;

            case ParametersInterface::PARSER_JSON:
                $parameters = JsonParser::parse($content);
                break;

            case ParametersInterface::PARSER_INI:
                $parameters = IniParser::parse($content);
                break;

            default:
                throw new \InvalidArgumentException('Cannot determine parser');
        }

        return new static($parameters);
    }

    /**
     * @inheritDoc
     */
    public static function createFromJson($content)
    {
        return static::createFromString($content, ParametersInterface::PARSER_JSON);
    }

    /**
     * @inheritDoc
     */
    public static function createFromIni($content)
    {
        return static::createFromString($content, ParametersInterface::PARSER_INI);
    }

    /**
     * @inheritDoc
     */
    public function bulk(array $parameters)
    {
        foreach ($parameters as $offset => $parameter) {
            $this->set($offset, $parameter);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function merge(ParametersInterface $parameters)
    {
        $this->mergeRecursive($parameters, $this);

        return $this;
    }

    /**
     * @param ParametersInterface $parameters
     * @param ParametersInterface $instance
     */
    private function mergeRecursive(ParametersInterface $parameters, ParametersInterface $instance)
    {
        foreach ($parameters as $offset => $parameter) {
            if (isset($instance[$offset]) && ($instance[$offset] instanceof ParametersInterface) && ($parameter instanceof ParametersInterface)) {
                $this->mergeRecursive($parameter, $instance[$offset]);
            } else {
                $instance[$offset] = $parameter;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function toPHP()
    {
        return sprintf('%s;', var_export($this->toArray(), true));
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $array = [];

        foreach ($this as $offset => $parameter) {
            $array[$offset] = ($parameter instanceof ParametersInterface) ? $parameter->toArray() : $parameter;
        }

        return $array;
    }

    /**
     * @inheritDoc
     */
    public function toJSON()
    {
        return JsonParser::dump($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function toINI()
    {
        return IniParser::dump($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function toYaml()
    {
        return YamlParser::dump($this->toArray());
    }

    /**
     * @return $this
     */
    public function handlePlaceholders()
    {
        return $this->parsePlaceholders($this);
    }

    /**
     * @param ParametersInterface $parameters
     * @return $this
     */
    private function parsePlaceholders(ParametersInterface $parameters)
    {
        foreach ($parameters as $offset => $parameter) {
            if ($parameter instanceof ParametersInterface) {
                $this->parsePlaceholders($parameter);
            } elseif (gettype($parameter) === 'string') {
                $parameters->set($offset, $this->parsePlaceholderValue($parameter));
            }
        }

        return $this;
    }

    /**
     * @param $parameterValue
     * @param array $visitor
     * @return mixed
     */
    private function parsePlaceholderValue($parameterValue, array &$visitor = [])
    {
        $matched = (boolean)preg_match_all('/\{([\.a-z0-9_-]+)\}/uis', $parameterValue, $matchesAll, PREG_SET_ORDER);

        if (strpos($parameterValue, '{') !== false && $matched) {
            // Handle each matches and replace placeholders
            foreach ($matchesAll as $matches) {

                list($placeholder, $path) = $matches;
                $replaceValue = $this->path($path);

                if (isset($visitor[$path])) {
                    $parameterValue = sprintf('[RECURSION: %s]', array_pop($visitor));
                    continue;
                }

                $visitor[$path] = $placeholder;

                $replaceValue = $this->parsePlaceholderValue($replaceValue, $visitor);
                $parameterValue = str_replace($placeholder, $replaceValue, $parameterValue);
            }
        }

        return $parameterValue;
    }

    /**
     * @inheritDoc
     */
    public function path($offset, $separator = self::PATH_SEPARATOR)
    {
        $offsetParts = explode($separator, $offset);
        $instance = $this;

        foreach ($offsetParts as $offsetPart) {
            if ($instance instanceof ParametersInterface) {
                $instance = $instance[$offsetPart];
            }
        }

        return $instance;
    }

}
