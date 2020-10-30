<?php
/**
 * -- PHP Htaccess Parser --
 * Directive.php created at 02-12-2014
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

use Tivie\HtaccessParser\Exception\DomainException;
use Tivie\HtaccessParser\Exception\InvalidArgumentException;

/**
 * Class Directive
 * A Token corresponding to a directive segment of htaccess
 *
 * @package Tivie\HtaccessParser\Token
 * @copyright 2014 Estevão Soares dos Santos
 */
class Directive extends BaseToken
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $arguments = array();

    /**
     * @param string $name [optional]
     * @param array $arguments [optional]
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function __construct($name = null, array $arguments = array())
    {
        if ($name !== null && !is_scalar($name)) {
            throw new InvalidArgumentException('scalar', 0);
        }
        $this->name = $name;
        foreach ($arguments as $arg) {
            if (!is_scalar($arg)) {
                throw new DomainException("Arguments must be an array of scalars");
            }
            $this->arguments[] = $arg;
        }
    }

    /**
     * Get the Token's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the Token's name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the Directive's arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set the Directive's arguments
     *
     * @param array $array [required] An array of string arguments
     * @return $this
     * @throws DomainException
     */
    public function setArguments(array $array = array())
    {
        foreach ($array as $arg) {
            if (!is_scalar($arg)) {
                $type = gettype($arg);
                throw new DomainException("Arguments array should be an array of scalar, but found $type");
            }
            $this->addArgument($arg);
        }

        return $this;
    }

    /**
     * Add an argument to the Directive arguments array
     *
     * @param mixed $arg [required] A scalar
     * @param bool $unique [optional] If this argument is unique
     * @return $this
     * @throws InvalidArgumentException
     */
    public function addArgument($arg, $unique = false)
    {
        if (!is_scalar($arg)) {
            throw new InvalidArgumentException('scalar', 0);
        }

        // escape arguments with spaces
        if (strpos($arg, ' ') !== false && (strpos($arg, '"') === false) ) {
            $arg = "\"$arg\"";
        }

        if (in_array($arg, $this->arguments) && $unique) {
            return $this;
        }

        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * Remove an argument from the Directive's arguments array
     *
     * @param string $arg
     * @return $this
     */
    public function removeArgument($arg)
    {
        if (($name = array_search($arg, $this->arguments)) !== false) {
            unset($this->arguments[$name]);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $str = $this->getName();
        foreach ($this->arguments as $arg) {
            $str .= " $arg";
        }
        return $str;
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
        return $this->arguments;
    }

    /**
     * Get the Token's type
     *
     * @return int
     */
    public function getTokenType()
    {
        return TOKEN_DIRECTIVE;
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
            'name'      => $this->getName(),
            'arguments' => $this->getArguments()
        ];
    }

    /**
     * A helper method that returns a string corresponding to the Token's value
     * (or its arguments concatenated)
     *
     * @return string
     */
    public function getValue()
    {
        return (implode(' ', $this->getArguments()));
    }
}
