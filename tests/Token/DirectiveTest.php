<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 00:51
 */

namespace Tivie\HtaccessParser\Token;

use Tivie\HtaccessParser\TestCase\BaseTestCase;

/**
 * Class DirectiveTest
 *
 * @covers \Tivie\HtaccessParser\Token\Directive
 * @author Estevão Soares dos Santos
 */
class DirectiveTest extends BaseTestCase
{
    /**
     * @var Directive
     */
    public $testClass;

    /**
     * @var string
     */
    public $key = 'myDirective';

    public function setUp()
    {
        $this->testClass = new Directive($this->key);
        parent::setUp();
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Directive::getName
     */
    public function testGetName()
    {
        self::assertEquals($this->key, $this->testClass->getName());
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Directive::setName
     */
    public function testSetName()
    {
        $newKey = "myNewDIrective";
        $this->testClass->setName($newKey);

        self::assertEquals($newKey, $this->testClass->getName());
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Directive::getTokenType
     */
    public function testGetTokenType()
    {
        self::assertEquals(TOKEN_DIRECTIVE, $this->testClass->getTokenType());
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Directive::jsonSerialize
     */
    public function testJsonSerialize()
    {
        $args = ['foo', 'bar', 'baz'];
        $this->setProperty("arguments", $args);

        $expectedOtp = json_encode($args);
        self::assertEquals($expectedOtp, json_encode($this->testClass));

    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Directive::__toString
     */
    public function test__toString()
    {
        $args = ['foo', 'bar', 'baz'];
        $this->setProperty("arguments", $args);

        $expectedOtp = "{$this->key} foo bar baz";
        self::assertEquals($expectedOtp, (string) $this->testClass, "Casting Directive to string does not produce the expected value");
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Directive::setArguments
     * @covers \Tivie\HtaccessParser\Token\Directive::getArguments
     */
    public function testSetGetArguments()
    {
        $args = array('foo', 'bar', 'baz');
        $this->testClass->setArguments($args);
        self::assertEquals($args, $this->testClass->getArguments());
    }

    /**
     * @expectedException \Tivie\HtaccessParser\Exception\DomainException
     */
    public function testSetArgumentsExceptions()
    {
        $args = array('foo', new \StdClass());
        $this->testClass->setArguments($args);
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Directive::addArgument
     * @covers \Tivie\HtaccessParser\Token\Directive::removeArgument
     */
    public function testAddRemoveArgument()
    {
        $arg = 'foo';
        $this->testClass->addArgument($arg);
        self::assertContains($arg, $this->testClass->getArguments(), "Argument was not added successfully");

        $this->testClass->removeArgument('bar');
        self::assertContains($arg, $this->testClass->getArguments(), "Argument was removed indecently");

        $this->testClass->removeArgument($arg);
        self::assertNotContains($arg, $this->testClass->getArguments(), "Argument was not removed");
    }
}
