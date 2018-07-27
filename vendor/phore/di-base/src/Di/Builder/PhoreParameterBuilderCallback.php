<?php
    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 02.12.16
     * Time: 11:33
     */

    namespace Phore\Di\Builder;


    interface PhoreParameterBuilderCallback
    {
        public function buildValue (array $values, string $paramName, string $paramType = null, string $paramClassName = null, $paramDefault, bool $paramAllowsNull, bool $paramIsOptional, bool $paramIsArray, int $paramIndex);
    }