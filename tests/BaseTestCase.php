<?php
/**
 * Created by PhpStorm.
 * User: Estevao
 * Date: 03-12-2014
 * Time: 10:59
 */

abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    protected $testClass;

    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    public function setUp()
    {
        $this->reflection = new \ReflectionClass($this->testClass);
    }

    public function getMethod($method)
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

    public function setProperty($property, $value)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);

        $property->setValue($this->testClass, $value);
    }
}
