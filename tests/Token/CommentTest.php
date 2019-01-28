<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 11:08
 */

namespace Tivie\HtaccessParser\Token;

use Tivie\HtaccessParser\TestCase\BaseTestCase;

/**
 * Class CommentTest
 * @covers \Tivie\HtaccessParser\Token\Comment
 * @author Estevão Soares dos Santos
 */
class CommentTest extends BaseTestCase
{
    /**
     * @var Comment
     */
    public $testClass;

    public function setUp()
    {
        $this->testClass = new Comment();
        parent::setUp();
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Comment::getName
     */
    public function testGetName()
    {
        $name = '#comment';
        self::assertEquals($name, $this->testClass->getName());
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Comment::getTokenType
     */
    public function testGetTokenType()
    {
        self::assertEquals(TOKEN_COMMENT, $this->testClass->getTokenType());
    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Comment::jsonSerialize
     * @covers \Tivie\HtaccessParser\Token\Comment::__toString
     */
    public function testJsonSerializeAnToString()
    {
        $text = "This is a comment";
        $this->setProperty("text", $text);

        $expectedOtp = json_encode($text);
        self::assertEquals($text, (string) $this->testClass, "Casting Comment to string does not produce the expected value");
        self::assertEquals($expectedOtp, json_encode($this->testClass));

    }

    /**
     * @covers \Tivie\HtaccessParser\Token\Comment::setText
     * @covers \Tivie\HtaccessParser\Token\Comment::getText
     */
    public function testSetGetText()
    {
        $text = "This is a comment";
        $this->testClass->setText($text);
        self::assertEquals('# ' .$text, $this->testClass->getText());
    }
}
