<?php
/**
 * -- PHP Htaccess Parser --
 * Comment.php created at 02-12-2014
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

use Tivie\HtaccessParser\Exception\InvalidArgumentException;


/**
 * Class Comment
 * A Token corresponding to a comment segment of .htaccess
 *
 * @package Tivie\HtaccessParser\Token
 * @copyright 2014 Estevão Soares dos Santos
 */
class Comment extends BaseToken
{
    /**
     * @var string
     */
    private $text = '';

    /**
     * Create a new Comment token.
     *
     * This token corresponds to the following structure in .htaccess:
     * # ...
     *
     * @param string $text The comment text
     */
    public function __construct($text = '')
    {
        $this->text = (string)$text;
    }

    /**
     * Get the Token's name.
     * Always returns '#comment', since comments don't have a specific name
     *
     * @return string
     */
    public function getName()
    {
        return '#comment';
    }

    /**
     * Get the comment's text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set the Comment Text
     *
     * @param string $text The comment new text. A # will be prepended automatically if it isn't found at the beginning
     *                     of the string.
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setText($text)
    {
        if (!is_string($text)) {
            throw new InvalidArgumentException('string', 0);
        }

        $text = trim($text);

        if (strpos($text, '#') !== 0) {
            $text = '# ' . $text;
        }

        $this->text = $text;

        return $this;
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
        return $this->__toString();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->text;
    }

    /**
     * Get the Token's type
     *
     * @return int
     */
    public function getTokenType()
    {
        return TOKEN_COMMENT;
    }

    /**
     * Get the array representation of the Token
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'type'    => $this->getTokenType(),
            'comment' => $this->text
        ];
    }

    /**
     * Get the Token's arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return array($this->getText());
    }

    /**
     * Set the Token's arguments
     *
     * @param array $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->setText($arguments[0]);

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
        return $this->getText();
    }
}
