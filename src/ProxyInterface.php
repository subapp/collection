<?php

namespace Colibri\Collection;

/**
 * Interface ProxyInterface
 * @package Colibri\Collection
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