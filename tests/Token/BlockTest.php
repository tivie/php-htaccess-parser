<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 11:16
 */

namespace Tivie\HtaccessParser\Token;

use Tivie\HtaccessParser\TestCase\BaseTestCase;

/**
 * Class BlockTest
 *
 * @covers \Tivie\HtaccessParser\Token\Block
 * @author Estevão Soares dos Santos
 */
class BlockTest extends BaseTestCase
{
    /**
     * @var Block
     */
    public $testClass;

    public $blockName = 'SomeBlock';

    public function setUp()
    {
        $this->testClass = new Block($this->blockName);
        parent::setUp();
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Block::getName
     */
    public function testSetGetName()
    {
        self::assertEquals($this->blockName, $this->testClass->getName(), "Failed getting block name");

        $name = 'SomeOtherBlock';
        $this->testClass->setName($name);
        self::assertEquals($name, $this->getProperty('blockName'), "Failed setting block name");
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Block::getTokenType
     */
    public function testGetTokenType()
    {
        self::assertEquals(TOKEN_BLOCK, $this->testClass->getTokenType());
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Block::jsonSerialize
     */
    public function testJsonSerialize()
    {
        $expectedArray = [
            'arguments' => [],
            'children' => [],
        ];
        self::assertEquals($expectedArray, $this->testClass->jsonSerialize());
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Block::__toString
     */
    public function test__toString()
    {
        $expectedString = "<SomeBlock>\n</SomeBlock>";
        self::assertEquals($expectedString, (string) $this->testClass);
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Block::setArguments
     * @covers \Tivie\HtaccessParser\Token\Block::getArguments
     */
    public function testSetGetArguments()
    {
        $args = array('foo', 'bar', 'baz');
        $this->testClass->setArguments($args);
        self::assertEquals($args, $this->testClass->getArguments());
    }

    /**
     * @expectedException \Tivie\HtaccessParser\Exception\DomainException
     * @covers \Tivie\HtaccessParser\Token\Block::setArguments
     */
    public function testSetArgumentsExceptions()
    {
        $args = array('foo', new \StdClass());
        $this->testClass->setArguments($args);
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Block::addArgument
     * @covers \Tivie\HtaccessParser\Token\Block::removeArgument
     */
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

    /**
     * @covers \Tivie\HtaccessParser\Token\Block::addChild
     */
    public function testAddChild()
    {
        $child = $this->getMockBuilder('\Tivie\HtaccessParser\Token\TokenInterface')
                      ->setMethods(array())
                      ->getMock();
        $this->testClass->addChild($child);

        self::assertContains($child, $this->getProperty('children'), "Child token WAS NOT added successfully");
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Block::removeChild
     */
    public function testRemoveChild()
    {
        $child = $this->getMockBuilder('\Tivie\HtaccessParser\Token\TokenInterface')
            ->setMethods(array())
            ->getMock();

        $notChild = $this->getMockBuilder('\Tivie\HtaccessParser\Token\TokenInterface')
            ->setMethods(array())
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
