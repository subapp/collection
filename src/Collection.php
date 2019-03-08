<?php

namespace Subapp\Collection;

/**
 * Class Collection
 * @package Subapp\Collection
 */
class Collection implements CollectionInterface
{

    /**
     * @var array
     */
    protected $elements = [];

    /**
     * @var string
     */
    protected $class;

    /**
     * AbstractCollection constructor.
     * @param array $data
     * @param null|string $className
     */
    public function __construct(array $data = [], $className = null)
    {
        $this->setClass($className)->asBatch($data);
    }
    
    /**
     * @param $element
     * @throws \InvalidArgumentException
     */
    private function validate($element)
    {
        if (!$this->isElementInstanceOf($element)) {
            throw new \InvalidArgumentException(sprintf('Collection accept only objects (%s) but (%s) passed',
                $this->getClass(), (is_object($element) ? get_class($element) : gettype($element))));
        }
    }
    
    /**
     * @param $element
     * @return boolean
     */
    private function isElementInstanceOf($element)
    {
        $class = $this->getClass();
        
        $isClassExist = class_exists($class);
        $isInstanceOf = $isClassExist && is_object($element) && !($element instanceOf $class);
        
        return !$isClassExist || ($isClassExist && !$isInstanceOf);
    }

    /**
     * @inheritDoc
     */
    public function asBatch(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function set($offset, $element)
    {
        return $this->doSet($offset, $element);
    }

    /**
     * @param null|string $key
     * @param mixed       $element
     * @param bool        $prepend
     * @return $this
     */
    protected function doSet($key = null, $element, $prepend = false)
    {
        $this->validate($element);

        if (null === $key) {
            if (true === $prepend) {
                array_unshift($this->elements, $element);
            } else {
                array_push($this->elements, $element);
            }
        } else {
            $this->elements[$key] = $element;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     * @throws CollectionException
     */
    public function setClass($class)
    {
        if (null !== $class && !class_exists($class) && !interface_exists($class)) {
            throw new CollectionException(sprintf('Class %s could not be found. Please set existed class name', $class));
        }

        $this->class = $class;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function add($element)
    {
        return $this->push($element);
    }

    /**
     * @inheritDoc
     */
    public function push($element)
    {
        return $this->doSet(null, $element);
    }

    /**
     * @inheritDoc
     */
    public function append($element)
    {
        return $this->push($element);
    }

    /**
     * @inheritDoc
     */
    public function prepend($element)
    {
        return $this->doSet(null, $element, true);
    }

    /**
     * @inheritDoc
     */
    public function contains($element)
    {
        return in_array($element, $this->all());
    }

    /**
     * @inheritDoc
     */
    public function all(array $keys = [])
    {
        return empty($keys) ? $this->elements : array_intersect_key($this->elements, array_flip($keys));
    }

    /**
     * @inheritDoc
     */
    public function indexOf($element)
    {
        return array_search($element, $this->all());
    }

    /**
     * @inheritDoc
     */
    public function keys()
    {
        return array_keys($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function map(\Closure $callback, \Closure $keyNameCallback = null)
    {
        $collection = new static();

        $this->each(function ($key, $element) use ($collection, $callback, $keyNameCallback) {
            $keyName = $keyNameCallback instanceof \Closure ? $keyNameCallback($element) : $key;
            $collection->set($keyName, $callback($element));
        });

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function each(\Closure $closure)
    {
        foreach ($this as $key => $data) {
            $closure($key, $data);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filter(\Closure $closure)
    {
        $elements = [];

        $this->each(function ($key, $element) use (&$elements, $closure) {
            if (false !== $closure($element, $key)) {
                $elements[$key] = $element;
            }
        });

        return new Collection($elements);
    }

    /**
     * @inheritDoc
     */
    public function sort(\Closure $closure)
    {
        usort($this->elements, $closure);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty()
    {
        return !$this->isNotEmpty();
    }

    /**
     * @inheritDoc
     */
    public function isNotEmpty()
    {
        return $this->exists();
    }

    /**
     * @inheritDoc
     */
    public function exists()
    {
        return $this->count() > 0;
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->elements = [];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toObject()
    {
        $objectData = new \stdClass();

        foreach ($this as $key => $data) {
            $objectData->{$key} = ($data instanceof CollectionInterface) ? $data->toObject() : $data;
        }

        return $objectData;
    }

    /**
     * @inheritDoc
     */
    public function toJSON()
    {
        return json_encode($this);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @inheritDoc
     */
    public function has($offset)
    {
        return array_key_exists($offset, $this->elements);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->get($offset, null);
    }

    /**
     * @inheritDoc
     */
    public function get($offset, $default = null)
    {
        return $this->has($offset) ? $this->elements[$offset] : $default;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $element)
    {
        $this->doSet($offset, $element, false);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * @inheritDoc
     */
    public function remove($key)
    {
        unset($this->elements[$key]);

        return $this;
    }

    /**
     * @inheritDoc
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        $arrayData = [];

        foreach ($this as $key => $data) {
            $arrayData[$key] = ($data instanceof CollectionInterface) ? $data->toArray() : $data;
        }

        return $arrayData;
    }

    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        $this->asBatch(unserialize($serialized));
    }

}
