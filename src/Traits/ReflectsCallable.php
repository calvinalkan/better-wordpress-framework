<?php

declare(strict_types=1);

namespace Snicco\Traits;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ReflectsCallable
{
    
    private function unwrap(Closure $closure)
    {
        
        $reflection = new ReflectionFunction($closure);
        
        $static_vars = $reflection->getStaticVariables();
        
        return $static_vars['closure'] ?? Arr::first($static_vars);
        
    }
    
    /**
     * @param  string|array|Closure  $callable
     * @param $payload
     *
     * @return array
     * @throws ReflectionException
     */
    private function buildNamedParameters($callable, $payload) :array
    {
        
        if (is_string($callable)) {
            
            $callable = Str::parseCallback($callable);
            
        }
        
        $payload = ( ! is_array($payload)) ? [$payload] : $payload;
        
        $call_reflector = $this->getCallReflector($callable, 'handle');
        
        $params = collect($call_reflector->getParameters());
        
        $parameter_names = $params->map(function ($param) {
            
            return $param->getName();
            
        });
        
        if ($parameter_names->isEmpty()) {
            
            return $payload;
            
        }
        
        $reduced = $parameter_names->slice(0, count(($payload)));
        
        $payload = $reduced->combine($payload);
        
        return $payload->all();
        
    }
    
    /**
     * @param  array|Closure  $callback
     * @param  string  $default_method
     *
     * @return ReflectionFunction|ReflectionMethod
     * @throws ReflectionException
     */
    private function getCallReflector($callback, string $default_method = '')
    {
        
        if ($this->isClosure($callback)) {
            return new ReflectionFunction($callback);
        }
        
        [$class, $method] = ($this->classExists($callback[0]))
            ? [$callback[0], $callback[1] ?? $default_method]
            : Str::parseCallback($callback[0], $default_method);
        
        return new ReflectionMethod($class, $method);
        
    }
    
    /**
     * Accepts a string that contains and @ and returns the part before the @.
     *
     * @param $object
     *
     * @return bool
     */
    protected function isClosure($object) :bool
    {
        
        return $object instanceof Closure;
        
    }
    
    /**
     * @param $class_name_or_object
     *
     * @return bool
     */
    private function classExists($class_name_or_object) :bool
    {
        
        if (is_object($class_name_or_object)) return true;
        
        return class_exists($class_name_or_object);
        
    }
    
    private function classImplements($class, $interface) :bool
    {
        
        $used_interfaces = class_implements($class);
        
        return isset($used_interfaces[$interface]);
        
    }
    
    /**
     * @throws ReflectionException
     */
    private function buildNamedConstructorArgs(string $class, $arguments)
    {
        
        $payload = Arr::wrap($arguments);
        
        $constructor = (new ReflectionClass($class))->getConstructor();
        
        if ( ! $constructor) {
            
            return $arguments;
            
        }
        
        $params = collect($constructor->getParameters());
        
        $parameter_names = $params->map(function ($param) {
            
            return $param->getName();
            
        });
        
        if ($parameter_names->isEmpty()) {
            
            return $payload;
            
        }
        
        $reduced = $parameter_names->slice(0, count(($payload)));
        
        $payload = $reduced->combine($payload);
        
        return $payload->all();
        
    }
    
    /**
     * @param  string|object  $class
     */
    private function getClass($class)
    {
        
        if ( ! $this->classExists($class)) {
            
            return null;
            
        }
        
        if (is_string($class)) {
            
            return $class;
            
        }
        
        return get_class($class);
        
    }
    
}