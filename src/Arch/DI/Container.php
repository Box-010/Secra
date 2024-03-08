<?php

namespace Secra\Arch\DI;

use Exception;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Secra\Arch\DI\Attributes\Inject;
use Secra\Arch\DI\Attributes\Provide;
use Secra\Arch\DI\Attributes\Singleton;

#[Singleton]
class Container
{
    private array $dependencies = [];
    private array $singletons = [];

    public function __construct()
    {
        $this->set(Container::class, $this);
    }

    public function register(string $class)
    {
        $reflection = new ReflectionClass($class);
        $attributes = $reflection->getAttributes(Provide::class);
        if (isset($attributes[0])) {
            $provide = $attributes[0]->newInstance();
            $this->set($provide->class, $class);
        } else {
            throw new Exception("{$class} is not a provider");
        }
    }

    public function registerAll(string ...$classes)
    {
        foreach ($classes as $class) {
            $this->register($class);
        }
    }

    public function set($class, $implementation)
    {
        $this->dependencies[$class] = $implementation;
    }

    public function get($class)
    {
        if (isset($this->dependencies[$class])) {
            return $this->resolve($this->dependencies[$class]);
        }
        throw new Exception("{$class} is not registered");
    }

    private function resolve($dependency)
    {
        if (is_callable($dependency)) {
            // echo 'Resolving callable<br>';
            return $dependency();
        } elseif (is_string($dependency)) {
            // echo 'Resolving class: ' . $dependency . '<br>';
            if ($this->isSingleton($dependency)) {
                // echo 'Singleton<br>';
                if (!isset($this->singletons[$dependency])) {
                    // echo 'Creating new singleton<br>';
                    $this->singletons[$dependency] = $this->newInstance($dependency);
                }
                return $this->singletons[$dependency];
            } else {
                return $this->newInstance($dependency);
            }
        } else {
            return $dependency;
        }
    }

    private function newInstance($class)
    {
        // echo 'Creating new instance of ' . $class . '<br>';
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (is_null($constructor)) {
            // echo 'No constructor<br>';
            $instance = new $class;
        } else {
            // echo 'Constructor found<br>';
            $resolvedDependencies = $this->resolveDependencies($class, ...$constructor->getParameters());
            $instance = $reflection->newInstanceArgs($resolvedDependencies);
        }
        $this->resolveProperties($class, $instance);
        return $instance;
    }

    private function resolveProperties($class, $instance)
    {
        // echo 'Resolving properties for ' . $class . '<br>';
        $reflection = new ReflectionClass($class);
        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            // echo 'Inject: ' . $property->getName() . '<br>';
            $attributes = $property->getAttributes(Inject::class);
            if (isset($attributes[0])) {
                $property->setAccessible(true);
                $property->setValue($instance, $this->get($property->getType()->getName()));
            }
        }
    }

    private function resolveDependencies(string $class, ReflectionParameter ...$params)
    {
        return array_map(function (ReflectionParameter $param) use ($class) {
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType) {
                return $this->get($type->getName());
            }
            throw new Exception("{$class} has unresolvable dependencies: {$param->getName()}");
        }, $params);
    }

    private function isSingleton($class)
    {
        $reflection = new ReflectionClass($class);
        $attributes = $reflection->getAttributes(Singleton::class);
        return isset($attributes[0]);
    }
}
