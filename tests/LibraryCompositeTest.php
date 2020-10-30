<?php
/**
 * -- PHP Htaccess Parser --
 * HtaccessContainerTest.php created at 06-12-2014
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

use Tivie\HtaccessParser\TestCase\BaseTestCase;

class LibraryCompositeTest extends BaseTestCase
{
    /**
     * @var Parser
     */
    public $testClass;

    /**
     * @var array
     */
    public $testCase = [];

    /**
     * @var integer
     */
    public $numberOfTests = 2;

    public function setUp()
    {
        $this->testClass = new Parser();
        $max = $this->numberOfTests;
        $basePath = __DIR__ . "/resources/testcase";
        for ($i=1; $i <= $max; ++$i) {
            $fname = "$basePath$i";
            $this->testCase[] = [
                'file' => new \SplFileObject("$fname/htaccess"),
                'txt' => [
                    0 => "$fname/txt/normal.txt",
                    IGNORE_COMMENTS => "$fname/txt/no_comments.txt",
                    IGNORE_WHITELINES => "$fname/txt/no_whitelines.txt",
                    IGNORE_COMMENTS|IGNORE_WHITELINES => "$fname/txt/no_comments_no_whitelines.txt"
                ]
            ];
        }

        parent::setUp();
    }

    /**
     * @covers \Tivie\HtaccessParser\Parser::setFile
     * @covers \Tivie\HtaccessParser\Parser::ignoreComments
     * @covers \Tivie\HtaccessParser\Parser::ignoreWhitelines
     * @covers \Tivie\HtaccessParser\Parser::parse
     * @covers \Tivie\HtaccessParser\HtaccessContainer::txtSerialize
     * @covers \Tivie\HtaccessParser\HtaccessContainer::__toString
     */
    public function testCompareToExample()
    {
        for ($i=0; $i < $this->numberOfTests; ++$i) {
            $htaccessFile = $this->testCase[$i]['file'];
            $this->testClass->setFile($htaccessFile);

            /**
             * @var $type int
             * @var $file \SplFileObject
             */
            foreach ($this->testCase[$i]['txt'] as $type => $filename) {
                $parsed = $this->testClass->setMode($type)->parse();
                self::assertSame(file_get_contents($filename), $parsed->txtSerialize(), "Failed test (PARSE MODIFIED) with $filename");

                $parsed = $this->testClass->setMode(0)->parse();
                self::assertSame(
                    file_get_contents($filename),
                    $parsed->txtSerialize(
                        4,
                        (IGNORE_WHITELINES & $type),
                        (IGNORE_COMMENTS & $type)),
                    "Failed test with $filename"
                );
            }
        }
    }
}
