<?php
/**
 * -- PHP Htaccess Parser --
 * HtaccessContainerTest.php created at 05-12-2014
 *
 * Copyright 2014 Estevão Soares dos Santos
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

namespace Tivie\HtaccessParser;

use Tivie\HtaccessParser\Exception\InvalidArgumentException;
use Tivie\HtaccessParser\Token\TokenInterface;

const TOKEN_DIRECTIVE = 0;
const TOKEN_BLOCK = 1;
const TOKEN_COMMENT = 2;
const TOKEN_WHITELINE = 3;

class HtaccessContainerTest extends BaseTestCase
{

    /**
     * @var HtaccessContainer
     */
    public $testClass;

    /**
     * @var TokenInterface
     */
    public $genericToken;

    public function setUp(): void
    {
        $this->testClass = new HtaccessContainer();
        $this->genericToken = $this->createTokenMock();
        parent::setUp();
    }

    public function testOffsetSet()
    {
        $htaccess = new HtaccessContainer();
        $token = $this->createTokenMock(1);
        $htaccess->offsetSet(null, $token);
        self::assertContains($token, $htaccess);
        self::assertSame($token, $htaccess[0]);

        $token = $this->createTokenMock();
        $htaccess[] = $token;
        self::assertContains($token, $htaccess);
        self::assertSame($token, $htaccess[1]);


        $htaccess = new HtaccessContainer();
        $htaccess->offsetSet(5, $token);
        self::assertSame($token, $htaccess[5]);

        $token = $this->createTokenMock(2);
        $htaccess[5] = $token;
        self::assertContains($token, $htaccess);
        self::assertSame($token, $htaccess[5]);
    }

    public function testOffsetSet2()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->testClass[] = 'foobar';
    }

    public function testOffsetSet3()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->testClass['foo'] = $this->createTokenMock();
    }

    public function testSearch()
    {
        $this->testClass[] = $this->createTokenMock();
        $this->testClass[] = $this->createTokenMock();

        $mock = $this->getMockBuilder('\Tivie\HtaccessParser\Token\Block')
                     ->onlyMethods(['getName', 'getTokenType', 'hasChildren'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('getName')
             ->willReturn('fooBlock');

        $mock->expects($this->any())
             ->method('getTokenType')
             ->willReturn(TOKEN_BLOCK);

        $mock->expects($this->any())
             ->method('hasChildren')
             ->willReturn(TRUE);

        $mockChild = $this->getMockBuilder('\Tivie\HtaccessParser\Token\Directive')
             ->onlyMethods(['getName', 'getTokenType'])
             ->getMock();

        $mockChild->expects($this->any())
             ->method('getName')
             ->willReturn('fooDirective');

        $mock[] = $this->testClass[] = $this->createTokenMock();
        $mock[] = $this->testClass[] = $this->createTokenMock();
        $mock[] = $mockChild;

        $this->testClass[] = $mock;

        self::assertSame($mock, $this->testClass->search('fooBlock', TOKEN_BLOCK, true), "Search method failed to return the correct token");
        self::assertSame($mockChild, $this->testClass->search('fooDirective', null, true), "Search method failed to return the correct token");
    }

    public function testSlice()
    {
        $htaccess = $this->fillContainer($this->testClass, 6, true);

        $offset = 2;
        $length = 3;

        $array = $htaccess->getArrayCopy();
        $expArr = array_slice($array, $offset, $length);
        $expObj = new HtaccessContainer($expArr);

        self::assertSame($expArr, $htaccess->slice($offset, $length, false, true));
        self::assertEquals($expObj, $htaccess->slice($offset, $length));
    }

    public function testSplice()
    {
        $tokenM1 = $this->genericToken;
        $tokenM2 = $this->createTokenMock(TOKEN_BLOCK);
        $htaccess = $this->fillContainer($this->testClass);
        $array = [$tokenM2, $tokenM2, $tokenM2];
        $max = count($htaccess);
        $spliced = $htaccess->splice(1, $max, $array);

        self::assertSame($tokenM1, $htaccess[0]);
        self::assertNotSame($tokenM1, $htaccess[1]);
        self::assertNotSame($tokenM1, $htaccess[2]);
        self::assertNotSame($tokenM1, $htaccess[3]);

        self::assertNotSame($tokenM2, $htaccess[0]);
        self::assertSame($tokenM2, $htaccess[1]);
        self::assertSame($tokenM2, $htaccess[2]);
        self::assertSame($tokenM2, $htaccess[3]);

        $expReturn = [];
        for ($i=0;$i<$max-1;++$i) {
            $expReturn[] = $tokenM1;
        }
        self::assertSame($expReturn, $spliced);
    }

    public function testInsertAt()
    {
        $tokenM1 = $this->createTokenMock(null);
        $testToken = $this->createTokenMock(TOKEN_DIRECTIVE);

        $htaccess = $this->testClass;
        $htaccess[0] = $tokenM1;
        $htaccess[1] = $tokenM1;
        $htaccess[2] = $tokenM1;
        $htaccess[3] = $tokenM1;

        $htaccess->insertAt(2, $testToken);


        self::assertContains($testToken, $htaccess, "Token was not inserted in HtaccessContainer object");
        self::assertSame($testToken, $htaccess[2], "Token was not inserted in HtaccessContainer at the correc index");
        self::assertNotSame($tokenM1, $htaccess[2], "Failed asserting that TestToken is different from tokenM1");
    }

    protected function fillContainer($htaccess = null, $num = 6, $rand = false)
    {
        $htaccess = ($htaccess) ? $htaccess : $this->testClass;

        for($i=0;$i<$num;++$i) {
            if ($rand) {
                $htaccess[] = $this->createTokenMock(mt_rand(0,4));
            } else {
                $htaccess[] = $this->genericToken;
            }
        }

        return $htaccess;
    }

    protected function createTokenMock($type = null)
    {
        return match ($type) {
            TOKEN_DIRECTIVE => $this->getMockBuilder('\Tivie\HtaccessParser\Token\Directive')
                                    ->getMock(),
            TOKEN_BLOCK => $this->getMockBuilder('\Tivie\HtaccessParser\Token\Block')
                                ->getMock(),
            TOKEN_COMMENT => $this->getMockBuilder('\Tivie\HtaccessParser\Token\Comment')
                                  ->getMock(),
            TOKEN_WHITELINE => $this->getMockBuilder('\Tivie\HtaccessParser\Token\WhiteLine')
                                    ->getMock(),
            default => $this->getMockBuilder('\Tivie\HtaccessParser\Token\TokenInterface')
                            ->getMock(),
        };
    }

}
