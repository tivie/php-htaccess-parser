<?php
/**
 * Created by PhpStorm.
 * Date: 03-12-2014
 * Time: 11:08
 */

namespace Tivie\HtaccessParser\Token;

use Tivie\HtaccessParser\BaseTestCase;

/**
 * Class CommentTest
 * @author Estevão Soares dos Santos
 */
class CommentTest extends BaseTestCase
{
    /**
     * @var Comment
     */
    public $testClass;

    public function setUp(): void
    {
        $this->testClass = new Comment();
        parent::setUp();
    }

    public function testGetName()
    {
        $name = '#comment';
        self::assertEquals($name, $this->testClass->getName());
    }

    public function testGetTokenType()
    {
        self::assertEquals(TOKEN_COMMENT, $this->testClass->getTokenType());
    }

    public function testJsonSerializeAnToString()
    {
        $text = "This is a comment";
        $this->setProperty("text", $text);

        $expectedOtp = json_encode($text);
        self::assertEquals($text, (string) $this->testClass, "Casting Comment to string does not produce the expected value");
        self::assertEquals($expectedOtp, json_encode($this->testClass));

    }

    public function testSetGetText()
    {
        $text = "This is a comment";
        $this->testClass->setText($text);
        self::assertEquals('# ' .$text, $this->testClass->getText());
    }
}
