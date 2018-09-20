<?php

namespace Subapp\Collection;

/**
 * Interface ProxyInterface
 * @package Subapp\Collection
 */
interface ProxyInterface
{

    /**
     * @return mixed
     */
    public function initialize();

    /**
     * @return boolean
     */
    public function isInitialized();

}