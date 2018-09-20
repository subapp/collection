<?php

namespace Subapp\Collection;

/**
 * Interface CollectionInterface
 * @package Subapp\Collection
 */
interface CollectionInterface extends \ArrayAccess, \IteratorAggregate, \Countable, \JsonSerializable, \Serializable
{

    /**
     * @param $key
     * @return mixed
     */
    public function has($key);

    /**
     * @param $element
     * @return boolean
     */
    public function contains($element);

    /**
     * @param $element
     * @return integer
     */
    public function indexOf($element);

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @param array $keys
     * @return array
     */
    public function all(array $keys = []);

    /**
     * @param array $elements
     * @return $this
     */
    public function batch(array $elements);

    /**
     * @param $offset
     * @param $element
     * @return $this
     */
    public function set($offset, $element);

    /**
     * @param $element
     * @return $this
     */
    public function add($element);

    /**
     * @param $element
     * @return $this
     */
    public function push($element);

    /**
     * @param $element
     * @return $this
     */
    public function append($element);

    /**
     * @param $element
     * @return $this
     */
    public function prepend($element);

    /**
     * @param $key
     * @return mixed
     */
    public function remove($key);

    /**
     * @param \Closure $callback
     * @param \Closure|null $keyNameCallback
     * @return mixed
     */
    public function map(\Closure $callback, \Closure $keyNameCallback = null);

    /**
     * @param \Closure $closure
     * @return $this
     */
    public function each(\Closure $closure);

    /**
     * @param \Closure $closure
     * @return $this
     */
    public function filter(\Closure $closure);

    /**
     * @param \Closure $closure
     * @return $this
     */
    public function sort(\Closure $closure);

    /**
     * @return boolean
     */
    public function exists();

    /**
     * @return boolean
     */
    public function isEmpty();

    /**
     * @return boolean
     */
    public function isNotEmpty();

    /**
     * @return $this
     */
    public function clear();

    /**
     * @return object
     */
    public function toObject();

    /**
     * @return array
     */
    public function toArray();

    /**
     * @return string
     */
    public function toJSON();

}