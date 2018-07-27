<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 21.03.18
 * Time: 10:52
 */

namespace Phore\Di\Container\Producer;


use Phore\Di\Container\DiContainer;

interface DiResolvable
{
    public function resolve(DiContainer $container);
}