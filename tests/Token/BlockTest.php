<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 11:16
 */

namespace Tivie\HtaccessParser\Token;

use Tivie\HtaccessParser\BaseTestCase;
use Tivie\HtaccessParser\Exception\DomainException;

/**
 * Class BlockTest
 *
 * @author Estevão Soares dos Santos
 */
class BlockTest extends BaseTestCase
{
    /**
     * @var Block
     */
    public $testClass;

    public $blockName = 'SomeBlock';

    public function setUp(): void
    {
        $this->testClass = new Block($this->blockName);
        parent::setUp();
    }

    public function testSetGetName()
    {
        self::assertEquals($this->blockName, $this->testClass->getName(), "Failed getting block name");

        $name = 'SomeOtherBlock';
        $this->testClass->setName($name);
        self::assertEquals($name, $this->getProperty('blockName'), "Failed setting block name");
    }

    public function testGetTokenType()
    {
        self::assertEquals(TOKEN_BLOCK, $this->testClass->getTokenType());
    }

    public function testJsonSerialize()
    {
        $expectedArray = [
            'arguments' => [],
            'children' => [],
        ];
        self::assertEquals($expectedArray, $this->testClass->jsonSerialize());
    }

    public function test__toString()
    {
        $expectedString = "<SomeBlock>". PHP_EOL ."</SomeBlock>";
        self::assertEquals($expectedString, (string) $this->testClass);
    }

    public function testSetGetArguments()
    {
        $args = array('foo', 'bar', 'baz');
        $this->testClass->setArguments($args);
        self::assertEquals($args, $this->testClass->getArguments());
    }

    public function testSetArgumentsExceptions()
    {
        $this->expectException(DomainException::class);
        $args = array('foo', new \StdClass());
        $this->testClass->setArguments($args);
    }

    public function testAddRemoveArgument()
    {
        $arg = 'foo';
        $this->testClass->addArgument($arg);
        self::assertContains($arg, $this->getProperty('arguments'), "Argument was not added successfully to Block");

        $this->testClass->removeArgument('bar');
        self::assertContains($arg, $this->getProperty('arguments'), "Argument was removed indecently from Block");

        $this->testClass->removeArgument($arg);
        self::assertNotContains($arg, $this->getProperty('arguments'), "Argument was not removed from Block (and it should)");
    }

    public function testAddChild()
    {
        $child = $this->getMockBuilder('\Tivie\HtaccessParser\Token\TokenInterface')
                      ->getMock();
        $this->testClass->addChild($child);

        self::assertContains($child, $this->getProperty('children'), "Child token WAS NOT added successfully");
    }

    public function testRemoveChild()
    {
        $child = $this->getMockBuilder('\Tivie\HtaccessParser\Token\TokenInterface')
            ->getMock();

        $notChild = $this->getMockBuilder('\Tivie\HtaccessParser\Token\TokenInterface')
            ->getMock();

        $this->setProperty('children', array($child));

        //Test equal removal
        $this->testClass->removeChild($child);
        self::assertNotContains($child, $this->getProperty('children'), "Test EQUAL removal: Child was NOT removed from block but it should");


        $this->setProperty('children', array($child));

        // Test strict removal
        $this->testClass->removeChild($notChild, true);
        self::assertContains($child,$this->getProperty('children'), "Test STRICT removal: Child was removed from block but it shouldn't");

        // Test loose removal
        $this->testClass->removeChild($notChild, false);
        self::assertNotContains($child,$this->getProperty('children'), "Test LOOSE removal: Child was NOT removed from block but it should");


        $this->setProperty('children', array($child));

        // Test loose removal 2
        $child->foo = 'bar';
        $this->testClass->removeChild($notChild, false);
        self::assertContains($child, $this->getProperty('children'), "Test LOOSE removal 2: Child was removed from block but it shouldn't");

        // Test loose removal 3
        $notChild->foo = 'bazinga';
        $this->testClass->removeChild($notChild, false);
        self::assertContains($child, $this->getProperty('children'), "Test LOOSE removal 3: Child was removed from block but it shouldn't");

        // Test loose removal 4
        $notChild->foo = 'bar';
        $this->testClass->removeChild($notChild, false);
        self::assertNotContains($child, $this->getProperty('children'), "Test LOOSE removal 4: Child was NOT removed from block but it should");
    }
}
