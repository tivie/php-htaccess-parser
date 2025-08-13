<?php
/**
 * -- PHP Htaccess Parser --
 * Block.php created at 02-12-2014
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

namespace Tivie\HtaccessParser\Token;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use Tivie\HtaccessParser\Exception\DomainException;
use Tivie\HtaccessParser\Exception\InvalidArgumentException;


/**
 * Class Block
 * A Token corresponding to a block (module) segment of .htaccess
 *
 * @package Tivie\HtaccessParser\Token
 * @copyright 2014-2024 Estêvão Soares dos Santos
 */
class Block extends BaseToken implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var string
     */
    private string $blockName;

    /**
     * @var array
     */
    private array $arguments = array();

    /**
     * @var TokenInterface[]
     */
    private array $children = array();

    /**
     * @var int
     */
    private int $indentation = 4;

    /**
     * Create a new Block token.
     *
     * This token corresponds to the following structure in .htaccess:
     *
     * <%blockName% %argument%>
     *    ...
     * </%blockName%>
     *
     * @param string $blockName [optional] The name of the block
     * @param string|null $argument [optional] The argument of the block
     */
    public function __construct(string $blockName = 'blockName', ?string $argument = null)
    {
        $this->setName($blockName);

        if (!is_null($argument)) {
            $this->arguments[] = $argument;
        }
    }

    /**
     * Set the block's name
     *
     * @param string $blockName [required] The name of the Block
     * @return $this
     */
    public function setName(string $blockName): static
    {
        $this->blockName = $blockName;
        return $this;
    }

    /**
     * Get the Token's name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->blockName;
    }

    /**
     * Set the block's arguments
     *
     * @param array $arguments [required] An array of arguments
     * @return $this
     * @throws DomainException
     */
    public function setArguments(array $arguments): static
    {
        foreach ($arguments as $arg) {
            if (!is_scalar($arg)) {
                $type = gettype($arg);
                throw new DomainException("Arguments array should be an array of scalar, but found $type");
            }
        }

        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Get the block's arguments
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * A helper method that returns a string corresponding to the Token's value
     * (or its arguments concatenated)
     *
     * @return string
     */
    public function getValue(): string
    {
        return (implode(' ', $this->getArguments()));
    }

    /**
     * Add an argument to the Block arguments array
     *
     * @param mixed $arg [required] A scalar
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addArgument(mixed $arg): static
    {
        if (!is_scalar($arg)) {
            throw new InvalidArgumentException('scalar', 0);
        }
        if (!in_array($arg, $this->arguments)) {
            $this->arguments[] = $arg;
        }

        return $this;
    }

    /**
     * Remove an argument from the Block arguments array
     *
     * @param string $arg
     * @return $this
     */
    public function removeArgument(string $arg): static
    {
        if (($key = array_search($arg, $this->arguments)) !== false) {
            unset($this->arguments[$key]);
        }

        return $this;
    }

    /**
     * Add a child to this block
     *
     * @param TokenInterface $child
     * @return $this
     */
    public function addChild(TokenInterface $child): static
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove a child from this block
     *
     * @param TokenInterface $child [required] The child to remove
     * @param bool $strict [optional] Default true. If the comparison should be strict. A non strict comparsion
     *                                          will remove a child if it has the same properties with the same values
     * @return $this
     */
    public function removeChild(TokenInterface $child, bool $strict = true): static
    {
        $index = array_search($child, $this->children, !!$strict);

        if ($index !== false) {
            unset($this->children[$index]);
        }

        return $this;
    }

    /**
     * Check if Block has children
     *
     * @return bool
     */
    public function hasChildren(): bool
    {
        return ($this->count() > 0);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable|ArrayIterator An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator(): Traversable|ArrayIterator
    {
        return new ArrayIterator($this->children);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return argument will be cast to boolean if non-boolean was returned.
     */
    public function offsetExists(mixed $offset): bool
    {
        if (!is_scalar($offset)) {
            throw new \InvalidArgumentException("Offset must be a scalar");
        }
        return isset($this->children[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return TokenInterface Can return all argument types.
     */
    public function offsetGet(mixed $offset): TokenInterface
    {
        if (!$this->offsetExists($offset)) {
            throw new \DomainException("$offset is not set");
        }
        return $this->children[$offset];
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the argument to.
     * @param mixed $value The value to set.
     * @throws InvalidArgumentException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_null($offset) && !is_scalar($offset)) {
            throw new InvalidArgumentException('scalar', 0);
        }

        if (!$value instanceof TokenInterface) {
            throw new InvalidArgumentException('TokenInterface', 1);
        }

        if (!in_array($value, $this->children)) {
            $this->children[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if (!is_scalar($offset)) {
            throw new \InvalidArgumentException("Offset must be a scalar");
        }
        unset($this->children[$offset]);
    }

    /**
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer. The return argument is cast to an integer.
     */
    public function count(): int
    {
        return count($this->children);
    }

    /**
     * Return an array ready for serialization. Ignores comments and whitelines
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is an argument of any type other than a resource.
     */
    function jsonSerialize(): array
    {
        $array = [
            'arguments' => $this->arguments,
            'children' => array()
        ];

        foreach ($this->children as $child) {
            if (!$child instanceof WhiteLine & !$child instanceof Comment) {
                $array['children'][$child->getName()] = $child->jsonSerialize();
            }
        }

        return $array;
    }

    /**
     * Sets the indentation level
     *
     * @param integer $spaces [required] The number of spaces to indent lines when outputting to string
     * @return $this
     */
    public function setIndentation(int $spaces): static
    {
        $this->indentation = $spaces;
        return $this;
    }

    /**
     * Get a string representation of this Token
     *
     * @return string
     */
    public function __toString(): string
    {
        $ind = str_repeat(' ', $this->indentation);

        //Opening tag
        $str = "<" . $this->blockName;
        // Arguments list
        foreach ($this->arguments as $arg) {
            $str .= " $arg";
        }
        $str .= '>' . PHP_EOL;

        //Children
        foreach ($this->children as $child) {
            $str .= "$ind$child" . PHP_EOL;
        }

        //Closing tag
        $str .= "</{$this->blockName}>";

        return $str;
    }

    /**
     * Get the Token's type
     *
     * @return int
     */
    public function getTokenType(): int
    {
        return TOKEN_BLOCK;
    }

    /**
     * Get the array representation of the Token
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            'type'      => $this->getTokenType(),
            'name'      => $this->getName(),
            'arguments' => $this->getArguments(),
            'children'  => array()
        ];

        foreach ($this->children as $child) {
            $array['children'][] = $child->toArray();
        }
        return $array;
    }
}
