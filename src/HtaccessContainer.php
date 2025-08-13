<?php
/**
 * -- PHP Htaccess Parser --
 * HtaccessContainer.php created at 03-12-2014
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

use ArrayAccess;
use ArrayObject as BaseArrayObject;
use Tivie\HtaccessParser\Exception\InvalidArgumentException;
use Tivie\HtaccessParser\Token\Block;
use Tivie\HtaccessParser\Token\Comment;
use Tivie\HtaccessParser\Token\TokenInterface;
use Tivie\HtaccessParser\Token\WhiteLine;

/**
 * Class HtaccessContainer
 * A generic ArrayObject that can be used to store a parsed htaccess. Implements JsonSerializable
 *
 * @package Tivie\HtaccessParser
 * @copyright 2014-2024 Estêvão Soares dos Santos
 */
class HtaccessContainer extends BaseArrayObject implements HtaccessInterface
{

    /**
     * @var int
     */
    private int $indentation = 4;

    /**
     * @var bool
     */
    private bool $ignoreWhiteLines = false;

    /**
     * @var bool
     */
    private bool $ignoreComments = false;

    /**
     * Create a new HtaccessContainer
     *
     * @param array $array [optional] An array to populate the ArrayObject
     */
    public function __construct(array $array = array())
    {
        parent::__construct($array);
    }

    /**
     * Set the indentation level
     *
     * @api
     * @param int $spaces [required] The number of spaces to indent lines
     * @return $this
     */
    public function setIdentation(int $spaces): static
    {
        $this->indentation = $spaces;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIgnoreComments(): bool
    {
        return $this->ignoreComments;
    }

    /**
     * @param boolean $ignoreComments
     * @return $this
     */
    public function setIgnoreComments(bool $ignoreComments): static
    {
        $this->ignoreComments = $ignoreComments;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIgnoreWhiteLines(): bool
    {
        return $this->ignoreWhiteLines;
    }

    /**
     * @param boolean $ignoreWhiteLines
     * @return $this
     */
    public function setIgnoreWhiteLines(bool $ignoreWhiteLines): static
    {
        $this->ignoreWhiteLines = $ignoreWhiteLines;
        return $this;
    }

    /**
     * Search this object for a Token with a specific name and return the first match
     *
     * @param string $name [required] Name of the token
     * @param int|null $type [optional] TOKEN_DIRECTIVE | TOKEN_BLOCK
     * @param bool $deepSearch [optional] If the search should be multidimensional. Default is true
     * @return null|TokenInterface Returns the Token or null if none is found
     */
    public function search(string $name, ?int $type = null, bool $deepSearch = true): ?TokenInterface
    {
        /** @var TokenInterface[] $array */
        $array = $this->getArrayCopy();

        foreach ($array as $token) {
            if (fnmatch($name, $token->getName())) {
                if ($type === null) {
                    return $token;
                }
                if ($token->getTokenType() === $type) {
                    return $token;
                }
            }
            if ($token instanceof Block && $token->hasChildren() && $deepSearch) {
                if ($res = $this->deepSearch($token, $name, $type)) {
                    return $res;
                }
            }
        }
        return null;
    }

    /**
     * @param Block $parent
     * @param string $name
     * @param int|null $type
     * @return TokenInterface|null
     */
    private function deepSearch(Block $parent, string $name, int|null $type): ?TokenInterface
    {
        /** @var TokenInterface[] $parent */
        foreach ($parent as $token) {
            if (fnmatch($name, $token->getName())) {
                if ($type === null) {
                    return $token;
                }
                if ($token->getTokenType() === $type) {
                    return $token;
                }
            }
            if ($token instanceof Block && $token->hasChildren()) {
                if ($res = $this->deepSearch($token, $name, $type)) {
                    return $res;
                }
            }
        }
        return null;
    }

    /**
     * Search this object for a Token with a specific name and return the index(key) of the first match
     *
     * @param string $name [required] Name of the token
     * @param int|null $type [optional] TOKEN_DIRECTIVE | TOKEN_BLOCK
     * @return int|null Returns the index or null if Token is not found
     */
    public function getIndex(string $name, ?int $type = null): ?int
    {
        /** @var TokenInterface[] $array */
        $array = $this->getArrayCopy();

        foreach ($array as $index => $token) {
            if ($token->getName() === $name) {
                if ($type === null) {
                    return $index;
                }
                if ($token->getTokenType() === $type) {
                    return $index;
                }
            }
        }
        return null;
    }

    /**
     * Get a representation ready to be encoded with json_encoded.
     * Note: Whitelines and Comments are ignored and will not be included in the serialization
     *
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     *@link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @api
     */
    public function jsonSerialize(): array
    {
        /** @var TokenInterface[] $array */
        $array = $this->getArrayCopy();
        $otp = array();
        foreach ($array as $arr) {
            if (!$arr instanceof WhiteLine & !$arr instanceof Comment) {
                $otp[$arr->getName()] = $arr;
            }
        }

        return $otp;
    }

    /**
     * Returns a representation of the htaccess, ready for inclusion in a file
     *
     * @param int|null $indentation [optional] Defaults to null
     * @param bool|null $ignoreWhiteLines [optional] Defaults to null
     * @param bool $ignoreComments [optional] Defaults to null
     * @return string
     *@api
     */
    public function txtSerialize(?int $indentation = null, ?bool $ignoreWhiteLines = null, bool $ignoreComments = false): string
    {
        /** @var TokenInterface[] $array */
        $array = $this->getArrayCopy();
        $otp = '';

        $this->indentation = (is_null($indentation)) ? $this->indentation : $indentation;
        $ignoreWhiteLines = (is_null($ignoreWhiteLines)) ? $this->ignoreWhiteLines : $ignoreWhiteLines;
        $ignoreComments = (is_null($ignoreComments)) ? $this->ignoreCommentss : $ignoreComments;


        foreach ($array as $_num => $token) {
            $otp .= $this->txtSerializeToken($token, 0, !!$ignoreWhiteLines, !!$ignoreComments);
        }

        // remove whitelines at the end
        $otp = rtrim($otp);
        // and add an empty newline
        $otp .= PHP_EOL;

        return $otp;
    }

    /**
     * Get a string representation of this ArrayObject
     *
     * @api
     * @return string
     */
    public function __toString(): string
    {
        return $this->txtSerialize();
    }

    private function txtSerializeToken(TokenInterface $token, int $indentation, bool $ignoreWhiteLines, bool $ignoreComments): string
    {
        $ind = str_repeat(' ', $indentation);

        if ($token instanceof Block) {
            return $this->blockToString($token, $indentation, $ignoreWhiteLines, $ignoreComments);

        } else if ($token instanceof WhiteLine) {
            return ($ignoreWhiteLines) ? '' : PHP_EOL;

        } else if ($token instanceof Comment) {
            return ($ignoreComments) ? '' : $ind . $token . PHP_EOL;

        } else {
            return $ind . $token . PHP_EOL;
        }
    }

    private function blockToString(Block $block, int $indentation, bool $ignoreWhiteLines, bool $ignoreComments): string
    {
        $otp = '';

        // Calculate indentation
        $ind = str_repeat(' ', $indentation);

        //Opening Tag
        $otp .= $ind . '<' . $block->getName();
        // Arguments list
        foreach ($block->getArguments() as $arg) {
            $otp .= " $arg";
        }
        $otp .= '>' . PHP_EOL;

        if ($block->hasChildren()) {
            foreach ($block as $child) {
                $otp .= $this->txtSerializeToken($child, $indentation + $this->indentation, $ignoreWhiteLines, $ignoreComments);
            }
        }

        //Closing tag
        $otp .= $ind . "</{$block->getName()}>" . PHP_EOL;
        return $otp;
    }

    /**
     * Returns the sequence of elements as specified by the offset and length parameters.
     *
     * @param int $offset [required] If offset is non-negative, the sequence will start at that offset.
     *                                      If offset is negative, the sequence will start that far from the end of the
     *                                      array.
     * @param int|null $length [optional] If length is given and is positive, then the sequence will have up to that
     *                                      many elements in it. If the array is shorter than the length, then only the
     *                                      available array elements will be present. If length is given and is negative
     *                                      then the sequence will stop that many elements from the end of the array.
     *                                      If it is omitted, then the sequence will have everything from offset up until
     *                                      the end of the array.
     * @param bool $preserveKeys [optional] Note that arraySlice() will reorder and reset the numeric array indices by
     *                                      default. You can change this behaviour by setting preserveKeys to TRUE.
     * @param bool $asArray [optional] By default, slice() returns a new instance of HtaccessContainer object.
     *                                      If you prefer a basic array instead, set asArray to true
     * @return array|HtaccessContainer      Returns the slice.
     */
    public function slice(int $offset, int|null $length = null, bool $preserveKeys = false, bool $asArray = false): HtaccessContainer|array
    {
        $array = $this->getArrayCopy();
        $newArray = array_slice($array, $offset, $length, $preserveKeys);
        return ($asArray) ? $newArray : new self($newArray);
    }

    /**
     * @param int $offset [required] If offset is positive then the token will be inserted at that offset from the
     *                               beginning. If offset is negative then it starts that far from the end of the input
     *                              array.
     * @param TokenInterface $token [required] The token to insert
     *
     * @return $this
     */
    public function insertAt(int $offset, TokenInterface $token): static
    {
        $this->splice($offset, 0, array($token));

        return $this;
    }

    /**
     * Removes the elements designated by offset and length, and replaces them with the elements of the replacement
     * array, if supplied.
     *
     * @param int $offset [required] If offset is positive then the start of removed portion is at that offset
     *                               from the beginning. If offset is negative then it starts that far from the
     *                               end of the input array.
     * @param int|null $length [optional] If length is omitted, removes everything from offset to the end. If length
     *                               is specified and is positive, then that many elements will be removed.
     *                               If length is specified and is negative then the end of the removed portion
     *                               will be that many elements from the end.
     *                               Tip: to remove everything from offset to the end of the array when
     *                               replacement is also specified, use count($input) for length.
     * @param ArrayAccess|array $replacement [optional] If replacement array is specified, then the removed elements
     *                               are replaced with elements from this array.
     *
     * @return array                 Returns the array consisting of the extracted elements.
     */
    public function splice(int $offset, ?int $length = null, ArrayAccess|array $replacement = array()): array
    {
        $array = $this->getArrayCopy();
        $spliced = array_splice($array, $offset, $length, $replacement);
        $this->exchangeArray($array);
        return $spliced;
    }

    /**
     * @inheritDocs
     * @override ArrayObject::offsetSet
     * @param mixed $offset
     * @param TokenInterface $value
     * @throws InvalidArgumentException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_null($offset) && !is_int($offset)) {
            throw new InvalidArgumentException('scalar', 0);
        }

        if (!$value instanceof TokenInterface) {
            throw new InvalidArgumentException('TokenInterface', 1);
        }
        parent::offsetSet($offset, $value);
    }
}
