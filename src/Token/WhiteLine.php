<?php
/**
 * -- PHP Htaccess Parser --
 * WhiteLine.php created at 02-12-2014
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

namespace Tivie\HtaccessParser\Token;

/**
 * Class WhiteLine
 * A Token corresponding to a white line (blank line) segment of .htaccess
 *
 * @package Tivie\HtaccessParser\Token
 * @copyright 2014 Estevão Soares dos Santos
 */
class WhiteLine extends BaseToken
{

    /**
     * Get the Token's name
     *
     * @return string
     */
    public function getName()
    {
        return 'WhiteLine';
    }

    /**
     * Get a string representation of the Token
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    /**
     * Get the Token's type
     *
     * @return int
     */
    public function getTokenType()
    {
        return TOKEN_WHITELINE;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return '';
    }

    /**
     * Get the array representation of the Token
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'type'      => $this->getTokenType(),
            'WhiteLine' => ''
        ];
    }

    /**
     * Get the Token's arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return array('');
    }

    /**
     * Set the Token's arguments
     *
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        return $this;
    }

    /**
     * A helper method that returns a string corresponding to the Token's value
     * (or its arguments concatenated)
     *
     * @return string
     */
    public function getValue()
    {
        return '';
    }
}
