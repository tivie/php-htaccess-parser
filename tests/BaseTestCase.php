<?php
/**
 * Created by PhpStorm.
 * User: Estevao
 * Date: 03-12-2014
 * Time: 10:59
 */

namespace Tivie\HtaccessParser;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

abstract class BaseTestCase extends TestCase
{
    protected $testClass;

    /**
     * @var ReflectionClass
     */
    protected ReflectionClass $reflection;

    public function setUp(): void
    {
        $this->reflection = new ReflectionClass($this->testClass);
    }

    public function getMethod($method): ReflectionMethod
    {
        $method = $this->reflection->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }

    public function getProperty($property)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($this->testClass);
    }

    public function setProperty($property, $value): void
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);

        $property->setValue($this->testClass, $value);
    }
}
