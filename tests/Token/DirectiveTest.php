<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 00:51
 */

namespace Tivie\HtaccessParser\Token;

use Tivie\HtaccessParser\BaseTestCase;

/**
 * Class DirectiveTest
 *
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

    public function setUp(): void
    {
        $this->testClass = new Directive($this->key);
        parent::setUp();
    }

    public function testGetName()
    {
        self::assertEquals($this->key, $this->testClass->getName());
    }

    public function testSetName()
    {
        $newKey = "myNewDIrective";
        $this->testClass->setName($newKey);

        self::assertEquals($newKey, $this->testClass->getName());
    }

    public function testGetTokenType()
    {
        self::assertEquals(TOKEN_DIRECTIVE, $this->testClass->getTokenType());
    }

    public function testJsonSerialize()
    {
        $args = ['foo', 'bar', 'baz'];
        $this->setProperty("arguments", $args);

        $expectedOtp = json_encode($args);
        self::assertEquals($expectedOtp, json_encode($this->testClass));

    }

    public function test__toString()
    {
        $args = ['foo', 'bar', 'baz'];
        $this->setProperty("arguments", $args);

        $expectedOtp = "{$this->key} foo bar baz";
        self::assertEquals($expectedOtp, (string) $this->testClass, "Casting Directive to string does not produce the expected value");
    }

    public function testSetGetArguments()
    {
        $args = array('foo', 'bar', 'baz');
        $this->testClass->setArguments($args);
        self::assertEquals($args, $this->testClass->getArguments());
    }

    public function testSetArgumentsExceptions()
    {
        $this->expectException(\Tivie\HtaccessParser\Exception\DomainException::class);
        $args = array('foo', new \StdClass());
        $this->testClass->setArguments($args);
    }

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
