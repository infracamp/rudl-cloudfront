<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 21.03.18
 * Time: 10:40
 */

namespace Phore\Di\Container;


use Phore\Di\Builder\PhoreParameterBuilderCallback;
use Phore\Di\Container\Producer\DiResolvable;
use Phore\Di\Container\Producer\DiService;
use Phore\Di\Container\Producer\DiValue;
use Phore\Di\Caller\PhoreBaseDiCaller;
use Psr\Container\ContainerInterface;

class DiContainer extends PhoreBaseDiCaller implements ContainerInterface
{

    /**
     * @var DiResolvable[]
     */
    private $instances = [];

    public function __construct()
    {
        parent::__construct();
        $this->__setBuilder(new class ($this) implements PhoreParameterBuilderCallback {

            private $diContainer;

            public function __construct(DiContainer $diContainer)
            {
                $this->diContainer = $diContainer;
            }

            /**
             * @param array       $values
             * @param string      $paramName
             * @param string|null $paramType
             * @param string|null $paramClassName
             * @param             $paramDefault
             * @param bool        $paramAllowsNull
             * @param bool        $paramIsOptional
             * @param bool        $paramIsArray
             * @param int         $paramIndex
             *
             * @return null
             * @throws DiUnresolvableException
             * @throws \ErrorException
             */
            public function buildValue(
                array $values,
                string $paramName,
                string $paramType = null,
                string $paramClassName = null,
                $paramDefault,
                bool $paramAllowsNull,
                bool $paramIsOptional,
                bool $paramIsArray,
                int $paramIndex
            ) {
                // Check if it is Resolvable by Argument Name
                if ($this->diContainer->isResolvable($paramName, $values)) {
                    return $this->diContainer->resolve($paramName, $values);
                }
                // Check if it is resolvable by argument class name hint
                if ($paramClassName !== null && $this->diContainer->isResolvable($paramClassName, $values)) {
                    return $this->diContainer->resolve($paramClassName, $values);
                }

                if (!$paramDefault !== null)
                    return $paramDefault;

                if ($paramIsOptional)
                    return null;
                throw new DiUnresolvableInternalException("Cannot resolve '$paramName' nor '$paramClassName' in argument#$paramIndex");
            }
        });
        $this->add(self::class, new DiValue($this));
    }


    public function has($name) : bool
    {
        return $this->isResolvable($name);
    }

    /**
     * @param $name
     *
     * @return mixed
     * @throws DiUnresolvableException
     * @throws \ErrorException
     */
    public function get($name)
    {
        return $this->resolve($name);
    }


    public function isResolvable($name, array $addParams = []) : bool
    {
        if (isset ($addParams[$name])) {
            return true;
        } else if (isset ($this->instances[$name])) {
            return true;
        }
        return false;
    }


    /**
     * @param       $name
     * @param array $addParams
     *
     * @return
     * @throws DiUnresolvableException
     * @throws \ErrorException
     */
    public function resolve($name, array $addParams = [])
    {
        if (isset ($addParams[$name])) {
            $resv = $addParams[$name];
            $resv = $this->argumentToResolvable($resv);
        } else if ($this->instances[$name]) {
            $resv = $this->instances[$name];
        } else {
            throw new DiUnresolvableException("'$name' is not resolvable by di-container.");
        }
        if (!$resv instanceof DiResolvable) {
            throw new \ErrorException(
                "Expected DiResolvable. Found: ".gettype($resv)
                ." for name '$name'"
            );
        }
        return $resv->resolve($this);
    }


    /**
     * Wrap allowed arguments (callbacks, etc) into DiResolvable Objects
     *
     * @param $input
     *
     * @return DiResolvable
     */
    public function argumentToResolvable ($input) : DiResolvable
    {
        if ( ! $input instanceof DiResolvable) {
            if (is_callable($input)) {
                return new DiService($input);
            } else {
                return new DiValue($input);
            }
        }
        return $input;
    }

    /**
     * Add a new Value/Factory to Dependency Injection
     *
     * <Example>
     * $di->add("param1", "New Value");
     * $di->add("param1", function () { echo "new value"; });
     * $di->add("param1", new DiService());
     * </Example>
     *
     * @deprecated Use define() instead
     *
     * @param $name
     * @param $factoryOrValue
     *
     *
     * @return DiContainer
     */
    public function add ($name, $factoryOrValue) : self
    {
        return $this->define($name, $factoryOrValue);
    }

    /**
     * Add a new Value/Factory to Dependency Injection
     *
     * <Example>
     * $di->add("param1", "New Value");
     * $di->add("param1", function () { echo "new value"; });
     * $di->add("param1", new DiService());
     * </Example>
     *
     * @param $name
     * @param $factoryOrValue
     *
     * @return DiContainer
     */
    public function define ($name, $factoryOrValue) : self
    {
        $factoryOrValue = $this->argumentToResolvable($factoryOrValue);
        $this->instances[$name] = $factoryOrValue;
        return $this;
    }

}