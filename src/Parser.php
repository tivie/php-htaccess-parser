<?php
/**
 * -- PHP Htaccess Parser --
 * Parser.php created at 02-12-2014
 *
 * Copyright 2014-2024 Estevão Soares dos Santos
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

const IGNORE_WHITELINES = 2;
const IGNORE_COMMENTS = 4;
const AS_ARRAY = 8;

use ArrayAccess;
use SplFileObject;
use Tivie\HtaccessParser\Exception\Exception;
use Tivie\HtaccessParser\Exception\DomainException;
use Tivie\HtaccessParser\Exception\InvalidArgumentException;
use Tivie\HtaccessParser\Exception\SyntaxException;
use Tivie\HtaccessParser\Token\BaseToken;
use Tivie\HtaccessParser\Token\Block;
use Tivie\HtaccessParser\Token\Comment;
use Tivie\HtaccessParser\Token\Directive;
use Tivie\HtaccessParser\Token\WhiteLine;

/**
 * Class Parser
 * Htaccess Parser implemented in PHP
 *
 * @package Tivie\HtaccessParser
 * @copyright 2014-2024 Estêvão Soares dos Santos
 */
class Parser
{
    /**
     * @var SplFileObject
     */
    protected SplFileObject $file;

    /**
     * @var array|ArrayAccess|null
     */
    protected array|ArrayAccess|null $container = null;

    /**
     * @var int Defaults to IGNORE_WHITELINES
     */
    protected int $mode = 2;

    private int $_cpMode = 2;

    /**
     * @var bool
     */
    protected bool $rewind = true;

    /**
     * Create a new Htaccess Parser object
     *
     * @param SplFileObject|null $htaccessFile [optional] The .htaccess file to read.
     *                                                Must be set before running the parse method
     */
    public function __construct(SplFileObject|null $htaccessFile = null)
    {
        if ($htaccessFile) {
            $this->file = $htaccessFile;
        }
    }

    /**
     * Set the .htaccess file to parse
     *
     * @api
     * @param SplFileObject $file
     * @return $this
     */
    public function setFile(SplFileObject $file): static
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Set the receiving container of the parsed htaccess
     *
     * @api
     * @param ArrayAccess|array $container Can be an array, an ArrayObject or an object that implements ArrayAccess
     * @return $this
     */
    public function setContainer(ArrayAccess|array $container): static
    {
        $this->container = $container;
        return $this;
    }

    /**
     * If the Parser should use arrays instead of Token Objects (that implement TokenInterface).
     * Setting this to true returns a simple multidimensional array with scalars (no objects).
     * Default is false.
     *
     * @api
     * @param boolean $bool
     * @return $this
     */
    public function useArrays(bool $bool = true): static
    {
        return $this->bitwiseCtrl(!!$bool, AS_ARRAY);
    }

    /**
     * If the parser should ignore whitelines (blank lines)
     *
     * @api
     * @param boolean $bool
     * @return $this
     */
    public function ignoreWhitelines(bool $bool = true): static
    {
        return $this->bitwiseCtrl(!!$bool, IGNORE_WHITELINES);
    }

    /**
     * If the parser should ignore comment lines. Default is false
     *
     * @api
     * @param boolean $bool
     * @return $this
     */
    public function ignoreComments(bool $bool = true): static
    {
        return $this->bitwiseCtrl(!!$bool, IGNORE_COMMENTS);
    }

    /**
     * If the parser should rewind the .htaccess file pointer before reading. Default is true
     *
     * @api
     * @param bool $bool
     * @return $this
     */
    public function rewindFile(bool $bool = true): static
    {
        $this->rewind = !!$bool;
        return $this;
    }

    /**
     * Set the parser mode. (primarily for unit tests, use individual methods instead)
     *
     * @param int $mode
     * @return $this
     */
    public function setMode(int $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Parse a .htaccess file
     *
     * @param SplFileObject|null $file [optional] The .htaccess file. If null is passed and the file wasn't previously
     *                                             set, it will raise an exception
     * @param int|null $optFlags [optional] Option flags
     *                                              - IGNORE_WHITELINES  [2] Ignores whitelines (default)
     *                                              - IGNORE_COMMENTS    [4] Ignores comments
     *                                              - As_ARRAY           [8] Returns an array
     * @param bool|null $rewind [optional] If the file pointer should be moved to the start (default is true)
     * @return array|ArrayAccess|HtaccessContainer
     * @throws Exception
     * @throws SyntaxException
     * @api
     */
    public function parse(SplFileObject $file = null, int|null $optFlags = null, bool|null $rewind = null): HtaccessContainer|ArrayAccess|array
    {
        //Prepare passed options
        $file = ($file !== null) ? $file : $this->file;
        $optFlags = ($optFlags !== null) ? $optFlags : $this->mode;
        $rewind = ($rewind !== null) ? !!$rewind : $this->rewind;

        if (!$file instanceof SplFileObject) {
            throw new Exception(".htaccess file is not set. You must set it (with Parser::setFile) before calling parse");
        }

        if (!$file->isReadable()) {
            $path = $file->getRealPath();
            throw new Exception(".htaccess file '$path'' is not readable");
        }

        // Rewind file pointer
        if ($rewind) {
            $file->rewind();
        }

        // Current Parse Mode
        $this->_cpMode = $optFlags;

        // Modes
        $asArray = (AS_ARRAY & $optFlags);

        // Container
        if ($asArray) {
            $htaccess = array();
        } else {
            $htaccess = ($this->container !== null) ? $this->container : new HtaccessContainer();
        }

        //Dump file line by line into $htaccess
        while ($file->valid()) {

            //Get line
            $line = $file->getCurrentLine();

            //Parse Line
            $parsedLine = $this->parseLine($line, $file);

            if (!is_null($parsedLine)) {
                $htaccess[] = $parsedLine;
            }
        }

        return $htaccess;
    }

    /**
     * @param string $line
     * @param SplFileObject $file
     * @return BaseToken|null
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws SyntaxException
     */
    private function parseLine(string $line, SplFileObject $file): BaseToken|null
    {
        $ignoreWhiteLines = (IGNORE_WHITELINES & $this->_cpMode);
        $ignoreComments = (IGNORE_COMMENTS & $this->_cpMode);

        //Trim line
        $line = trim($line);

        $lineBreaks = array();

        if ($this->isMultiLine($line)) {
            $line = $this->parseMultiLine($line, $file, $lineBreaks);
        }

        if ($this->isWhiteLine($line)) {
            return (!$ignoreWhiteLines) ? $this->parseWhiteLine() : null;
        }

        if ($this->isComment($line)) {
            return (!$ignoreComments) ? $this->parseCommentLine($line, $lineBreaks) : null;
        }

        if ($this->isDirective($line)) {
            return $this->parseDirectiveLine($line, $file, $lineBreaks);
        }

        if ($this->isBlock($line)) {
            return $this->parseBlockLine($line, $file, $lineBreaks);
        }

        //Syntax not recognized so we throw SyntaxException
        throw new SyntaxException($file->key(), $line, "Unexpected line");
    }

    /**
     * Check if line is a white line
     *
     * @param string $line
     * @return bool
     */
    protected function isWhiteLine(string $line): bool
    {
        $line = trim($line);
        return ($line == null);
    }

    /**
     * Check if line is spanned across multiple lines
     *
     * @param string $line
     * @return bool
     */
    protected function isMultiLine(string $line): bool
    {
        $line = trim($line);
        return (preg_match('/\\\\$/', $line) > 0);
    }

    /**
     * Check if line is a comment
     *
     * @param string $line
     * @return bool
     */
    protected function isComment(string $line): bool
    {
        $line = trim($line);
        return (preg_match('/^#/', $line) > 0);
    }

    /**
     * Check if line is a directive
     *
     * @param string $line
     * @return bool
     */
    protected function isDirective(string $line): bool
    {
        $line = trim($line);
        $pattern = '/^[^#<]/';
        return (preg_match($pattern, $line) > 0);
    }

    /**
     * Check if line is a block
     *
     * @param string $line
     * @return bool
     */
    protected function isBlock(string $line): bool
    {
        $line = trim($line);
        return (preg_match('/^<[^\/].*>$/', $line) > 0);
    }

    /**
     * Check if line is a Block end
     *
     * @param string $line
     * @param string|null $blockName [optional] The block's name
     * @return bool
     */
    protected function isBlockEnd(string $line, string $blockName = null): bool
    {
        $line = trim($line);
        $pattern = '/^\<\/';
        $pattern .= ($blockName) ? $blockName : '[^\s\>]+';
        $pattern .= '\>$/i';
        return (preg_match($pattern, $line) > 0);
    }

    /**
     * Parse a Multi Line
     *
     * @param string $line
     * @param SplFileObject $file
     * @param array $lineBreaks
     * @return string
     */
    protected function parseMultiLine(string $line, SplFileObject $file, array &$lineBreaks): string
    {
        while ($this->isMultiLine($line) && $file->valid()) {
            $lineBreaks[] = strlen($line);

            $line2 = $file->getCurrentLine();

            // trim the ending slash
            $line = rtrim($line, '\\');
            // concatenate with next line
            $line = trim($line . $line2);

        }
        return $line;
    }

    /**
     * Parse a White Line
     *
     * @return WhiteLine
     */
    protected function parseWhiteLine(): WhiteLine
    {
        return new WhiteLine();
    }

    /**
     * Parse a Comment Line
     *
     * @param string $line
     * @param array $lineBreaks
     * @return Comment
     * @throws DomainException
     */
    protected function parseCommentLine(string $line, array $lineBreaks): Comment
    {
        $comment = new Comment();
        $comment->setText($line)
                ->setLineBreaks($lineBreaks);

        return $comment;
    }

    /**
     * Parse a Directive Line
     *
     * @param string $line
     * @param SplFileObject $file
     * @param array $lineBreaks
     * @return Directive
     * @throws DomainException
     * @throws InvalidArgumentException
     * @throws SyntaxException
     */
    protected function parseDirectiveLine(string $line, SplFileObject $file, array $lineBreaks): Directive
    {
        $directive = new Directive();

        $args = $this->directiveRegex($line);
        $name = array_shift($args);

        if ($name === null) {
            $lineNum = $file->key();
            throw new SyntaxException($lineNum, $line, "Could not parse the name of the directive");
        }

        $directive->setName($name)
                  ->setArguments($args)
                  ->setLineBreaks($lineBreaks);

        return $directive;
    }

    /**
     * Parse a Block Line
     *
     * @param string $line
     * @param SplFileObject $file
     * @param array $lineBreaks
     * @return Block
     * @throws DomainException
     * @throws SyntaxException
     */
    protected function parseBlockLine(string $line, SplFileObject $file, array $lineBreaks): Block
    {
        $block = new Block();

        $args = $this->blockRegex($line);
        $name = array_shift($args);

        if ($name === null) {
            $lineNum = $file->key();
            throw new SyntaxException($lineNum, $line, "Could not parse the name of the block");
        }

        $block->setName($name)
              ->setArguments($args)
              ->setLineBreaks($lineBreaks);

        // Now we parse the children
        $newLine = $file->getCurrentLine();

        while (!$this->isBlockEnd($newLine, $name)) {
            $parsedLine = $this->parseLine($newLine, $file);
            if (!is_null($parsedLine)) {
                $block->addChild($parsedLine);
            }
            $newLine = $file->getCurrentLine();
        }

        return $block;
    }

    private function bitwiseCtrl($bool, $flag): static
    {
        if ($bool) {
            $this->mode = $this->mode | $flag;
        } else {
            $this->mode = $this->mode & ~$flag;
        }

        return $this;
    }

    private function directiveRegex($str): array
    {
        $pattern = '/"(?:\\.|[^\"])*"|\S+/';
        $matches = array();
        $trimmedMatches = array();
        if (preg_match_all($pattern, $str, $matches) && isset($matches[0])) {
            foreach ($matches[0] as $match) {
                $match = trim($match);
                if ($match != '') {
                    $trimmedMatches[] = $match;
                }
            }
            return $trimmedMatches;
        }
        return array();
    }

    private function blockRegex($line): array
    {
        $pattern = '/(?:[\s|<]")([^<>"]+)(?:"[\s|>])|([^<>\s]+)/';
        $final = array();

        if (preg_match_all($pattern, $line, $matches) > 0) {
            array_walk($matches[0], function ($val, $key) use (&$final) {
                if ($val != null) {
                    $val = trim($val);
                    $val = trim($val, '<>');
                    $final[$key] = $val;
                }
            });
            ksort($final);
        }
        return $final;
    }
}
