<?php
    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 02.12.16
     * Time: 11:31
     */

    namespace Phore\Di\Builder;



    class PhoreParameterBuilder
    {


        private function buildFromParameterArray (array $parameterRef) : PhoreCallbackParameterDef {
            $paramDef = new PhoreCallbackParameterDef();
            for ($paramIndex = 0; $paramIndex<count ($parameterRef); $paramIndex++) {
                $curParam = $parameterRef[$paramIndex];

                $className = null;
                if ($curParam->getClass() !== null) {
                    $className = $curParam->getClass()->getName();
                }

                $defaultValue = null;
                if ($curParam->isDefaultValueAvailable()) {
                    $defaultValue = $curParam->getDefaultValue();
                }

                $paramDef->parameters[] = [
                    $curParam->getName(),
                    $curParam->getType(),
                    $className,
                    $defaultValue,
                    $curParam->allowsNull(),
                    $curParam->isOptional(),
                    $curParam->isArray(),
                    $paramIndex
                ];
            }
            return $paramDef;
        }


        public function buildParamDefForConstructor ($class) : PhoreCallbackParameterDef
        {
            $ref = new \ReflectionClass($class);
            if (($constructorRef = $ref->getConstructor()) === null) {
                return new PhoreCallbackParameterDef();
            }
            return $this->buildFromParameterArray($constructorRef->getParameters());
        }


        public function buildParamDef (callable $callable) : PhoreCallbackParameterDef {
            if (is_array($callable)) {
                if (is_object($callable[0])) {
                    $ref = new \ReflectionMethod(get_class($callable[0]), $callable[1]);
                } else if (is_string($callable[0])) {
                    $ref = new \ReflectionMethod($callable[0], $callable[1]);
                } else {
                    throw new \InvalidArgumentException("Array is no valid callback." . var_export($callable, true));
                }
            } else {
                $ref = new \ReflectionFunction($callable);
            }
            return $this->buildFromParameterArray($ref->getParameters());
        }

        public function buildParams (PhoreCallbackParameterDef $paramDef, PhoreParameterBuilderCallback $builder, array $values)
        {
            $params = [];
            foreach ($paramDef->parameters as $curParams) {
                $params[] = $builder->buildValue($values, ...$curParams);
            }
            return $params;

        }

    }