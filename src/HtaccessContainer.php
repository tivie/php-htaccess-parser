<?php
/**
 * -- PHP Htaccess Parser --
 * HtaccessContainer.php created at 03-12-2014
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
 * @copyright 2014 Estevão Soares dos Santos
 */
class HtaccessContainer extends BaseArrayObject implements HtaccessInterface
{

    /**
     * @var int
     */
    private $indentation = 4;

    /**
     * @todo NOT IMPLEMENTED YET
     * @var int
     */
    private $lineLimit = 80;

    /**
     * Create a new HtaccessContainer
     *
     * @param array $array [optional] An array to populate the ArrayObject
     */
    public function __construct(array $array = array(), $options = 0)
    {
        parent::__construct($array, BaseArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Set the indentation level
     *
     * @api
     * @param integer $spaces [required] The number of spaces to indent lines
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setIdentation($spaces)
    {
        if (!is_int($spaces)) {
            throw new InvalidArgumentException('integer', 0);
        }

        $this->indentation = $spaces;

        return $this;
    }

    /**
     * Search this object for a Token with a specific name and returns it.
     *
     * @param $name
     * @param int $type TOKEN_DIRECTIVE | TOKEN_BLOCK
     * @return null|TokenInterface
     */
    public function search($name, $type = null)
    {
        if (($index = $this->getIndex($name, $type)) !== null) {
            return $this->offsetGet($index);
        } else {
            return null;
        }
    }

    /**
     * Search this object for a Token with specific name and returns its index(key)
     *
     * @param string $name
     * @param int $type
     * @return int|null Returns the index or null if Token is not found
     */
    public function getIndex($name, $type = null)
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
     * @api
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        /** @var \Tivie\HtaccessParser\Token\TokenInterface[] $array */
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
     * @api
     * @return string
     */
    public function txtSerialize()
    {
        return $this->__toString();
    }

    /**
     * Get a string representation of this ArrayObject
     *
     * @api
     * @return string
     */
    public function __toString()
    {
        /** @var \Tivie\HtaccessParser\Token\TokenInterface[] $array */
        $array = $this->getArrayCopy();
        $otp = '';

        foreach ($array as $token) {
            $otp .= $this->txtSerializeToken($token, 0);
        }
        return $otp;
    }

    private function txtSerializeToken(TokenInterface $token, $indentation)
    {
        $ind = '';
        for ($i = 0; $i < $indentation; ++$i) {
            $ind .= ' ';
        }

        if ($token instanceof Block) {
            return $this->blockToString($token, $indentation);
        } else {
            if ($token instanceof WhiteLine) {
                return PHP_EOL;
            } else {
                return $ind . (string)$token . PHP_EOL;
            }
        }
    }

    private function blockToString(Block $block, $indentation)
    {
        $otp = '';
        $ind = '';

        // Calculate indentation
        for ($i = 0; $i < $indentation; ++$i) {
            $ind .= ' ';
        }

        //Opening Tag
        $otp .= $ind . '<' . $block->getName();
        // Arguments list
        foreach ($block->getArguments() as $arg) {
            $otp .= " $arg";
        }
        $otp .= '>' . PHP_EOL;

        if ($block->hasChildren()) {
            foreach ($block as $child) {
                $otp .= $this->txtSerializeToken($child, $indentation + $this->indentation);
            }
        }

        //Closing tag
        $otp .= $ind . "</" . $block->getName() . ">" . PHP_EOL;
        return $otp;
    }
}