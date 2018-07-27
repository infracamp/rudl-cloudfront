<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 21.03.18
 * Time: 10:49
 */

namespace Phore\Di\Container\Producer;


use Phore\Di\Container\DiContainer;

class DiValue implements DiResolvable
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function resolve (DiContainer $container)
    {
        return $this->value;
    }

}