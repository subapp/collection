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
    protected $storage = [];

    /**
     * @var string
     */
    protected $className;

    /**
     * AbstractCollection constructor.
     * @param array $data
     * @param null|string $className
     */
    public function __construct(array $data = [], $className = null)
    {
        $this->setClassName($className)->batch($data);
    }

    /**
     * @inheritDoc
     */
    public function batch(array $data)
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
     * @param null|string $keyName
     * @param mixed $element
     * @param bool $prepend
     * @return $this
     * @throws CollectionException
     */
    protected function doSet($keyName = null, $element, $prepend = false)
    {
        if (null !== $this->className && !($element instanceof $this->className)) {
            throw new CollectionException(sprintf('Collection accept only objects which %s inherited', $this->className));
        }

        if (null === $keyName) {
            if (true === $prepend) {
                array_unshift($this->storage, $element);
            } else {
                array_push($this->storage, $element);
            }
        } else {
            $this->storage[$keyName] = $element;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     * @return $this
     * @throws CollectionException
     */
    public function setClassName($className)
    {
        if (null !== $className && !class_exists($className) && !interface_exists($className)) {
            throw new CollectionException(sprintf('Class %s could not be found. Please set existed class name', $className));
        }

        $this->className = $className;

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
        return empty($keys) ? $this->storage : array_intersect_key($this->storage, array_flip($keys));
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
        return array_keys($this->storage);
    }

    /**
     * @inheritDoc
     */
    public function map(\Closure $callback, \Closure $keyNameCallback = null)
    {
        $collection = new Collection();

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
        usort($this->storage, $closure);

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
        return count($this->storage);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        $this->storage = [];

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
        return new \ArrayIterator($this->storage);
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
        return array_key_exists($offset, $this->storage);
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
        return $this->has($offset) ? $this->storage[$offset] : $default;
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
        unset($this->storage[$key]);

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
        $this->batch(unserialize($serialized));
    }

}
